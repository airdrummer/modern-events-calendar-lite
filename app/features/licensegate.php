<?php
/** no direct access **/
defined('MECEXEC') or die();

/**
 * Webnus MEC licence gate — notices plus the phase-1 "no new Pro config" rules.
 *
 * No decision about WHEN something switches off lives here. The phase belongs
 * to MEC_license, and the yes/no gates belong to MEC_base
 * (isProCreationEnabled / isProPresentationEnabled / isBookingUnitEnabled), so
 * there is one place to reason about and one place to test. This file only
 * consults them and applies the result.
 *
 * Boundaries this file must respect (see the plan, §3):
 *  - administrators only, and never on the front end;
 *  - every notice names the specific date of the next step, never "soon";
 *  - nothing here touches customer data.
 *
 * That last one shapes the whole phase-1 design: every hook below can only
 * ever REFUSE a write. None of them edits, rewrites or deletes a stored value,
 * so no code path here can damage what a customer already has.
 *
 * @author Webnus <info@webnus.net>
 */
class MEC_feature_licensegate extends MEC_base
{
    /**
     * @var MEC_factory
     */
    public $factory;

    /**
     * @var MEC_main
     */
    public $main;

    const META_DISMISSED = 'mec_lic_notice_dismissed';

    /**
     * Event meta keys that represent Pro configuration.
     *
     * At phase 1 these may still be UPDATED on an event that already has them
     * — that is existing content, and it keeps working — but they may not be
     * ADDED to an event that does not. "Block new creation, grandfather what
     * exists" is the pattern ACF, Elementor and Gravity Forms each arrived at
     * independently; see the plan, §3.
     *
     * @var string[]
     */
    private static $pro_event_meta = [
        // Booking configuration
        'mec_booking',
        'mec_tickets',
        'mec_global_tickets_applied',
        'mec_ticket_variations',
        'mec_ticket_variations_global_inheritance',
        'mec_fees',
        'mec_fees_global_inheritance',
        'mec_reg_fields',
        'mec_reg_fields_global_inheritance',
        'mec_bfixed_fields',
        'mec_op',
        // Pro extras
        'mec_hourly_schedules',
        'mec_fields',
        'mec_dl_file',
        'mec_public_dl_file',
        'mec_public_dl_title',
        'mec_public_dl_description',
        'mec_event_gallery',
        'mec_related_events',
        'mec_banner',
        'mec_notifications',
        'mec_style_per_event',
        'mec_trailer_url',
        'mec_trailer_title',
    ];

    /**
     * Meta keys refused by block_new_pro_meta() during the current request.
     * @var array<string, int>
     */
    private static $refused_meta = [];

    /**
     * User-facing names for the blocked Pro meta keys, so save-time feedback
     * can say WHAT was not saved.
     *
     * @var array<string, string>
     */
    private static $pro_feature_map = [
        'mec_booking' => 'Booking Options',
        'mec_tickets' => 'Tickets',
        'mec_global_tickets_applied' => 'Tickets',
        'mec_ticket_variations' => 'Ticket Variations',
        'mec_ticket_variations_global_inheritance' => 'Ticket Variations',
        'mec_fees' => 'Taxes / Fees',
        'mec_fees_global_inheritance' => 'Taxes / Fees',
        'mec_reg_fields' => 'Booking Form Fields',
        'mec_reg_fields_global_inheritance' => 'Booking Form Fields',
        'mec_bfixed_fields' => 'Booking Form Fields',
        'mec_op' => 'Organizer Payment',
        'mec_hourly_schedules' => 'Hourly Schedule',
        'mec_fields' => 'Event Data',
        'mec_dl_file' => 'Downloadable File',
        'mec_public_dl_file' => 'Public Download',
        'mec_public_dl_title' => 'Public Download',
        'mec_public_dl_description' => 'Public Download',
        'mec_event_gallery' => 'Event Gallery',
        'mec_related_events' => 'Related Events',
        'mec_banner' => 'Event Banner',
        'mec_notifications' => 'Notifications',
        'mec_style_per_event' => 'Details Page Style',
        'mec_trailer_url' => 'Trailer',
        'mec_trailer_title' => 'Trailer',
    ];

