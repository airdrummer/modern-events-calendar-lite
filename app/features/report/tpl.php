<?php
/** no direct access **/
defined('MECEXEC') or die();

/** @var MEC_feature_report $this */

$tab = $_GET['tab'] ?? 'selective';
$current_event_id = $_GET['event_id'] ?? 0;

$events = $this->main->get_events(-1, ['pending', 'draft', 'future', 'publish']);
$date_format = get_option('date_format');

// License status for the unified page header (render only, same as settings).
$mec_license_token = ($this->isProBuild() and class_exists('MEC_license')) ? MEC_license::instance()->licensed() : false;
$mec_partner = ($this->isProBuild() and class_exists('MEC_license')) ? MEC_license::instance()->partner_licence() : null;
$mec_license_active = ($mec_license_token or is_array($mec_partner));

$mec_report_tabs = [
    'selective' => ['label' => esc_html__('Selective Email', 'modern-events-calendar-lite'), 'icon' => 'mec-sl-envelope'],
    'mass' => ['label' => esc_html__('Mass Email', 'modern-events-calendar-lite'), 'icon' => 'mec-sl-paper-plane'],
    'export_purge' => ['label' => esc_html__('Export & Purge', 'modern-events-calendar-lite'), 'icon' => 'mec-sl-drawer'],
];
?>
<div class="wrap" id="mec-wrap">
    <div class="mec-page-head">
        <span class="mec-page-eyebrow"><?php esc_html_e('Modern Events Calendar', 'modern-events-calendar-lite'); ?></span>
        <h1><span class="mec-page-mark" aria-hidden="true"><i class="mec-sl-chart"></i></span><?php esc_html_e('Booking Report', 'modern-events-calendar-lite'); ?></h1>
        <p class="mec-page-lead"><?php esc_html_e('Track bookings and attendees, email them selectively or in bulk, and export or purge historical bookings.', 'modern-events-calendar-lite'); ?></p>
        <div class="mec-settings-meta">
            <span class="mec-tag">MEC <?php echo esc_html(MEC_VERSION); ?></span>
            <?php if($this->isProBuild()): ?>
                <?php if($mec_license_active): ?>
                    <span class="mec-tag ok"><span class="d"></span><?php esc_html_e('License active', 'modern-events-calendar-lite'); ?></span>
                <?php else: ?>
                    <span class="mec-tag warn"><span class="d"></span><?php esc_html_e('License not activated', 'modern-events-calendar-lite'); ?></span>
                <?php endif; ?>
            <?php else: ?>
                <span class="mec-tag"><?php esc_html_e('Lite (free)', 'modern-events-calendar-lite'); ?></span>
            <?php endif; ?>
        </div>
    </div>
    <hr class="wp-header-end" />
    <nav class="mec-tabbar" aria-label="<?php esc_attr_e('Booking report tools', 'modern-events-calendar-lite'); ?>">
        <?php foreach($mec_report_tabs as $slug => $report_tab): ?>
            <a href="<?php echo esc_url($this->main->add_qs_var('tab', $slug)); ?>" class="mec-tabbar__tab<?php echo $tab === $slug ? ' mec-tabbar__tab--active' : ''; ?>"<?php echo $tab === $slug ? ' aria-current="page"' : ''; ?>>
                <i class="<?php echo esc_attr($report_tab['icon']); ?>" aria-hidden="true"></i>
                <span><?php echo esc_html($report_tab['label']); ?></span>
            </a>
        <?php endforeach; ?>
    </nav>
    <div class="wns-be-container mec-full">
        <div class="wns-be-main">
            <div id="wns-be-content">
                <div class="wns-be-group-tab">
                    <div class="mec-container booking-report-container">
                        <?php if($tab === 'mass'): ?>
                        <h2 class="mec-form-subtitle mec-page-section"><?php esc_html_e('Mass Email', 'modern-events-calendar-lite'); ?></h2>
                        <p class="description"><?php echo esc_html__('Using this section, you can select all the attendees by event and offer them a new event.', 'modern-events-calendar-lite'); ?></p>
                        <div class="mec-report-select-event-wrap">
                            <?php if(count($events)): ?>
                            <form method="post" id="mec_report_mass_action_form">
                                <div class="mec-panel">
                                    <h5 class="mec-panel__title"><?php esc_html_e('Select Events', 'modern-events-calendar-lite'); ?></h5>
                                    <ul class="mec-checklist mec-report-events-list">
                                        <?php
                                            foreach($events as $event)
                                            {
                                                $id = $event->ID;
                                                if($this->main->get_original_event($id) !== $id) $id = $this->main->get_original_event($id);

                                                $sold_tickets = $this->getBook()->get_all_sold_tickets($id);
                                                echo '<li><label class="mec-check"><input type="checkbox" name="events[]" value="'.esc_attr($id).'" class="mec-report-events"><span>'.sprintf(esc_html__('%s (%s sold tickets)', 'modern-events-calendar-lite'), $event->post_title, $sold_tickets).'</span></label></li>';
                                            }
                                        ?>
                                    </ul>
                                </div>
                                <div class="mec-panel">
                                    <h5 class="mec-panel__title"><?php esc_html_e('Action', 'modern-events-calendar-lite'); ?></h5>
                                    <div class="mec-form-row mec-field mec-field--check">
                                        <div class="mec-field__control">
                                            <label class="mec-check">
                                                <input type="radio" name="task" value="suggest" onchange="jQuery('#mec_report_suggest_new_event_options').toggleClass('w-hidden')">
                                                <span><?php esc_html_e('Suggest another event', 'modern-events-calendar-lite'); ?></span>
                                            </label>
                                        </div>
                                    </div>
                                    <div id="mec_report_suggest_new_event_options" class="w-hidden">
                                        <div class="mec-form-row mec-field">
                                            <div class="mec-field__label">
                                                <label for="mec_new_event"><?php esc_html_e('New Event', 'modern-events-calendar-lite'); ?></label>
                                            </div>
                                            <div class="mec-field__control">
                                                <select name="new_event" id="mec_new_event">
                                                    <option value="">-----</option>
                                                    <?php foreach($events as $event): ?>
                                                        <option value="<?php echo esc_attr($event->ID); ?>"><?php echo $event->post_title; ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mec-form-row mec-field mec-ix-actions">
                                        <div class="mec-field__control">
                                            <?php wp_nonce_field('mec_report_mass'); ?>
                                            <input type="hidden" name="action" value="mec_report_mass">
                                            <button class="button button-primary" type="submit"><?php esc_html_e('Send', 'modern-events-calendar-lite'); ?></button>
                                        </div>
                                    </div>
                                </div>
                                <div id="mec_report_mass_message" class="mec-report-mass-message"></div>
                            </form>
                            <?php endif; ?>
                        </div>
                        <?php elseif($tab === 'selective'): ?>
                        <h2 class="mec-form-subtitle mec-page-section"><?php esc_html_e('Selective Email', 'modern-events-calendar-lite'); ?></h2>
                        <p class="description"><?php echo esc_html__('Using this section, you can see the list of participant attendees by the order of date.', 'modern-events-calendar-lite'); ?></p>
                        <div class="mec-report-select-event-wrap">
                            <div class="w-row">
                                <div class="w-col-sm-12">
                                    <div class="mec-form-row mec-field">
                                        <div class="mec-field__label">
                                            <label for="mec-report-event-id"><?php esc_html_e('Event', 'modern-events-calendar-lite'); ?></label>
                                        </div>
                                        <div class="mec-field__control">
                                            <select name="mec-report-event-id" id="mec-report-event-id" class="mec-reports-selectbox mec-reports-selectbox-event">
                                                <option value="none"><?php echo esc_html__( 'Select event' , 'modern-events-calendar-lite'); ?></option>
                                                <?php
                                                    if(count($events))
                                                    {
                                                        foreach($events as $event)
                                                        {
                                                            $id = $event->ID;
                                                            if($this->main->get_original_event($id) !== $id) $id = $this->main->get_original_event($id);

                                                            $start_date = get_post_meta($id, 'mec_start_date', true);

                                                            echo '<option value="'.esc_attr($id).'" '.(($current_event_id == $id) ? 'selected' : '').'>' . sprintf(esc_html__('%s (from %s)', 'modern-events-calendar-lite'), $event->post_title, date($date_format, strtotime($start_date))) . '</option>';
                                                        }
                                                    }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mec-report-sendmail-wrap"><div class="w-row"><div class="w-col-sm-12"></div></div></div>
                        <div class="mec-report-backtoselect-wrap"><div class="w-row"><div class="w-col-sm-12"><button class="button button-primary"><?php echo esc_html__('Back to list', 'modern-events-calendar-lite'); ?></button></div></div></div>
                        <div class="mec-report-selected-event-attendees-wrap"><div class="w-row"><div class="w-col-sm-12"></div></div></div>
                        <div class="mec-report-sendmail-form-wrap">
                            <div class="w-row">
                                <div class="w-col-sm-12">
                                    <?php $send_email_label = esc_html__('Send Email', 'modern-events-calendar-lite'); ?>
                                    <div class="mec-send-email-form-wrap mec-panel">
                                        <h5 class="mec-panel__title"><?php echo esc_html__('Bulk Email', 'modern-events-calendar-lite'); ?></h5>
                                        <h4 class="mec-send-email-count"><?php echo sprintf(esc_html__('You are sending email to %s attendees', 'modern-events-calendar-lite'), '<span>0</span>'); ?></h4>
                                        <div class="mec-form-row mec-field">
                                            <div class="mec-field__control">
                                                <input type="text" class="widefat" id="mec-send-email-subject" placeholder="<?php echo esc_html__('Email Subject', 'modern-events-calendar-lite'); ?>"/>
                                            </div>
                                        </div>
                                        <div id="mec-send-email-editor-wrap"></div>
                                        <div class="mec-form-row mec-field mec-field--check">
                                            <div class="mec-field__control">
                                                <label class="mec-check"><input type="checkbox" id="mec-send-admin-copy" value="1"><span><?php echo esc_html__('Send a copy to admin', 'modern-events-calendar-lite'); ?></span></label>
                                            </div>
                                        </div>
                                        <p class="description"><?php echo esc_html__('You can use the following placeholders', 'modern-events-calendar-lite'); ?></p>
                                        <ul class="mec-placeholders-list">
                                            <li><span>%%name%%</span>: <?php echo esc_html__('Attendee Name', 'modern-events-calendar-lite'); ?></li>
                                        </ul>
                                        <div id="mec-send-email-message" class="mec-util-hidden mec-error"></div>
                                        <input type="hidden" id="mec-send-email-label" value="<?php echo esc_attr($send_email_label); ?>" />
                                        <input type="hidden" id="mec-send-email-label-loading" value="<?php echo esc_attr__('Loading...', 'modern-events-calendar-lite'); ?>" />
                                        <input type="hidden" id="mec-send-email-success" value="<?php echo esc_attr__('Emails successfully sent', 'modern-events-calendar-lite'); ?>" />
                                        <input type="hidden" id="mec-send-email-no-user-selected" value="<?php echo esc_attr__('No user selected!', 'modern-events-calendar-lite'); ?>" />
                                        <input type="hidden" id="mec-send-email-empty-subject" value="<?php echo esc_attr__('Email subject cannot be empty!', 'modern-events-calendar-lite'); ?>" />
                                        <input type="hidden" id="mec-send-email-empty-content" value="<?php echo esc_attr__('Email content cannot be empty!', 'modern-events-calendar-lite'); ?>" />
                                        <input type="hidden" id="mec-send-email-error" value="<?php echo esc_attr__('An error occurred. Please try again.', 'modern-events-calendar-lite'); ?>" />
                                        <span class="mec-send-email-button"><?php echo esc_html($send_email_label); ?></span>
                                    </div>
                                    <?php wp_enqueue_editor(); ?>
                                </div>
                            </div>
                        </div>
                        <?php else: ?>
                        <h2 class="mec-form-subtitle mec-page-section"><?php esc_html_e('Export & Purge', 'modern-events-calendar-lite'); ?></h2>
                        <?php if(isset($_GET['mec_export_purge_done'])): ?>
                            <?php
                                $cnt = isset($_GET['mec_export_purge_count']) ? intval($_GET['mec_export_purge_count']) : 0;
                                $url = isset($_GET['mec_export_purge_url']) ? esc_url_raw($_GET['mec_export_purge_url']) : '';
                                $url = $url ? esc_url_raw(urldecode($url)) : '';
                            ?>
                            <div class="mec-success mec-report-notice">
                                <p>
                                    <?php echo sprintf(esc_html__('%d rows exported. File saved and purge completed.', 'modern-events-calendar-lite'), $cnt); ?>
                                    <?php if($url): ?>
                                        <br><?php echo sprintf(esc_html__('Download: %s', 'modern-events-calendar-lite'), '<a href="'.esc_url($url).'" target="_blank">'.esc_html($url).'</a>'); ?>
                                    <?php endif; ?>
                                </p>
                            </div>
                        <?php endif; ?>
                        <p class="description">
                            <?php echo esc_html__( 'Export all bookings before the cutoff date to a CSV file, email it to the recipients, and then delete those outdated booking entries.', 'modern-events-calendar-lite' ); ?>
                            <?php echo esc_html__( 'WooCommerce orders will remain untouched.', 'modern-events-calendar-lite' ); ?>
                        </p>
                        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                            <?php wp_nonce_field('mec_export_purge'); ?>
                            <input type="hidden" name="action" value="mec_export_purge" />
                            <div id="mec-export-purge-form" class="mec-export-purge-form">
                                <div class="mec-form-row mec-field">
                                    <div class="mec-field__label">
                                        <label for="mec_export_purge_cutoff"><?php esc_html_e('Cutoff Date', 'modern-events-calendar-lite'); ?></label>
                                    </div>
                                    <div class="mec-field__control">
                                        <input type="date" id="mec_export_purge_cutoff" name="mec_export_purge_cutoff" required />
                                        <p class="mec-field__help"><?php esc_html_e('Bookings with start date before this date are included.', 'modern-events-calendar-lite'); ?></p>
                                    </div>
                                </div>
                                <div class="mec-form-row mec-field">
                                    <div class="mec-field__label">
                                        <label for="mec_export_purge_emails"><?php esc_html_e('Email Recipients', 'modern-events-calendar-lite'); ?></label>
                                    </div>
                                    <div class="mec-field__control">
                                        <input type="text" id="mec_export_purge_emails" name="mec_export_purge_emails" placeholder="admin@example.com, ops@example.com" />
                                        <p class="mec-field__help"><?php esc_html_e('Comma or semicolon separated email addresses to receive the CSV.', 'modern-events-calendar-lite'); ?></p>
                                    </div>
                                </div>
                                <div class="mec-form-row mec-field mec-ix-actions">
                                    <div class="mec-field__control">
                                        <button type="submit" class="button button-primary"><?php esc_html_e('Export and Purge', 'modern-events-calendar-lite'); ?></button>
                                    </div>
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
