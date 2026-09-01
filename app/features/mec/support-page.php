<?php

/** no direct access **/
defined('MECEXEC') or die();

/** @var MEC_feature_mec $this */

wp_enqueue_style('mec-lity-style');
wp_enqueue_script('mec-lity-script');

global $wp_version;

$styling = $this->main->get_styling();
$darkadmin_mode = $styling['dark_mode'] ?? '';
$logo = plugin_dir_url(__FILE__) . '../../../assets/img/' . ($darkadmin_mode == 1 ? 'mec-logo-w2.png' : 'mec-logo-w.png');

$dox = 'https://webnus.net/dox/modern-events-calendar/';
?>
<div id="webnus-dashboard" class="wrap about-wrap mec-support-page">
    <div class="welcome-head w-clearfix">
        <div class="w-row">
            <div class="w-col-sm-9">
                <h1><?php echo esc_html__('Support', 'modern-events-calendar-lite'); ?></h1>
                <div class="w-welcome">
                    <?php echo sprintf(esc_html__('%s: everything you need to master MEC and get help fast.', 'modern-events-calendar-lite'), '<strong>' . esc_html__('Modern Events Calendar', 'modern-events-calendar-lite') . '</strong>'); ?>
                </div>
            </div>
            <div class="w-col-sm-3">
                <img src="<?php echo esc_url($logo); ?>" alt="<?php echo esc_attr__('Modern Events Calendar', 'modern-events-calendar-lite'); ?>" />
                <span class="w-theme-version"><?php echo esc_html__('Version', 'modern-events-calendar-lite'); ?> <?php echo MEC_VERSION; ?></span>
            </div>
        </div>
    </div>
    <div class="welcome-content w-clearfix extra">

        <?php if (!$this->getPRO()): ?>
            <div class="w-row mec-pro-notice" style="margin-bottom: 30px;">
                <div class="w-col-sm-12">
                    <div class="info-msg support-box">
                        <p><strong><?php esc_html_e('🚨 Is your MEC up to date? 🚨', 'modern-events-calendar-lite'); ?></strong></p>
                        <p><?php esc_html_e('Hackers are exploiting WordPress plugin vulnerabilities faster than ever with AI-powered tools. If you\'re still on an older version, update MEC now.', 'modern-events-calendar-lite'); ?></p>
                        <p><small><?php esc_html_e('Tip: enable auto-updates for MEC so security fixes are applied as soon as they\'re released.', 'modern-events-calendar-lite'); ?></small></p>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($this->getPRO()): ?>
            <div class="w-row mec-pro-notice" style="margin-bottom: 30px;">
                <div class="w-col-sm-12">
                    <div class="info-msg support-box">
                        <p><strong><?php esc_html_e('🚨 Is your MEC up to date? 🚨', 'modern-events-calendar-lite'); ?></strong></p>
                        <p><?php esc_html_e('Hackers are exploiting WordPress plugin vulnerabilities faster than ever with AI-powered tools. If you\'re still on an older version, update MEC now.', 'modern-events-calendar-lite'); ?></p>
                        <p><small><?php esc_html_e('Updating MEC is free and takes less than a minute. Keep your site secure by staying current.', 'modern-events-calendar-lite'); ?></small></p>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if (current_user_can('read')): ?>
            <div class="mec-support-grid mec-support-grid--4">
                <a class="mec-support-card" href="<?php echo esc_url($dox); ?>" target="_blank" rel="noopener">
                    <span class="mec-support-card__icon"><i class="mec-sl-book-open"></i></span>
                    <span class="mec-support-card__title"><?php esc_html_e('Documentation', 'modern-events-calendar-lite'); ?></span>
                    <span class="mec-support-card__desc"><?php esc_html_e('Step-by-step guides covering every MEC feature and setting.', 'modern-events-calendar-lite'); ?></span>
                    <span class="mec-support-card__cta"><?php esc_html_e('Browse docs', 'modern-events-calendar-lite'); ?> <i class="mec-sl-arrow-right-circle"></i></span>
                </a>
                <a class="mec-support-card" href="https://www.youtube.com/channel/UCmQ-VeVK7nLR3bGpAkSYB1Q" target="_blank" rel="noopener">
                    <span class="mec-support-card__icon"><i class="mec-sl-social-youtube"></i></span>
                    <span class="mec-support-card__title"><?php esc_html_e('Video Tutorials', 'modern-events-calendar-lite'); ?></span>
                    <span class="mec-support-card__desc"><?php esc_html_e('Watch over-the-shoulder tutorials and get started in minutes.', 'modern-events-calendar-lite'); ?></span>
                    <span class="mec-support-card__cta"><?php esc_html_e('Watch videos', 'modern-events-calendar-lite'); ?> <i class="mec-sl-arrow-right-circle"></i></span>
                </a>
                <a class="mec-support-card" href="<?php echo esc_url($dox . 'knowledgebase/'); ?>" target="_blank" rel="noopener">
                    <span class="mec-support-card__icon"><i class="mec-sl-bulb"></i></span>
                    <span class="mec-support-card__title"><?php esc_html_e('Knowledgebase', 'modern-events-calendar-lite'); ?></span>
                    <span class="mec-support-card__desc"><?php esc_html_e('Short answers to the questions users ask us the most.', 'modern-events-calendar-lite'); ?></span>
                    <span class="mec-support-card__cta"><?php esc_html_e('Find answers', 'modern-events-calendar-lite'); ?> <i class="mec-sl-arrow-right-circle"></i></span>
                </a>
                <a class="mec-support-card" href="<?php echo esc_url($dox . 'category/developer-document/'); ?>" target="_blank" rel="noopener">
                    <span class="mec-support-card__icon"><i class="mec-sl-wrench"></i></span>
                    <span class="mec-support-card__title"><?php esc_html_e('Developer Docs', 'modern-events-calendar-lite'); ?></span>
                    <span class="mec-support-card__desc"><?php esc_html_e('Hooks, filters, theming guides and the MEC API reference.', 'modern-events-calendar-lite'); ?></span>
                    <span class="mec-support-card__cta"><?php esc_html_e('Open reference', 'modern-events-calendar-lite'); ?> <i class="mec-sl-arrow-right-circle"></i></span>
                </a>
            </div>

            <div class="mec-support-search">
                <form role="search" action="<?php echo esc_url($dox); ?>" method="get" target="_blank">
                    <i class="mec-sl-magnifier" aria-hidden="true"></i>
                    <input type="search" name="s" placeholder="<?php esc_attr_e('Search the knowledgebase. Try "booking form", "taxes", "shortcode"…', 'modern-events-calendar-lite'); ?>" />
                    <button type="submit" class="mec-support-btn"><?php esc_html_e('Search', 'modern-events-calendar-lite'); ?></button>
                </form>
            </div>

            <h2 class="mec-support-section-title"><?php esc_html_e('Browse by Topic', 'modern-events-calendar-lite'); ?></h2>
            <div class="mec-support-grid mec-support-grid--4">
                <div class="mec-support-card mec-support-card--topic">
                    <div class="mec-support-card__head">
                        <span class="mec-support-card__icon"><i class="mec-sl-rocket"></i></span>
                        <span class="mec-support-card__title"><?php esc_html_e('Getting Started', 'modern-events-calendar-lite'); ?></span>
                    </div>
                    <ul class="mec-support-card__list">
                        <li><a href="<?php echo esc_url($dox . 'installation/'); ?>" target="_blank" rel="noopener"><i class="mec-sl-arrow-right-circle"></i><?php esc_html_e('Install MEC', 'modern-events-calendar-lite'); ?></a></li>
                        <li><a href="<?php echo esc_url($dox . 'activation/'); ?>" target="_blank" rel="noopener"><i class="mec-sl-arrow-right-circle"></i><?php esc_html_e('Activate Your License', 'modern-events-calendar-lite'); ?></a></li>
                        <li><a href="<?php echo esc_url($dox . 'add-event/'); ?>" target="_blank" rel="noopener"><i class="mec-sl-arrow-right-circle"></i><?php esc_html_e('Add Your First Event', 'modern-events-calendar-lite'); ?></a></li>
                        <li><a href="<?php echo esc_url($dox . 'frontend-event-submission/'); ?>" target="_blank" rel="noopener"><i class="mec-sl-arrow-right-circle"></i><?php esc_html_e('Frontend Event Submission', 'modern-events-calendar-lite'); ?></a></li>
                        <li><a href="<?php echo esc_url($dox . 'import-and-export-events/'); ?>" target="_blank" rel="noopener"><i class="mec-sl-arrow-right-circle"></i><?php esc_html_e('Import & Export Events', 'modern-events-calendar-lite'); ?></a></li>
                    </ul>
                </div>
                <div class="mec-support-card mec-support-card--topic">
                    <div class="mec-support-card__head">
                        <span class="mec-support-card__icon"><i class="mec-sl-settings"></i></span>
                        <span class="mec-support-card__title"><?php esc_html_e('Settings & Shortcodes', 'modern-events-calendar-lite'); ?></span>
                    </div>
                    <ul class="mec-support-card__list">
                        <li><a href="<?php echo esc_url($dox . 'general-settings/'); ?>" target="_blank" rel="noopener"><i class="mec-sl-arrow-right-circle"></i><?php esc_html_e('General Settings', 'modern-events-calendar-lite'); ?></a></li>
                        <li><a href="<?php echo esc_url($dox . 'appearance-settings/'); ?>" target="_blank" rel="noopener"><i class="mec-sl-arrow-right-circle"></i><?php esc_html_e('Appearance & Styling', 'modern-events-calendar-lite'); ?></a></li>
                        <li><a href="<?php echo esc_url($dox . 'single-event-settings/'); ?>" target="_blank" rel="noopener"><i class="mec-sl-arrow-right-circle"></i><?php esc_html_e('Single Event Page', 'modern-events-calendar-lite'); ?></a></li>
                        <li><a href="<?php echo esc_url($dox . 'making-advanced-shortcodes/'); ?>" target="_blank" rel="noopener"><i class="mec-sl-arrow-right-circle"></i><?php esc_html_e('Making Advanced Shortcodes', 'modern-events-calendar-lite'); ?></a></li>
                        <li><a href="<?php echo esc_url($dox . 'mec-integrations/'); ?>" target="_blank" rel="noopener"><i class="mec-sl-arrow-right-circle"></i><?php esc_html_e('Integrations', 'modern-events-calendar-lite'); ?></a></li>
                    </ul>
                </div>
                <div class="mec-support-card mec-support-card--topic">
                    <div class="mec-support-card__head">
                        <span class="mec-support-card__icon"><i class="mec-sl-wallet"></i></span>
                        <span class="mec-support-card__title"><?php esc_html_e('Booking & Payments', 'modern-events-calendar-lite'); ?></span>
                    </div>
                    <ul class="mec-support-card__list">
                        <li><a href="<?php echo esc_url($dox . 'add-a-booking-system/'); ?>" target="_blank" rel="noopener"><i class="mec-sl-arrow-right-circle"></i><?php esc_html_e('Add a Booking System', 'modern-events-calendar-lite'); ?></a></li>
                        <li><a href="<?php echo esc_url($dox . 'booking-settings/'); ?>" target="_blank" rel="noopener"><i class="mec-sl-arrow-right-circle"></i><?php esc_html_e('Booking Settings & Tickets', 'modern-events-calendar-lite'); ?></a></li>
                        <li><a href="<?php echo esc_url($dox . 'payment-gateways/'); ?>" target="_blank" rel="noopener"><i class="mec-sl-arrow-right-circle"></i><?php esc_html_e('Payment Gateways', 'modern-events-calendar-lite'); ?></a></li>
                        <li><a href="<?php echo esc_url($dox . 'organizer-payment/'); ?>" target="_blank" rel="noopener"><i class="mec-sl-arrow-right-circle"></i><?php esc_html_e('Organizer Payment', 'modern-events-calendar-lite'); ?></a></li>
                        <li><a href="<?php echo esc_url($dox . 'event-notifications/'); ?>" target="_blank" rel="noopener"><i class="mec-sl-arrow-right-circle"></i><?php esc_html_e('Emails & Notifications', 'modern-events-calendar-lite'); ?></a></li>
                    </ul>
                </div>
                <div class="mec-support-card mec-support-card--topic">
                    <div class="mec-support-card__head">
                        <span class="mec-support-card__icon"><i class="mec-sl-shield"></i></span>
                        <span class="mec-support-card__title"><?php esc_html_e('Update, Translate & Fix', 'modern-events-calendar-lite'); ?></span>
                    </div>
                    <ul class="mec-support-card__list">
                        <li><a href="<?php echo esc_url($dox . 'auto-update/'); ?>" target="_blank" rel="noopener"><i class="mec-sl-arrow-right-circle"></i><?php esc_html_e('Auto-Update MEC', 'modern-events-calendar-lite'); ?></a></li>
                        <li><a href="<?php echo esc_url($dox . 'manual-update/'); ?>" target="_blank" rel="noopener"><i class="mec-sl-arrow-right-circle"></i><?php esc_html_e('Update Manually (FTP)', 'modern-events-calendar-lite'); ?></a></li>
                        <li><a href="<?php echo esc_url($dox . 'translate-mec/'); ?>" target="_blank" rel="noopener"><i class="mec-sl-arrow-right-circle"></i><?php esc_html_e('Translate MEC', 'modern-events-calendar-lite'); ?></a></li>
                        <li><a href="<?php echo esc_url($dox . 'including-mec-in-your-theme/'); ?>" target="_blank" rel="noopener"><i class="mec-sl-arrow-right-circle"></i><?php esc_html_e('Theme Integration Guide', 'modern-events-calendar-lite'); ?></a></li>
                        <li><a href="<?php echo esc_url($dox . 'knowledgebase/troubleshooting-other/'); ?>" target="_blank" rel="noopener"><i class="mec-sl-arrow-right-circle"></i><?php esc_html_e('Troubleshooting', 'modern-events-calendar-lite'); ?></a></li>
                    </ul>
                </div>
            </div>

            <h2 class="mec-support-section-title">
                <?php esc_html_e('Video Tutorials', 'modern-events-calendar-lite'); ?>
                <a class="mec-support-section-link" href="https://www.youtube.com/channel/UCmQ-VeVK7nLR3bGpAkSYB1Q" target="_blank" rel="noopener"><?php esc_html_e('View channel', 'modern-events-calendar-lite'); ?> <i class="mec-sl-arrow-right-circle"></i></a>
            </h2>
            <div class="mec-support-grid mec-support-grid--3">
                <div class="mec-support-card mec-support-card--video">
                    <a class="mec-support-video__thumb" href="https://youtu.be/V8DAZXuVxrQ" data-lity>
                        <i class="mec-sl-control-play"></i>
                    </a>
                    <span class="mec-support-card__title"><?php esc_html_e('Activate Your License', 'modern-events-calendar-lite'); ?></span>
                    <ul class="mec-support-card__list mec-support-card__list--compact">
                        <li><i class="mec-sl-check"></i><?php esc_html_e('Log in to your Webnus dashboard', 'modern-events-calendar-lite'); ?></li>
                        <li><i class="mec-sl-check"></i><?php esc_html_e('Copy your license key', 'modern-events-calendar-lite'); ?></li>
                        <li><i class="mec-sl-check"></i><?php esc_html_e('Activate MEC in one click', 'modern-events-calendar-lite'); ?></li>
                    </ul>
                    <a class="mec-support-card__cta" href="https://youtu.be/V8DAZXuVxrQ" data-lity><?php esc_html_e('Watch video', 'modern-events-calendar-lite'); ?> <i class="mec-sl-control-play"></i></a>
                </div>
                <div class="mec-support-card mec-support-card--video">
                    <a class="mec-support-video__thumb" href="https://youtu.be/difbDGz6blU" data-lity>
                        <i class="mec-sl-control-play"></i>
                    </a>
                    <span class="mec-support-card__title"><?php esc_html_e('Build a Booking Form', 'modern-events-calendar-lite'); ?></span>
                    <ul class="mec-support-card__list mec-support-card__list--compact">
                        <li><i class="mec-sl-check"></i><?php esc_html_e('Enable booking from settings', 'modern-events-calendar-lite'); ?></li>
                        <li><i class="mec-sl-check"></i><?php esc_html_e('Set up your booking form', 'modern-events-calendar-lite'); ?></li>
                        <li><i class="mec-sl-check"></i><?php esc_html_e('Customize fields & tickets', 'modern-events-calendar-lite'); ?></li>
                    </ul>
                    <a class="mec-support-card__cta" href="https://youtu.be/difbDGz6blU" data-lity><?php esc_html_e('Watch video', 'modern-events-calendar-lite'); ?> <i class="mec-sl-control-play"></i></a>
                </div>
                <div class="mec-support-card mec-support-card--video">
                    <a class="mec-support-video__thumb" href="https://youtu.be/mdXWngl4Lso" data-lity>
                        <i class="mec-sl-control-play"></i>
                    </a>
                    <span class="mec-support-card__title"><?php esc_html_e('MEC Settings Overview', 'modern-events-calendar-lite'); ?></span>
                    <ul class="mec-support-card__list mec-support-card__list--compact">
                        <li><i class="mec-sl-check"></i><?php esc_html_e('Tour every settings panel', 'modern-events-calendar-lite'); ?></li>
                        <li><i class="mec-sl-check"></i><?php esc_html_e('Configure MEC for your site', 'modern-events-calendar-lite'); ?></li>
                        <li><i class="mec-sl-check"></i><?php esc_html_e('Use the advanced options', 'modern-events-calendar-lite'); ?></li>
                    </ul>
                    <a class="mec-support-card__cta" href="https://youtu.be/mdXWngl4Lso" data-lity><?php esc_html_e('Watch video', 'modern-events-calendar-lite'); ?> <i class="mec-sl-control-play"></i></a>
                </div>
            </div>

            <div class="mec-support-grid mec-support-grid--2">
                <div class="mec-support-card mec-support-card--flat">
                    <h3 class="mec-support-card__heading"><?php esc_html_e('Frequently Asked Questions', 'modern-events-calendar-lite'); ?></h3>
                    <div class="mec-faq-accordion">
                        <div class="mec-faq-accordion-trigger"><a href="" class="active"><?php echo esc_html__('How should I update the plugin?', 'modern-events-calendar-lite'); ?></a></div>
                        <div class="mec-faq-accordion-content active">
                            <?php echo sprintf(__('You have two options:<br>
                        1- Uploading the plugin file using FTP. For more information, please <a href="%s" target="_blank" rel="noopener">click here</a>.<br>
                        2- Using the auto-update feature which needs adding the purchase code in the corresponding section of the plugin. For more information, please <a href="%s" target="_blank" rel="noopener">click here</a>.', 'modern-events-calendar-lite'), $dox . 'manual-update/', $dox . 'auto-update/'); ?>
                        </div>

                        <div class="mec-faq-accordion-trigger"><a href=""><?php echo esc_html__('Do I lose all my data or customization if I update MEC?', 'modern-events-calendar-lite'); ?></a></div>
                        <div class="mec-faq-accordion-content"><?php esc_html_e('Your events, bookings and settings are stored in the database and are kept after the update. However, if you have added files to the main MEC folder, those files will be removed, so please get a full backup before updating.', 'modern-events-calendar-lite'); ?>
                        </div>

                        <div class="mec-faq-accordion-trigger"><a href=""><?php echo esc_html__('Can I customize the event pages?', 'modern-events-calendar-lite'); ?></a></div>
                        <div class="mec-faq-accordion-content">
                            <?php echo sprintf(__('Yes, it is possible. In order to see the related documentations, please <a href="%s" target="_blank" rel="noopener">click here</a>.', 'modern-events-calendar-lite'), $dox . 'including-mec-in-your-theme/'); ?>
                        </div>

                        <div class="mec-faq-accordion-trigger"><a href=""><?php echo esc_html__('Does MEC have default languages or does it need to be translated?', 'modern-events-calendar-lite'); ?></a></div>
                        <div class="mec-faq-accordion-content">
                            <?php echo sprintf(__('MEC ships with a number of community translations, but they may be incomplete. You can easily add or update a translation yourself. For more information, please <a href="%s" target="_blank" rel="noopener">click here</a>.', 'modern-events-calendar-lite'), $dox . 'translate-mec/'); ?>
                        </div>

                        <div class="mec-faq-accordion-trigger"><a href=""><?php echo esc_html__('Can I have more than one calendar on one website?', 'modern-events-calendar-lite'); ?></a></div>
                        <div class="mec-faq-accordion-content"><?php esc_html_e('Absolutely! You can create unlimited shortcodes, each with its own skin, filters and events, then place them anywhere on your website.', 'modern-events-calendar-lite'); ?></div>

                        <div class="mec-faq-accordion-trigger"><a href=""><?php echo esc_html__('Can I import/export events to/from MEC?', 'modern-events-calendar-lite'); ?></a></div>
                        <div class="mec-faq-accordion-content"><?php echo sprintf(esc_html__('Yes, you can export your MEC data as XML or import an existing file. You can also migrate from The Events Calendar, Calendarize It, EventOn or Events Schedule. See %s for details.', 'modern-events-calendar-lite'), '<a href="' . esc_url($dox . 'import-and-export-events/') . '" target="_blank" rel="noopener">' . esc_html__('Import & Export', 'modern-events-calendar-lite') . '</a>'); ?></div>
                    </div>
                </div>

                <div class="mec-support-card mec-support-card--flat">
                    <h3 class="mec-support-card__heading"><?php esc_html_e('System Information', 'modern-events-calendar-lite'); ?></h3>
                    <ul class="system-information">
                        <li>
                            <div class="mec-si-label"><?php esc_html_e('Home URL', 'modern-events-calendar-lite'); ?></div>
                            <div class="mec-si-value"><?php echo esc_url(get_home_url()); ?></div>
                        </li>
                        <li>
                            <div class="mec-si-label"><?php esc_html_e('Site URL', 'modern-events-calendar-lite'); ?></div>
                            <div class="mec-si-value"><?php echo esc_url(get_site_url()); ?></div>
                        </li>
                        <li>
                            <div class="mec-si-label"><?php esc_html_e('Locale', 'modern-events-calendar-lite'); ?></div>
                            <div class="mec-si-value"><?php echo esc_html(get_locale()); ?></div>
                        </li>
                        <li>
                            <div class="mec-si-label"><?php esc_html_e('Character Set', 'modern-events-calendar-lite'); ?></div>
                            <div class="mec-si-value"><?php echo esc_html(get_option('blog_charset')); ?></div>
                        </li>
                        <li>
                            <div class="mec-si-label"><?php esc_html_e('MEC Version', 'modern-events-calendar-lite'); ?></div>
                            <div class="mec-si-value"><?php echo MEC_VERSION; ?></div>
                        </li>
                        <li>
                            <div class="mec-si-label"><?php esc_html_e('Package', 'modern-events-calendar-lite'); ?></div>
                            <div class="mec-si-value"><?php echo $this->getPRO() ? esc_html__('Pro', 'modern-events-calendar-lite') : esc_html__('Lite', 'modern-events-calendar-lite'); ?></div>
                        </li>
                        <li>
                            <div class="mec-si-label"><?php esc_html_e('WordPress Version', 'modern-events-calendar-lite'); ?></div>
                            <div class="mec-si-value"><?php echo esc_html($wp_version); ?></div>
                        </li>
                        <li>
                            <div class="mec-si-label"><?php esc_html_e('Multisite', 'modern-events-calendar-lite'); ?></div>
                            <div class="mec-si-value"><?php echo function_exists('is_multisite') && is_multisite() ? esc_html__('Yes', 'modern-events-calendar-lite') : esc_html__('No', 'modern-events-calendar-lite'); ?></div>
                        </li>
                        <li>
                            <div class="mec-si-label"><?php esc_html_e('WordPress Memory Limit', 'modern-events-calendar-lite'); ?></div>
                            <div class="mec-si-value"><?php echo esc_html(ini_get('memory_limit')); ?></div>
                        </li>
                        <li>
                            <div class="mec-si-label"><?php esc_html_e('PHP Version', 'modern-events-calendar-lite'); ?></div>
                            <div class="mec-si-value"><?php echo esc_html(phpversion()); ?></div>
                        </li>
                        <li>
                            <div class="mec-si-label"><?php esc_html_e('PHP cURL', 'modern-events-calendar-lite'); ?></div>
                            <div class="mec-si-value"><?php echo function_exists('curl_version') ? esc_html__('Yes', 'modern-events-calendar-lite') : esc_html__('No', 'modern-events-calendar-lite'); ?></div>
                        </li>
                    </ul>
                    <?php if (current_user_can('manage_options')): ?>
                        <div class="mec-support-debuglog">
                            <h4><?php esc_html_e('Debug Log', 'modern-events-calendar-lite'); ?></h4>
                            <?php if (defined('WP_DEBUG') && WP_DEBUG): ?>
                                <?php
                                $log_file = WP_CONTENT_DIR . '/debug.log';
                                if (defined('WP_DEBUG_LOG') && is_string(WP_DEBUG_LOG)) $log_file = WP_DEBUG_LOG;

                                $log_file_fize = file_exists($log_file) ? filesize($log_file) : 0;
                                ?>
                                <?php if ($log_file_fize): ?>
                                    <p><?php echo sprintf(esc_html__("Log file size is about %s. %s", 'modern-events-calendar-lite'), (round(($log_file_fize / 1024), 2) . 'KB'), '<a href="' . esc_url($this->main->URL('admin') . '?mec-download-log-file=1') . '">' . esc_html__('Download') . '</a>'); ?></p>
                                <?php else: ?>
                                    <p class="info-msg"><?php esc_html_e("WP Debug mode is turned on but there is no log to download at the moment.", 'modern-events-calendar-lite'); ?></p>
                                <?php endif; ?>
                            <?php else: ?>
                                <p class="info-msg"><?php esc_html_e("WordPress debug is not enabled in your website. To download the debug log file, you need to enable WP Debug mode on your website first.", 'modern-events-calendar-lite'); ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="mec-support-contact <?php echo $this->getPRO() ? 'mec-support-contact--pro' : ''; ?>">
                <div class="mec-support-contact__text">
                    <h3><?php esc_html_e('Still need a hand?', 'modern-events-calendar-lite'); ?></h3>
                    <?php if ($this->getPRO()): ?>
                        <p><?php esc_html_e('Our support specialists are one click away. Open a chat and we will get back to you shortly.', 'modern-events-calendar-lite'); ?></p>
                    <?php else: ?>
                        <p><?php esc_html_e('Search the knowledgebase for a quick answer, or post your question in the WordPress.org forums where our team is active.', 'modern-events-calendar-lite'); ?></p>
                    <?php endif; ?>
                </div>
                <?php if ($this->getPRO()): ?>
                    <a href="#" class="mec-support-btn mec-support-btn--lg support-button"><i class="mec-sl-bubble"></i> <?php esc_html_e('Chat with Support', 'modern-events-calendar-lite'); ?></a>
                <?php else: ?>
                    <a href="<?php echo esc_url($dox . 'knowledgebase/'); ?>" target="_blank" rel="noopener" class="mec-support-btn mec-support-btn--lg"><i class="mec-sl-bubble"></i> <?php esc_html_e('Open Knowledgebase', 'modern-events-calendar-lite'); ?></a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php $this->factory->params('footer', function () {
?>
    <script>
        (function($) {

            var allPanels = $('.mec-faq-accordion > .mec-faq-accordion-content');
            $('.mec-faq-accordion>.mec-faq-accordion-content.active').show();

            $('.mec-faq-accordion > .mec-faq-accordion-trigger > a').click(function() {
                $this = $(this);
                $target = $this.parent().next();

                if (!$target.hasClass('active')) {
                    allPanels.removeClass('active').slideUp();
                    $target.addClass('active').slideDown();
                    $('.mec-faq-accordion > .mec-faq-accordion-trigger > a').removeClass('active')
                    $this.addClass('active');
                } else {
                    $this.removeClass('active');
                    $target.removeClass('active').slideUp();
                }
                return false;
            });

            <?php if ($this->getPRO()): ?>
            $('.support-button').on('click', function(event) {
                event.preventDefault();
                if (window.$crisp && window.$crisp.push) {
                    window.$crisp.push(['do', 'chat:open']);
                }
            });
            <?php endif; ?>

        })(jQuery);
    </script>
    <?php if ($this->getPRO()): ?>
        <script type="text/javascript">window.$crisp=[];window.CRISP_WEBSITE_ID="1ec276e5-9da2-4a16-859f-eae7bcbb4ae9";(function(){d=document;s=d.createElement("script");s.src="https://client.crisp.chat/l.js";s.async=1;d.getElementsByTagName("head")[0].appendChild(s);})();</script>
    <?php endif;
}); ?>
