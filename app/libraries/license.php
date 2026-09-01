<?php
/** no direct access **/
defined('MECEXEC') or die();

/**
 * Webnus MEC licence core.
 *
 * Standalone by design. This class must NOT extend MEC_base and must NOT call
 * into any other MEC library:
 *
 *  - It is hard-required from mec.php before the MEC object graph is built, so
 *    that it cannot be swapped out through the theme-override path in
 *    MEC::import(). See mec-init.php:169-218.
 *  - Lite does not ship this file. Every caller must therefore tolerate the
 *    class being absent — see MEC_base::isProBuild().
 *
 * @author Webnus <info@webnus.net>
 */
final class MEC_license
{
    /** Full Pro. Notice only. */
    const PHASE_FULL = 0;

    /** New Pro configuration blocked. Existing content untouched. */
    const PHASE_NO_NEW = 1;

    /** Pro presentation layer off. Booking still live. */
    const PHASE_NO_EXTRAS = 2;

    /** Booking unit off (books + gateways + coupons + op, together). */
    const PHASE_NO_BOOKING = 3;

    /** Exact Lite parity. */
    const PHASE_LITE = 4;

    const OPT_TOKEN = 'mec_lic_token';
    const OPT_VERDICT = 'mec_lic_verdict';
    const OPT_SEEN = 'mec_lic_seen';
    const OPT_SERIAL = 'mec_lic_serial';
    const OPT_CLAIM = 'mec_lic_claim';

    /** How long a computed verdict is trusted before re-verifying. */
    const VERDICT_TTL = 3600;

    /**
     * HTTP statuses that mean "the store considered this and said no".
     *
     * Deliberately a whitelist rather than the whole 4xx range: REFUSED stops
     * the client asking forever, so anything ambiguous must stay retryable.
     *
     *   400 malformed claim — deterministic, retrying cannot help
     *   401 / 403 not entitled
     *   402 payment required
     *   409 conflict, e.g. no activation slot free
     */
    const REFUSAL_CODES = array(400, 401, 402, 403, 409);

    /**
     * @var MEC_license|null
     */
    private static $instance = null;

    /**
     * Per-request memo. Verification is ~22ms on the pure-PHP fallback, so it
     * must not run more than once per request.
     * @var array|null
     */
    private $verdict = null;

    /**
     * @var array|null
     */
    private $build_cache = null;

    /**
     * @var bool
     */
    private $build_loaded = false;

    private function __construct()
    {
    }

    /**
     * @return MEC_license
     */
    public static function instance()
    {
        if (self::$instance === null) self::$instance = new self();
        return self::$instance;
    }

    /* ------------------------------------------------------------------ */
    /* Public API                                                          */
    /* ------------------------------------------------------------------ */

    /**
     * Is this site licensed?
     *
     * Note this is NOT the same question as "may Pro run" — an unlicensed site
     * still runs Pro during phases 0-3. Gate features on phase(), not on this.
     *
     * @return bool
     */
    public function licensed()
    {
        $v = $this->verdict();
        return !empty($v['licensed']);
    }

    /**
     * Current enforcement phase, 0-4.
     * @return int
     */
    public function phase()
    {
        $v = $this->verdict();
        return isset($v['phase']) ? (int) $v['phase'] : self::PHASE_LITE;
    }

    /**
     * The 32 raw bytes that entangled checks derive from. Null when unlicensed,
     * which is the point: there is no boolean to flip, the material is absent.
     *
     * @return string|null
     */
    public function seed()
    {
        $v = $this->verdict();
        if (empty($v['licensed']) or empty($v['seed'])) return null;

        $seed = self::b64url_decode($v['seed']);
        return ($seed !== false and strlen($seed) === 32) ? $seed : null;
    }

    /**
     * Derive a named value from the seed. Returns null when unlicensed so that
     * callers naturally produce non-working values rather than working ones.
     *
     * @param string $context
     * @return string|null
     */
    public function derive($context)
    {
        $seed = $this->seed();
        if ($seed === null) return null;

        return hash_hmac('sha256', (string) $context, $seed);
    }

