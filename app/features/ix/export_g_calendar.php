<?php
/** no direct access **/
defined('MECEXEC') or die();

/** @var MEC_feature_ix $this */

$events = $this->main->get_events(-1, ['publish', 'pending', 'future', 'draft', 'private']);
$ix_options = $this->main->get_ix_options();

// Start the export process if token is exists
if(isset($ix_options['google_export_token']) && $ix_options['google_export_token']) $this->action = 'google-calendar-export-start';

$active_tab = 'MEC-g-calendar-export';
include MEC::import('app.features.ix.header', true, true);
?>
<div class="wns-be-container mec-full">
    <div class="wns-be-main">
        <div id="wns-be-content">
            <div class="wns-be-group-tab">
                <div class="mec-container">
                    <div class="export-content">
                        <div class="mec-export-events-g-calendar">
                            <h2 class="mec-form-subtitle mec-page-section"><?php esc_html_e('Add events to Google Calendar', 'modern-events-calendar-lite'); ?></h2>
                            <p class="description"><?php esc_html_e("Add your desired website events to your Google Calendar.", 'modern-events-calendar-lite'); ?> <?php echo sprintf(esc_html__('You should set %s as redirect page in Google App Console.', 'modern-events-calendar-lite'), '<code>'.esc_url($this->main->add_qs_vars(array('mec-ix-action'=>'google-calendar-export-get-token'), $this->main->URL('backend').'admin.php?page=MEC-ix&tab=MEC-g-calendar-export')).'</code>'); ?></p>
                            <form id="mec_g_calendar_export_form_authenticate" action="<?php echo esc_url($this->main->get_full_url()); ?>" method="POST">
                                <div class="mec-form-row mec-field">
                                    <div class="mec-field__label">
                                        <label for="mec_ix_google_export_client_id"><?php esc_html_e('App Client ID', 'modern-events-calendar-lite'); ?></label>
                                    </div>
                                    <div class="mec-field__control">
                                        <input type="text" id="mec_ix_google_export_client_id" name="ix[google_export_client_id]" value="<?php echo (isset($ix_options['google_export_client_id']) ? esc_attr($ix_options['google_export_client_id']) : ''); ?>" />
                                    </div>
                                </div>
                                <div class="mec-form-row mec-field">
                                    <div class="mec-field__label">
                                        <label for="mec_ix_google_export_client_secret"><?php esc_html_e('App Client Secret', 'modern-events-calendar-lite'); ?></label>
                                    </div>
                                    <div class="mec-field__control">
                                        <input type="text" id="mec_ix_google_export_client_secret" name="ix[google_export_client_secret]" value="<?php echo (isset($ix_options['google_export_client_secret']) ? esc_attr($ix_options['google_export_client_secret']) : ''); ?>" />
                                    </div>
                                </div>
                                <div class="mec-form-row mec-field">
                                    <div class="mec-field__label">
                                        <label for="mec_ix_google_export_calendar_id"><?php esc_html_e('Calendar ID', 'modern-events-calendar-lite'); ?></label>
                                    </div>
                                    <div class="mec-field__control">
                                        <input type="text" id="mec_ix_google_export_calendar_id" name="ix[google_export_calendar_id]" value="<?php echo (isset($ix_options['google_export_calendar_id']) ? esc_attr($ix_options['google_export_calendar_id']) : ''); ?>" />
                                    </div>
                                </div>
                                <div class="mec-form-row mec-field mec-ix-actions">
                                    <div class="mec-field__control">
                                        <button id="mec_ix_google_export_authenticate_form_button" class="button button-primary" type="submit"><?php esc_html_e('Authenticate', 'modern-events-calendar-lite'); ?></button>
                                    </div>
                                </div>
                                <p id="mec_ix_google_export_authenticate_message" class="mec-ix-message mec-util-hidden"></p>
                            </form>
                        </div>
                        <?php if($this->action == 'google-calendar-export-start'): ?>
                        <div class="mec-panel">
                            <form id="mec_g_calendar_export_form_do">
                                <h5 class="mec-panel__title"><?php esc_html_e('Website Events', 'modern-events-calendar-lite'); ?></h5>
                                <ul class="mec-select-deselect-actions" data-for="#mec_export_g_calendar_events">
                                    <li data-action="select-all"><?php esc_html_e('Select All', 'modern-events-calendar-lite'); ?></li>
                                    <li data-action="deselect-all"><?php esc_html_e('Deselect All', 'modern-events-calendar-lite'); ?></li>
                                    <li data-action="toggle"><?php esc_html_e('Toggle', 'modern-events-calendar-lite'); ?></li>
                                </ul>
                                <ul id="mec_export_g_calendar_events" class="mec-checklist">
                                    <?php foreach($events as $event): ?>
                                    <li>
                                        <label>
                                            <input type="checkbox" name="mec-events[]" value="<?php echo esc_attr($event->ID); ?>" checked="checked" />
                                            <span><strong><?php echo esc_html($event->post_title); ?></strong></span>
                                        </label>
                                    </li>
                                    <?php endforeach; ?>
                                </ul>
                                <h5 class="mec-panel__title"><?php esc_html_e('Import Options', 'modern-events-calendar-lite'); ?></h5>
                                <div class="mec-form-row mec-field mec-field--check">
                                    <div class="mec-field__control">
                                        <label class="mec-check">
                                            <input type="checkbox" name="export_attendees" value="1" />
                                            <span><?php esc_html_e('Export Attendees', 'modern-events-calendar-lite'); ?></span>
                                        </label>
                                    </div>
                                </div>
                                <div class="mec-form-row mec-field mec-ix-actions">
                                    <div class="mec-field__control">
                                        <button id="mec_ix_google_export_do_form_button" class="button button-primary" type="submit"><?php esc_html_e('Add to Google Calendar', 'modern-events-calendar-lite'); ?></button>
                                    </div>
                                </div>
                                <p id="mec_ix_google_export_do_message" class="mec-ix-message mec-util-hidden"></p>
                            </form>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

