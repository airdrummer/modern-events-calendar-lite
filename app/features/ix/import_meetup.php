<?php
/** no direct access **/
defined('MECEXEC') or die();

/** @var MEC_feature_ix $this */

$ix_options = $this->main->get_ix_options();

$active_tab = 'MEC-meetup-import';
include MEC::import('app.features.ix.header', true, true);
?>
<div class="wns-be-container mec-full">
    <div class="wns-be-main">
        <div id="wns-be-content">
            <div class="wns-be-group-tab">
                <div class="mec-container">
                    <div class="import-content">
                        <div class="mec-meetup-import">
                            <?php if($this->action === 'meetup-import-config'): ?>
                            <form id="mec_meetup_import_form" action="<?php echo esc_url($this->main->get_full_url()); ?>" method="POST">
                                <h2 class="mec-form-subtitle mec-page-section"><?php esc_html_e('Connect your account', 'modern-events-calendar-lite'); ?></h2>
                                <p class="description"><?php echo sprintf(esc_html__('In order to import your events from meetup, first you need an OAuth client in meetup. You can create one using the %s link.', 'modern-events-calendar-lite'), '<a href="https://www.meetup.com/api/oauth/list/" target="_blank">https://www.meetup.com/api/oauth/list/</a>'); ?></p>
                                <p class="description"><?php echo sprintf(esc_html__('For the redirect URL, put the %s URL.', 'modern-events-calendar-lite'), '<a href="'.get_home_url().'" target="_blank">'.get_home_url().'</a>'); ?></p>
                                <div class="mec-form-row mec-field">
                                    <div class="mec-field__label">
                                        <label for="mec_ix_meetup_public_key"><?php esc_html_e('Meetup Public Key', 'modern-events-calendar-lite'); ?></label>
                                    </div>
                                    <div class="mec-field__control">
                                        <input type="text" id="mec_ix_meetup_public_key" name="ix[meetup_public_key]" value="<?php echo isset($ix_options['meetup_public_key']) ? esc_attr($ix_options['meetup_public_key']) : ''; ?>" required="required" />
                                    </div>
                                </div>
                                <div class="mec-form-row mec-field">
                                    <div class="mec-field__label">
                                        <label for="mec_ix_meetup_secret_key"><?php esc_html_e('Meetup Secret Key', 'modern-events-calendar-lite'); ?></label>
                                    </div>
                                    <div class="mec-field__control">
                                        <input type="text" id="mec_ix_meetup_secret_key" name="ix[meetup_secret_key]" value="<?php echo isset($ix_options['meetup_secret_key']) ? esc_attr($ix_options['meetup_secret_key']) : ''; ?>" required="required" />
                                    </div>
                                </div>
                                <div class="mec-form-row mec-field">
                                    <div class="mec-field__label">
                                        <label for="mec_ix_meetup_group_name"><?php esc_html_e('Group URL Name', 'modern-events-calendar-lite'); ?></label>
                                    </div>
                                    <div class="mec-field__control">
                                        <input type="text" id="mec_ix_meetup_group_name" name="ix[meetup_group_name]" value="<?php echo isset($ix_options['meetup_group_name']) ? esc_attr($ix_options['meetup_group_name']) : ''; ?>" required="required" />
                                    </div>
                                    <p class="mec-field__help"><?php echo sprintf(esc_html__('If your group URL is https://www.meetup.com/blahblahblah/ then the URL name is %s.', 'modern-events-calendar-lite'), '<strong>blahblahblah</strong>'); ?></p>
                                </div>
                                <div class="mec-form-row mec-field mec-ix-actions">
                                    <div class="mec-field__control">
                                        <input type="hidden" name="mec-ix-action" value="meetup-import-login" />
                                        <button id="mec_ix_meetup_form_button" class="button button-primary" type="submit"><?php esc_html_e('Start', 'modern-events-calendar-lite'); ?></button>
                                    </div>
                                </div>
                            </form>
                            <?php elseif($this->action === 'meetup-import-login'): ?>
                            <div class="mec-ix-meetup-login">
                                <h2 class="mec-form-subtitle mec-page-section"><?php esc_html_e('Connect your account', 'modern-events-calendar-lite'); ?></h2>
                                <p class="description"><?php echo esc_html__('Please use the following button to login to your meetup account and allow your website to access your meetup account.', 'modern-events-calendar-lite'); ?></p>
                                <a class="button button-primary" href="<?php echo esc_url_raw($this->response['login']); ?>"><?php esc_html_e('Login to Meetup', 'modern-events-calendar-lite'); ?></a>
                            </div>
                            <?php elseif($this->action === 'meetup-import-start'): ?>
                            <div class="mec-ix-meetup-started">
                                <?php if($this->response['success'] == 0): ?>
                                <div class="mec-error"><?php echo MEC_kses::element($this->response['error']); ?></div>
                                <a class="button button-primary" href="<?php echo $this->main->URL('backend').'admin.php?page=MEC-ix&tab=MEC-meetup-import'; ?>"><?php esc_html_e('Start Again', 'modern-events-calendar-lite'); ?></a>
                                <?php else: ?>
                                <form id="mec_meetup_do_form" action="<?php echo esc_url($this->main->remove_qs_var('mec-ix-action', $this->main->get_full_url())); ?>" method="POST">
                                    <div class="mec-panel">
                                        <h5 class="mec-panel__title"><?php esc_html_e('Meetup Events', 'modern-events-calendar-lite'); ?></h5>
                                        <div class="mec-success"><?php echo sprintf(esc_html__('We found %s events for %s group. Please select your desired events to import.', 'modern-events-calendar-lite'), '<strong>'.esc_html($this->response['data']['count']).'</strong>', '<strong>'.esc_html($this->response['data']['title']).'</strong>'); ?></div>
                                        <ul class="mec-select-deselect-actions" data-for="#mec_import_meetup_events">
                                            <li data-action="select-all"><?php esc_html_e('Select All', 'modern-events-calendar-lite'); ?></li>
                                            <li data-action="deselect-all"><?php esc_html_e('Deselect All', 'modern-events-calendar-lite'); ?></li>
                                            <li data-action="toggle"><?php esc_html_e('Toggle', 'modern-events-calendar-lite'); ?></li>
                                        </ul>
                                        <ul id="mec_import_meetup_events" class="mec-checklist">
                                            <?php foreach($this->response['data']['events'] as $event): ?>
                                            <li>
                                                <label>
                                                    <input type="checkbox" name="m-events[]" value="<?php echo esc_attr($event['id']); ?>" checked="checked" />
                                                    <span><?php echo sprintf(esc_html__('Event Title: %s Event Date: %s - %s', 'modern-events-calendar-lite'), '<strong>'.esc_html($event['title']).'</strong>', '<strong>'.esc_html($event['start']).'</strong>', '<strong>'.esc_html($event['end']).'</strong>'); ?></span>
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
                                        <input type="hidden" name="mec-ix-action" value="meetup-import-do" />
                                        <input type="hidden" name="ix[meetup_api_key]" value="<?php echo (isset($this->ix['meetup_api_key']) ? esc_attr($this->ix['meetup_api_key']) : ''); ?>" />
                                        <input type="hidden" name="ix[meetup_group_url]" value="<?php echo (isset($this->ix['meetup_group_url']) ? esc_attr($this->ix['meetup_group_url']) : ''); ?>" />
                                        <div class="mec-form-row mec-field mec-ix-actions">
                                            <div class="mec-field__control">
                                                <button id="mec_ix_meetup_import_do_form_button" class="button button-primary" type="submit"><?php esc_html_e('Import', 'modern-events-calendar-lite'); ?></button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                                <?php endif; ?>
                            </div>
                            <?php elseif($this->action == 'meetup-import-do'): ?>
                            <div class="mec-ix-meetup-import-do">
                                <?php if($this->response['success'] == 0): ?>
                                <div class="mec-error"><?php echo MEC_kses::element($this->response['error']); ?></div>
                                <?php else: ?>
                                <div class="mec-success"><?php echo sprintf(esc_html__('%s events successfully imported to your website from meetup.', 'modern-events-calendar-lite'), '<strong>'.count($this->response['data']).'</strong>'); ?></div>
                                <div class="info-msg"><strong><?php esc_html_e('Attention', 'modern-events-calendar-lite'); ?>:</strong> <?php esc_html_e("Although we tried our best to make the events completely compatible with MEC but some modification might be needed. We suggest you to edit the imported listings one by one on MEC edit event page and make sure thay're correct.", 'modern-events-calendar-lite'); ?></div>
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
