<?php
/** no direct access **/
defined('MECEXEC') or die();

/** @var MEC_feature_mec $this */

$multilingual = $this->main->is_multilingual();
$locale = $this->main->get_backend_active_locale();

$messages = $this->main->get_messages();
$values = $this->main->get_messages_options(($multilingual ? $locale : NULL));
?>
<div class="mec-page-head">
    <span class="mec-page-eyebrow"><?php esc_html_e('Modern Events Calendar', 'modern-events-calendar-lite'); ?></span>
    <h1><span class="mec-page-mark" aria-hidden="true"><span class="dashicons dashicons-translation"></span></span><?php esc_html_e('MEC Settings', 'modern-events-calendar-lite'); ?><span class="mec-page-title-sep" aria-hidden="true">/</span><?php esc_html_e('Translate Options', 'modern-events-calendar-lite'); ?></h1>
    <p class="mec-page-lead"><?php esc_html_e('Customize the text labels used across calendars, bookings and notifications.', 'modern-events-calendar-lite'); ?></p>
    <?php $this->main->render_settings_page_meta(); ?>
</div>
<hr class="wp-header-end" />
<div class="wns-be-container wns-be-container-sticky">

    <div id="wns-be-infobar">
        <div class="mec-infobar-crumb">
            <span class="c-root"><?php esc_html_e('MEC Settings', 'modern-events-calendar-lite'); ?></span>
            <span class="c-sep">/</span>
            <span class="c-tab"><?php esc_html_e('Translate Options', 'modern-events-calendar-lite'); ?></span>
        </div>
        <div class="mec-search-settings-wrap">
            <i class="mec-sl-magnifier"></i>
            <input id="mec-search-settings" type="text" placeholder="<?php esc_html_e('Search...' , 'modern-events-calendar-lite'); ?>">
        </div>
        <button type="submit" class="dpr-btn dpr-save-btn" form="mec_messages_form"><?php esc_html_e('Save Changes', 'modern-events-calendar-lite'); ?></button>
    </div>

    <div class="wns-be-sidebar">
        <?php $this->main->get_sidebar_menu('messages'); ?>
    </div>

    <div class="wns-be-main messages-settings">

        <div id="wns-be-notification"></div>

        <div id="wns-be-content">
            <div class="wns-be-group-tab">
                <div class="mec-container">
                    <form id="mec_messages_form" data-mec-settings-form="1">
                        <div class="mec-options-fields active">
                            <h2 class="mec-form-subtitle mec-page-section"><?php esc_html_e('Messages', 'modern-events-calendar-lite'); ?></h2>
                            <p class="mec-messages-intro"><?php esc_html_e('Adjust the labels and phrases used across your calendars, booking form and shortcodes. Leave a field empty to keep its default; clearing a field resets it.', 'modern-events-calendar-lite'); ?></p>
                            <?php if($multilingual): ?>
                            <p class="mec-field__help mec-field__help--warn"><?php echo sprintf(esc_html__('You are editing the messages of the %s language. For multilingual websites, translating the phrases from the language files is recommended.', 'modern-events-calendar-lite'), '<strong>' . esc_html($locale) . '</strong>'); ?></p>
                            <?php endif; ?>
                            <?php
                            // Totals for the live "customized" summary
                            $messages_total = 0;
                            $messages_customized = 0;

                            foreach($messages as $category)
                            {
                                foreach($category['messages'] as $key => $message)
                                {
                                    $messages_total++;
                                    if(isset($values[$key]) and trim($values[$key])) $messages_customized++;
                                }
                            }
                            ?>
                            <div class="mec-form-row" id="mec_messages_form_container">
                                <div class="mec-msg-toolbar">
                                    <div class="mec-msg-toolbar__status">
                                        <span class="mec-msg-counter<?php echo $messages_customized ? ' mec-msg-counter--active' : ''; ?>" id="mec_msg_counter"><?php echo esc_html(sprintf(__('%1$s of %2$s customized', 'modern-events-calendar-lite'), number_format_i18n($messages_customized), number_format_i18n($messages_total))); ?></span>
                                    </div>
                                    <div class="mec-msg-toolbar__actions">
                                        <button type="button" class="mec-msg-bulk-toggle" data-mec-msg-bulk="open"><?php esc_html_e('Expand All', 'modern-events-calendar-lite'); ?></button>
                                        <button type="button" class="mec-msg-bulk-toggle" data-mec-msg-bulk="close"><?php esc_html_e('Collapse All', 'modern-events-calendar-lite'); ?></button>
                                    </div>
                                </div>
                                <div class="mec-msg-panels">
                                    <?php
                                    $panel_index = 0;
                                    foreach($messages as $cat_key => $category):
                                        $panel_open = ($panel_index === 0);
                                        $panel_count = count($category['messages']);
                                        $panel_customized = 0;

                                        foreach($category['messages'] as $key => $message)
                                        {
                                            if(isset($values[$key]) and trim($values[$key])) $panel_customized++;
                                        }
                                    ?>
                                    <section class="mec-msg-panel<?php echo $panel_customized ? ' mec-msg-panel--customized' : ''; ?>" data-mec-msg-panel data-status="<?php echo $panel_open ? 'open' : 'close'; ?>">
                                        <button type="button" class="mec-msg-panel__head" aria-expanded="<?php echo $panel_open ? 'true' : 'false'; ?>" aria-controls="mec-msg-panel-<?php echo esc_attr($cat_key); ?>">
                                            <span class="mec-msg-panel__chevron dashicons dashicons-arrow-down" aria-hidden="true"></span>
                                            <span class="mec-msg-panel__title"><?php echo esc_html($category['category']['name']); ?></span>
                                            <span class="mec-msg-panel__meta">
                                                <span class="mec-msg-badge"><?php echo esc_html(sprintf(_n('%s label', '%s labels', $panel_count, 'modern-events-calendar-lite'), number_format_i18n($panel_count))); ?></span>
                                                <span class="mec-msg-badge mec-msg-badge--custom" data-mec-msg-custom<?php echo $panel_customized ? '' : ' hidden'; ?>><?php echo esc_html(sprintf(__('%s customized', 'modern-events-calendar-lite'), number_format_i18n($panel_customized))); ?></span>
                                            </span>
                                        </button>
                                        <div class="mec-msg-panel__body" id="mec-msg-panel-<?php echo esc_attr($cat_key); ?>">
                                            <?php foreach($category['messages'] as $key => $message): ?>
                                            <?php
                                                $value = (isset($values[$key]) and trim($values[$key])) ? stripslashes($values[$key]) : '';
                                                $field_id = 'mec_m_'.$key;
                                                $default_length = (function_exists('mb_strlen') ? mb_strlen($message['default']) : strlen($message['default']));
                                                $is_long = $default_length > 60;
                                            ?>
                                            <div class="mec-form-row mec-field mec-msg-row<?php echo trim($value) !== '' ? ' mec-msg-row--customized' : ''; ?>" data-mec-msg-row>
                                                <div class="mec-field__label">
                                                    <label for="<?php echo esc_attr($field_id); ?>"><?php echo esc_html($message['label']); ?></label>
                                                </div>
                                                <div class="mec-field__control">
                                                    <?php if($is_long): ?>
                                                    <textarea id="<?php echo esc_attr($field_id); ?>" name="mec[messages][<?php echo esc_attr($key); ?>]" rows="2" placeholder="<?php echo esc_attr($message['default']); ?>"><?php echo esc_textarea($value); ?></textarea>
                                                    <?php else: ?>
                                                    <input id="<?php echo esc_attr($field_id); ?>" name="mec[messages][<?php echo esc_attr($key); ?>]" type="text" placeholder="<?php echo esc_attr($message['default']); ?>" value="<?php echo esc_attr($value); ?>" />
                                                    <?php endif; ?>
                                                    <button type="button" class="mec-msg-reset" data-mec-msg-reset title="<?php esc_attr_e('Reset to default', 'modern-events-calendar-lite'); ?>" aria-label="<?php echo esc_attr(sprintf(__('Reset "%s" to default', 'modern-events-calendar-lite'), $message['label'])); ?>">
                                                        <span class="dashicons dashicons-undo" aria-hidden="true"></span>
                                                    </button>
                                                    <?php if($is_long): ?>
                                                    <p class="mec-field__help mec-msg-default"><?php echo sprintf(esc_html__('Default: %s', 'modern-events-calendar-lite'), '<q>' . esc_html($message['default']) . '</q>'); ?></p>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </section>
                                    <?php
                                        $panel_index++;
                                    endforeach;
                                    ?>
                                </div>
                            </div>
                            <div class="mec-form-row">
                                <?php wp_nonce_field('mec_options_form'); ?>
                                <?php if($multilingual): ?>
                                <input name="mec_locale" type="hidden" value="<?php echo esc_attr($locale); ?>" />
                                <?php endif; ?>
                                <button style="display: none;" id="mec_messages_form_button" class="button button-primary mec-button-primary" type="submit"><?php esc_html_e('Save Changes', 'modern-events-calendar-lite'); ?></button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div id="wns-be-footer">
        <button type="submit" class="dpr-btn dpr-save-btn" form="mec_messages_form"><?php esc_html_e('Save Changes', 'modern-events-calendar-lite'); ?></button>
    </div>