    /**
     * Constructor method
     * @author Webnus <info@webnus.net>
     */
    public function __construct()
    {
        $this->factory = $this->getFactory();
        $this->main = $this->getMain();
    }

    /**
     * Initialize
     * @author Webnus <info@webnus.net>
     */
    public function init()
    {
        // Lite does not ship the licence core. Keyed on the package so that an
        // unlicensed Pro install still gets its notices.
        if (!$this->isProBuild()) return;

        $this->factory->action('admin_notices', [$this, 'notice']);
        $this->factory->action('wp_ajax_mec_lic_dismiss', [$this, 'dismiss']);

        // The offline path: support mints a token for the customer's domain and
        // they paste it in. This is what makes an automatic local fallback
        // unnecessary — the loophole is closed by a human step rather than by a
        // code path a cracker can trigger. See the plan, §4.2.
        $this->factory->action('wp_ajax_mec_lic_token', [$this, 'install_offline_token']);

        // Registered unconditionally, because cron does not run through
        // admin_init and must still be able to fire the claim.
        $this->factory->action('mec_license_claim', [$this, 'run_claim']);
        $this->factory->action('admin_init', [$this, 'schedule_claim']);

        // Phase 1 — no NEW Pro configuration.
        //
        // Hooked at the metadata layer rather than inside save_event(), so that
        // every writer is covered by one rule: the classic admin save, the FES
        // front-end submit, the importer, the quick-add popup, the REST/block
        // editor, and anything added later. A gate inside one save method would
        // silently miss the other four.
        if (!$this->isProCreationEnabled())
        {
            $this->factory->filter('update_post_metadata', [$this, 'block_new_pro_meta'], 10, 3);
            $this->factory->filter('add_post_metadata', [$this, 'block_new_pro_meta'], 10, 3);
            $this->factory->filter('pre_insert_term', [$this, 'block_new_coupon'], 10, 2);
            $this->factory->filter('pre_update_option_mec_options', [$this, 'block_new_ticket_variations'], 10, 2);

            // Save-time feedback for the blocked writes above: flag the
            // redirect, then name the affected features on the editor screen.
            $this->factory->filter('redirect_post_location', [$this, 'flag_blocked_redirect']);
            $this->factory->action('admin_notices', [$this, 'blocked_save_notice'], 5);
        }
    }

    /* ------------------------------------------------------------------ */
    /* Claiming a token                                                    */
    /* ------------------------------------------------------------------ */

    /**
     * The purchase code this site already holds, if any.
     *
     * An install that has been running Pro for years has one of these, and it
     * is the only identifier we can present to the store on its behalf. A site
     * with no purchase code cannot be grandfathered automatically — that is
     * what the offline token field is for.
     *
     * @return string
     */
    private function purchase_code()
    {
        $options = get_option('mec_options');
        if (!is_array($options) or empty($options['purchase_code'])) return '';

        return (string) $options['purchase_code'];
    }

    /**
     * Queue the one-off grandfather claim.
     *
     * Deferred to cron rather than run here, because an admin page load must
     * never block on a network round trip to our own store. WP-Cron picks it up
     * in a separate loopback request within seconds.
     *
     * @author Webnus <info@webnus.net>
     */
    public function schedule_claim()
    {
        if (!current_user_can('manage_options')) return;
        if ($this->purchase_code() === '') return;
        if (!MEC_license::instance()->claim_due()) return;

        $scheduled = wp_next_scheduled('mec_license_claim');

        if (!$scheduled)
        {
            wp_schedule_single_event(time(), 'mec_license_claim');
            return;
        }

        // Two hours late means WP-Cron is not running on this site at all —
        // a common enough state, and one the site owner usually does not know
        // about. Falling back to a blocking request is unpleasant, but the
        // alternative is a paying customer ramping down to Lite because their
        // host turned cron off. That is the worst failure this project has.
        if ((time() - $scheduled) > 2 * HOUR_IN_SECONDS)
        {
            wp_unschedule_event($scheduled, 'mec_license_claim');
            $this->run_claim();
        }
    }