    /**
     * Entanglement token for the booking form.
     *
     * This is the one place the entanglement design bites. When booking is
     * GATED OFF (phase >= 3, unlicensed) the front-end booking form does not
     * render at all, so it cannot send this token. book() verifies it, and
     * refuses to create a booking without a value that only the licence seed
     * can produce. The seed lives inside the signed token a cracker does not
     * have, so commenting out the isBookingUnitEnabled() gate alone is not
     * enough — the token is still absent and booking still fails.
     *
     * Three states:
     *   licensed, any phase        → '' (exempt; Pro booking runs normally)
     *   unlicensed, phase < 3      → '' (booking still allowed, no entanglement)
     *   unlicensed, phase >= 3     → derived token (form is gone; this is the
     *                                value book() would demand, and cannot get)
     *
     * @return string
     */
    public function booking_token()
    {
        // Licensed sites are exempt — they get Pro booking, no entanglement.
        if ($this->licensed()) return '';

        // Booking still allowed at phases 0-2. No entanglement needed.
        if ($this->phase() < self::PHASE_NO_BOOKING) return '';

        $derived = $this->derive('booking_gate_v1');
        // Unlicensed at phase >= 3 with no seed (the normal nulled case) → ''
        // is returned, which verify_booking_token refuses to accept.
        return $derived === null ? '' : 'm' . substr($derived, 0, 16);
    }

    /**
     * Verify a booking token sent from the front-end form.
     *
     * Returns true when booking is allowed (licensed, or phase < 3) and false
     * otherwise. The $token argument only matters at phase >= 3, where it must
     * match the derived value — which a nulled site cannot produce.
     *
     * @param string $token
     * @return bool
     */
    public function verify_booking_token($token)
    {
        // Licensed site → exemption.
        if ($this->licensed()) return true;

        // Booking still allowed at phases 0-2.
        if ($this->phase() < self::PHASE_NO_BOOKING) return true;

        // Phase >= 3: a valid derived token is required.
        $expected = $this->booking_token();
        if ($expected === '') return false;

        return is_string($token) && strlen($token) > 0 && hash_equals($expected, $token);
    }

    /**
     * sha256 of the normalised host. Binds a token to one domain.
     * @return string
     */
    public function site_id()
    {
        return hash('sha256', self::normalised_host());
    }

    /**
     * The host a token for this site must be issued for, in the exact form the
     * binding is computed from.
     *
     * Shown to the customer so they can paste it into a support ticket, rather
     * than describing their site from memory. The two are very often different:
     * home_url() may carry a subdomain, a path or a port the customer does not
     * think of as part of "their domain", and a token minted for what they said
     * instead of what this returns comes back WRONG_SITE — a full support round
     * trip for a site that is, by then, already ramping.
     *
     * @return string
     */
    public function host()
    {
        return self::normalised_host();
    }

    /**
     * The expiry timestamp of the current token, or null if perpetual (or no
     * token). Used by the dashboard to tell the customer when their offline
     * token expires, so they can activate the real licence in time.
     *
     * @return int|null
     */
    public function token_expiry()
    {
        $token = get_option(self::OPT_TOKEN, '');
        if (!is_string($token) or $token === '') return null;

        $claims = $this->verify_token($token);
        if (!is_array($claims)) return null;

        if (($claims['grant'] ?? '') !== 'limited') return null;
        return isset($claims['exp']) ? (int) $claims['exp'] : null;
    }

    /**
     * Unix timestamp at which the next phase begins, or null if already at the
     * final phase. Notices must state a specific date, never "soon".
     *
     * @return int|null
     */
    public function next_transition()
    {
        // A licensed site never ramps, so it has nothing pending.
        if ($this->licensed()) return null;

        $build = $this->build();
        if ($build === null) return null;

        $anchor = $this->anchor();
        if ($anchor === null) return null;

        $phase = $this->phase();

        foreach ($build['phases'] as $row)
        {
            list($day, $to) = $row;
            if ((int) $to > $phase) return $anchor + ((int) $day * DAY_IN_SECONDS);
        }

        return null;
    }

