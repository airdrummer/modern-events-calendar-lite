<?php
/** no direct access **/
defined('MECEXEC') or die();
?>

<div class="mec-page-head">
    <span class="mec-page-eyebrow"><?php esc_html_e('Modern Events Calendar', 'modern-events-calendar-lite'); ?></span>
    <h1><span class="mec-page-mark" aria-hidden="true"><span class="dashicons dashicons-database-export"></span></span><?php esc_html_e('MEC Settings', 'modern-events-calendar-lite'); ?><span class="mec-page-title-sep" aria-hidden="true">/</span><?php esc_html_e('Import / Export', 'modern-events-calendar-lite'); ?></h1>
    <p class="mec-page-lead"><?php esc_html_e('Export settings to a file or import them from another site.', 'modern-events-calendar-lite'); ?></p>
    <?php $this->main->render_settings_page_meta(); ?>
</div>
<hr class="wp-header-end" />
<div class="wns-be-container wns-be-container-sticky">

    <div id="wns-be-infobar">
        <div class="mec-infobar-crumb">
            <span class="c-root"><?php esc_html_e('MEC Settings', 'modern-events-calendar-lite'); ?></span>
            <span class="c-sep">/</span>
            <span class="c-tab"><?php esc_html_e('Import / Export', 'modern-events-calendar-lite'); ?></span>
        </div>
    </div>

    <div class="wns-be-sidebar">
        <?php $this->main->get_sidebar_menu('ie'); ?>
    </div>

    <div class="wns-be-main ie-settings">

        <div id="wns-be-notification"></div>

        <div id="wns-be-content">
            <div class="wns-be-group-tab">
                <div class="mec-container">
                    <form id="ie-setting-form" data-mec-settings-form="1">
                        <div id="ie-options" class="mec-options-fields">
                            <h2 class="mec-form-subtitle mec-page-section"><?php esc_html_e('Import / Export', 'modern-events-calendar-lite'); ?></h2>
                            <h5 class="mec-panel__title"><?php esc_html_e('Import', 'modern-events-calendar-lite'); ?></h5>
                            <p><?php esc_html_e('Insert your backup files below and press import to restore your site\'s options to the last backup.', 'modern-events-calendar-lite'); ?></p>
                            <p class="mec-field__help mec-field__help--warn"><?php esc_html_e('WARNING! Restoring backup will overwrite all of your current option values. Caution Indeed.', 'modern-events-calendar-lite'); ?></p>
                            <div class="mec-form-row">
                                <div class="mec-col-12">
                                    <div class="mec-import-settings-wrap">
                                        <textarea class="mec-import-settings-content" placeholder="<?php esc_html_e('Please paste your options here', 'modern-events-calendar-lite'); ?>"></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="mec-form-row">
                                <div class="mec-col-12">
                                    <a class="button button-secondary mec-import-settings" href="#"><?php esc_html_e("Import Settings", 'modern-events-calendar-lite'); ?></a>
                                </div>
                            </div>
                            <div class="mec-import-options-notification"></div>

                            <h5 class="mec-panel__title"><?php esc_html_e('Export', 'modern-events-calendar-lite'); ?></h5>
                            <?php
                                $nonce = wp_create_nonce("mec_settings_download");
                                $export_link = admin_url('admin-ajax.php?action=download_settings&nonce='.$nonce);
                            ?>
                            <div class="mec-form-row">
                                <div class="mec-col-12">
                                    <a class="button button-secondary mec-export-settings" href="<?php echo esc_url($export_link); ?>"><?php esc_html_e("Download Settings", 'modern-events-calendar-lite'); ?></a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>

</div>