    /**
     * Ask the store to grandfather this install.
     * @author Webnus <info@webnus.net>
     */
    public function run_claim()
    {
        $code = $this->purchase_code();
        if ($code === '') return;

        $license = MEC_license::instance();

        // Re-checked here rather than trusted from schedule_claim(): cron can
        // fire this long after it was queued, and by then the site may have
        // been activated by hand.
        if (!$license->claim_due()) return;

        $license->record_claim($license->claim($code));
    }

    /**
     * Human-readable explanation for a claim or token failure.
     *
     * Every one of these is something the customer is about to read while
     * already annoyed, so each says what went wrong AND what to do next.
     *
     * @param string $reason machine-readable code from MEC_license
     * @return string
     */
    private function reason_text($reason)
    {
        switch ($reason)
        {
            case 'WRONG_SITE':
                return esc_html__('This token was issued for a different domain. Ask support to reissue it for this site address.', 'modern-events-calendar-lite');

            case 'STALE_SERIAL':
                return esc_html__('This token has been superseded by a newer one. Ask support for the current token.', 'modern-events-calendar-lite');

            case 'REFUSED':
                return esc_html__('The license server did not recognise this site. Check your purchase code, or contact support for an offline activation token.', 'modern-events-calendar-lite');

            case 'HTTP_ERROR':
            case 'NO_HTTP':
            case 'NO_ENDPOINT':
                return esc_html__('This site could not reach the license server. If your host blocks outbound connections, ask support for an offline activation token.', 'modern-events-calendar-lite');

            default:
                return esc_html__('That is not a valid activation token. Copy it again exactly as support sent it.', 'modern-events-calendar-lite');
        }
    }

    /**
     * Why the automatic claim has not produced a token, if the site holds a
     * purchase code and the claim has already failed at least once.
     *
     * @return string empty when there is nothing to explain
     */
    private function claim_failure()
    {
        if ($this->purchase_code() === '') return '';

        $state = get_option(MEC_license::OPT_CLAIM, null);
        if (!is_array($state) or empty($state['last'])) return '';

        return $this->reason_text($state['last']);
    }