    /**
     * Verify and store a token. Used both by the /license/claim response and by
     * the manual offline-token field.
     *
     * @param string $raw "<base64url payload>.<base64url signature>"
     * @return true|string true on success, otherwise a machine-readable reason
     */
    public function install_token($raw)
    {
        $claims = $this->verify_token($raw);
        if (is_string($claims)) return $claims;

        // Monotonic serial: a captured older token cannot undo a later revocation.
        $serial = (int) get_option(self::OPT_SERIAL, 0);
        if ((int) $claims['sn'] < $serial) return 'STALE_SERIAL';

        update_option(self::OPT_TOKEN, $raw, true);
        update_option(self::OPT_SERIAL, (int) $claims['sn'], true);
        delete_option(self::OPT_VERDICT);

        $this->verdict = null;

        return true;
    }

    /**
     * Discard the stored token. Does NOT lower the serial — that is what stops
     * a rollback replay.
     */
    public function forget()
    {
        delete_option(self::OPT_TOKEN);
        delete_option(self::OPT_VERDICT);
        $this->verdict = null;
    }

    /**
     * Revoke the licence on the store side and locally.
     *
     * Called when the customer explicitly revokes from the dashboard. Unlike
     * forget() — which only drops the local token — this also:
     *   1. Tells the store to deactivate the EDD activation (frees the slot so
     *      the customer can reuse the code on another site).
     *   2. Bumps the serial (so the token we held is now stale and cannot be
     *      replayed even if someone has a copy).
     *
     * Failure to reach the store is not fatal: the local token is still
     * dropped, so the site loses Pro immediately. The EDD activation may lag,
     * but that is a billing concern, not a security one.
     *
     * @param string $code the purchase code, so the store can deactivate EDD
     * @return void
     */
    public function revoke($code = '')
    {
        // Tell the store to deactivate EDD and bump the serial.
        if (defined('MEC_API_LICENSE') and function_exists('wp_remote_post') and $code !== '')
        {
            wp_remote_post(MEC_API_LICENSE . '/revoke', array(
                'timeout' => 15,
                'body'    => array(
                    'sid'    => $this->site_id(),
                    'domain' => self::normalised_host(),
                    'code'   => (string) $code,
                ),
            ));
        }

        // Always drop the local token regardless of the network outcome.
        $this->forget();
    }

    /* ------------------------------------------------------------------ */
    /* Claiming a token from the store                                     */
    /* ------------------------------------------------------------------ */

    /**
     * Ask the store for a signed token for this domain.
     *
     * Nothing in the response is trusted. The body is handed straight to
     * install_token(), which verifies the Ed25519 signature against the public
     * key compiled into the build, so a fake licence server, a hosts entry or a
     * pre_http_request filter returning {"licensed":true} all achieve exactly
     * nothing. That is the whole point of the design: the transport is allowed
     * to be hostile.
     *
     * @param string $code purchase code, may be empty for a grandfather claim
     * @return true|string true on success, otherwise a machine-readable reason
     */
    public function claim($code = '')
    {
        if (!defined('MEC_API_LICENSE')) return 'NO_ENDPOINT';
        if (!function_exists('wp_remote_post')) return 'NO_HTTP';

        $response = wp_remote_post(MEC_API_LICENSE . '/claim', array(
            // Short, because this can run on an admin page load. A licence
            // check is never worth making someone wait.
            'timeout' => 15,
            'body' => array(
                'sid' => $this->site_id(),
                'domain' => self::normalised_host(),
                'code' => (string) $code,
                'serial' => (int) get_option(self::OPT_SERIAL, 0),
                'version' => defined('MEC_VERSION') ? MEC_VERSION : '',
            ),
        ));

        if (is_wp_error($response)) return 'HTTP_ERROR';

        $status = (int) wp_remote_retrieve_response_code($response);
        $data = json_decode(wp_remote_retrieve_body($response), true);

        if (!is_array($data) or empty($data['payload']) or empty($data['sig']))
        {
            // REFUSED is permanent — the client never asks again — so only the
            // codes that genuinely mean "we considered this and the answer is
            // no" may produce it.
            //
            // Not every 4xx does. A 404 means the endpoint is not there, which
            // is what a site sees if the plugin ships before the endpoint is
            // deployed, or if a proxy swallows the route. Treating that as a
            // verdict would permanently strand every install that happened to
            // check in during the gap — silently, and with no way back except
            // an offline token issued by hand. Same for 405, 408, 429 and the
            // Cloudflare 5xx-ish family: they are infrastructure, not answers.
            return in_array($status, self::REFUSAL_CODES, true) ? 'REFUSED' : 'HTTP_ERROR';
        }

        $result = $this->install_token($data['payload'] . '.' . $data['sig']);

        // On success, keep the EDD-matched account id (analytics identity).
        // Opaque id only; the store never sends an email here.
        if ($result === true and isset($data['account']) and is_string($data['account']) and $data['account'] !== '')
        {
            update_option('mec_license_account_id', $data['account'], false);
        }

        return $result;
    }

