<?php

/** no direct access **/
defined('MECEXEC') or die();

/** @var MEC_main $this */

// get screen id
$current_user = wp_get_current_user();

// user event created
$count_events = wp_count_posts($this->get_main_post_type());
$user_post_count = $count_events->publish ?? '0';

// user calendar created
$count_calendars = wp_count_posts('mec_calendars');
$user_post_count_c = $count_calendars->publish ?? '0';

// mec location
$user_location_count_l = wp_count_terms('mec_location', array(
    'hide_empty' => false,
    'parent' => 0
));

// mec organizer
$user_organizer_count_l = wp_count_terms('mec_organizer', array(
    'hide_empty' => false,
    'parent' => 0
));

$version = $verify = NULL;
if ($this->isProBuild()) $mec_license_status = get_option('mec_license_status');

// MEC Database
$db = $this->getDB();

// MEC Settings
$settings = $this->get_settings();

// MEC Booking Status
$booking_status = ($this->getPRO() and isset($settings['booking_status']) and $settings['booking_status']);

// Add ChartJS library
if ($booking_status) wp_enqueue_script('mec-chartjs-script');

// Whether to show dashboard boxes or not!
$box_support = apply_filters('mec_dashboard_box_support', true);
$box_stats = apply_filters('mec_dashboard_box_stats', true);
?>
<style>
    .upcoming-events .mec-credit-url {
        display: none;
    }
