<?php

namespace MEC\Tracking;

/**
 * Lightweight, dependency-free PostHog tracking.
 *
 * Sends product-analytics events to PostHog's public capture endpoint using
 * WordPress's HTTP API (wp_remote_post) so we avoid Composer / the posthog-php
 * SDK, which requires PHP 8.2 while MEC still supports PHP 5.6.
 *
 * The project token is a PUBLIC key (phc_...) and is read from the
 * MEC_POSTHOG_API_KEY constant (define it in wp-config.php) or the
 * `mec_posthog_api_key` filter. Nothing is sent when no key is configured.
 *
 * Identity rules (marketing plan, "Identity"):
 *   - When the install is matched to an EDD customer (licence claim returned
 *     an account id), distinct_id becomes `edd_customer_<id>` and the previous
 *     anonymous install UUID is merged into it once via a $identify event, so
 *     no history is lost.
 *   - Otherwise distinct_id stays the anonymous per-install UUID.
 *   - email is a PERSON property only ($set); it is never an event property.
 *
 * Common properties on every event: site_instance_id, edition, event_source,
 * plugin_version, wp_version, php_version, plus edd_customer_id whenever the
 * account id is known.
 */
class PostHog
{
    /**
     * Default US Cloud ingestion host.
     */
    const DEFAULT_HOST = 'https://us.i.posthog.com';

    /**
     * Bundled PostHog project token (public phc_ write key) shipped with the
     * plugin so tracking works on every install without per-site config.
     * This is PUBLIC by design (same key that ships in JS/mobile bundles);
     * it can only ingest events, not read data. Override per-site via the
     * MEC_POSTHOG_API_KEY constant or the `mec_posthog_api_key` filter.
     */
    const DEFAULT_API_KEY = 'phc_mMkTxML4n4W8QRTKx3UMJjs3Rm9zQXSCXuH2f9pacZn6';

    /**
     * Option key storing the anonymous per-install ID (fallback distinct_id).
     */
    const INSTALL_ID_OPTION = 'mec_posthog_install_id';

    /**
     * Option key storing the user's tracking-consent choice. Empty until the
     * consent popup is answered; then 'accepted' or 'declined'.
     */
    const CONSENT_OPTION = 'mec_posthog_consent';

    /**
     * Option key storing the EDD-matched account id ('edd_customer_123') once
     * a licence claim succeeded. Used as distinct_id from then on.
     */
    const ACCOUNT_ID_OPTION = 'mec_posthog_account_id';

    /**
     * Option key storing whether the install-UUID → account-id merge has been
     * sent to PostHog (sent exactly once per account).
     */
    const MERGED_OPTION = 'mec_posthog_account_merged';

    /**
     * Option key for the retroactive queue: install-lifecycle events that
     * fired before consent was answered, replayed once the user accepts.
     */
    const QUEUE_OPTION = 'mec_posthog_queue';

    /**
     * Events that may be captured before consent exists and are queued for
     * retroactive replay on accept. Install lifecycle only — nothing that
     * contains behavioral data.
     *
     * @var array
     */
    protected $queueable = array('mec_lite_activated', 'mec_pro_activated');

    /**
     * Maximum queued events kept (queue is a safety net, not a buffer).
     */
    const QUEUE_MAX = 20;

    /**
     * Settings whose value changes we track individually (filterable). An
     * on->off flip here is a strong "feature abandoned" (churn) signal.
     *
     * @var array
     */
    protected $watch_settings = array(
        'booking_status', 'wc_status', 'mec_cart_status', 'fes_guest_status',
        'auto_emails_module_status', 'sms_status',
    );

    /**
     * Snapshot of the settings array taken just before a save is written,
     * used to detect which specific settings changed.
     *
     * @var array
     */
    protected $old_settings = array();

    /**
     * Snapshot of the WHOLE options array (styling, sk-options, gateways, …)
     * taken just before a save, used to detect which top-level sections the
     * save actually touched (events 6 and 7).
     *
     * @var array
     */
    protected $old_options = array();

    /**
     * Whether the retroactive queue was flushed on this request already.
     *
     * @var bool
     */
    protected $queue_flushed = false;