    /**
     * May the automatic grandfather claim run right now?
     *
     * The plan promises at most one server request per site. In practice that
     * has to mean one SUCCESSFUL request: a site whose first attempt hit a
     * five-second network blip would otherwise ramp to Lite while holding a
     * perfectly valid purchase code. So failures retry, on a widening backoff
     * that reaches the end of its schedule well inside the notice period.
     *
     * @return bool
     */
    public function claim_due()
    {
        // Already licensed. Never ask again, for anything.
        if ($this->licensed()) return false;

        $state = get_option(self::OPT_CLAIM, null);
        if (!is_array($state)) return true;

        // REFUSED is an answer, not a failure. Repeating the question would
        // load the endpoint for every pirated copy in existence.
        if (isset($state['last']) and $state['last'] === 'REFUSED') return false;

        $backoff = $this->claim_backoff();

        // Attempts already made. A state row only exists once one has, so this
        // is at least 1, and the wait before attempt n+1 is $backoff[n - 1].
        $n = isset($state['n']) ? (int) $state['n'] : 1;
        if ($n < 1 or $n > count($backoff)) return false;

        return (time() - (isset($state['at']) ? (int) $state['at'] : 0)) >= $backoff[$n - 1];
    }

    /**
     * Record the outcome of a claim so claim_due() can pace the next one.
     *
     * @param true|string $result whatever claim() returned
     */
    public function record_claim($result)
    {
        if ($result === true)
        {
            delete_option(self::OPT_CLAIM);
            return;
        }

        $state = get_option(self::OPT_CLAIM, null);
        $n = (is_array($state) and isset($state['n'])) ? (int) $state['n'] : 0;

        update_option(self::OPT_CLAIM, array(
            'at' => time(),
            'n' => $n + 1,
            'last' => (string) $result,
        ), false);
    }

    /**
     * How long to wait before the 2nd, 3rd, 4th, 5th and 6th attempt.
     *
     * Six attempts spanning roughly ten days, so a site that is merely offline
     * for a weekend, or asking during an outage on our side, still gets its
     * token well before anything switches off.
     *
     * @return int[]
     */
    private function claim_backoff()
    {
        return array(HOUR_IN_SECONDS, 6 * HOUR_IN_SECONDS, DAY_IN_SECONDS, 2 * DAY_IN_SECONDS, WEEK_IN_SECONDS);
    }