</div>

<?php
$this->getFactory()->params('footer', function()
{
    ?>
    <script>
    jQuery(document).ready(function()
    {
        jQuery(".dpr-save-btn").on('click', function(event)
        {
            event.preventDefault();
            jQuery("#mec_messages_form_button").trigger('click');
        });
    });

    jQuery("#mec_messages_form").on('submit', function(event)
    {
        event.preventDefault();

        // A save is already running (double click): ignore this one.
        if(jQuery(".dpr-save-btn").hasClass('loading')) return;

        // Add loading Class to the button
        jQuery(".dpr-save-btn").addClass('loading').text("<?php echo esc_js(esc_attr__('Saving ...', 'modern-events-calendar-lite')); ?>");
        try
        {

        var messages = jQuery("#mec_messages_form").serialize();

        // Shared save feedback (backend.js); a stale cached backend.js without it
        // only ends the loading state, as before.
        var saveDone = function(ok)
        {
            if(typeof window.mecSettingsSaveFeedback === 'function') return window.mecSettingsSaveFeedback(jQuery('#mec_messages_form'), ok);
            jQuery('.dpr-save-btn').removeClass('loading').text("<?php echo esc_js(esc_attr__('Save Changes', 'modern-events-calendar-lite')); ?>");
            if(typeof window.mecSettingsSaving === 'function') window.mecSettingsSaving(false);
        };

        jQuery.ajax(
        {
            type: "POST",
            url: ajaxurl,
            timeout: 30000,
            data: "action=mec_save_messages&"+messages,
            beforeSend: function()
            {
                if(typeof window.mecSettingsSaving === 'function') window.mecSettingsSaving(true);
            },
            success: function(data)
            {
                saveDone(window.mecSettingsSaveOk && window.mecSettingsSaveOk(data));
            },
            error: function(jqXHR, textStatus, errorThrown)
            {
                saveDone(false);
            }
        });
        }
        catch(err)
        {
            // Never leave the button locked if something throws before the request is sent.
            jQuery('.dpr-save-btn').removeClass('loading').text("<?php echo esc_js(esc_attr__('Save Changes', 'modern-events-calendar-lite')); ?>");
            if(typeof window.mecSettingsSaving === 'function') window.mecSettingsSaving(false);
            throw err;
        }
    });
    </script>
    <?php
});