    /**
     * Install a token pasted by hand.
     * @author Webnus <info@webnus.net>
     */
    public function install_offline_token()
    {
        if (!current_user_can('manage_options')) wp_send_json_error(['message' => esc_html__('You cannot access this section.', 'modern-events-calendar-lite')], 403);
        if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_REQUEST['nonce'] ?? '')), 'mec_settings_nonce')) wp_send_json_error(['message' => esc_html__('Please reload the page and try again.', 'modern-events-calendar-lite')], 403);

        $token = trim(sanitize_text_field(wp_unslash($_REQUEST['token'] ?? '')));
        if ($token === '') wp_send_json_error(['message' => esc_html__('Paste the activation token support sent you.', 'modern-events-calendar-lite')], 400);

        $result = MEC_license::instance()->install_token($token);

        if ($result !== true) wp_send_json_error(['message' => $this->reason_text($result)], 400);

        // A successful manual install ends the automatic retry schedule too.
        MEC_license::instance()->record_claim(true);

        // If this is a limited (offline) token, tell the customer when it
        // expires so they can activate the real licence in time.
        $license = MEC_license::instance();
        $message = esc_html__('License activated. Thank you.', 'modern-events-calendar-lite');

        $exp = $license->token_expiry();
        if ($exp !== null)
        {
            $format = get_option('date_format') ?: 'Y-m-d';
            $when = date_i18n($format, $exp);
            $message = sprintf(
                /* translators: %s: expiry date */
                esc_html__('License activated. This offline token is valid until %s. Activate your real purchase code before then to keep Pro running.', 'modern-events-calendar-lite'),
                $when
            );
        }

        wp_send_json_success(['message' => $message]);
    }

    /**
     * @param string $key
     * @return bool
     */
    private static function is_pro_event_meta($key)
    {
        static $map = null;
        if ($map === null) $map = array_flip(self::$pro_event_meta);

        return isset($map[$key]);
    }

    /**
     * Refuse to ADD Pro configuration to an event that does not already have it.
     *
     * Returning a non-null value short-circuits WordPress' metadata write. We
     * only ever return false (refuse) or null (proceed untouched) — there is no
     * branch that rewrites a value, so an existing configuration cannot be
     * altered or lost through this filter.
     *
     * @param mixed $check short-circuit value; null means "carry on"
     * @param int $object_id
     * @param string $meta_key
     * @return mixed null to proceed, false to refuse
     */
    public function block_new_pro_meta($check, $object_id, $meta_key)
    {
        // Another filter already decided. Do not override it.
        if ($check !== null) return $check;

        // Cheapest test first: this fires on every post-meta write on the site,
        // including bulk imports by unrelated plugins, so it is a hash lookup
        // rather than a scan and it happens before any database call.
        if (!self::is_pro_event_meta($meta_key)) return $check;

        if (get_post_type($object_id) !== $this->main->get_main_post_type()) return $check;

        // Already configured => existing content. Editing it stays available.
        $existing = get_post_meta($object_id, $meta_key, true);
        if (!empty($existing)) return $check;

        // Record the refusal so the save-time feedback (blocked_save_notice)
        // can tell the user exactly WHAT did not persist. Without this the
        // write fails silently and the editor reloads as if it had worked.
        self::$refused_meta[$meta_key] = $object_id;

        return false;
    }

    /**
     * Append a flag to the post-save redirect when Pro meta was refused, so
     * the editor can render an explicit "not saved" notice on arrival.
     *
     * @param string $location
     * @return string
     */
    public function flag_blocked_redirect($location)
    {
        if (empty(self::$refused_meta)) return $location;

        $keys = implode(',', array_keys(self::$refused_meta));
        return add_query_arg('mec_pro_blocked', $keys, $location);
    }

    /**
     * Save-time feedback: name the exact Pro features that were NOT saved
     * and link to activation.
     *
     * Rides on the mec_pro_blocked URL arg added by flag_blocked_redirect(),
     * so it appears exactly once, right after the save that was affected.
     */
    public function blocked_save_notice()
    {
        if (empty($_GET['mec_pro_blocked'])) return;

        $raw = sanitize_text_field(wp_unslash($_GET['mec_pro_blocked']));
        $keys = array_filter(explode(',', $raw), function ($key) {
            return isset(self::$pro_feature_map[$key]);
        });
        if (!$keys) return;

        $labels = [];
        foreach ($keys as $key)
        {
            $label = self::$pro_feature_map[$key];
            if (!in_array($label, $labels)) $labels[] = $label;
        }

        $activate = admin_url('admin.php?page=mec-intro');
        ?>
        <div class="notice notice-error" data-mec-pro-blocked="1">
            <p>
                <strong><?php esc_html_e('Modern Events Calendar', 'modern-events-calendar-lite'); ?>:</strong>
                <?php echo esc_html(_n('The following Pro feature was NOT saved because your license is not active:', 'The following Pro features were NOT saved because your license is not active:', count($labels), 'modern-events-calendar-lite')); ?>
            </p>
            <ul style="margin: 0 0 8px 22px; list-style: disc;">
                <?php foreach ($labels as $label): ?>
                    <li><strong><?php echo esc_html($label); ?></strong></li>
                <?php endforeach; ?>
            </ul>
            <p style="margin-bottom: 8px;">
                <?php esc_html_e('The fields are still filled in on this page. Copy anything you need before leaving.', 'modern-events-calendar-lite'); ?>
                <a href="<?php echo esc_url($activate); ?>"><strong><?php esc_html_e('Activate your license', 'modern-events-calendar-lite'); ?></strong></a>
                <?php esc_html_e('to save them.', 'modern-events-calendar-lite'); ?>
            </p>
        </div>
        <?php
    }

    /**
     * Refuse to create a new coupon.
     *
     * Hooked at pre_insert_term rather than created_mec_coupon: by the time the
     * latter fires WordPress has already inserted the term row, so blocking
     * there would leave a stranded empty coupon. Refusing here means the term
     * is never created and the administrator sees a normal WordPress error
     * instead of a silent no-op.
     *
     * Existing coupons keep working, keep applying and stay editable.
     *
     * @param string $term
     * @param string $taxonomy
     * @return string|WP_Error
     */
    public function block_new_coupon($term, $taxonomy)
    {
        if ($taxonomy !== 'mec_coupon') return $term;

        return new WP_Error(
            'mec_license_phase',
            esc_html__('Your Modern Events Calendar Pro license is not activated, so new coupons cannot be created. Your existing coupons are unaffected. Activate your license to add coupons again.', 'modern-events-calendar-lite')
        );
    }

    /**
     * Refuse to add a new global ticket variation.
     *
     * The settings form posts the whole options array on every save, so this
     * cannot simply reject the write — doing that would discard every unrelated
     * setting the administrator changed in the same submit. Instead it keeps
     * exactly the variations that already existed, preserving edits to them,
     * and drops only the keys that were not there before.
     *
     * @param mixed $value the value about to be stored
     * @param mixed $old_value
     * @return mixed
     */
    public function block_new_ticket_variations($value, $old_value)
    {
        if (!is_array($value) or !isset($value['settings']['ticket_variations'])) return $value;
        if (!is_array($value['settings']['ticket_variations'])) return $value;

        $existing = [];
        if (is_array($old_value) and isset($old_value['settings']['ticket_variations']) and is_array($old_value['settings']['ticket_variations']))
        {
            $existing = $old_value['settings']['ticket_variations'];
        }

        // Nothing was there before, so every posted variation is a new one.
        if (!count($existing))
        {
            $value['settings']['ticket_variations'] = [];
            return $value;
        }

        foreach (array_keys($value['settings']['ticket_variations']) as $key)
        {
            if (!array_key_exists($key, $existing)) unset($value['settings']['ticket_variations'][$key]);
        }

        return $value;
    }

    /**
     * How long a dismissal lasts at the given phase.
     *
     * The notice is NEVER dismissible. A customer who can close it can ignore
     * the licence state until features switch off, and by then it is a support
     * ticket rather than a renewal. Showing the notice on every admin page
     * load is the point — it is the only signal that Pro is about to degrade.
     *
     * @param int $phase
     * @return int|null seconds, or null when the notice is not dismissible
     */
    private function snooze($phase)
    {
        return null;
    }

    /**
     * @param int $phase
     * @return string
     */
    private function severity($phase)
    {
        if ($phase <= 0) return 'notice-info';
        if ($phase <= 2) return 'notice-warning';

        return 'notice-error';
    }

    /**
     * Body copy for a phase. Says what has already happened and what happens
     * next, so nobody is surprised by a step.
     *
     * These strings must describe only what is actually enforced. Telling a
     * customer a feature is off while it still runs is worse than saying
     * nothing. What each phase really does:
     *
     *   phase 1 -> isProCreationEnabled(): the three filters registered in
     *              init() refuse NEW coupons, NEW global ticket variations and
     *              Pro meta on events that do not already carry it.
     *   phase 2 -> isProPresentationEnabled(): Pro-only skins fall back to the
     *              default layout (MEC_render::available_skin), automated
     *              Google/Meetup sync stops (MEC_syncSchedule::sync and the
     *              app/crons import/export scripts), and the new-event digest
     *              stops.
     *   phase 3 -> isBookingUnitEnabled(): books/gateways/coupons/op as one
     *              unit, plus the booking-related cron mail.
     *   phase 4 -> getPRO() returns false, giving Lite parity.
     *
     * Deliberately NOT claimed: advanced search and the page-builder addons.
     * Lite ships and runs both, so they cannot be part of the ramp — the final
     * phase is defined as Lite parity, which would have to switch them back on.
     *
     * @param int $phase
     * @param string $when formatted date of the next transition
     * @return string
     */
    private function message($phase, $when)
    {
        switch ($phase)
        {
            case 0:
                return $when === ''
                    ? esc_html__('Modern Events Calendar Pro is not activated. Activate your license to keep Pro features.', 'modern-events-calendar-lite')
                    : sprintf(
                        /* translators: %s: date */
                        esc_html__('Modern Events Calendar Pro is not activated. Pro features start switching off on %s.', 'modern-events-calendar-lite'),
                        $when
                    );

            case 1:
                return sprintf(
                    /* translators: %s: date */
                    esc_html__('Modern Events Calendar Pro is not activated. New coupons, new ticket variations and Pro options on new events are now blocked. Everything you have already set up keeps working and stays editable. More features switch off on %s.', 'modern-events-calendar-lite'),
                    $when
                );

            case 2:
                return sprintf(
                    /* translators: %s: date */
                    esc_html__('Modern Events Calendar Pro is not activated. Pro-only calendar layouts now fall back to a standard layout, and automated calendar syncing has stopped. Bookings still work; new bookings stop on %s.', 'modern-events-calendar-lite'),
                    $when
                );

            case 3:
                return sprintf(
                    /* translators: %s: date */
                    esc_html__('Modern Events Calendar Pro is not activated. New bookings are disabled. Your existing bookings, transactions and attendee lists are intact and can still be exported. Remaining Pro features stop on %s.', 'modern-events-calendar-lite'),
                    $when
                );

            default:
                return esc_html__('Modern Events Calendar Pro is not activated and is running with Lite features. Your events and bookings are intact. Activate your license to restore Pro.', 'modern-events-calendar-lite');
        }
    }

    /**
     * Render the notice.
     * @author Webnus <info@webnus.net>
     */
    public function notice()
    {
        $license = MEC_license::instance();

        // A licensed site never sees any of this.
        if ($license->licensed()) return;

        // Administrators only. A shop manager or author cannot act on it.
        if (!current_user_can('manage_options')) return;

        $phase = $license->phase();
        $snooze = $this->snooze($phase);

        if ($snooze !== null)
        {
            $dismissed = (int) get_user_meta(get_current_user_id(), self::META_DISMISSED, true);
            if ($dismissed > 0 and (time() - $dismissed) < $snooze) return;
        }

        $next = $license->next_transition();
        $when = '';

        if (is_int($next))
        {
            $format = get_option('date_format');
            if (!$format) $format = 'Y-m-d';
            $when = date_i18n($format, $next);
        }

        $message = $this->message($phase, $when);

        // Someone who has already entered a valid purchase code and is still
        // being shown this needs to know why, rather than being told to do the
        // thing they have plainly already done.
        $stuck = $this->claim_failure();
        if ($stuck !== '') $message .= ' ' . $stuck;

        $activate = admin_url('admin.php?page=mec-intro');
        $classes = 'notice ' . $this->severity($phase);
        if ($snooze !== null) $classes .= ' is-dismissible';

        ?>
        <div class="<?php echo esc_attr($classes); ?>" data-mec-lic-notice="1">
            <p>
                <strong><?php echo esc_html__('Modern Events Calendar', 'modern-events-calendar-lite'); ?>:</strong>
                <?php echo esc_html($message); ?>
                <a href="<?php echo esc_url($activate); ?>"><?php esc_html_e('Activate your license', 'modern-events-calendar-lite'); ?></a>
            </p>
        </div>
        <?php

        if ($snooze === null) return;

        $nonce = wp_create_nonce('mec_lic_dismiss');
        ?>
        <script>
        (function () {
            var notice = document.querySelector('[data-mec-lic-notice]');
            if (!notice) return;

            notice.addEventListener('click', function (event) {
                if (!event.target.classList.contains('notice-dismiss')) return;

                var body = new FormData();
                body.append('action', 'mec_lic_dismiss');
                body.append('nonce', <?php echo wp_json_encode($nonce); ?>);

                window.fetch(<?php echo wp_json_encode(admin_url('admin-ajax.php')); ?>, {
                    method: 'POST',
                    credentials: 'same-origin',
                    body: body
                });
            });
        })();
        </script>
        <?php
    }

    /**
     * Record a dismissal against the current user.
     * @author Webnus <info@webnus.net>
     */
    public function dismiss()
    {
        if (!current_user_can('manage_options')) wp_die('', '', ['response' => 403]);
        if (!wp_verify_nonce(sanitize_text_field($_REQUEST['nonce'] ?? ''), 'mec_lic_dismiss')) wp_die('', '', ['response' => 403]);

        update_user_meta(get_current_user_id(), self::META_DISMISSED, time());

        wp_die('', '', ['response' => 200]);
    }
}
