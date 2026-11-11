<?php
/** no direct access **/
defined('MECEXEC') or die();

/** @var MEC_feature_ix $this */

$third_parties = $this->main->get_integrated_plugins_for_import();

$active_tab = 'MEC-thirdparty';
include MEC::import('app.features.ix.header', true, true);
?>
<div class="wns-be-container mec-full">
    <div class="wns-be-main">
        <div id="wns-be-content">
            <div class="wns-be-group-tab">
                <div class="mec-container">
                    <div class="import-content">
                        <h2 class="mec-form-subtitle mec-page-section"><?php esc_html_e('Third Party Plugins', 'modern-events-calendar-lite'); ?></h2>
                        <form id="mec_thirdparty_import_form" action="<?php echo esc_url($this->main->get_full_url()); ?>" method="POST">
                            <p class="description"><?php echo sprintf(esc_html__("You can import events from the following integrated plugins to %s.", 'modern-events-calendar-lite'), '<strong>'.esc_html__('Modern Events Calendar', 'modern-events-calendar-lite').'</strong>'); ?></p>
                            <div class="mec-form-row mec-field">
                                <div class="mec-field__label">
                                    <label for="third_party"><?php esc_html_e('Source Plugin', 'modern-events-calendar-lite'); ?></label>
                                </div>
                                <div class="mec-field__control">
                                    <select name="ix[third-party]" id="third_party" title="<?php esc_attr_e('Third Party', 'modern-events-calendar-lite') ?>">
                                        <?php foreach($third_parties as $third_party=>$label): ?>
                                            <option <?php echo ((isset($this->ix['third-party']) and $this->ix['third-party'] == $third_party) ? 'selected="selected"' : ''); ?> value="<?php echo esc_attr($third_party); ?>"><?php echo esc_html($label); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <input type="hidden" name="mec-ix-action" value="thirdparty-import-start" />
                                    <button class="button button-primary"><?php esc_html_e('Start', 'modern-events-calendar-lite'); ?></button>
                                </div>
                            </div>
                        </form>

                        <?php if($this->action == 'thirdparty-import-start'): ?>
                        <div class="mec-ix-thirdparty-import-started">
                            <?php if($this->response['success'] == 0): ?>
                            <div class="mec-error"><?php echo MEC_kses::element($this->response['message']); ?></div>
                            <?php elseif(isset($this->response['data']['count']) && !$this->response['data']['count']): ?>
                            <div class="mec-error"><?php echo esc_html__('No events found!', 'modern-events-calendar-lite'); ?></div>
                            <?php else: ?>
                            <form id="mec_thirdparty_import_do_form" action="<?php echo esc_url($this->main->get_full_url()); ?>" method="POST">
                                <div id="mec_ix_thirdparty_import_options_wrapper">
                                    <div class="mec-panel">
                                        <h5 class="mec-panel__title"><?php esc_html_e('Found Events', 'modern-events-calendar-lite'); ?></h5>
                                        <div class="mec-success"><?php echo sprintf(esc_html__('We found %s events. Please select your desired events to import.', 'modern-events-calendar-lite'), '<strong>'.esc_html($this->response['data']['count']).'</strong>'); ?></div>
                                        <ul class="mec-select-deselect-actions" data-for="#mec_import_thirdparty_events">
                                            <li data-action="select-all"><?php esc_html_e('Select All', 'modern-events-calendar-lite'); ?></li>
                                            <li data-action="deselect-all"><?php esc_html_e('Deselect All', 'modern-events-calendar-lite'); ?></li>
                                            <li data-action="toggle"><?php esc_html_e('Toggle', 'modern-events-calendar-lite'); ?></li>
                                        </ul>
                                        <ul id="mec_import_thirdparty_events" class="mec-checklist">
                                            <?php foreach($this->response['data']['events'] as $event): if(trim($event->post_title) == '') continue; ?>
                                            <li>
                                                <label>
                                                    <input type="checkbox" name="tp-events[]" value="<?php echo esc_attr($event->ID); ?>" checked="checked" />
                                                    <span><?php echo sprintf(esc_html__('Event Title: %s', 'modern-events-calendar-lite'), '<strong>'.esc_html($event->post_title).'</strong>'); ?></span>
                                                </label>
                                            </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                    <div class="mec-panel">
                                        <h5 class="mec-panel__title"><?php esc_html_e('Import Options', 'modern-events-calendar-lite'); ?></h5>

                                        <?php if(!in_array($this->ix['third-party'], array('event-espresso', 'events-manager-single', 'events-manager-recurring'))): ?>
                                        <div class="mec-form-row mec-field mec-field--check">
                                            <div class="mec-field__control">
                                                <label class="mec-check">
                                                    <input type="checkbox" name="ix[import_organizers]" value="1" checked="checked" />
                                                    <span><?php
                                                    if($this->ix['third-party'] == 'weekly-class') esc_html_e('Import Instructors', 'modern-events-calendar-lite');
                                                    else esc_html_e('Import Organizers', 'modern-events-calendar-lite');
                                                    ?></span>
                                                </label>
                                            </div>
                                        </div>
                                        <?php endif; ?>

                                        <?php if(in_array($this->ix['third-party'], array('the-events-calendar'))): ?>
                                        <div class="mec-form-row mec-field mec-field--check">
                                            <div class="mec-field__control">
                                                <label class="mec-check">
                                                    <input type="checkbox" name="ix[import_author]" value="1" checked="checked" />
                                                    <span><?php esc_html_e('Import Author', 'modern-events-calendar-lite'); ?></span>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="mec-form-row mec-field mec-field--check">
                                            <div class="mec-field__control">
                                                <label class="mec-check">
                                                    <input type="checkbox" name="ix[import_tickets]" value="1" />
                                                    <span><?php esc_html_e('Import Tickets', 'modern-events-calendar-lite'); ?></span>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="mec-form-row mec-field mec-field--check">
                                            <div class="mec-field__control">
                                                <label class="mec-check">
                                                    <input type="checkbox" name="ix[import_bookings]" value="1" />
                                                    <span><?php esc_html_e('Import Attendees and Bookings', 'modern-events-calendar-lite'); ?></span>
                                                </label>
                                            </div>
                                        </div>
                                        <?php endif; ?>

                                        <?php if($this->ix['third-party'] != 'bookly'): ?>
                                        <div class="mec-form-row mec-field mec-field--check">
                                            <div class="mec-field__control">
                                                <label class="mec-check">
                                                    <input type="checkbox" name="ix[import_locations]" value="1" checked="checked" />
                                                    <span><?php esc_html_e('Import Locations', 'modern-events-calendar-lite'); ?></span>
                                                </label>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                        <div class="mec-form-row mec-field mec-field--check">
                                            <div class="mec-field__control">
                                                <label class="mec-check">
                                                    <input type="checkbox" name="ix[import_categories]" value="1" checked="checked" />
                                                    <span><?php
                                                        if($this->ix['third-party'] == 'weekly-class') esc_html_e('Import Class Types', 'modern-events-calendar-lite');
                                                        else esc_html_e('Import Categories', 'modern-events-calendar-lite');
                                                    ?></span>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="mec-form-row mec-field mec-field--check">
                                            <div class="mec-field__control">
                                                <label class="mec-check">
                                                    <input type="checkbox" name="ix[import_featured_image]" value="1" checked="checked" />
                                                    <span><?php esc_html_e('Import Featured Image', 'modern-events-calendar-lite'); ?></span>
                                                </label>
                                            </div>
                                        </div>
                                        <input type="hidden" name="mec-ix-action" value="thirdparty-import-do" />
                                        <input type="hidden" name="ix[third-party]" value="<?php echo esc_attr($this->ix['third-party']); ?>" />
                                        <?php wp_nonce_field('mec_ix_thirdparty_import'); ?>
                                    </div>
                                </div>
                                <div class="mec-form-row mec-field mec-ix-actions">
                                    <div class="mec-field__control">
                                        <button id="mec_ix_thirdparty_import_do_form_button" class="button button-primary" type="submit"><?php esc_html_e('Import', 'modern-events-calendar-lite'); ?></button>
                                    </div>
                                </div>
                            </form>
                            <div id="mec_ix_thirdparty_import_logs"></div>
                        <?php endif; ?>
                        </div>
                        <?php elseif($this->action == 'thirdparty-import-do'): ?>
                        <div class="mec-ix-thirdparty-import-do">
                            <?php if($this->response['success'] == 0): ?>
                            <div class="mec-error"><?php echo MEC_kses::element($this->response['message']); ?></div>
                            <?php else: ?>
                            <div class="mec-success"><?php echo sprintf(esc_html__('%s events successfully imported to your website.', 'modern-events-calendar-lite'), '<strong>'.esc_html($this->response['data']).'</strong>'); ?></div>
                            <div class="info-msg"><strong><?php esc_html_e('Attention', 'modern-events-calendar-lite'); ?>:</strong> <?php esc_html_e("Although we tried our best to make the events completely compatible with MEC but some modification might be needed. We suggest you to edit the imported listings one by one on MEC edit event page and make sure they are correct.", 'modern-events-calendar-lite'); ?></div>
                            <?php endif; ?>
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
    jQuery("#mec_thirdparty_import_do_form").on("submit", function(event)
    {
        event.preventDefault();

        mec_ix_thirdparty_start();
        mec_ix_thirdparty_import(1);
    });

    function mec_ix_thirdparty_import(step)
    {
        var data = jQuery("#mec_thirdparty_import_do_form").serialize();
        jQuery.ajax(
        {
            type: "POST",
            url: ajaxurl,
            data: "action=mec_ix_thirdparty_import&step="+step+"&"+data,
            success: function(data)
            {
                mec_ix_thirdparty_log(data.imported + " / " + data.all + " <?php echo esc_js(esc_html__('imported.', 'modern-events-calendar-lite')); ?>");

                if(data.finished)
                {
                    mec_ix_thirdparty_log('<div class="info-msg"><strong><?php esc_html_e('Attention', 'modern-events-calendar-lite'); ?>:</strong> <?php esc_html_e("Although we tried our best to make the events completely compatible with MEC but some modification might be needed. We suggest you to edit the imported listings one by one on MEC edit event page and make sure they are correct.", 'modern-events-calendar-lite'); ?></div>');
                    mec_ix_thirdparty_finish('success');
                }
                else
                {
                    setTimeout(function()
                    {
                        mec_ix_thirdparty_import(data.next_step);
                    }, 500);
                }
            },
            error: function(jqXHR, textStatus, errorThrown)
            {
                mec_ix_thirdparty_log("<?php echo esc_js(esc_html__('An unknown error occurred! Most probably due to an error on the server.', 'modern-events-calendar-lite')); ?>", 'error');
                mec_ix_thirdparty_finish('log');
            }
        });
    }

    function mec_ix_thirdparty_log(message, type)
    {
        let html = '<p>'+message+'</p>';

        if(type === 'error') html = '<div class="mec-error">'+message+'</div>';
        else if(type === 'success') html = '<div class="mec-success">'+message+'</div>';

        const $log_wrapper = jQuery("#mec_ix_thirdparty_import_logs");
        $log_wrapper.prepend(html);
    }

    function mec_ix_thirdparty_start()
    {
        // Elements
        const $button = jQuery("#mec_ix_thirdparty_import_do_form_button");
        const $form_wrapper = jQuery("#mec_ix_thirdparty_import_options_wrapper");

        // Add loading Class to the button
        $button.prop("disabled", "disabled").text("<?php echo esc_js(esc_html__('Importing ... ', 'modern-events-calendar-lite')); ?>");

        // Slide the form up
        $form_wrapper.slideUp(800);

        mec_ix_thirdparty_log("<?php echo esc_js(esc_html__("The import is started ...", 'modern-events-calendar-lite')); ?>");
        mec_ix_thirdparty_log("<?php echo esc_js(esc_html__("Please keep this page open untill you see the finish message.", 'modern-events-calendar-lite')); ?>");
    }

    function mec_ix_thirdparty_finish(type)
    {
        mec_ix_thirdparty_log("<strong><?php echo esc_js(esc_html__("The import is finished.", 'modern-events-calendar-lite')); ?></strong>", type);
        jQuery("#mec_ix_thirdparty_import_do_form_button").remove();
    }
    </script>
    <?php
});