    /**
     * Register hooks.
     *
     * @return void
     */
    public function init()
    {
        // Snapshot the previous settings before the write (both hooks fire only
        // inside MEC_main::save_options()), so we can diff specific keys.
        add_action('mec_save_options', array($this, 'snapshot_old'), 1);

        // Fired right after update_option('mec_options', ...) in MEC_main::save_options().
        add_action('mec_saved_options', array($this, 'track_settings_saved'), 10, 1);
    }

    /**
     * Capture the options array as it is right before the save is persisted.
     *
     * @return void
     */
    public function snapshot_old()
    {
        $opt = get_option('mec_options', array());
        if (!is_array($opt)) $opt = array();

        $this->old_options = $opt;
        $this->old_settings = (isset($opt['settings']) && is_array($opt['settings'])) ? $opt['settings'] : array();
    }

    /**
     * Track a "settings saved" event.
     *
     * @param array $final The final MEC options array that was saved.
     * @return void
     */
    public function track_settings_saved($final)
    {
        $settings = (is_array($final) && isset($final['settings']) && is_array($final['settings'])) ? $final['settings'] : array();

        // Detect which watched settings changed (old snapshot vs new).
        $watch = apply_filters('mec_posthog_watch_settings', $this->watch_settings);
        $changes = array();
        foreach ($watch as $key)
        {
            $before = isset($this->old_settings[$key]) ? $this->old_settings[$key] : null;
            $after  = isset($settings[$key]) ? $settings[$key] : null;
            if ((string) $before !== (string) $after)
            {
                $changes[$key] = array('from' => $before, 'to' => $after);
            }
        }

        // Cheap config snapshot (flags only, no heavy DB queries).
        $collector = new Collector();
        $flags = $collector->feature_flags();

        $properties = array_merge($flags, array(
            'module_count'     => $collector->module_count(),
            'enabled_gateways' => $collector->enabled_gateways(),
            'changed_settings' => $changes ? array_keys($changes) : null,
        ));

        // Keep the current config on the person too.
        $set = array_merge($flags, array('module_count' => $collector->module_count()));

        $this->capture('mec_settings_saved', $properties, $set);

        // Dedicated event when Booking is specifically toggled on/off, so
        // "how many installs enabled/disabled booking" is a clean trend.
        if (isset($changes['booking_status']))
        {
            $this->capture('mec_booking_toggled', array(
                'enabled' => (int) $changes['booking_status']['to'],
            ));
        }

        // Event 6 — appearance / skin / shortcode customization.
        $this->maybe_track_customization($final, $settings);

        // Event 7 — commerce configuration (Pro only).
        if ($this->edition() === 'pro') $this->maybe_track_commerce($final, $settings, $changes);
    }

    /**
     * Event 6: mec_customization_saved — fired when a save actually changed
     * appearance-related sections (styling, custom CSS, or any skin's options).
     *
     * @param array $final    The final options array that was saved.
     * @param array $settings The settings sub-array that was saved.
     * @return void
     */
    protected function maybe_track_customization($final, $settings)
    {
        $changed = $this->section_diff($this->old_options, $final, 'styling')
            + $this->section_diff($this->old_options, $final, 'styles');

        // sk-options: one sub-array per skin; report which skins changed.
        $old_skins = isset($this->old_options['sk-options']) && is_array($this->old_options['sk-options']) ? $this->old_options['sk-options'] : array();
        $new_skins = isset($final['sk-options']) && is_array($final['sk-options']) ? $final['sk-options'] : array();
        $changed_skins = array();
        foreach (array_unique(array_merge(array_keys($old_skins), array_keys($new_skins))) as $skin)
        {
            $o = isset($old_skins[$skin]) ? (array) $old_skins[$skin] : array();
            $n = isset($new_skins[$skin]) ? (array) $new_skins[$skin] : array();
            if ($o != $n) $changed_skins[] = (string) $skin;
        }

        if (!count($changed) && !count($changed_skins)) return;

        $area = count($changed_skins) ? 'skins' : (isset($this->old_options['styles']) || isset($final['styles']) ? 'custom_css' : 'styling');

        $this->capture('mec_customization_saved', array(
            'customization_area'      => $area,
            'view_type'               => count($changed_skins) ? $changed_skins[0] : null,
            'changed_keys_allowlist'  => array_slice(array_merge($changed, array_map(function ($s) { return 'sk-options.' . $s; }, $changed_skins)), 0, 15),
            'uses_custom_css'         => (isset($final['styles']['CSS']) && trim((string) $final['styles']['CSS']) !== '') ? 1 : 0,
        ));
    }

