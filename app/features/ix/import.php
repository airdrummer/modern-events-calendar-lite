<?php
/** no direct access **/
defined('MECEXEC') or die();

/** @var MEC_feature_ix $this */

$third_parties = $this->main->get_integrated_plugins_for_import();

$active_tab = 'MEC-import';
include MEC::import('app.features.ix.header', true, true);
?>
<div class="wns-be-container mec-full">
    <div class="wns-be-main">
        <div id="wns-be-content">
            <div class="wns-be-group-tab">
                <div class="mec-container">
                    <div class="import-content">
                        <div class="mec-panel">
                            <h5 class="mec-panel__title"><?php esc_html_e('Import MEC XML Feed', 'modern-events-calendar-lite'); ?></h5>
                            <form id="mec_import_xml_form" action="<?php echo esc_url($this->main->get_full_url()); ?>" method="POST" enctype="multipart/form-data">
                                <p class="description"><?php echo sprintf(esc_html__("You can import %s events from another website to this website. You just need an XML feed of the events that can be exported from source website!", 'modern-events-calendar-lite'), '<strong>'.esc_html__('Modern Events Calendar', 'modern-events-calendar-lite').'</strong>'); ?></p>
                                <div class="mec-form-row mec-field">
                                    <div class="mec-field__control">
                                        <input type="file" name="feed" id="feed" title="<?php esc_attr_e('XML Feed', 'modern-events-calendar-lite'); ?>">
                                        <input type="hidden" name="mec-ix-action" value="import-start-xml">
                                        <?php wp_nonce_field('mec_import_start_upload'); ?>
                                        <button class="button button-primary"><?php esc_html_e('Upload & Import', 'modern-events-calendar-lite'); ?></button>
                                    </div>
                                </div>
                            </form>

                            <?php if($this->action == 'import-start-xml'): ?>
                            <div class="mec-ix-import-started">
                                <?php if($this->response['success'] == 0): ?>
                                <div class="mec-error"><?php echo MEC_kses::element($this->response['message']); ?></div>
                                <?php else: ?>
                                <div class="mec-success"><?php echo MEC_kses::element($this->response['message']); ?></div>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                        </div>

                        <div class="mec-panel">
                            <h5 class="mec-panel__title"><?php esc_html_e('Import .ics File', 'modern-events-calendar-lite'); ?></h5>
                            <?php if($this->getPRO()): ?>
                            <form id="mec_import_ics_form" action="<?php echo esc_url($this->main->get_full_url()); ?>" method="POST" enctype="multipart/form-data">
                                <p class="description"><?php echo sprintf(esc_html__("ICS format supports by many different service providers like Facebook. Apple Calendar etc. You can import your ics file into the %s using this form.", 'modern-events-calendar-lite'), '<strong>'.esc_html__('Modern Events Calendar', 'modern-events-calendar-lite').'</strong>'); ?></p>
                                <div class="mec-form-row mec-field">
                                    <div class="mec-field__control">
                                        <input type="file" name="feed" id="feed" title="<?php esc_attr_e('ICS Feed', 'modern-events-calendar-lite'); ?>">
                                        <input type="hidden" name="mec-ix-action" value="import-start-ics">
                                        <?php wp_nonce_field('mec_import_start_upload'); ?>
                                        <button class="button button-primary"><?php esc_html_e('Upload & Import', 'modern-events-calendar-lite'); ?></button>
                                    </div>
                                </div>
                            </form>
                            <?php else: ?>
                            <div class="info-msg"><?php echo sprintf(esc_html__("%s is required to use this feature.", 'modern-events-calendar-lite'), $this->main->pro_notice_link()); ?></div>
                            <?php endif; ?>

                            <?php if($this->action == 'import-start-ics'): ?>
                            <div class="mec-ix-import-started">
                                <?php if($this->response['success'] == 0): ?>
                                <div class="mec-error"><?php echo MEC_kses::element($this->response['message']); ?></div>
                                <?php else: ?>
                                <div class="mec-success"><?php echo MEC_kses::element($this->response['message']); ?></div>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                        </div>

                        <?php do_action('mec_import_item', $this); ?>

                        <div class="mec-panel">
                            <h5 class="mec-panel__title"><?php esc_html_e('Import Booking CSV File', 'modern-events-calendar-lite'); ?></h5>
                            <?php if($this->getPRO()): ?>
                            <form id="mec_import_csv_booking_form" action="<?php echo esc_url($this->main->get_full_url()); ?>" method="POST" enctype="multipart/form-data">
                                <p class="description"><?php echo sprintf(esc_html__("You can export bookings from %s using the booking menu in source website. You need a CSV export and then you're able to simply import it using this form in to your target website.", 'modern-events-calendar-lite'), '<strong>'.esc_html__('Modern Events Calendar', 'modern-events-calendar-lite').'</strong>'); ?></p>
                                <p class="description"><?php echo sprintf(esc_html__("If you want to create the booking file yourself you can check %s as a sample file.", 'modern-events-calendar-lite'), '<a href="'.esc_url($this->main->asset('files/sample.csv')).'">sample.csv</a>'); ?></p>
                                <p class="description mec-field__help--warn"><?php echo esc_html__("Please note that you should create (or imports) events and tickets before importing the bookings otherwise booking won't import due to lack of data.", 'modern-events-calendar-lite'); ?></p>
                                <div class="mec-form-row mec-field">
                                    <div class="mec-field__control">
                                        <input type="file" name="feed" id="feed" title="<?php esc_attr_e('CSV File', 'modern-events-calendar-lite'); ?>">
                                        <input type="hidden" name="mec-ix-action" value="import-start-bookings">
                                        <?php wp_nonce_field('mec_import_start_upload'); ?>
                                        <button class="button button-primary"><?php esc_html_e('Upload & Import', 'modern-events-calendar-lite'); ?></button>
                                    </div>
                                </div>
                            </form>
                            <?php else: ?>
                            <div class="info-msg"><?php echo sprintf(esc_html__("%s is required to use this feature.", 'modern-events-calendar-lite'), $this->main->pro_notice_link()); ?></div>
                            <?php endif; ?>

                            <?php if($this->action == 'import-start-bookings'): ?>
                            <div class="mec-ix-import-started">
                                <?php if($this->response['success'] == 0): ?>
                                <div class="mec-error"><?php echo MEC_kses::element($this->response['message']); ?></div>
                                <?php else: ?>
                                <div class="mec-success"><?php echo MEC_kses::element($this->response['message']); ?></div>
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