<?php $this->factory->params('footer', function()
{
    ?>
    <script>
    jQuery("#mec_g_calendar_export_form_authenticate").on('submit', function(e)
    {
        e.preventDefault();

        // Message
        var $message = jQuery('#mec_ix_google_export_authenticate_message');

        // Hide the Message
        $message.hide();

        // Add loading Class to the button
        jQuery("#mec_ix_google_export_authenticate_form_button").addClass('loading').text("<?php esc_attr_e('Checking ...', 'modern-events-calendar-lite'); ?>");

        var options = jQuery("#mec_g_calendar_export_form_authenticate").serialize();
        jQuery.ajax(
        {
            type: "POST",
            url: ajaxurl,
            data: "action=mec_ix_g_calendar_authenticate&"+options,
            dataType: "json",
            success: function(data)
            {
                // Remove the loading Class to the button
                setTimeout(function(){
                    jQuery("#mec_ix_google_export_authenticate_form_button").removeClass('loading').text("<?php esc_attr_e('Authenticate', 'modern-events-calendar-lite'); ?>");
                }, 1000);

                // Remove the classes
                $message.removeClass('mec-error').removeClass('mec-success');

                if(data.success) jQuery('#mec_ix_google_export_authenticate_message').addClass('mec-success');
                else jQuery('#mec_ix_google_export_authenticate_message').addClass('mec-error');

                $message.html(data.message).show();
            },
            error: function(jqXHR, textStatus, errorThrown)
            {
                // Remove the loading Class to the button
                setTimeout(function()
                {
                    jQuery("#mec_ix_google_export_authenticate_form_button").removeClass('loading').text("<?php esc_attr_e('Authenticate', 'modern-events-calendar-lite'); ?>");
                }, 1000);
            }
        });
    });

    jQuery("#mec_g_calendar_export_form_do").on('submit', function(e)
    {
        e.preventDefault();

        // Message
        var $message = jQuery('#mec_ix_google_export_do_message');

        // Hide the Message
        $message.hide();

        // Add loading Class to the button
        jQuery("#mec_ix_google_export_do_form_button").addClass('loading').text("<?php esc_attr_e('Exporting ...', 'modern-events-calendar-lite'); ?>");

        var options = jQuery("#mec_g_calendar_export_form_do").serialize();
        jQuery.ajax(
        {
            type: "POST",
            url: ajaxurl,
            data: "action=mec_ix_add_to_g_calendar&"+options,
            dataType: "json",
            success: function(data)
            {
                // Remove the loading Class to the button
                setTimeout(function()
                {
                    jQuery("#mec_ix_google_export_do_form_button").removeClass('loading').text("<?php esc_attr_e('Add to Google Calendar', 'modern-events-calendar-lite'); ?>");
                }, 1000);

                // Remove the classes
                $message.removeClass('mec-error').removeClass('mec-success');

                if(data.success) jQuery('#mec_ix_google_export_do_message').addClass('mec-success');
                else jQuery('#mec_ix_google_export_do_message').addClass('mec-error');

                $message.html(data.message).show();
            },
            error: function(jqXHR, textStatus, errorThrown)
            {
                // Remove the loading Class to the button
                setTimeout(function(){
                    jQuery("#mec_ix_google_export_do_form_button").removeClass('loading').text("<?php esc_attr_e('Add to Google Calendar', 'modern-events-calendar-lite'); ?>");
                }, 1000);
            }
        });
    });
    </script>
    <?php
});