    /**
     * Event 7: mec_commerce_configuration_saved (Pro only) — fired when a save
     * changed booking / ticket / gateway configuration.
     *
     * @param array $final    The final options array that was saved.
     * @param array $settings The settings sub-array that was saved.
     * @param array $changes  Watched settings changes from track_settings_saved().
     * @return void
     */
    protected function maybe_track_commerce($final, $settings, $changes)
    {
        $commerce_keys = array(
            'booking_status', 'booking_registration', 'wc_status', 'mec_cart_status',
            'fes_guest_status', 'booking_auto_confirm', 'partial_payment_status',
            'appointments_status',
        );

        $settings_hit = array();
        foreach ($commerce_keys as $key)
        {
            $before = isset($this->old_settings[$key]) ? $this->old_settings[$key] : null;
            $after  = isset($settings[$key]) ? $settings[$key] : null;
            if ((string) $before !== (string) $after) $settings_hit[] = $key;
        }

        // Ticket variations / fees live in settings and are commerce too.
        foreach (array('ticket_variations', 'fees') as $key)
        {
            $before = isset($this->old_settings[$key]) ? $this->old_settings[$key] : null;
            $after  = isset($settings[$key]) ? $settings[$key] : null;
            if ($before != $after) $settings_hit[] = $key;
        }

        // Any gateway option change.
        $gateway_changes = $this->section_diff($this->old_options, $final, 'gateways');

        if (!count($settings_hit) && !count($gateway_changes) && !isset($changes['booking_status'])) return;

        $collector = new Collector();

        $booking_enabled = !empty($settings['booking_status']) ? 1 : 0;
        $appointments_enabled = !empty($settings['appointments_status']) ? 1 : 0;
        $variations = (isset($settings['ticket_variations']) && is_array($settings['ticket_variations'])) ? $settings['ticket_variations'] : array();

        $this->capture('mec_commerce_configuration_saved', array(
            'configuration_area'  => count($gateway_changes) ? 'gateways' : 'booking',
            'booking_enabled'     => $booking_enabled,
            'appointments_enabled' => $appointments_enabled,
            'simultaneously_active' => ($booking_enabled && $appointments_enabled) ? 1 : 0,
            'ticketing_enabled'   => ($booking_enabled && count($variations)) ? 1 : 0,
            'ticket_type'         => count($variations) ? 'variations' : 'simple',
            'ticket_count_bucket' => Util::bucket(count($variations)),
            'gateways_enabled'    => $collector->enabled_gateways(),
        ));
    }

    /**
     * Key names (never values) that differ between the old and new version of
     * one top-level options section.
     *
     * @param array  $old
     * @param array  $new
     * @param string $section
     * @return array
     */
    protected function section_diff($old, $new, $section)
    {
        $o = (isset($old[$section]) && is_array($old[$section])) ? $old[$section] : array();
        $n = (isset($new[$section]) && is_array($new[$section])) ? $new[$section] : array();

        $changed = array();
        foreach (array_unique(array_merge(array_keys($o), array_keys($n))) as $key)
        {
            $ov = isset($o[$key]) ? $o[$key] : null;
            $nv = isset($n[$key]) ? $n[$key] : null;
            if ($ov != $nv) $changed[] = (string) $key;
        }

        return $changed;
    }

