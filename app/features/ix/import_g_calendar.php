<?php
/** no direct access **/
defined('MECEXEC') or die();

/** @var MEC_feature_ix $this */

$ix_options = $this->main->get_ix_options();

$active_tab = '';
include MEC::import('app.features.ix.header', true, true);
?>
<div class="wns-be-container mec-full">
    <div class="wns-be-main">
        <div id="wns-be-content">
            <div class="wns-be-group-tab">
                <div class="mec-container">
                    <div class="import-content">
                        <div class="mec-google-import">
                            <form id="mec_google_import_form" action="<?php echo esc_url($this->main->get_full_url()); ?>" method="POST">
                                <h2 class="mec-form-subtitle mec-page-section"><?php esc_html_e('Import from Google Calendar', 'modern-events-calendar-lite'); ?></h2>
                                <p class="description"><?php esc_html_e('This will import all of your Google calendar events into MEC.', 'modern-events-calendar-lite'); ?></p>
                                <div class="mec-form-row mec-field">
                                    <div class="mec-field__label">
                                        <label for="mec_ix_google_import_api_key"><?php esc_html_e('Google API Key', 'modern-events-calendar-lite'); ?></label>
                                    </div>
                                    <div class="mec-field__control">
                                        <input type="text" id="mec_ix_google_import_api_key" name="ix[google_import_api_key]" value="<?php echo (isset($ix_options['google_import_api_key']) ? esc_attr($ix_options['google_import_api_key']) : ''); ?>" />
                                    </div>
                                </div>
                                <div class="mec-form-row mec-field">
                                    <div class="mec-field__label">
                                        <label for="mec_ix_google_import_calendar_id"><?php esc_html_e('Calendar ID', 'modern-events-calendar-lite'); ?></label>
                                    </div>
                                    <div class="mec-field__control">
                                        <input type="text" id="mec_ix_google_import_calendar_id" name="ix[google_import_calendar_id]" value="<?php echo (isset($ix_options['google_import_calendar_id']) ? esc_attr($ix_options['google_import_calendar_id']) : ''); ?>" />
                                    </div>
                                </div>
                                <div class="mec-form-row mec-field">
                                    <div class="mec-field__label">
                                        <label for="mec_ix_google_import_start_date"><?php esc_html_e('Start Date', 'modern-events-calendar-lite'); ?></label>
                                    </div>
                                    <div class="mec-field__control">
                                        <input type="text" id="mec_ix_google_import_start_date" name="ix[google_import_start_date]" value="<?php echo (isset($ix_options['google_import_start_date']) ? esc_attr($ix_options['google_import_start_date']) : date('Y-m-d', strtotime('-1 Month'))); ?>" class="mec_date_picker" />
                                    </div>
                                </div>
                                <div class="mec-form-row mec-field">
                                    <div class="mec-field__label">
                                        <label for="mec_ix_google_import_end_date"><?php esc_html_e('End Date', 'modern-events-calendar-lite'); ?></label>
                                    </div>
                                    <div class="mec-field__control">
                                        <input type="text" id="mec_ix_google_import_end_date" name="ix[google_import_end_date]" value="<?php echo (isset($ix_options['google_import_end_date']) ? esc_attr($ix_options['google_import_end_date']) : date('Y-m-d', strtotime('+3 Months'))); ?>" class="mec_date_picker" />
                                    </div>
                                </div>
                                <div class="mec-form-row mec-field mec-ix-actions">
                                    <div class="mec-field__control">
                                        <input type="hidden" name="mec-ix-action" value="google-calendar-import-start" />
                                        <button id="mec_ix_google_import_form_button" class="button button-primary" type="submit"><?php esc_html_e('Start', 'modern-events-calendar-lite'); ?></button>
                                    </div>
                                </div>
                            </form>
                            <?php if($this->action == 'google-calendar-import-start'): ?>
                            <div class="mec-ix-google-import-started">
                                <?php if($this->response['success'] == 0): ?>
                                <div class="mec-error"><?php echo MEC_kses::element($this->response['error']); ?></div>
                                <?php else: ?>
                                <form id="mec_google_import_do_form" action="<?php echo esc_url($this->main->get_full_url()); ?>" method="POST">
                                    <div class="mec-panel">
                                        <h5 class="mec-panel__title"><?php esc_html_e('Google Calendar Events', 'modern-events-calendar-lite'); ?></h5>
                                        <div class="mec-success"><?php echo sprintf(esc_html__('We found %s events for %s calendar. Please select your desired events to import.', 'modern-events-calendar-lite'), '<strong>'.esc_html($this->response['data']['count']).'</strong>', '<strong>'.esc_html($this->response['data']['title']).'</strong>'); ?></div>
                                        <ul class="mec-select-deselect-actions" data-for="#mec_import_g_calendar_events">
                                            <li data-action="select-all"><?php esc_html_e('Select All', 'modern-events-calendar-lite'); ?></li>
                                            <li data-action="deselect-all"><?php esc_html_e('Deselect All', 'modern-events-calendar-lite'); ?></li>
                                            <li data-action="toggle"><?php esc_html_e('Toggle', 'modern-events-calendar-lite'); ?></li>
                                        </ul>
                                        <ul id="mec_import_g_calendar_events" class="mec-checklist">
                                            <?php $timezone = $this->main->get_timezone(); foreach($this->response['data']['events'] as $event): if(trim($event['title']) == '') continue; ?>
                                            <?php
                                                $date_start = new DateTime((
                                                    (isset($event['start']->date) && trim($event['start']->date))
                                                        ? $event['start']->date
                                                        : $event['start']->dateTime
                                                ));
                                                if($timezone != $this->response['data']['timezone']) $date_start->setTimezone(new DateTimeZone($timezone));

                                                $date_end = new DateTime((
                                                    (isset($event['end']->date) && trim($event['end']->date))
                                                        ? $event['end']->date
                                                        : $event['end']->dateTime
                                                ));
                                                if($timezone != $this->response['data']['timezone']) $date_end->setTimezone(new DateTimeZone($timezone));
                                            ?>
                                            <li>
                                                <label>
                                                    <input type="checkbox" name="g-events[]" value="<?php echo esc_attr($event['id']); ?>" checked="checked" />
                                                    <span><?php echo sprintf(esc_html__('Event Title: %s Event Date: %s - %s', 'modern-events-calendar-lite'), '<strong>'.esc_html($event['title']).'</strong>', '<strong title="'.esc_attr('First Date of Event', 'modern-events-calendar-lite').'">'.($date_start->format('Y-m-d H:i:s')).'</strong>', '<strong title="'.esc_attr('First Date of Event', 'modern-events-calendar-lite').'">'.($date_end->format('Y-m-d H:i:s')).'</strong>'); ?></span>
                                                </label>
                                            </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                    <div class="mec-panel">
                                        <h5 class="mec-panel__title"><?php esc_html_e('Import Options', 'modern-events-calendar-lite'); ?></h5>
                                        <div class="mec-form-row mec-field mec-field--check">
                                            <div class="mec-field__control">
                                                <label class="mec-check">
                                                    <input type="checkbox" name="ix[import_organizers]" value="1" checked="checked" />
                                                    <span><?php esc_html_e('Import Organizers', 'modern-events-calendar-lite'); ?></span>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="mec-form-row mec-field mec-field--check">
                                            <div class="mec-field__control">
                                                <label class="mec-check">
                                                    <input type="checkbox" name="ix[import_locations]" value="1" checked="checked" />
                                                    <span><?php esc_html_e('Import Locations', 'modern-events-calendar-lite'); ?></span>
                                                </label>
                                            </div>
                                        </div>
                                        <input type="hidden" name="mec-ix-action" value="google-calendar-import-do" />
                                        <input type="hidden" name="ix[google_import_api_key]" value="<?php echo (isset($this->ix['google_import_api_key']) ? esc_attr($this->ix['google_import_api_key']) : ''); ?>" />
                                        <input type="hidden" name="ix[google_import_calendar_id]" value="<?php echo (isset($this->ix['google_import_calendar_id']) ? esc_attr($this->ix['google_import_calendar_id']) : ''); ?>" />
                                        <div class="mec-form-row mec-field mec-ix-actions">
                                            <div class="mec-field__control">
                                                <button id="mec_ix_google_import_do_form_button" class="button button-primary" type="submit"><?php esc_html_e('Import', 'modern-events-calendar-lite'); ?></button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                                <?php endif; ?>
                            </div>
                            <?php elseif($this->action == 'google-calendar-import-do'): ?>
                            <div class="mec-ix-google-import-do">
                                <?php if($this->response['success'] == 0): ?>
                                <div class="mec-error"><?php echo MEC_kses::element($this->response['error']); ?></div>
                                <?php else: ?>
                                <div class="mec-success"><?php echo sprintf(esc_html__('%s events successfully imported to your website from Google Calendar.', 'modern-events-calendar-lite'), '<strong>'.count($this->response['data']).'</strong>'); ?></div>
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
</div>