    /**
     * Parse and cryptographically verify a token without storing it.
     *
     * @param string $raw
     * @return array|string claims on success, machine-readable reason on failure
     */
    public function verify_token($raw)
    {
        $build = $this->build();
        if ($build === null) return 'NO_BUILD';

        if (!is_string($raw) or strpos($raw, '.') === false) return 'MALFORMED';

        $parts = explode('.', trim($raw));
        if (count($parts) !== 2) return 'MALFORMED';

        $payload = self::b64url_decode($parts[0]);
        $sig = self::b64url_decode($parts[1]);

        if ($payload === false or $sig === false) return 'MALFORMED';
        if (strlen($sig) !== 64) return 'MALFORMED';

        if (!self::ed25519_verify($sig, $payload, $build['pubkey'])) return 'BAD_SIGNATURE';

        $claims = json_decode($payload, true);
        if (!is_array($claims)) return 'MALFORMED';

        foreach (array('v', 'sid', 'grant', 'seed', 'sn') as $required)
        {
            if (!isset($claims[$required])) return 'MALFORMED';
        }

        if ((int) $claims['v'] !== 1) return 'BAD_VERSION';

        // Site binding. A token copied to another domain is inert.
        if (!hash_equals((string) $claims['sid'], $this->site_id())) return 'WRONG_SITE';

        // Grant type. 'perpetual' (online claim) never expires. 'limited'
        // (offline token) carries an 'exp' field and expires after it.
        $grant = $claims['grant'];
        if ($grant !== 'perpetual' && $grant !== 'limited') return 'BAD_GRANT';

        if ($grant === 'limited')
        {
            if (!isset($claims['exp']) || (int) $claims['exp'] < time()) return 'EXPIRED';
        }

        $seed = self::b64url_decode($claims['seed']);
        if ($seed === false or strlen($seed) !== 32) return 'BAD_SEED';

        return $claims;
    }

    /* ------------------------------------------------------------------ */
    /* Verdict                                                             */
    /* ------------------------------------------------------------------ */

    /**
     * @return array {licensed:bool, phase:int, seed:string|null}
     */
    private function verdict()
    {
        if ($this->verdict !== null) return $this->verdict;

        $build = $this->build();

        // Build constants absent or tampered with. Nothing legitimate produces
        // this, so fail closed. See the plan, §5.2.
        if ($build === null)
        {
            return $this->verdict = array('licensed' => false, 'phase' => self::PHASE_LITE, 'seed' => null);
        }

        $token = get_option(self::OPT_TOKEN, '');

        // Hardening vs. the prior scheme: the cache is now only trusted for
        // the LICENSED path, and only when its fingerprint still matches the
        // stored token verbatim. An attacker who writes a fake row into
        // wp_options cannot benefit, because:
        //
        //   - For the UNLICENSED path we always recompute. Faking a "licensed"
        //     verdict requires a matching token, and any token we hold is
        //     cryptographically verified below — a string the attacker chose
        //     still has to carry a valid Ed25519 signature for this site.
        //   - The cache stores the whole token, so a tampered token row is
        //     detected by the fingerprint comparison.
        //
        // The TTL remains a performance optimisation for the happy path only.
        $cached = get_option(self::OPT_VERDICT, null);
        if (is_array($cached)
            and !empty($cached['licensed'])
            and isset($cached['fp'], $cached['at'], $cached['phase'], $cached['token'])
            and $cached['token'] === $token
            and $cached['token'] !== ''
            and (time() - (int) $cached['at']) < self::VERDICT_TTL)
        {
            return $this->verdict = array(
                'licensed' => true,
                'phase' => (int) $cached['phase'],
                'seed' => isset($cached['seed']) ? $cached['seed'] : null,
            );
        }

        $verdict = $this->compute($token);

        // Only cache the LICENSED verdict. The unlicensed verdict is cheap to
        // recompute (pure PHP, no I/O beyond reading the token option) and
        // caching it would create the exact bypass surface the rewrite above
        // closes.
        if (!empty($verdict['licensed']))
        {
            update_option(self::OPT_VERDICT, array(
                'fp' => substr(hash('sha256', $token), 0, 32),
                'at' => time(),
                'licensed' => true,
                'phase' => $verdict['phase'],
                'seed' => $verdict['seed'],
                'token' => $token,
            ), true);
        }
        else
        {
            // Drop any stale licensed cache so a revoked site does not keep
            // running as Pro for up to VERDICT_TTL.
            if (is_array($cached) and !empty($cached['licensed']))
            {
                delete_option(self::OPT_VERDICT);
            }
        }

        return $this->verdict = $verdict;
    }