    /**
     * Send an event to PostHog.
     *
     * @param string $event      Event name.
     * @param array  $properties Event properties.
     * @param array  $set        Extra person properties merged into $set.
     * @param string $source     'php' (default) or 'js' for relayed client events.
     * @return void
     */
    public function capture($event, $properties = array(), $set = array(), $source = 'php')
    {
        $api_key = $this->get_api_key();
        if (!$api_key) return;

        // Opt-in gate: only send once the user has accepted tracking.
        if (get_option(self::CONSENT_OPTION) !== 'accepted' && !apply_filters('mec_posthog_force', false))
        {
            // Install-lifecycle events may fire before the consent popup is even
            // seen (plugin activation). Queue them for retroactive replay.
            if (in_array($event, $this->queueable, true)) $this->queue_event($event, $properties, $set, $source);

            return;
        }

        // Replay anything queued earlier (activation before consent) once.
        $this->flush_queue();

        // Person properties: email lives on the person ONLY (never per event).
        $admin_email = get_option('admin_email');
        $person = array_merge(array('email' => $admin_email ?: null, 'site_url' => home_url()), (array) $set);

        $properties = array_merge($this->common_properties($source), (array) $properties);
        $properties['$set'] = array_filter($person, function ($v) { return !is_null($v); });

        $distinct_id = $this->get_distinct_id();

        // One-time merge of the anonymous install UUID into the account id, so
        // pre-licence history follows the customer in PostHog.
        if ($distinct_id !== $this->get_install_id()) $this->maybe_identify();

        $body = array(
            'api_key'     => $api_key,
            'event'       => $event,
            'distinct_id' => $distinct_id,
            'properties'  => array_filter($properties, function ($value) {
                return !is_null($value);
            }),
        );

        // Blocking + logging while debugging (so events are easy to verify locally);
        // fire-and-forget otherwise so tracking never slows down a request.
        $blocking = apply_filters('mec_posthog_blocking', (defined('WP_DEBUG') && WP_DEBUG), $event);

        $response = wp_remote_post($this->get_host() . '/i/v0/e/', array(
            'headers'  => array('Content-Type' => 'application/json'),
            'body'     => wp_json_encode($body),
            'timeout'  => 5,
            'blocking' => (bool) $blocking,
        ));

        if ($blocking && defined('WP_DEBUG') && WP_DEBUG)
        {
            if (is_wp_error($response)) error_log('MEC PostHog: ' . $response->get_error_message());
            else error_log('MEC PostHog: HTTP ' . wp_remote_retrieve_response_code($response) . ' - ' . wp_remote_retrieve_body($response));
        }
    }

    /**
     * Store an EDD-matched account id (e.g. 'edd_customer_123') captured from a
     * successful licence claim. From the next capture on, it becomes the
     * distinct_id; the anonymous install UUID is merged into it once so the
     * person's history is preserved.
     *
     * @param string $account_id
     * @return void
     */
    public function set_account_id($account_id)
    {
        $account_id = trim((string) $account_id);
        if ($account_id === '' || !preg_match('/^[a-z0-9_]+$/i', $account_id)) return;

        $current = get_option(self::ACCOUNT_ID_OPTION);
        if ($current === $account_id) return;

        update_option(self::ACCOUNT_ID_OPTION, $account_id, false);
        // A new account id needs its own one-time merge event.
        update_option(self::MERGED_OPTION, 0, false);
    }

    /**
     * The PostHog distinct_id for this install: the EDD account id when the
     * install has been matched to a customer, otherwise the anonymous install
     * UUID.
     *
     * @return string
     */
    protected function get_distinct_id()
    {
        $account = $this->account_id();
        if ($account !== '') return $account;

        return $this->get_install_id();
    }

    /**
     * The EDD-matched account id, wherever it was stored: written by
     * PostHog::set_account_id() or by the licence core after a successful
     * claim ('mec_license_account_id'). '' when unknown.
     *
     * @return string
     */
    protected function account_id()
    {
        $account = get_option(self::ACCOUNT_ID_OPTION);
        if (is_string($account) && $account !== '') return $account;

        $account = get_option('mec_license_account_id');
        if (is_string($account) && $account !== '') return $account;

        return '';
    }

    /**
     * Send the one-time $identify event that merges the anonymous install UUID
     * into the account distinct_id, so pre-licence history follows the person.
     *
     * @return void
     */
    protected function maybe_identify()
    {
        $account = $this->account_id();
        if ($account === '') return;
        if (get_option(self::MERGED_OPTION)) return;

        $api_key = $this->get_api_key();
        if (!$api_key) return;

        $body = array(
            'api_key'     => $api_key,
            'event'       => '$identify',
            'distinct_id' => $account,
            'properties'  => array(
                '$anon_distinct_id' => $this->get_install_id(),
            ),
        );

        wp_remote_post($this->get_host() . '/i/v0/e/', array(
            'headers'  => array('Content-Type' => 'application/json'),
            'body'     => wp_json_encode($body),
            'timeout'  => 5,
            'blocking' => (bool) apply_filters('mec_posthog_blocking', (defined('WP_DEBUG') && WP_DEBUG)),
        ));

        update_option(self::MERGED_OPTION, 1, false);
    }

