<?php

namespace MEC\Tracking;

/**
 * Plugin activation lifecycle event (marketing plan, events 1A / 1B).
 *
 * Fires mec_lite_activated / mec_pro_activated exactly once per activation,
 * with activation_type distinguishing a first install from a reactivation.
 * Because activation happens BEFORE the consent popup can possibly have been
 * answered, PostHog::capture() queues the event and replays it if/when the
 * admin accepts tracking.
 */
class Activator
{
    /**
     * Option key proving the plugin was activated at least once before, used
     * to tell new_install from reactivation.
     */
    const ONCE_OPTION = 'mec_posthog_activation_seen';

    /**
     * The activation callback (register_activation_hook).
     *
     * @param bool $network_wide Whether the plugin is network activated.
     * @return void
     */
    public static function activate($network_wide = false)
    {
        // Load the transport directly: on activation we cannot rely on MEC's
        // composer autoloader having been required in every load order.
        if (!class_exists('MEC\Tracking\PostHog', false))
        {
            require_once dirname(__FILE__) . '/PostHog.php';
        }

        $edition = defined('MEC_EDITION') ? MEC_EDITION : 'lite';

        $activation_type = get_option(self::ONCE_OPTION) ? 'reactivation' : 'new_install';
        update_option(self::ONCE_OPTION, 1, false);

        global $wp_version;

        (new PostHog())->capture('mec_' . $edition . '_activated', array(
            'activation_type'   => $activation_type,
            'network_active'    => $network_wide ? 1 : 0,
            'wordpress_version' => isset($wp_version) ? $wp_version : null,
        ));
    }
}