    /**
     * @param string $token
     * @return array
     */
    private function compute($token)
    {
        if ($token !== '' and $token !== false)
        {
            $claims = $this->verify_token($token);

            if (is_array($claims))
            {
                // Licensed. No expiry, no re-check, no ramp.
                return array('licensed' => true, 'phase' => self::PHASE_FULL, 'seed' => $claims['seed']);
            }
        }

        // Unlicensed: record that we have seen this, then ramp.
        $this->mark_seen();

        return array('licensed' => false, 'phase' => $this->computed_phase(), 'seed' => null);
    }

    /* ------------------------------------------------------------------ */
    /* The ramp                                                            */
    /* ------------------------------------------------------------------ */

    /**
     * Timestamp the ramp is measured from — the EARLIEST of every source, so
     * that deleting a local marker cannot rewind the phase.
     *
     * @return int|null
     */
    private function anchor()
    {
        $build = $this->build();
        if ($build === null) return null;

        $candidates = array((int) $build['release_iat']);

        foreach ($this->seen_values() as $seen)
        {
            if ($seen > 0) $candidates[] = $seen;
        }

        return min($candidates);
    }

    /**
     * @return int
     */
    private function computed_phase()
    {
        $build = $this->build();
        if ($build === null) return self::PHASE_LITE;

        $anchor = $this->anchor();
        if ($anchor === null) return self::PHASE_LITE;

        $days = (int) floor(max(0, time() - $anchor) / DAY_IN_SECONDS);

        $phase = self::PHASE_FULL;
        foreach ($build['phases'] as $row)
        {
            list($threshold, $to) = $row;
            if ($days >= (int) $threshold) $phase = (int) $to;
        }

        return $phase;
    }

    /**
     * Every location we have ever recorded "first seen unlicensed".
     * @return int[]
     */
    private function seen_values()
    {
        $out = array();

        $opt = (int) get_option(self::OPT_SEEN, 0);
        if ($opt > 0) $out[] = $opt;

        $file = $this->seen_file();
        if ($file !== null and file_exists($file))
        {
            $raw = (int) trim((string) @file_get_contents($file));
            if ($raw > 0) $out[] = $raw;
        }

        return $out;
    }

    /**
     * Write the first-seen marker to every location that does not already have
     * one. Never overwrites an older value.
     */
    private function mark_seen()
    {
        $now = time();

        $existing = $this->seen_values();
        $earliest = $existing ? min($existing) : $now;

        if ((int) get_option(self::OPT_SEEN, 0) <= 0) update_option(self::OPT_SEEN, $earliest, true);

        $file = $this->seen_file();
        if ($file !== null and !file_exists($file))
        {
            $dir = dirname($file);
            if (is_dir($dir) and is_writable($dir)) @file_put_contents($file, (string) $earliest);
        }
    }

    /**
     * @return string|null
     */
    private function seen_file()
    {
        if (!function_exists('wp_upload_dir')) return null;

        $uploads = wp_upload_dir();
        if (!is_array($uploads) or empty($uploads['basedir'])) return null;

        return rtrim($uploads['basedir'], '/\\') . DIRECTORY_SEPARATOR . '.mec-state';
    }

    /* ------------------------------------------------------------------ */
    /* Signed build constants                                              */
    /* ------------------------------------------------------------------ */