    /**
     * Properties attached to EVERY event (marketing plan, "common rules").
     *
     * @param string $source 'php' or 'js'.
     * @return array
     */
    protected function common_properties($source)
    {
        global $wp_version;

        $props = array(
            'site_instance_id' => $this->get_install_id(),
            'edition'          => $this->edition(),
            'event_source'     => ($source === 'js') ? 'js' : 'php',
            'plugin_version'   => defined('MEC_VERSION') ? MEC_VERSION : null,
            'wp_version'       => isset($wp_version) ? $wp_version : null,
            'php_version'      => PHP_VERSION,
        );

        // edd_customer_id wherever available (Identity rule).
        $account = $this->account_id();
        if ($account !== '') $props['edd_customer_id'] = $account;

        return $props;
    }

    /**
     * 'lite' or 'pro'. MEC_EDITION is defined by each package's bootstrap; the
     * file check is a fallback for installs running a mixed old/new file set.
     *
     * @return string
     */
    protected function edition()
    {
        if (defined('MEC_EDITION')) return MEC_EDITION;

        return (defined('MEC_ABSPATH') && file_exists(MEC_ABSPATH . 'app/libraries/license.php')) ? 'pro' : 'lite';
    }

    /**
     * Queue an event for retroactive replay after consent is accepted.
     *
     * @param string $event
     * @param array  $properties
     * @param array  $set
     * @param string $source
     * @return void
     */
    protected function queue_event($event, $properties, $set, $source)
    {
        $queue = get_option(self::QUEUE_OPTION, array());
        if (!is_array($queue)) $queue = array();

        $queue[] = array('event' => $event, 'properties' => $properties, 'set' => $set, 'source' => $source);
        if (count($queue) > self::QUEUE_MAX) $queue = array_slice($queue, -self::QUEUE_MAX);

        update_option(self::QUEUE_OPTION, $queue, false);
    }

    /**
     * Replay queued events once (after consent was accepted). Sent with
     * 'mec_posthog_force' so the consent gate cannot loop them back into the
     * queue.
     *
     * @return void
     */
    public function flush_queue()
    {
        if ($this->queue_flushed) return;
        $this->queue_flushed = true;

        $queue = get_option(self::QUEUE_OPTION, array());
        if (!is_array($queue) || !count($queue)) return;

        delete_option(self::QUEUE_OPTION);

        foreach ($queue as $item)
        {
            $this->capture(
                isset($item['event']) ? $item['event'] : '',
                isset($item['properties']) ? $item['properties'] : array(),
                isset($item['set']) ? $item['set'] : array(),
                isset($item['source']) ? $item['source'] : 'php'
            );
        }
    }

    /**
     * The PostHog project API key (public phc_ token). Falls back to the
     * bundled default so every install tracks to the same project.
     *
     * @return string
     */
    protected function get_api_key()
    {
        $key = defined('MEC_POSTHOG_API_KEY') ? MEC_POSTHOG_API_KEY : self::DEFAULT_API_KEY;

        return (string) apply_filters('mec_posthog_api_key', $key);
    }

    /**
     * A stable, anonymous per-install identifier used as the PostHog
     * distinct_id until the install is matched to an EDD customer, generated
     * once and stored in the options table.
     *
     * @return string
     */
    protected function get_install_id()
    {
        $id = get_option(self::INSTALL_ID_OPTION);
        if (!$id)
        {
            $id = function_exists('wp_generate_uuid4') ? wp_generate_uuid4() : md5(home_url() . uniqid('', true));
            update_option(self::INSTALL_ID_OPTION, $id, false);
        }

        return $id;
    }

    /**
     * The PostHog ingestion host (no trailing slash).
     *
     * @return string
     */
    protected function get_host()
    {
        $host = defined('MEC_POSTHOG_HOST') ? MEC_POSTHOG_HOST : self::DEFAULT_HOST;

        return untrailingslashit(apply_filters('mec_posthog_host', $host));
    }
}
