<?php

namespace MEC\Tracking;

/**
 * Reversible usage-tracking toggle in Settings -> General -> Advanced.
 *
 * Renders a single checkbox (via the mec_settings_general_advanced action) and
 * keeps the canonical consent option (PostHog::CONSENT_OPTION) in sync with it.
 * The popup and this toggle both drive that one option, so they never disagree.
 */
class SettingsControl
{
    /**
     * Settings key submitted by the checkbox (stored in mec_options['settings']).
     */
    const SETTING_KEY = 'mec_usage_tracking';

    /**
     * Register hooks.
     *
     * @return void
     */
    public function init()
    {
        add_action('mec_settings_general_advanced', array($this, 'render_field'), 10, 1);

        // Run before PostHog::track_settings_saved (priority 10) so the save that
        // toggles tracking is itself sent (on) or suppressed by the gate (off).
        add_action('mec_saved_options', array($this, 'sync_from_settings'), 5, 1);
    }

    /**
     * Output the checkbox row inside the General -> Advanced section.
     *
     * @param array $settings Current MEC settings (unused; state read from the canonical option).
     * @return void
     */
    public function render_field($settings = array())
    {
        $checked = (get_option(PostHog::CONSENT_OPTION) === 'accepted');
        ?>
        <div class="mec-form-row mec-basvanced-advanced w-hidden">
            <h5 class="mec-form-subtitle"><?php esc_html_e('Usage Tracking', 'modern-events-calendar-lite'); ?></h5>
            <label>
                <input type="hidden" name="mec[settings][<?php echo esc_attr(self::SETTING_KEY); ?>]" value="0" />
                <input value="1" type="checkbox" name="mec[settings][<?php echo esc_attr(self::SETTING_KEY); ?>]" <?php echo $checked ? 'checked="checked"' : ''; ?> /><?php esc_html_e('Share non-sensitive usage data (only feature usage) to help us improve the plugin and guide future development.', 'modern-events-calendar-lite'); ?>
            </label>
            <p class="description" style="margin-top:6px;">
                <strong><?php esc_html_e('Privacy first:', 'modern-events-calendar-lite'); ?></strong>
                <?php esc_html_e('Your data is strictly for internal development and will never be sold or used for ads.', 'modern-events-calendar-lite'); ?>
            </p>
        </div>
        <?php
    }

    /**
     * Sync the checkbox choice into the canonical consent option on save.
     *
     * @param array $final The full options array being saved.
     * @return void
     */
    public function sync_from_settings($final)
    {
        if (!is_array($final) || !isset($final['settings']) || !array_key_exists(self::SETTING_KEY, (array) $final['settings'])) return;

        $choice = !empty($final['settings'][self::SETTING_KEY]) ? 'accepted' : 'declined';

        update_option(PostHog::CONSENT_OPTION, $choice);
        update_option('mec_posthog_consent_time', current_time('mysql'));

        // Replay queued install-lifecycle events when tracking is turned on
        // from Settings (same as accepting the popup).
        if ($choice === 'accepted') (new PostHog())->flush_queue();
    }
}