    /**
     * The build payload, verified against its own signature.
     *
     * Returns null when the constants are missing or have been edited. Callers
     * treat null as phase 4 — there is no legitimate way for a shipped Pro
     * build to reach that state.
     *
     * @return array|null {pubkey:string, release_iat:int, phases:array}
     */
    private function build()
    {
        if ($this->build_loaded) return $this->build_cache;
        $this->build_loaded = true;

        if (!defined('MEC_LICENSE_PUBKEY') or !defined('MEC_LICENSE_BUILD') or !defined('MEC_LICENSE_BUILD_SIG'))
        {
            return $this->build_cache = null;
        }

        $pubkey = self::b64url_decode(MEC_LICENSE_PUBKEY);
        if ($pubkey === false or strlen($pubkey) !== 32) return $this->build_cache = null;

        $payload = self::b64url_decode(MEC_LICENSE_BUILD);
        $sig = self::b64url_decode(MEC_LICENSE_BUILD_SIG);

        if ($payload === false or $sig === false or strlen($sig) !== 64) return $this->build_cache = null;

        if (!self::ed25519_verify($sig, $payload, $pubkey)) return $this->build_cache = null;

        $data = json_decode($payload, true);
        if (!is_array($data) or !isset($data['release_iat'], $data['phases'])) return $this->build_cache = null;
        if (!is_array($data['phases']) or !count($data['phases'])) return $this->build_cache = null;

        foreach ($data['phases'] as $row)
        {
            if (!is_array($row) or count($row) !== 2) return $this->build_cache = null;
        }

        $data['pubkey'] = $pubkey;

        return $this->build_cache = $data;
    }

    /* ------------------------------------------------------------------ */
    /* Primitives                                                          */
    /* ------------------------------------------------------------------ */

    /**
     * Ed25519 detached verification.
     *
     * Deliberately does NOT route through ParagonIE_Sodium_Compat::
     * crypto_sign_verify_detached(). That wrapper only tests
     * extension_loaded('sodium'), so on a host with the single function in
     * disable_functions it delegates straight back to the disabled symbol and
     * fatals. Call the pure-PHP core directly instead.
     *
     * @param string $sig 64 raw bytes
     * @param string $msg
     * @param string $pk 32 raw bytes
     * @return bool
     */
    private static function ed25519_verify($sig, $msg, $pk)
    {
        if (extension_loaded('sodium') and function_exists('sodium_crypto_sign_verify_detached'))
        {
            try
            {
                return (bool) sodium_crypto_sign_verify_detached($sig, $msg, $pk);
            }
            catch (Throwable $e)
            {
                return false;
            }
        }

        if (!class_exists('ParagonIE_Sodium_Core_Ed25519'))
        {
            // WordPress 5.2+ bundles sodium_compat. See wp-includes/compat.php.
            if (defined('ABSPATH') and defined('WPINC'))
            {
                $autoload = ABSPATH . WPINC . '/sodium_compat/autoload.php';
                if (file_exists($autoload)) require_once $autoload;
            }
        }

        if (class_exists('ParagonIE_Sodium_Core_Ed25519'))
        {
            try
            {
                return (bool) ParagonIE_Sodium_Core_Ed25519::verify_detached($sig, $msg, $pk);
            }
            catch (Throwable $e)
            {
                return false;
            }
        }

        return false;
    }

    /**
     * Host with scheme, port, path, case and a leading "www." removed, so that
     * https://WWW.Example.com/ and http://example.com bind to the same site.
     *
     * @return string
     */
    private static function normalised_host()
    {
        $url = function_exists('home_url') ? home_url() : '';

        $host = parse_url((string) $url, PHP_URL_HOST);
        if (!is_string($host) or $host === '') $host = (string) $url;

        $host = strtolower(trim($host));
        if (strpos($host, 'www.') === 0) $host = substr($host, 4);

        return $host;
    }

    /**
     * @param string $data
     * @return string
     */
    public static function b64url_encode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * @param string $data
     * @return string|false
     */
    public static function b64url_decode($data)
    {
        if (!is_string($data) or $data === '') return false;

        $padded = strtr($data, '-_', '+/');
        $remainder = strlen($padded) % 4;
        if ($remainder) $padded .= str_repeat('=', 4 - $remainder);

        return base64_decode($padded, true);
    }
}
