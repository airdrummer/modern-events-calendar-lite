<?php

namespace MEC\Tracking;

/**
 * Client-event relay.
 *
 * Front-end behavioural events (calendar render, search/filter usage) are
 * collected in the browser and relayed through this endpoint, so the visitor
 * never loads posthog.js and the site admin's consent choice (already
 * collected by the Consent popup) is the only gate needed.
 *
 * Hardening: this endpoint is reachable by anonymous visitors, so it accepts
 * ONLY two event names, ONLY the exact properties listed per event (values
 * constrained to fixed vocabularies), enforces a per-IP rate limit, and
 * replies 204 with no body. Nothing here can echo data back or be used as an
 * open proxy to PostHog.
 */
class ClientRelay
{
    const AJAX_ACTION = 'mec_track_event';

    /**
     * Per-event allowed properties and their value vocabularies / sanitizers.
     *
     * @var array
     */
    protected static $allowlist = array(
        'mec_calendar_view_rendered' => array(
            'calendar_url'          => 'url',
            'view_type'             => 'key',
            'skin'                  => 'key',
            'shortcode_id_hash'     => 'hash12',
            'events_shown_bucket'   => 'bucket',
            'device_type'           => array('desktop', 'mobile', 'tablet'),
        ),
        'mec_calendar_discovery_used' => array(
            'discovery_action'      => array('search', 'filter'),
            'filter_type'           => 'key',
            'selected_count'        => 'int',
            'result_count_bucket'   => 'bucket',
            'view_type'             => 'key',
            'calendar_url'          => 'url',
        ),
    );

    /**
     * Requests allowed per IP per hour (both events combined).
     */
    const RATE_LIMIT = 30;

    /**
     * Register hooks. Runs from Base::init_tracking(); admin-ajax.php counts
     * as admin context, so both ajax actions are registered there.
     *
     * @return void
     */
    public function init()
    {
        add_action('wp_ajax_' . self::AJAX_ACTION, array($this, 'handle'));
        add_action('wp_ajax_nopriv_' . self::AJAX_ACTION, array($this, 'handle'));
    }

    /**
     * Handle a relayed client event.
     *
     * @return void
     */
    public function handle()
    {
        // Always the same flat 204 reply, so the endpoint cannot be probed for
        // whether tracking is enabled or what data was accepted.
        $reply = function () { wp_die('', '', array('response' => 204)); };

        if (!$this->rate_ok()) $reply();

        $raw = isset($_POST['event']) ? sanitize_text_field(wp_unslash($_POST['event'])) : '';
        if (!isset(self::$allowlist[$raw])) $reply();

        $props = isset($_POST['props']) && is_array($_POST['props']) ? wp_unslash($_POST['props']) : array();

        $properties = array();
        foreach (self::$allowlist[$raw] as $key => $rule)
        {
            if (!isset($props[$key])) continue;

            $value = $this->sanitize($props[$key], $rule);
            if ($value !== null) $properties[$key] = $value;
        }

        // Nothing usable survived sanitizing — skip the capture call entirely.
        if (!count($properties)) $reply();

        (new PostHog())->capture($raw, $properties, array(), 'js');

        $reply();
    }

    /**
     * Simple transient-based per-IP rate limit.
     *
     * @return bool
     */
    protected function rate_ok()
    {
        $ip  = isset($_SERVER['REMOTE_ADDR']) ? preg_replace('/[^0-9a-fA-F:.]/', '', (string) $_SERVER['REMOTE_ADDR']) : '';
        if ($ip === '') return false;

        $key   = 'mec_trk_' . md5($ip);
        $count = (int) get_transient($key);

        if ($count >= self::RATE_LIMIT) return false;

        set_transient($key, $count + 1, HOUR_IN_SECONDS);
        return true;
    }

    /**
     * Sanitize one property against its rule. Returns null when the value must
     * be dropped.
     *
     * @param mixed          $value
     * @param string|array   $rule
     * @return string|int|null
     */
    protected function sanitize($value, $rule)
    {
        if (is_array($rule)) // fixed vocabulary
        {
            $value = sanitize_text_field((string) $value);
            return in_array($value, $rule, true) ? $value : null;
        }

        switch ($rule)
        {
            case 'url':
                // Canonical public URL: scheme + host + path only (no query, no fragment).
                $url  = esc_url_raw((string) $value);
                if ($url === '') return null;
                $parts = wp_parse_url($url);
                if (empty($parts['host'])) return null;
                return $parts['scheme'] . '://' . $parts['host'] . (isset($parts['path']) ? $parts['path'] : '');

            case 'key':
                $value = sanitize_key((string) $value);
                return ($value !== '') ? $value : null;

            case 'hash12':
                $value = preg_replace('/[^a-f0-9]/i', '', (string) $value);
                return (strlen($value) === 12) ? strtolower($value) : null;

            case 'bucket':
                $value = sanitize_key((string) $value);
                return ($value !== '') ? $value : null;

            case 'int':
                return is_numeric($value) ? (int) $value : null;
        }

        return null;
    }
}