</style>
<div id="webnus-dashboard" class="wrap about-wrap">
    <div class="welcome-head w-clearfix">
        <div class="w-row">
            <div class="w-col-sm-9">
                <h1> <?php echo sprintf(esc_html__('Welcome %s', 'modern-events-calendar-lite'), $current_user->user_firstname); ?> </h1>
                <div class="w-welcome">
                    <?php echo sprintf(esc_html__('%s - Most Powerful & Easy to Use Events Management System', 'modern-events-calendar-lite'), '<strong>' . ($this->getPRO() ? esc_html__('Modern Events Calendar', 'modern-events-calendar-lite') : esc_html__('Modern Events Calendar (Lite)', 'modern-events-calendar-lite')) . '</strong>'); ?>
                </div>
            </div>
            <div class="w-col-sm-3">
                <?php $styling = $this->get_styling();
                $darkadmin_mode = $styling['dark_mode'] ?? '';
                if ($darkadmin_mode == 1): $darklogo = plugin_dir_url(__FILE__) . '../../../assets/img/mec-logo-w2.png';
                else: $darklogo = plugin_dir_url(__FILE__) . '../../../assets/img/mec-logo-w.png';
                endif; ?>
                <img src="<?php echo esc_url($darklogo); ?>" />
                <span class="w-theme-version"><?php echo esc_html__('Version', 'modern-events-calendar-lite'); ?> <?php echo MEC_VERSION; ?></span>
            </div>
        </div>
    </div>
    <!-- remove update notification section for high request -->
    <div class="welcome-content w-clearfix extra">
        <?php
        // Remote announcement box. The wrapper row renders only when the
        // remote actually returns a post — an empty/unreachable response must
        // not leave a margin-only row behind.
        // Category 3 targets Lite installs, 6 targets Pro.
        $notification_category = $this->getPRO() ? 6 : 3;

        $response_lite = wp_remote_get(
            add_query_arg(
                array( // posts from 101 to 200
                    'per_page' => 1,
                    'page' => 1,
                    'categories' => $notification_category,
                ),
                'https://notifications.webnus.site/wp-json/wp/v2/posts'
            ),
            array(
                'timeout' => 50, // Fix for: cURL error 28: Operation timed out after...
            )
        );

        $body = null;
        if (!is_wp_error($response_lite) && isset($response_lite['body'])) {
            $decoded = json_decode($response_lite['body']);
            if (is_array($decoded)) {
                $body = $decoded;
            }
        }

        if (!empty($body) && is_array($body) && count($body) > 0) :
            $featured_media = $body[0]->featured_media ?? '';
            $title          = $body[0]->title->rendered ?? '';
            $content        = $body[0]->content->rendered ?? '';

            // Get featured image from $featured_media
            $featured_image = wp_remote_get(
                'https://notifications.webnus.site/wp-json/wp/v2/media/' . $featured_media,
                array(
                    'timeout' => 50, // Fix for: cURL error 28: Operation timed out after...
                )
            );
            $body_featured_image = json_decode($featured_image['body']);
            $lite_featured_image = $body_featured_image->guid->rendered;
            ?>
            <div class="w-row mec-lite-notification" style="margin-bottom: 30px;margin-top: 30px;">
                <div class="w-col-sm-12">
                    <?php echo '<link rel = "stylesheet" type = "text/css" href="https://files.webnus.site/addons-api/mec-extra-content/style2.css" /><div class="mec-custom-msg-2-notification-set-box extra"><div style="margin: 0" class="w-row mec-custom-msg-notification-wrap"><div class="w-col-sm-12"><div class="w-clearfix w-box mec-cmsg-2-notification-box-wrap mec-new-addons-wrap" style="margin-top:0;"><div class="w-box-head">Announcement</div><div class="w-box-content"><div class="mec-addons-notification-box-image" style="width: 240px; margin-right: 10px;"><img src="' . $lite_featured_image . '" /></div><div class="mec-addons-notification-box-content mec-new-addons" style="width: calc(100% - 270px);"><div class="w-box-content"><div class="csm-message-notice" style="text-align: center; background: #BAF0FC57; border-radius: 6px;letter-spacing: 4.4px; color: #00CAE6; text-transform: uppercase; padding: 10px 5px; font-weight: bold; margin-bottom: 40px;">' . $title . '</div><p>' . $content . '</p><div style="clear:both"></div></div></div></div></div></div></div></div>'; ?>
                </div>
            </div>
        <?php endif; ?>
        <?php
        // Package, not licence. This box says "You're using the lite version",
        // which is simply untrue on an unlicensed Pro install — and it sits
        // directly above the licence notice that tells them the actual problem.
        ?>
        <?php if (!$this->isProBuild()): ?>
            <div class="w-row mec-pro-notice" style="margin-bottom: 30px;">
                <div class="w-col-sm-12">
                    <div class="info-msg">
                        <p>
                            <?php echo sprintf(esc_html__("You're using %s version of Modern Events Calendar. To use advanced booking system, modern skins like Agenda, Timetable, Masonry, Yearly View, Available Spots, etc you should upgrade to the Pro version.", 'modern-events-calendar-lite'), '<strong>' . esc_html__('lite', 'modern-events-calendar-lite') . '</strong>'); ?>
                        </p>
                        <a class="info-msg-link" href="<?php echo esc_url($this->get_pro_link()); ?>" target="_blank">
                            <?php esc_html_e('GO PREMIUM', 'modern-events-calendar-lite'); ?>
                        </a>
                        <div class="info-msg-coupon">

                        </div>
                        <div class="socialfollow">
                            <a target="_blank" href="https://www.facebook.com/WebnusCo/" class="facebook">
                                <i class="mec-sl-social-facebook"></i>
                            </a>
                            <a target="_blank" href="https://twitter.com/webnus" class="twitter">
                                <i class="mec-sl-social-twitter"></i>
                            </a>
                            <a target="_blank" href="https://www.instagram.com/webnus/" class="instagram">
                                <i class="mec-sl-social-instagram"></i>
                            </a>
                            <a target="_blank" href="https://www.youtube.com/channel/UCmQ-VeVK7nLR3bGpAkSYB1Q" class="youtube">
                                <i class="mec-sl-social-youtube"></i>
                            </a>
                            <a target="_blank" href="https://dribbble.com/Webnus" class="dribbble">
                                <i class="mec-sl-social-dribbble"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>


        <?php endif; ?>
        <?php echo MEC_kses::full($this->mec_custom_msg_2('yes', 'yes')); ?>
        <?php echo MEC_kses::full($this->mec_custom_msg('yes', 'yes')); ?>
        <div class="w-row">
            <div class="w-col-sm-12">
                <div class="w-box mec-intro-section">
                    <div class="w-box-content mec-intro-section-welcome">
                        <h3><?php esc_html_e('Getting started with Modern Events Calendar', 'modern-events-calendar-lite'); ?></h3>
                        <p><?php esc_html_e('In this short video, you can learn how to make an event and put a calendar on your website. Please watch this 2 minutes video to the end.', 'modern-events-calendar-lite'); ?></p>
                    </div>
                    <div class="w-box-content mec-intro-section-ifarme">
                        <iframe width="560" height="315" src="https://www.youtube.com/embed/P0c2G1qhusk?si=96nFmtSdPzARY4ed" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
                    </div>
                    <div class="w-box-content mec-intro-section-links wp-core-ui">
                        <a class="mec-intro-section-link-tag button button-primary button-hero" href="<?php esc_html_e(admin_url('post-new.php?post_type=mec-events')); ?>" target="_blank"><?php esc_html_e('Add New Event', 'modern-events-calendar-lite'); ?>
                            <a class="mec-intro-section-link-tag button button-secondary button-hero" href="<?php esc_html_e(admin_url('admin.php?page=MEC-settings')); ?>" target="_blank"><?php esc_html_e('Settings', 'modern-events-calendar-lite'); ?>
                                <a class="mec-intro-section-link-tag button button-secondary button-hero" href="https://webnus.net/dox/modern-events-calendar/" target="_blank"><?php esc_html_e('Documentation', 'modern-events-calendar-lite'); ?></a>
                    </div>
                </div>
            </div>
            <?php
            // Package, not licence. On getPRO() this box appeared on Lite only;
            // now that getPRO() also goes false at the final phase, an unlicensed
            // Pro install would render it alongside the full activation box below
            // — two boxes both titled "License Activation" on the same screen.
            ?>
            <?php if (!$this->isProBuild() && has_action('addons_activation')) : ?>
                <div class="w-col-sm-12">
                    <div class="w-box mec-activation">
                        <div class="w-box-head">
                            <?php esc_html_e('License Activation', 'modern-events-calendar-lite'); ?>
                        </div>
                        <?php if (current_user_can('administrator')): ?>
                            <div class="w-box-content">
                                <div class="box-addons-activation">
                                    <?php $mec_options = get_option('mec_options'); ?>
                                    <div class="box-addon-activation-toggle-head"><i class="mec-sl-plus"></i><span><?php esc_html_e('Activate Addons', 'modern-events-calendar-lite'); ?></span></div>
                                    <div class="box-addon-activation-toggle-content">
                                        <?php do_action('addons_activation'); ?>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="w-box-content">
                                <p style="background: #f7f7f7f7;display: inline-block;padding: 17px 35px;border-radius: 3px;/* box-shadow: 0 1px 16px rgba(0,0,0,.034); */"><?php echo esc_html__('You cannot access this section.', 'modern-events-calendar-lite'); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
            <?php // Package, not licence: this box is how an unlicensed site recovers. ?>
            <?php if ($this->isProBuild()) : ?>
                <div class="w-col-sm-12">
                    <div class="w-box mec-activation">
                        <div class="w-box-head">
                            <?php esc_html_e('License Activation', 'modern-events-calendar-lite'); ?>
                        </div>
                        <?php
                        if (current_user_can('administrator')):
                        ?>
                            <div class="w-box-content">
                                <p><?php echo esc_html__('In order to use all plugin features and options, please enter your purchase code.', 'modern-events-calendar-lite'); ?></p>
                                <div class="box-mec-avtivation">
                                    <?php
                                    $mec_options = get_option('mec_options');
                                    $product_license = '';
                                    $license_status = '';
                                    $class_name = 'mec_activate';
                                    $button_value = esc_html__('submit', 'modern-events-calendar-lite');

                                    if (!empty($mec_options) and is_array($mec_options) and isset($mec_options['purchase_code'])) $product_license = $mec_options['purchase_code'];

                                    // An offline-activated site has no purchase code stored, but
                                    // it is still licensed. Show a placeholder so the field does
                                    // not look broken/empty next to the green "activated" state.
                                    $mec_is_offline_licensed = class_exists('MEC_license')
                                        && MEC_license::instance()->licensed()
                                        && empty($product_license);

                                    // mec_license_status only records what the legacy
                                    // activation endpoint said, which now governs auto-updates
                                    // alone. Whether Pro actually runs is the signed token, and a
                                    // site can hold a perfectly valid purchase code while having
                                    // no token — the claim was refused, or the server was
                                    // unreachable. That is precisely the state in which Pro is
                                    // quietly ramping down, so it must not show green.
                                    $mec_license_token = class_exists('MEC_license') && MEC_license::instance()->licensed();

                                    if ($mec_license_token) {
                                        // The signed token is valid — Pro is running. This covers
                                        // both the online claim and the offline-token path. In the
                                        // offline case, purchase_code may be empty and
                                        // mec_license_status may be unset, but the token is what
                                        // matters.
                                        $license_status = 'PurchaseSuccess';
                                        $revoke = true;
                                        $class_name = 'mec_revoke';
                                        $button_value = esc_html__('revoke', 'modern-events-calendar-lite');
                                    } elseif (!empty($mec_options['purchase_code']) && $mec_license_status == 'active') {
                                        $license_status = 'PurchaseError';
                                        $revoke = true;
                                        $class_name = 'mec_revoke';
                                        $button_value = esc_html__('revoke', 'modern-events-calendar-lite');
                                    } elseif (!empty($mec_options['purchase_code']) && $mec_license_status == 'faild') {
                                        $license_status = 'PurchaseError';
                                        $revoke = false;
                                    }

                                    // If the site is running on a limited (offline) token, show the
                                    // expiry date prominently so the customer activates the real
                                    // licence before it lapses.
                                    $mec_token_exp = (class_exists('MEC_license')) ? MEC_license::instance()->token_expiry() : null;
                                    ?>
                                    <form id="MECActivation" action="#" method="post">
                                        <?php if ($mec_token_exp !== null): ?>
                                        <div class="mec-notice mec-notice--warning">
                                            <div class="mec-notice-accent"></div>
                                            <div class="mec-notice-body">
                                                <div class="mec-notice-title"><?php esc_html_e('Offline activation', 'modern-events-calendar-lite'); ?></div>
                                                <p class="mec-notice-text">
                                                    <?php
                                                    $exp_format = get_option('date_format') ?: 'Y-m-d';
                                                    printf(
                                                        /* translators: %s: expiry date */
                                                        esc_html__('Your offline activation token expires on %s. Enter your real purchase code below before then to keep Pro features running.', 'modern-events-calendar-lite'),
                                                        '<strong>' . esc_html(date_i18n($exp_format, $mec_token_exp)) . '</strong>'
                                                    );
                                                    ?>
                                                </p>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                        <div class="LicenseField">
                                            <input type="password" placeholder="<?php echo $mec_is_offline_licensed ? esc_attr__('Activated via offline token. Enter your purchase code to switch', 'modern-events-calendar-lite') : 'Put your purchase code here'; ?>" name="MECPurchaseCode" value="<?php echo esc_html($product_license); ?>">
                                            <input type="submit" class="<?php echo esc_html($class_name); ?>" value="<?php echo esc_html($button_value); ?>">
                                            <div class="MECPurchaseStatus <?php echo esc_html($license_status); ?>"></div>
                                        </div>
                                        <div class="MECLicenseMessage mec-message-hidden">
                                            <?php
                                            echo esc_html__('Activation failed. Please check your purchase code or license type. Note: Your purchase code should match your licesne type.', 'modern-events-calendar-lite') . '<a style="text-decoration: underline; padding-left: 7px;" href="https://webnus.net/dox/modern-events-calendar/auto-update/" target="_blank">'  . esc_html__('Troubleshooting', 'modern-events-calendar-lite') . '</a>';
                                            ?>
                                        </div>
                                    </form>
                                </div>

                                <div class="box-addons-activation">
                                    <?php $mec_options = get_option('mec_options'); ?>
                                    <div class="box-addon-activation-toggle-head"><i class="mec-sl-plus"></i><span><?php esc_html_e('Activate Addons', 'modern-events-calendar-lite'); ?></span></div>
                                    <div class="box-addon-activation-toggle-content">
                                        <?php do_action('addons_activation'); ?>
                                    </div>
                                </div>

                                <?php
                                // Offline activation. For the customer who bought through a
                                // reseller, lost their purchase code, or runs on a host with no
                                // outbound HTTPS. Support mints a token for their domain and they
                                // paste it here.
                                //
                                // Hidden once the site is licensed: an activated site (online or
                                // offline) has no use for another token, and showing the box next
                                // to a green "activated" state is confusing.
                                if (!$mec_license_token):
                                ?>
                                <div class="box-mec-offline-activation">
                                    <p><?php esc_html_e('No outbound internet connection on this server, or bought through a reseller? Ask support for an offline activation token and paste it below.', 'modern-events-calendar-lite'); ?></p>
                                    <?php
                                    // The exact host the token is bound to. Shown because the
                                    // customer's idea of their domain and home_url() disagree
                                    // often enough — a subdomain, a path, a stray www — and a
                                    // token minted for the wrong one is refused, costing a
                                    // second support round trip on a site that is already
                                    // ramping. Quoting it here makes the ticket unambiguous.
                                    ?>
                                    <p class="mec-offline-site-id">
                                        <?php printf(
                                            esc_html__('Quote this site address when you ask: %s', 'modern-events-calendar-lite'),
                                            '<code>' . esc_html(MEC_license::instance()->host()) . '</code>'
                                        ); ?>
                                        <button type="button" class="mec-offline-copy-host" data-host="<?php echo esc_attr(MEC_license::instance()->host()); ?>"><?php esc_html_e('Copy', 'modern-events-calendar-lite'); ?></button>
                                    </p>
                                    <p class="mec-offline-support-link">
                                        <a class="mec-offline-support-btn" href="https://webnus.net/support" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Contact support', 'modern-events-calendar-lite'); ?></a>
                                    </p>
                                    <form id="MECOfflineActivation" action="#" method="post">
                                        <div class="LicenseField">
                                            <input type="text" name="MECOfflineToken" autocomplete="off" spellcheck="false" placeholder="<?php echo esc_attr__('Paste your offline activation token here', 'modern-events-calendar-lite'); ?>">
                                            <input type="submit" value="<?php echo esc_attr__('Activate offline', 'modern-events-calendar-lite'); ?>">
                                        </div>
                                        <div class="MECOfflineMessage" role="status" aria-live="polite"></div>
                                    </form>
                                </div>

                                <script>
                                (function () {
                                    var copyBtn = document.querySelector('.mec-offline-copy-host');

                                    // One-click copy of the host so the support ticket is unambiguous.
                                    if (copyBtn) {
                                        copyBtn.addEventListener('click', function () {
                                            var host = copyBtn.getAttribute('data-host') || '';
                                            var label = copyBtn.textContent;

                                            var done = function () {
                                                copyBtn.textContent = <?php echo wp_json_encode(esc_html__('Copied!', 'modern-events-calendar-lite')); ?>;
                                                copyBtn.classList.add('mec-copied');
                                                window.setTimeout(function () {
                                                    copyBtn.textContent = label;
                                                    copyBtn.classList.remove('mec-copied');
                                                }, 1500);
                                            };

                                            if (navigator.clipboard && navigator.clipboard.writeText) {
                                                navigator.clipboard.writeText(host).then(done, done);
                                            } else {
                                                var ta = document.createElement('textarea');
                                                ta.value = host;
                                                document.body.appendChild(ta);
                                                ta.select();
                                                try { document.execCommand('copy'); } catch (e) {}
                                                document.body.removeChild(ta);
                                                done();
                                            }
                                        });
                                    }

                                    var form = document.getElementById('MECOfflineActivation');
                                    if (!form) return;

                                    var out = form.querySelector('.MECOfflineMessage');
                                    var submit = form.querySelector('input[type=submit]');

                                    form.addEventListener('submit', function (event) {
                                        event.preventDefault();

                                        var body = new FormData();
                                        body.append('action', 'mec_lic_token');
                                        body.append('nonce', mec_admin_localize.ajax_nonce);
                                        body.append('token', form.querySelector('input[name=MECOfflineToken]').value);

                                        submit.disabled = true;
                                        out.textContent = '';

                                        window.fetch(mec_admin_localize.ajax_url, {
                                            method: 'POST',
                                            credentials: 'same-origin',
                                            body: body
                                        }).then(function (response) {
                                            return response.json();
                                        }).then(function (result) {
                                            var success = !!(result && result.success);
                                            var message = (result && result.data && result.data.message) ? result.data.message : '';

                                            if (success) {
                                                // Keep the server message (it carries the expiry date)
                                                // across the reload: without this the page refresh
                                                // wipes it before the customer can read it.
                                                try { sessionStorage.setItem('mec_offline_activated', message); } catch (e) {}

                                                // Reload immediately so the success card replaces the
                                                // box (no inline flash message to lose).
                                                window.location.reload();
                                                return;
                                            }

                                            out.textContent = message;
                                            out.className = 'MECOfflineMessage mec-offline-error';
                                        })['catch'](function () {
                                            out.textContent = <?php echo wp_json_encode(esc_html__('Could not reach this site\'s admin. Please reload the page and try again.', 'modern-events-calendar-lite')); ?>;
                                            out.className = 'MECOfflineMessage mec-offline-error';
                                        }).then(function () {
                                            submit.disabled = false;
                                        });
                                    });
                                })();
                                </script>
                            </div>
                                <?php endif; // !$mec_license_token ?>

                                <?php
                                // Success feedback after the reload above: the server message
                                // (with the token expiry date) was parked in sessionStorage;
                                // render it as a green notice above the license form, once.
                                ?>
                                <script>
                                (function () {
                                    var message = '';
                                    try {
                                        message = sessionStorage.getItem('mec_offline_activated') || '';
                                        sessionStorage.removeItem('mec_offline_activated');
                                    } catch (e) {}

                                    if (!message) return;

                                    function show() {
                                        var box = document.querySelector('.box-mec-avtivation');
                                        if (!box) return;

                                        // The green success card carries the expiry date already,
                                        // so the persistent yellow expiry warning is redundant on
                                        // this one load. It returns on the next visit.
                                        var yellow = box.querySelector('.mec-notice--warning');
                                        if (yellow) yellow.parentNode.removeChild(yellow);

                                        var card = document.createElement('div');
                                        card.className = 'mec-notice mec-notice--success';
                                        card.innerHTML = '<div class="mec-notice-accent"></div><div class="mec-notice-body"><div class="mec-notice-title"><?php echo esc_js(esc_html__('Offline activation', 'modern-events-calendar-lite')); ?></div><p class="mec-notice-text"></p></div>';
                                        card.querySelector('.mec-notice-text').textContent = message;

                                        box.insertBefore(card, box.firstChild);
                                    }

                                    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', show);
                                    else show();
                                })();
                                </script>
                        <?php
                        else: ?>
                            <div class="w-box-content">
                                <p style="background: #f7f7f7f7;display: inline-block;padding: 17px 35px;border-radius: 3px;/* box-shadow: 0 1px 16px rgba(0,0,0,.034); */"><?php echo esc_html__('You cannot access this section.', 'modern-events-calendar-lite'); ?></p>
                            </div>
                        <?php
                        endif;
                        ?>
                    </div>
                </div>
            <?php endif; ?>
            <?php if (current_user_can('read')): ?>
                <div class="w-col-sm-3">
                    <div class="w-box doc">
                        <div class="w-box-child mec-count-child">
                            <p><?php echo '<p class="mec_dash_count">' . esc_html($user_post_count) . '</p> ' . esc_html__('Events', 'modern-events-calendar-lite'); ?></p>
                        </div>
                    </div>
                </div>
                <div class="w-col-sm-3">
                    <div class="w-box doc">
                        <div class="w-box-child mec-count-child">
                            <p><?php echo '<p class="mec_dash_count">' . esc_html($user_post_count_c) . '</p> ' . esc_html__('Shortcodes', 'modern-events-calendar-lite'); ?></p>
                        </div>
                    </div>
                </div>
                <div class="w-col-sm-3">
                    <div class="w-box doc">
                        <div class="w-box-child mec-count-child">
                            <p><?php echo '<p class="mec_dash_count">' . esc_html($user_location_count_l) . '</p> ' . esc_html__('Locations', 'modern-events-calendar-lite'); ?></p>
                        </div>
                    </div>
                </div>
                <div class="w-col-sm-3">
                    <div class="w-box doc">
                        <div class="w-box-child mec-count-child">
                            <p><?php echo '<p class="mec_dash_count">' . esc_html($user_organizer_count_l) . '</p> ' . esc_html__('Organizers', 'modern-events-calendar-lite'); ?></p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <?php if ($box_stats): ?>
            <div class="w-row">
                <div class="w-col-sm-<?php echo ($booking_status ? 6 : 12); ?>">
                    <div class="w-box upcoming-events">
                        <div class="w-box-head">
                            <?php esc_html_e('Upcoming Events', 'modern-events-calendar-lite'); ?>
                        </div>
                        <div class="w-box-content">
                            <?php
                            $render = $this->getRender();
                            echo MEC_kses::full($render->skin('list', array(
                                'sk-options' => array('list' => array(
                                    'style' => 'minimal',
                                    'start_date_type' => 'today',
                                    'pagination_method' => '0',
                                    'limit' => '6',
                                    'month_divider' => '0',
                                    'load_more_button' => false,
                                    'ignore_js' => true
                                ))
                            )));
                            ?>
                        </div>
                    </div>
                </div>
                <?php if ($booking_status): ?>
                    <div class="w-col-sm-6">
                        <div class="w-box gateways">
                            <div class="w-box-head">
                                <?php echo esc_html__('Popular Gateways', 'modern-events-calendar-lite'); ?>
                            </div>
                            <div class="w-box-content">
                                <?php
                                $results = $db->select("SELECT COUNT(`meta_id`) AS count, `meta_value` AS gateway FROM `#__postmeta` WHERE `meta_key`='mec_gateway' GROUP BY `meta_value`", 'loadAssocList');

                                $labels = '';
                                $data = '';
                                $bg_colors = '';

                                foreach ($results as $result) {
                                    if (!class_exists($result['gateway'])) {
                                        continue;
                                    }

                                    $gateway = new $result['gateway'];
                                    $stats[] = array('label' => $gateway->title(), 'count' => $result['count']);

                                    $labels .= '"' . esc_html($gateway->title()) . '",';
                                    $data .= ((int) $result['count']) . ',';
                                    $bg_colors .= "'" . $gateway->color() . "',";
                                }
                                echo '<canvas id="mec_gateways_chart" width="300" height="300"></canvas>';

                                $this->getFactory()->params('footer', '<script>
                            jQuery(document).ready(function()
                            {
                                var ctx = document.getElementById("mec_gateways_chart");
                                var mecGatewaysChart = new Chart(ctx,
                                {
                                    type: "doughnut",
                                    data:
                                    {
                                        labels: [' . trim($labels, ', ') . '],
                                        datasets: [
                                        {
                                            data: [' . trim($data, ', ') . '],
                                            backgroundColor: [' . trim($bg_colors, ', ') . ']
                                        }]
                                    }
                                });
                            });
                            </script>');
                                ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            <?php if ($booking_status and current_user_can('mec_settings')) echo (new MEC_feature_mec())->widget_total_bookings(); ?>
        <?php endif; ?>

        <?php if ($this->getPRO()) (new MEC_feature_mec())->widget_print(); ?>

        <div class="w-row">
            <div class="w-col-sm-12">
                <div class="w-box change-log">
                    <div class="w-box-head">
                        <?php echo esc_html__('Change Log', 'modern-events-calendar-lite'); ?>
                    </div>
                    <div class="w-box-content">
                        <pre><?php echo file_get_contents(plugin_dir_path(__FILE__) . '../../../changelog.txt'); ?></pre>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