$this->getFactory()->params('footer', function()
{
    ?>
    <script>
    jQuery(document).ready(function($)
    {
        var $container = $('#mec_messages_form_container');
        if(!$container.length) return;

        var L10N = (window.mec_admin_localize && window.mec_admin_localize.l10n) || {};
        var counterTpl = L10N.msg_customized_count || '%1$s of %2$s customized';
        var panelTpl = L10N.msg_panel_customized || '%s customized';

        var $panels = $container.find('[data-mec-msg-panel]');
        var $rows = $container.find('[data-mec-msg-row]');

        var setPanelStatus = function($panel, status)
        {
            $panel.attr('data-status', status);
            $panel.children('.mec-msg-panel__head').attr('aria-expanded', status === 'open' ? 'true' : 'false');
        };

        var syncCounts = function()
        {
            var customized = 0;

            $panels.each(function()
            {
                var $panel = $(this);
                var count = $panel.find('.mec-msg-row--customized').length;
                var $badge = $panel.find('[data-mec-msg-custom]');

                if(count > 0)
                {
                    $badge.removeAttr('hidden').text(panelTpl.replace('%s', count));
                    $panel.addClass('mec-msg-panel--customized');
                }
                else
                {
                    $badge.attr('hidden', 'hidden');
                    $panel.removeClass('mec-msg-panel--customized');
                }

                customized += count;
            });

            $('#mec_msg_counter')
                .text(counterTpl.replace('%1$s', customized).replace('%2$s', $rows.length))
                .toggleClass('mec-msg-counter--active', customized > 0);
        };

        var syncRow = function($row)
        {
            var value = String($row.find('input, textarea').first().val() || '').trim();
            $row.toggleClass('mec-msg-row--customized', value !== '');
        };

        // Toggle a panel
        $container.on('click', '.mec-msg-panel__head', function(event)
        {
            event.preventDefault();
            var $panel = $(this).closest('[data-mec-msg-panel]');
            setPanelStatus($panel, $panel.attr('data-status') === 'open' ? 'close' : 'open');
        });

        // Expand / Collapse all
        $container.on('click', '[data-mec-msg-bulk]', function(event)
        {
            event.preventDefault();
            var status = $(this).attr('data-mec-msg-bulk');
            $panels.each(function(){ setPanelStatus($(this), status); });
        });

        // Reset a single field to its default (empty value = default)
        $container.on('click', '[data-mec-msg-reset]', function(event)
        {
            event.preventDefault();
            var $row = $(this).closest('[data-mec-msg-row]');
            var $field = $row.find('input, textarea').first();

            $field.val('').trigger('input').trigger('change');
            $field.focus();
        });

        // Live "customized" state
        $container.on('input', '[data-mec-msg-row] input, [data-mec-msg-row] textarea', function()
        {
            syncRow($(this).closest('[data-mec-msg-row]'));
            syncCounts();
        });

        // Settings search integration: while a query is active, matched rows
        // may live inside collapsed panels, so expand everything and restore
        // the previous state once the search is cleared.
        var searchState = null;

        $('#mec-search-settings').on('input', function()
        {
            var query = String(this.value || '').trim();

            if(query)
            {
                if(!searchState) searchState = $panels.map(function(){ return $(this).attr('data-status'); }).get();
                $panels.each(function(){ setPanelStatus($(this), 'open'); });
            }
            else if(searchState)
            {
                var states = searchState;
                searchState = null;
                $panels.each(function(i){ setPanelStatus($(this), states[i] || 'close'); });
            }
        });

        syncCounts();
    });
    </script>
    <?php
});