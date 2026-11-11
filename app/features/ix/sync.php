<?php
/** no direct access **/
defined('MECEXEC') or die();

/** @var MEC_feature_ix $this */

$ix = $this->main->get_ix_options();

$active_tab = 'MEC-sync';
include MEC::import('app.features.ix.header', true, true);
?>
<div class="wns-be-container mec-full">
    <div class="wns-be-main">
        <div id="wns-be-content">
            <div class="wns-be-group-tab">
                <div class="mec-container">
                    <div class="sync-content">
                        <?php if(!$this->main->getPRO()): ?>
                        <div class="info-msg"><?php echo sprintf(esc_html__("%s is required to use synchronization feature.", 'modern-events-calendar-lite'), $this->main->pro_notice_link()); ?></div>
                        <?php else: ?>
                        <form id="mec_ix_sync_form" action="<?php echo esc_url($this->main->get_full_url()); ?>" method="POST">
                            <div class="mec-form-row mec-field mec-field--check mec-syn-schedule">
                                <div class="mec-field__control">
                                    <input type="hidden" name="ix[sync_g_import]" value="0" />
                                    <label class="mec-check" for="mec_ix_sync_g_import">
                                        <input type="checkbox" id="mec_ix_sync_g_import" name="ix[sync_g_import]" value="1" <?php echo (isset($ix['sync_g_import']) and $ix['sync_g_import'] == '1') ? 'checked="checked"' : ''; ?> onchange="jQuery('#mec_sync_g_import_cron').toggleClass('mec-util-hidden');" />
                                        <span><?php esc_html_e('Auto Google Import', 'modern-events-calendar-lite'); ?></span>
                                    </label>
                                    <?php $cron = MEC_ABSPATH.'app'.DS.'crons'.DS.'g-import.php'; ?>
                                    <p id="mec_sync_g_import_cron" class="mec-field__help mec-cron-note <?php echo (isset($ix['sync_g_import']) and $ix['sync_g_import'] == '1') ? '' : 'mec-util-hidden'; ?>"><strong><?php esc_html_e('Important Note', 'modern-events-calendar-lite'); ?>: </strong><?php echo sprintf(esc_html__("Set a cronjob to call %s file by php atleast once per day otherwise it won't import Google Calendar events.", 'modern-events-calendar-lite'), '<code>'.esc_html($cron).'</code>'); ?></p>
                                </div>
                            </div>
                            <div class="mec-form-row mec-field mec-field--check mec-syn-schedule">
                                <div class="mec-field__control">
                                    <input type="hidden" name="ix[sync_g_export]" value="0" />
                                    <label class="mec-check" for="mec_ix_sync_g_export">
                                        <input type="checkbox" id="mec_ix_sync_g_export" name="ix[sync_g_export]" value="1" <?php echo (isset($ix['sync_g_export']) and $ix['sync_g_export'] == '1') ? 'checked="checked"' : ''; ?> onchange="jQuery('#mec_sync_g_export_cron').toggleClass('mec-util-hidden');" />
                                        <span><?php esc_html_e('Auto Google Export', 'modern-events-calendar-lite'); ?></span>
                                    </label>
                                    <?php $cron = MEC_ABSPATH.'app'.DS.'crons'.DS.'g-export.php'; ?>
                                    <div id="mec_sync_g_export_cron" class="mec-cron-note <?php echo (isset($ix['sync_g_export']) and $ix['sync_g_export'] == '1') ? '' : 'mec-util-hidden'; ?>">
                                        <div class="mec-form-row mec-field mec-field--check">
                                            <div class="mec-field__control">
                                                <input type="hidden" name="ix[sync_g_export_attendees]" value="0" />
                                                <label class="mec-check">
                                                    <input type="checkbox" name="ix[sync_g_export_attendees]" value="1" <?php echo (isset($ix['sync_g_export_attendees']) and $ix['sync_g_export_attendees'] == '1') ? 'checked="checked"' : ''; ?> />
                                                    <span><?php esc_html_e('Export Attendees', 'modern-events-calendar-lite'); ?></span>
                                                </label>
                                            </div>
                                        </div>
                                        <p class="mec-field__help"><strong><?php esc_html_e('Important Note', 'modern-events-calendar-lite'); ?>: </strong><?php echo sprintf(esc_html__("Set a cronjob to call %s file by php atleast once per day otherwise it won't export your website events into Google Calendar.", 'modern-events-calendar-lite'), '<code>'.esc_html($cron).'</code>'); ?></p>
                                    </div>
                                </div>
                            </div>

                            <?php if(false): // Disabled for Now ?>
                            <div class="mec-form-row mec-field mec-field--check mec-syn-schedule">
                                <div class="mec-field__control">
                                    <input type="hidden" name="ix[sync_f_import]" value="0" />
                                    <label class="mec-check" for="mec_ix_sync_f_import">
                                        <input type="checkbox" id="mec_ix_sync_f_import" name="ix[sync_f_import]" value="1" <?php echo (isset($ix['sync_f_import']) and $ix['sync_f_import'] == '1') ? 'checked="checked"' : ''; ?> onchange="jQuery('#mec_sync_f_import_cron').toggleClass('mec-util-hidden');" />
                                        <span><?php esc_html_e('Auto Facebook Import', 'modern-events-calendar-lite'); ?></span>
                                    </label>
                                    <?php $cron = MEC_ABSPATH.'app'.DS.'crons'.DS.'f-import.php'; ?>
                                    <p id="mec_sync_f_import_cron" class="mec-field__help mec-cron-note <?php echo (isset($ix['sync_f_import']) and $ix['sync_f_import'] == '1') ? '' : 'mec-util-hidden'; ?>"><strong><?php esc_html_e('Important Note', 'modern-events-calendar-lite'); ?>: </strong><?php echo sprintf(esc_html__("Set a cronjob to call %s file by php atleast once per day otherwise it won't import any event from Facebook.", 'modern-events-calendar-lite'), '<code>'.esc_html($cron).'</code>'); ?></p>
                                </div>
                            </div>
                            <?php endif; ?>

                            <div class="mec-form-row mec-field mec-field--check mec-syn-schedule">
                                <div class="mec-field__control">
                                    <input type="hidden" name="ix[sync_meetup_import]" value="0" />
                                    <label class="mec-check" for="mec_ix_sync_meetup_import">
                                        <input type="checkbox" id="mec_ix_sync_meetup_import" name="ix[sync_meetup_import]" value="1" <?php echo (isset($ix['sync_meetup_import']) and $ix['sync_meetup_import'] == '1') ? 'checked="checked"' : ''; ?> onchange="jQuery('#mec_sync_meetup_import_cron').toggleClass('mec-util-hidden');" />
                                        <span><?php esc_html_e('Auto Meetup Import', 'modern-events-calendar-lite'); ?></span>
                                    </label>
                                    <?php $cron = MEC_ABSPATH.'app'.DS.'crons'.DS.'meetup-import.php'; ?>
                                    <p id="mec_sync_meetup_import_cron" class="mec-field__help mec-cron-note <?php echo (isset($ix['sync_meetup_import']) and $ix['sync_meetup_import'] == '1') ? '' : 'mec-util-hidden'; ?>"><strong><?php esc_html_e('Important Note', 'modern-events-calendar-lite'); ?>: </strong><?php echo sprintf(esc_html__("Set a cronjob to call %s file by php atleast once per day otherwise it won't import any event from Meetup.", 'modern-events-calendar-lite'), '<code>'.esc_html($cron).'</code>'); ?></p>
                                </div>
                            </div>

                            <div class="mec-panel mec-syn-schedule">
                                <h5 class="mec-panel__title"><?php esc_html_e('Auto set cronjobs (Once Daily)', 'modern-events-calendar-lite'); ?></h5>
                                <p class="description"><?php esc_html_e('First you need to enable the above options for each to be able to use this.', 'modern-events-calendar-lite'); ?></p>
                                <p class="description"><?php esc_html_e('If you cannot set CronJob on your server, you can use the options below. Please make sure to NOT use the following options and setting up the server manually together.', 'modern-events-calendar-lite'); ?></p>
                                <div class="mec-form-row mec-field mec-field--check">
                                    <div class="mec-field__control">
                                        <input type="hidden" name="ix[sync_g_import_auto]" value="0" />
                                        <label class="mec-check" for="mec_ix_sync_g_import_auto">
                                            <input type="checkbox" id="mec_ix_sync_g_import_auto" name="ix[sync_g_import_auto]" value="1" <?php echo (isset($ix['sync_g_import_auto']) and $ix['sync_g_import_auto'] == '1') ? 'checked="checked"' : ''; ?> />
                                            <span><?php esc_html_e('Google import', 'modern-events-calendar-lite'); ?></span>
                                        </label>
                                    </div>
                                </div>
                                <div class="mec-form-row mec-field mec-field--check">
                                    <div class="mec-field__control">
                                        <input type="hidden" name="ix[sync_g_export_auto]" value="0" />
                                        <label class="mec-check" for="mec_ix_sync_g_export_auto">
                                            <input type="checkbox" id="mec_ix_sync_g_export_auto" name="ix[sync_g_export_auto]" value="1" <?php echo (isset($ix['sync_g_export_auto']) and $ix['sync_g_export_auto'] == '1') ? 'checked="checked"' : ''; ?> />
                                            <span><?php esc_html_e('Google export', 'modern-events-calendar-lite'); ?></span>
                                        </label>
                                    </div>
                                </div>
                                <div class="mec-form-row mec-field mec-field--check">
                                    <div class="mec-field__control">
                                        <input type="hidden" name="ix[sync_meetup_import_auto]" value="0" />
                                        <label class="mec-check" for="mec_ix_sync_meetup_import_auto">
                                            <input type="checkbox" id="mec_ix_sync_meetup_import_auto" name="ix[sync_meetup_import_auto]" value="1" <?php echo (isset($ix['sync_meetup_import_auto']) and $ix['sync_meetup_import_auto'] == '1') ? 'checked="checked"' : ''; ?> />
                                            <span><?php esc_html_e('Meetup import', 'modern-events-calendar-lite'); ?></span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="mec-form-row mec-field mec-ix-actions">
                                <div class="mec-field__control">
                                    <input type="hidden" name="mec-ix-action" value="save-sync-options" />
                                    <button class="button button-primary" type="submit"><?php esc_html_e('Save', 'modern-events-calendar-lite'); ?></button>
                                </div>
                            </div>
                        </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
