<?php
/** no direct access **/
defined('MECEXEC') or die();

/** @var MEC_feature_ix $this */

$ix_options = $this->main->get_ix_options();

$active_tab = 'MEC-f-calendar-import';
include MEC::import('app.features.ix.header', true, true);
?>
<div class="wns-be-container mec-full">
    <div class="wns-be-main">
        <div id="wns-be-content">
            <div class="wns-be-group-tab">
                <div class="mec-container">
                    <div class="import-content">
                        <?php if(version_compare(PHP_VERSION, '5.4.0', '<')): ?>
                        <p class="mec-error"><?php esc_html_e('The Facebook SDK requires PHP version 5.4 or higher.', 'modern-events-calendar-lite'); ?></p>
                        <?php else: ?>
                        <div class="mec-facebook-import">
                            <form id="mec_facebook_import_form" action="<?php echo esc_url($this->main->get_full_url()); ?>" method="POST">
                                <h2 class="mec-form-subtitle mec-page-section"><?php esc_html_e('Import from Facebook Calendar', 'modern-events-calendar-lite'); ?></h2>
                                <p class="description"><?php esc_html_e('Import all of your Facebook events into MEC.', 'modern-events-calendar-lite'); ?> <a href="https://webnus.net/dox/modern-events-calendar/import-facebook-events/" target="_blank"><?php esc_html_e('Documentation', 'modern-events-calendar-lite'); ?></a></p>
                                <div class="mec-form-row mec-field">
                                    <div class="mec-field__label">
                                        <label for="mec_ix_facebook_app_token"><?php esc_html_e('Facebook Page Access Token', 'modern-events-calendar-lite'); ?></label>
                                    </div>
                                    <div class="mec-field__control">
                                        <input type="text" id="mec_ix_facebook_app_token" name="ix[facebook_app_token]" value="<?php echo (isset($ix_options['facebook_app_token']) ? esc_attr($ix_options['facebook_app_token']) : ''); ?>" />
                                    </div>
                                </div>
                                <div class="mec-form-row mec-field">
                                    <div class="mec-field__label">
                                        <label for="mec_ix_facebook_import_page_link"><?php esc_html_e('Facebook Page Link', 'modern-events-calendar-lite'); ?></label>
                                    </div>
                                    <div class="mec-field__control">
                                        <input type="text" id="mec_ix_facebook_import_page_link" name="ix[facebook_import_page_link]" value="<?php echo (isset($ix_options['facebook_import_page_link']) ? esc_attr($ix_options['facebook_import_page_link']) : ''); ?>" />
                                    </div>
                                </div>
                                <div class="mec-form-row mec-field mec-ix-actions">
                                    <div class="mec-field__control">
                                        <input type="hidden" name="mec-ix-action" value="facebook-calendar-import-start" />
                                        <button id="mec_ix_facebook_import_form_button" class="button button-primary" type="submit"><?php esc_html_e('Start', 'modern-events-calendar-lite'); ?></button>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <?php if($this->action == 'facebook-calendar-import-start'): ?>
                        <div class="mec-ix-facebook-import-started">
                            <?php if($this->response['success'] == 0): ?>
                            <div class="mec-error"><?php echo MEC_kses::element($this->response['message']); ?></div>
                            <?php else: ?>
                            <form id="mec_facebook_import_do_form" action="<?php echo esc_url($this->main->get_full_url()); ?>" method="POST">
                                <div class="mec-panel">
                                    <h5 class="mec-panel__title"><?php esc_html_e('Facebook Events', 'modern-events-calendar-lite'); ?></h5>
                                    <div class="mec-success"><?php echo sprintf(esc_html__('We found %s events for %s page. Please select your desired events to import.', 'modern-events-calendar-lite'), '<strong>'.esc_html($this->response['data']['count']).'</strong>', '<strong>'.esc_html($this->response['data']['name']).'</strong>'); ?></div>
                                    <ul class="mec-select-deselect-actions" data-for="#mec_import_f_calendar_events">
                                        <li data-action="select-all"><?php esc_html_e('Select All', 'modern-events-calendar-lite'); ?></li>
                                        <li data-action="deselect-all"><?php esc_html_e('Deselect All', 'modern-events-calendar-lite'); ?></li>
                                        <li data-action="toggle"><?php esc_html_e('Toggle', 'modern-events-calendar-lite'); ?></li>
                                    </ul>
                                    <ul id="mec_import_f_calendar_events" class="mec-checklist">
                                        <?php foreach($this->response['data']['events'] as $event): if(trim($event['name']) == '') continue; ?>
                                        <li>
                                            <label>
                                                <input type="checkbox" name="f-events[]" value="<?php echo esc_attr($event['id']); ?>" checked="checked" />
                                                <span><?php echo sprintf(esc_html__('Event Title: %s', 'modern-events-calendar-lite'), '<strong>'.esc_html($event['name']).'</strong>'); ?></span>
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
                                                <input type="checkbox" name="ix[import_locations]" value="1" checked="checked" />
                                                <span><?php esc_html_e('Import Locations', 'modern-events-calendar-lite'); ?></span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="mec-form-row mec-field mec-field--check">
                                        <div class="mec-field__control">
                                            <label class="mec-check">
                                                <input type="checkbox" name="ix[import_link_event]" value="1" />
                                                <span><?php esc_html_e('Import Facebook Link as Event Link', 'modern-events-calendar-lite'); ?></span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="mec-form-row mec-field mec-field--check">
                                        <div class="mec-field__control">
                                            <label class="mec-check">
                                                <input type="checkbox" name="ix[import_link_more_info]" value="1" />
                                                <span><?php esc_html_e('Import Facebook Link as More Info Link', 'modern-events-calendar-lite'); ?></span>
                                            </label>
                                        </div>
                                    </div>
                                    <input type="hidden" name="mec-ix-action" value="facebook-calendar-import-do" />
                                    <input type="hidden" name="ix[facebook_import_page_link]" value="<?php echo (isset($this->ix['facebook_import_page_link']) ? esc_attr($this->ix['facebook_import_page_link']) : ''); ?>" />
                                    <input type="hidden" name="ix[facebook_app_token]" value="<?php echo (isset($this->ix['facebook_app_token']) ? esc_attr($this->ix['facebook_app_token']) : ''); ?>" />
                                    <div class="mec-form-row mec-field mec-ix-actions">
                                        <div class="mec-field__control">
                                            <button id="mec_ix_facebook_import_do_form_button" class="button button-primary" type="submit"><?php esc_html_e('Import', 'modern-events-calendar-lite'); ?></button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                            <?php endif; ?>
                        </div>
                        <?php elseif($this->action == 'facebook-calendar-import-do'): ?>
                        <div class="mec-ix-facebook-import-do">
                            <?php if($this->response['success'] == 0): ?>
                            <div class="mec-error"><?php echo MEC_kses::element($this->response['message']); ?></div>
                            <?php else: ?>
                            <div class="mec-success"><?php echo sprintf(esc_html__('%s events successfully imported to your website from Facebook Calendar.', 'modern-events-calendar-lite'), '<strong>'.count($this->response['data']).'</strong>'); ?></div>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
