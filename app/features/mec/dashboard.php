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

// I-04: stat cards link to their lists, so their numbers should match what
// those lists show - every status except trash/auto-draft, not just
// published (drafts and pending are real work in progress).
$mec_stat_count = function ($counts_object) {
    $statuses = (array) $counts_object;
    unset($statuses['trash'], $statuses['auto-draft']);
    return array_sum(array_map('intval', $statuses));
};
$mec_stat_events = $mec_stat_count($count_events);
$mec_stat_calendars = $mec_stat_count($count_calendars);

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

$styling = $this->get_styling();
$darkadmin_mode = $this->is_admin_dark_mode() ? 1 : '';

// IA-04: "Welcome {first name}" broke for users without a first name (a
// dangling "Welcome ") and kept its onboarding tone forever. Fall back to
// the display name, and once the site actually has published events, swap
// the header to a dashboard tone with a live status line instead of the
// static marketing sentence.
$mec_hello_name = trim((string) $current_user->user_firstname) ? $current_user->user_firstname : $current_user->display_name;
$mec_is_onboarding = ((int) $user_post_count === 0);

// IA-06: the version chip must answer "what package is this, and is it
// current?". Package + version render locally; the update state is a cheap
// read of the WP updates transient (wp.org for Lite, MEC's update server
// for Pro - both register themselves in the same place).
//
// The basename must match the key WP files updates under: the plugin's REAL
// entry file is mec.php (bugfix: a hand-built path to
// modern-events-calendar.php never matched, so the chip stayed green
// forever). MEC_ABSPATH points at the plugin root, so this is depth-proof.
$mec_plugin_basename = plugin_basename(MEC_ABSPATH . 'mec.php');
$mec_updates_transient = get_site_transient('update_plugins');
$mec_update_available = is_object($mec_updates_transient) && isset($mec_updates_transient->response[$mec_plugin_basename]);

/* ════════════════════════════════════════════════════════════════════
   IA-01 state engine.

   The page renders in six zones (A: brandhead · B: license/critical ·
   C: announcements · D: primary work · E: analytics · F: utilities) and
   what each zone shows is decided here, from data that is already
   computed. The license flags below used to live mid-template inside the
   activation box; they are lifted here so Zone B can act on them without
   duplicating logic. No new queries, no behavior change inside the form.
   ════════════════════════════════════════════════════════════════════ */

// Single fetch (Q-02): this option was read three times per load.
$mec_options = get_option('mec_options');

// Multisite: the licence is bought, entered and revoked once, on the
// network's main site, and every subsite inherits it. A subsite owner has
// nothing to enter; add-ons are separate products and render as a toggle
// inside the same box when the subsite has any.
$mec_network_subsite = $this->isProBuild() && class_exists('MEC_license') && !MEC_license::is_license_site();
// Only claim network coverage when the main-site licence is actually active;
// otherwise the green "Network license" notice would mislead the subsite admin.
$mec_network_licence = $mec_network_subsite && MEC_license::instance()->licensed();

// Bundled entitlement (X theme and anything like it): licensed through the
// theme, no purchase code of their own.
$mec_partner = (!$mec_network_subsite && class_exists('MEC_license')) ? MEC_license::instance()->partner_licence() : null;
$mec_partner_name = is_array($mec_partner) ? ucwords(str_replace(array('-', '_'), ' ', $mec_partner['partner'])) : '';

$product_license = (!empty($mec_options) and is_array($mec_options) and isset($mec_options['purchase_code'])) ? $mec_options['purchase_code'] : '';

// Whether Pro actually runs is the signed token, not mec_license_status:
// a site can hold a valid purchase code while the claim was refused or the
// server was unreachable - the exact state where Pro ramps down.
$mec_license_token = class_exists('MEC_license') && MEC_license::instance()->licensed();
$mec_token_exp = ($mec_license_token && class_exists('MEC_license')) ? MEC_license::instance()->token_expiry() : null;

// The offline notice must persist for the whole life of a limited token -
// it answers "how long is my license valid?", which never stops mattering.
// Gating it on "no purchase code stored" made it vanish on reload for any
// install that also holds a (possibly unclaimable) code. token_expiry() is
// non-null exactly for limited, non-partner tokens - the real offline
// population - so that is the whole condition.
$mec_is_offline_licensed = $mec_license_token && $mec_token_exp !== null;
$mec_license_active = $mec_license_token || is_array($mec_partner);

// IA-01 (v2): the Lite upsell is dismissible per user, but only for 30
// days - a new look must eventually return, and marketing campaigns must
// keep their surface. The template compares the stored timestamp.
$mec_upsell_dismissed_at = (int) get_user_meta(get_current_user_id(), 'mec_intro_upsell_dismissed', true);
$mec_show_upsell = !$mec_upsell_dismissed_at || (time() - $mec_upsell_dismissed_at) > 30 * DAY_IN_SECONDS;
?>
<div id="webnus-dashboard" class="wrap about-wrap mec-intro-page">
    <div class="mec-ds mec-intro-v2" id="mecIntro">

        <?php /* ═══ ZONE A - orientation: the .mec-ds header shared with the
               Support / Addons screens (IA-04 tone logic kept: greeting for
               fresh installs, dashboard tone once events exist). ═══ */ ?>
        <header class="mec-page-head mec-page-head--split">
            <div>
                <div class="mec-page-eyebrow"><?php esc_html_e('Modern Events Calendar', 'modern-events-calendar-lite'); ?></div>
                <div class="ms-title"><span class="mec-page-mark"><span class="ico" data-i="home"></span></span><h1><?php printf(esc_html__('Welcome %s', 'modern-events-calendar-lite'), esc_html($mec_hello_name)); ?></h1></div>
                <p class="mec-page-lead"><?php echo sprintf(esc_html__('%s - the most powerful yet easy-to-use event management system', 'modern-events-calendar-lite'), '<strong>' . ($this->getPRO() ? esc_html__('Modern Events Calendar', 'modern-events-calendar-lite') : esc_html__('Modern Events Calendar (Lite)', 'modern-events-calendar-lite')) . '</strong>'); ?></p>
                <div class="ms-meta">
                    <span class="tag">MEC <?php echo esc_html(MEC_VERSION); ?></span>
                    <?php if ($this->isProBuild()): ?><span class="tag pro"><?php esc_html_e('Pro', 'modern-events-calendar-lite'); ?></span><?php endif; ?>
                    <?php if ($mec_license_active): ?>
                        <span class="tag ok"><span class="d"></span><?php esc_html_e('License active', 'modern-events-calendar-lite'); ?></span>
                    <?php elseif ($this->isProBuild()): ?>
                        <span class="tag warn"><span class="d"></span><?php esc_html_e('License not activated', 'modern-events-calendar-lite'); ?></span>
                    <?php else: ?>
                        <span class="tag"><?php esc_html_e('Lite (free)', 'modern-events-calendar-lite'); ?></span>
                    <?php endif; ?>
                    <?php if ($mec_update_available): ?>
                        <span class="tag warn"><span class="d"></span><?php esc_html_e('Update available', 'modern-events-calendar-lite'); ?></span>
                    <?php else: ?>
                        <span class="tag ok"><span class="d"></span><?php esc_html_e('Up to date', 'modern-events-calendar-lite'); ?></span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="ms-actions">
                <a class="btn btn-sec" href="<?php echo esc_url(admin_url('post-new.php?post_type=mec-events')); ?>"><span class="ico" data-i="plus"></span><?php esc_html_e('New Event', 'modern-events-calendar-lite'); ?></a>
                <?php if ($mec_update_available): ?>
                <a class="btn btn-warn" href="<?php echo esc_url(admin_url('update-core.php')); ?>"><span class="ico" data-i="refresh"></span><?php esc_html_e('Update now', 'modern-events-calendar-lite'); ?></a>
                <?php endif; ?>
            </div>
        </header>
        <hr class="wp-header-end" />
        <?php /* ═══ ZONE C - announcements (single system: the remote feed) ═══ */ ?>
        <?php
        // IA-02: one notification system, one design. The announcement comes
        // exclusively from notifications.webnus.site (category 3 = Lite, 6 =
        // Pro) and renders in the unified mec-intro-notice--remote card.
        // The legacy mec_custom_msg / mec_custom_msg_2 boxes (old w-box
        // design, hardcoded promo HTML, broken option-cache chain - see
        // P-05) were retired from rendering everywhere.
        $notification_category = $this->getPRO() ? 6 : 3;

        // The announcement is served from a transient: this remote call used
        // to run inline on every dashboard load with a 50s timeout, so a slow
        // or unreachable notifications server could hang the whole admin page
        // (the old "Fix for cURL error 28" only made the hang longer). It now
        // runs only on a cold cache with a short timeout, and failures are
        // negative-cached so a dead endpoint is not retried on every load.
        $remote_posts = get_transient('mec_intro_remote_posts_' . $notification_category);

        if ($remote_posts === false) {
            $response_lite = wp_remote_get(
                add_query_arg(
                    array( // posts from 101 to 200
                        'per_page' => 1,
                        'page' => 1,
                        'categories' => $notification_category,
                        '_embed' => 'wp:featuredmedia',
                    ),
                    'https://notifications.webnus.site/wp-json/wp/v2/posts'
                ),
                array(
                    'timeout' => 3,
                )
            );

            $remote_posts = array();
            if (!is_wp_error($response_lite) && wp_remote_retrieve_response_code($response_lite) == 200) {
                $decoded = json_decode(wp_remote_retrieve_body($response_lite));
                if (is_array($decoded)) {
                    $remote_posts = $decoded;
                }
            }

            // Keep a good response for 12 hours; an error or empty result for
            // only 1 hour so a transient outage is retried reasonably soon.
            set_transient('mec_intro_remote_posts_' . $notification_category, $remote_posts, !empty($remote_posts) ? 12 * HOUR_IN_SECONDS : HOUR_IN_SECONDS);
        }

        $body = $remote_posts;

        if (!empty($body) && is_array($body) && count($body) > 0) :
            $title = $body[0]->title->rendered ?? '';
            $content = $body[0]->content->rendered ?? '';
            $lite_featured_image = $body[0]->_embedded->{'wp:featuredmedia'}[0]->source_url ?? '';

            // I-02: the announcement is dismissible per user, keyed by the
            // remote post ID. A dismissed post stays hidden for that user;
            // whatever the feed publishes next shows up until dismissed.
            $remote_post_id = (int) ($body[0]->id ?? 0);
            $mec_remote_dismissed_id = (int) get_user_meta(get_current_user_id(), 'mec_intro_remote_dismissed', true);
            ?>
            <?php if ($remote_post_id && $remote_post_id !== $mec_remote_dismissed_id): ?>
            <div class="mec-intro-notice mec-intro-notice--remote<?php echo $lite_featured_image ? ' has-image' : ''; ?>" id="mecRemoteNotice">
                <button type="button" class="mec-remote-close" aria-label="<?php echo esc_attr__('Dismiss this announcement', 'modern-events-calendar-lite'); ?>" title="<?php echo esc_attr__('Dismiss this announcement', 'modern-events-calendar-lite'); ?>" data-post-id="<?php echo esc_attr($remote_post_id); ?>"><i class="mec-sl-close" aria-hidden="true"></i></button>
                <?php if ($lite_featured_image): ?>
                    <span class="mec-intro-notice__img"><img src="<?php echo esc_url($lite_featured_image); ?>" alt="<?php echo esc_attr(wp_strip_all_tags($title)); ?>" /></span>
                <?php endif; ?>
                <div class="mec-intro-notice__body">
                    <span class="mec-intro-notice__tag"><?php esc_html_e('Announcement', 'modern-events-calendar-lite'); ?></span>
                    <h2><?php echo esc_html(wp_strip_all_tags($title)); ?></h2>
                    <?php // Remote HTML must pass core KSES. MEC_kses::full is NOT safe here: it whitelists raw <script>/<style> blocks for MEC's own trusted markup, which would defeat the point of sanitizing an untrusted feed. ?>
                    <p><?php echo wp_kses($content, wp_kses_allowed_html('post')); ?></p>
                </div>
            </div>
            <script>
                (function () {
                    var card = document.getElementById('mecRemoteNotice');
                    if (!card) return;

                    var btn = card.querySelector('.mec-remote-close');
                    if (!btn) return;

                    // Per-user dismissal, keyed by the remote post ID. The card
                    // hides optimistically; the server records which post was
                    // dismissed so this one stays gone while new ones appear.
                    btn.addEventListener('click', function () {
                        var body = new FormData();
                        body.append('action', 'mec_intro_remote_dismiss');
                        body.append('nonce', mec_admin_localize.ajax_nonce);
                        body.append('post_id', btn.getAttribute('data-post-id') || '0');

                        card.style.display = 'none';

                        window.fetch(mec_admin_localize.ajax_url, {
                            method: 'POST',
                            credentials: 'same-origin',
                            body: body
                        })['catch'](function () {});
                    });
                })();
            </script>
            <?php endif; ?>
        <?php
        // IA-02: no legacy fallback anymore. When the feed has nothing, no
        // announcement renders at all - the empty/unreachable case leaves no
        // margin-only row behind.
        endif;
        ?>


        <?php /* ═══ ZONE W - getting-started walkthrough (revision 13: right
               below the announcement row, above the license box) ═══ */ ?>
        <?php include plugin_dir_path(__FILE__) . 'dashboard-walkthrough.php'; ?>


        <?php /* ═══ ZONE B - license & critical status (IA-01) ═══ */ ?>
        <?php if ($this->isProBuild()): ?>

            <?php
            // Multisite subsite (revision 7): same box, same notice
            // component as the offline/partner states. The network notice
            // sits inside the License Activation box where the intro
            // paragraph would be (that paragraph is wrong for a subsite -
            // there is no code to enter), and the addons toggle renders
            // below it only when the subsite actually has add-ons.
            // C-04: administrator-only - no dead "no access" box for others.
            if ($mec_network_licence && current_user_can('administrator')): ?>
                <div class="w-box mec-activation mec-intro-card" id="mec-activation-box">
                    <h2 class="w-box-head">
                        <?php esc_html_e('License Activation', 'modern-events-calendar-lite'); ?>
                    </h2>
                        <div class="w-box-content">
                            <div class="mec-notice mec-notice--success">
                                <div class="mec-notice-accent"></div>
                                <div class="mec-notice-body">
                                    <h3 class="mec-notice-title"><?php esc_html_e('Network license', 'modern-events-calendar-lite'); ?></h3>
                                    <p class="mec-notice-text"><?php esc_html_e('MEC Pro is licensed by your network, so no purchase code is needed on this site.', 'modern-events-calendar-lite'); ?></p>
                                </div>
                            </div>
                            <?php if (has_action('addons_activation')) include plugin_dir_path(__FILE__) . 'dashboard-addonstoggle.php'; ?>
                        </div>
                </div>

            <?php
            // Multisite subsite without an active network licence: do not show
            // the green "Network license" claim, and do not offer a purchase
            // code form the subsite cannot use - point the admin at the main site.
            elseif ($mec_network_subsite && current_user_can('administrator')): ?>
                <div class="w-box mec-activation mec-intro-card" id="mec-activation-box">
                    <h2 class="w-box-head">
                        <?php esc_html_e('License Activation', 'modern-events-calendar-lite'); ?>
                    </h2>
                    <div class="w-box-content">
                        <div class="mec-notice mec-notice--warning">
                            <div class="mec-notice-accent"></div>
                            <div class="mec-notice-body">
                                <h3 class="mec-notice-title"><?php esc_html_e('Network license', 'modern-events-calendar-lite'); ?></h3>
                                <p class="mec-notice-text"><?php esc_html_e('MEC Pro is not activated on this network yet. Activate the license on the main site - every site in the network is covered by that one activation.', 'modern-events-calendar-lite'); ?></p>
                            </div>
                        </div>
                    </div>
                </div>

            <?php
            // Licensed (token or partner): the activation box stays fully
            // visible (owner decision, no collapsed toggle) - its own green
            // state and revoke button answer "am I okay?" directly.
            elseif ($mec_license_active): ?>
                <?php include plugin_dir_path(__FILE__) . 'dashboard-licensebox.php'; ?>

            <?php
            // Unlicensed: the box is THE action of this screen, so it sits
            // directly below the header. A stored-but-inactive code means
            // Pro is ramping down - that state gets an explicit warning bar.
            else: ?>
                <?php if (!empty($product_license)): ?>
                    <div class="mec-licensebar mec-licensebar--warn" role="alert">
                        <span class="mec-licensebar__dot" aria-hidden="true"></span>
                        <span class="mec-licensebar__text"><?php esc_html_e('Your purchase code is saved but not activated - Pro features will keep winding down until you activate the license.', 'modern-events-calendar-lite'); ?></span>
                    </div>
                <?php endif; ?>
                <?php include plugin_dir_path(__FILE__) . 'dashboard-licensebox.php'; ?>
            <?php endif; ?>

        <?php
        // Lite: the upsell card keeps its full size and its prime spot in
        // Zone B (v2 decision) - data-driven copy for mature installs,
        // coupon slot and social proof intact, dismissible for 30 days.
        elseif ($mec_show_upsell): ?>
            <div class="mec-intro-notice mec-intro-notice--upsell" id="mecIntroUpsell">
                <button type="button" class="mec-upsell-close" aria-label="<?php echo esc_attr__('Hide this for 30 days', 'modern-events-calendar-lite'); ?>"><i class="mec-sl-close" aria-hidden="true"></i></button>
                <div class="mec-intro-notice__body">
                    <span class="mec-intro-notice__tag"><?php esc_html_e('Lite', 'modern-events-calendar-lite'); ?></span>
                    <?php if (!$mec_is_onboarding): ?>
                        <p>
                            <?php printf(
                                esc_html__("You're managing %s events on the Lite version. Upgrade to Pro to unlock the booking system, modern skins like Agenda, Timetable, Masonry, Yearly View, Available Spots, and more.", 'modern-events-calendar-lite'),
                                '<strong>' . esc_html(number_format_i18n((int) $user_post_count)) . '</strong>'
                            ); ?>
                        </p>
                    <?php else: ?>
                        <p>
                            <?php esc_html_e("You're using the Lite version of Modern Events Calendar. Upgrade to Pro to unlock the booking system, modern skins like Agenda, Timetable, Masonry, Yearly View, Available Spots, and more.", 'modern-events-calendar-lite'); ?>
                        </p>
                    <?php endif; ?>
                    <div class="info-msg-coupon"></div>
                    <div class="socialfollow">
                        <?php
                        // A-02: icon-only links are announced as "link, link,
                        // link..." by screen readers - every one needs its
                        // accessible name. Twitter is labeled for X, the rebranded
                        // name; the URL redirects on its own.
                        ?>
                        <a target="_blank" rel="noopener noreferrer" href="https://www.facebook.com/WebnusCo/" class="facebook" aria-label="<?php echo esc_attr__('Webnus on Facebook', 'modern-events-calendar-lite'); ?>">
                            <i class="mec-sl-social-facebook" aria-hidden="true"></i>
                        </a>
                        <a target="_blank" rel="noopener noreferrer" href="https://twitter.com/webnus" class="twitter" aria-label="<?php echo esc_attr__('Webnus on X (Twitter)', 'modern-events-calendar-lite'); ?>">
                            <i class="mec-sl-social-twitter" aria-hidden="true"></i>
                        </a>
                        <a target="_blank" rel="noopener noreferrer" href="https://www.instagram.com/webnus/" class="instagram" aria-label="<?php echo esc_attr__('Webnus on Instagram', 'modern-events-calendar-lite'); ?>">
                            <i class="mec-sl-social-instagram" aria-hidden="true"></i>
                        </a>
                        <a target="_blank" rel="noopener noreferrer" href="https://www.youtube.com/channel/UCmQ-VeVK7nLR3bGpAkSYB1Q" class="youtube" aria-label="<?php echo esc_attr__('Webnus on YouTube', 'modern-events-calendar-lite'); ?>">
                            <i class="mec-sl-social-youtube" aria-hidden="true"></i>
                        </a>
                        <a target="_blank" rel="noopener noreferrer" href="https://dribbble.com/Webnus" class="dribbble" aria-label="<?php echo esc_attr__('Webnus on Dribbble', 'modern-events-calendar-lite'); ?>">
                            <i class="mec-sl-social-dribbble" aria-hidden="true"></i>
                        </a>
                    </div>
                </div>
                <a class="mec-intro-btn" href="<?php echo esc_url($this->get_pro_link()); ?>" target="_blank" rel="noopener noreferrer">
                    <?php esc_html_e('Go Premium', 'modern-events-calendar-lite'); ?>
                </a>
            </div>
            <script>
                (function () {
                    var card = document.getElementById('mecIntroUpsell');
                    if (!card) return;

                    var btn = card.querySelector('.mec-upsell-close');
                    if (!btn) return;

                    // Per-user, 30-day dismissal. The card hides optimistically;
                    // the server only records the timestamp (the template decides
                    // when it may return).
                    btn.addEventListener('click', function () {
                        var body = new FormData();
                        body.append('action', 'mec_intro_upsell_dismiss');
                        body.append('nonce', mec_admin_localize.ajax_nonce);

                        card.style.display = 'none';

                        window.fetch(mec_admin_localize.ajax_url, {
                            method: 'POST',
                            credentials: 'same-origin',
                            body: body
                        })['catch'](function () {});
                    });
                })();
            </script>
        <?php endif; ?>

        <?php /* ═══ ZONE D - primary work (fresh: checklist · mature: stats + upcoming) ═══ */ ?>
        <?php if ($mec_is_onboarding): ?>
            <?php
            // Fresh install: a 3-step checklist with REAL completion state
            // replaces the stats wall of zeros and the generic copy (v2
            // decision: stats stay hidden until the first event exists).
            $mec_step1_done = ((int) $user_post_count > 0);
            $mec_step2_done = ((int) $user_post_count_c > 0);
            $mec_step3_done = $mec_license_active;
            ?>
            <div class="mec-intro-card mec-intro-card--pad mec-checklist-card">
                <h2><?php esc_html_e('Set up your calendar', 'modern-events-calendar-lite'); ?></h2>
                <p><?php esc_html_e('Three quick steps and your first calendar is live.', 'modern-events-calendar-lite'); ?></p>
                <ol class="mec-checklist">
                    <li class="<?php echo $mec_step1_done ? 'done' : ''; ?>">
                        <a href="<?php echo esc_url(admin_url('post-new.php?post_type=mec-events')); ?>"><?php esc_html_e('Create your first event', 'modern-events-calendar-lite'); ?></a>
                        <span><?php esc_html_e('Dates, location, organizers - the works.', 'modern-events-calendar-lite'); ?></span>
                    </li>
                    <li class="<?php echo $mec_step2_done ? 'done' : ''; ?>">
                        <a href="<?php echo esc_url(admin_url('edit.php?post_type=mec_calendars')); ?>"><?php esc_html_e('Publish a calendar shortcode', 'modern-events-calendar-lite'); ?></a>
                        <span><?php esc_html_e('Pick a skin, then drop the shortcode on any page.', 'modern-events-calendar-lite'); ?></span>
                    </li>
                    <?php if ($this->isProBuild()): ?>
                    <li class="<?php echo $mec_step3_done ? 'done' : ''; ?>">
                        <a href="#mec-activation-box"><?php esc_html_e('Activate your license', 'modern-events-calendar-lite'); ?></a>
                        <span><?php esc_html_e('Enter your purchase code to unlock all Pro features.', 'modern-events-calendar-lite'); ?></span>
                    </li>
                    <?php else: ?>
                    <li class="optional">
                        <a href="<?php echo esc_url($this->get_pro_link()); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Unlock Pro features', 'modern-events-calendar-lite'); ?> <i class="mec-sl-action-redo" aria-hidden="true"></i></a>
                        <span><?php esc_html_e('Booking, Agenda, Timetable, Yearly View, Available Spots and more.', 'modern-events-calendar-lite'); ?></span>
                    </li>
                    <?php endif; ?>
                </ol>
            </div>

        <?php else: ?>

            <?php // Mature install: the daily numbers come first (v2 decision). ?>
            <?php if (current_user_can('read')): ?>
                <div class="g4 mec-stats">
                    <a class="ds-card stat" href="<?php echo esc_url(admin_url('edit.php?post_type=mec-events')); ?>">
                        <span class="ic"><span class="ico" data-i="calendar"></span></span>
                        <div><span class="stat-num"><?php echo esc_html(number_format_i18n($mec_stat_events)); ?></span><span class="stat-cap"><?php esc_html_e('Events', 'modern-events-calendar-lite'); ?></span></div>
                    </a>
                    <a class="ds-card stat" href="<?php echo esc_url(admin_url('edit.php?post_type=mec_calendars')); ?>">
                        <span class="ic"><span class="ico" data-i="layers"></span></span>
                        <div><span class="stat-num"><?php echo esc_html(number_format_i18n($mec_stat_calendars)); ?></span><span class="stat-cap"><?php esc_html_e('Shortcodes', 'modern-events-calendar-lite'); ?></span></div>
                    </a>
                    <a class="ds-card stat" href="<?php echo esc_url(admin_url('edit-tags.php?taxonomy=mec_location&post_type=' . $this->get_main_post_type())); ?>">
                        <span class="ic"><span class="ico" data-i="map"></span></span>
                        <div><span class="stat-num"><?php echo esc_html($user_location_count_l); ?></span><span class="stat-cap"><?php esc_html_e('Locations', 'modern-events-calendar-lite'); ?></span></div>
                    </a>
                    <a class="ds-card stat" href="<?php echo esc_url(admin_url('edit-tags.php?taxonomy=mec_organizer&post_type=' . $this->get_main_post_type())); ?>">
                        <span class="ic"><span class="ico" data-i="users"></span></span>
                        <div><span class="stat-num"><?php echo esc_html($user_organizer_count_l); ?></span><span class="stat-cap"><?php esc_html_e('Organizers', 'modern-events-calendar-lite'); ?></span></div>
                    </a>
                </div>

                <?php
                // Additive sales surface (v2): one thin strip under the stats
                // reminding Lite users what the numbers are missing. Licensed
                // Pro users see nothing.
                if (!$this->isProBuild()): ?>
                    <div class="mec-prostrip">
                        <span class="tag pro"><?php esc_html_e('Pro', 'modern-events-calendar-lite'); ?></span>
                        <span class="mec-prostrip__text"><?php esc_html_e('Booking system, Agenda, Timetable, Masonry, Yearly View, Available Spots…', 'modern-events-calendar-lite'); ?></span>
                        <a class="btn btn-pri btn-sm" href="<?php echo esc_url($this->get_pro_link()); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Go Premium', 'modern-events-calendar-lite'); ?> <span class="ico" data-i="arrow"></span></a>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <?php if ($box_stats): ?>
                <div class="w-row">
                    <div class="w-col-sm-<?php echo ($booking_status ? 6 : 12); ?>">
                        <div class="w-box upcoming-events mec-intro-card">
                            <h2 class="w-box-head">
                                <?php esc_html_e('Upcoming Events', 'modern-events-calendar-lite'); ?>
                            </h2>
                            <div class="w-box-content">
                                <?php
                                // I-08 + E-03: a light, dashboard-owned list
                                // instead of rendering the front-end skin (which
                                // pulled its own markup/CSS plus a credit link
                                // that then had to be hidden with CSS). ONE
                                // query drives both the list and the empty
                                // state; the OR clause keeps long-running and
                                // recurring spans visible after they start.
                                $mec_today = current_time('Y-m-d');
                                $mec_upcoming = new WP_Query(array(
                                    'post_type' => $this->get_main_post_type(),
                                    'post_status' => 'publish',
                                    'posts_per_page' => 6,
                                    'no_found_rows' => true,
                                    'meta_key' => 'mec_start_date',
                                    'orderby' => 'meta_value',
                                    'order' => 'ASC',
                                    'meta_query' => array(
                                        'relation' => 'OR',
                                        array('key' => 'mec_start_date', 'value' => $mec_today, 'compare' => '>=', 'type' => 'DATE'),
                                        array('key' => 'mec_end_date', 'value' => $mec_today, 'compare' => '>=', 'type' => 'DATE'),
                                    ),
                                ));

                                if ($mec_upcoming->have_posts()):
                                ?>
                                <ul class="mec-upcoming-list">
                                    <?php
                                    while ($mec_upcoming->have_posts()):
                                        $mec_upcoming->the_post();
                                        $mec_sd = (string) get_post_meta(get_the_ID(), 'mec_start_date', true);
                                        $mec_sd_ts = $mec_sd ? strtotime($mec_sd) : false;
                                    ?>
                                    <li>
                                        <span class="mec-upcoming-list__date"><b><?php echo $mec_sd_ts ? esc_html(date_i18n('d', $mec_sd_ts)) : ''; ?></b><i><?php echo $mec_sd_ts ? esc_html(date_i18n('M', $mec_sd_ts)) : ''; ?></i></span>
                                        <a href="<?php echo esc_url(get_permalink()); ?>"><?php echo esc_html(get_the_title()); ?></a>
                                    </li>
                                    <?php endwhile; wp_reset_postdata(); ?>
                                </ul>
                                <?php
                                else:
                                ?>
                                <div class="mec-upcoming-empty">
                                    <p><?php esc_html_e('Nothing coming up yet - publish an event with an upcoming date and it will appear here.', 'modern-events-calendar-lite'); ?></p>
                                    <div class="mec-intro-cta">
                                        <a class="mec-intro-btn" href="<?php echo esc_url(admin_url('post-new.php?post_type=mec-events')); ?>"><?php esc_html_e('Add New Event', 'modern-events-calendar-lite'); ?></a>
                                        <a class="mec-intro-btn mec-intro-btn--ghost" href="<?php echo esc_url(admin_url('admin.php?page=MEC-ix')); ?>"><?php esc_html_e('Import Events', 'modern-events-calendar-lite'); ?></a>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php if ($booking_status): ?>
                        <div class="w-col-sm-6">
                            <div class="w-box gateways mec-intro-card">
                                <h2 class="w-box-head">
                                    <?php echo esc_html__('Popular Gateways', 'modern-events-calendar-lite'); ?>
                                </h2>
                                <div class="w-box-content">
                                    <?php
                                    $results = $db->select("SELECT COUNT(`meta_id`) AS count, `meta_value` AS gateway FROM `#__postmeta` WHERE `meta_key`='mec_gateway' GROUP BY `meta_value`", 'loadAssocList');

                                    $labels = '';
                                    $data = '';
                                    $bg_colors = '';
                                    $stats = array();

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
                                    ?>

                                    <?php if (!empty($stats)): ?>
                                    <?php echo '<canvas id="mec_gateways_chart" width="300" height="300"></canvas>'; ?>
                                    <p class="mec-gateways-note"><?php esc_html_e('Payment gateways enabled across your events.', 'modern-events-calendar-lite'); ?></p>

                                    <?php $this->getFactory()->params('footer', '<script>
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
                                </script>'); ?>
                                    <?php else: ?>
                                    <?php
                                    // E-02: an empty chart renders as a broken-looking
                                    // blank circle - show where to fix it instead.
                                    ?>
                                    <div class="mec-gateways-empty">
                                        <p><?php esc_html_e('No payment gateway is enabled on your events yet.', 'modern-events-calendar-lite'); ?></p>
                                        <a class="mec-intro-btn" href="<?php echo esc_url(admin_url('admin.php?page=MEC-settings')); ?>"><?php esc_html_e('Booking Settings', 'modern-events-calendar-lite'); ?></a>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php /* ═══ ZONE E - analytics (mature, Pro + booking) ═══ */ ?>
            <?php if ($booking_status and current_user_can('mec_settings')) echo (new MEC_feature_mec())->widget_total_bookings(); ?>
        <?php endif; ?>

        <?php /* ═══ ZONE F - utilities (addons · print · changelog) ═══ */ ?>
        <?php
        // Revision 12: the walkthrough moved up to the top of the page
        // (above the license box), so it has no slot here anymore.
        ?>

        <?php
        // Lite + add-ons: the only add-on activation surface (Zone F
        // utility). C-04: administrator-only, no dead box for others.
        ?>
        <?php if (!$this->isProBuild() && has_action('addons_activation') && current_user_can('administrator')) : ?>
            <div class="w-box mec-activation mec-intro-card">
                <h2 class="w-box-head">
                    <?php esc_html_e('License Activation', 'modern-events-calendar-lite'); ?>
                </h2>
                    <div class="w-box-content">
                        <?php include plugin_dir_path(__FILE__) . 'dashboard-addonstoggle.php'; ?>
                    </div>
            </div>
        <?php endif; ?>

        <?php if ($this->getPRO()) (new MEC_feature_mec())->widget_print(); ?>

        <?php
        // P-03: changelog.txt used to be read and dumped raw into a <pre> on
        // every dashboard load - unescaped (any tag in the file would render
        // as HTML), uncached (disk I/O per request) and untrimmed (300+
        // versions in one endless block). It is now parsed into version
        // blocks, escaped on output, cached in a transient keyed by plugin
        // version + file mtime, and only the latest few versions render -
        // the rest stay behind a <details> toggle.
        $changelog_file = plugin_dir_path(__FILE__) . '../../../changelog.txt';
        // "_r2" is the parser revision: headers older than ~v7.19 use a Unicode
        // en-dash instead of an ASCII hyphen and some versions are 4-part
        // (1.9.5.1), so it changes whenever the parsing rules change.
        $changelog_cache_key = 'mec_intro_changelog_r2_' . MEC_VERSION . '_' . ((@filemtime($changelog_file)) ?: 0);
        $changelog_versions = get_transient($changelog_cache_key);

        if ($changelog_versions === false) {
            $changelog_versions = array();
            $changelog_raw = @file_get_contents($changelog_file);

            // The file ships with a UTF-8 BOM; strip it or the first version header ("v 7.36.4 - …") never matches.
            if ($changelog_raw) $changelog_raw = preg_replace('/^\xEF\xBB\xBF/', '', $changelog_raw);

            if ($changelog_raw) {
                // Split on "v X.Y.Z - date" header lines, keeping the headers.
                // The file mixes separators: newer headers use an ASCII hyphen,
                // older ones (pre ~7.19) a Unicode en-dash - and a few versions
                // are 4-part. Accept all of them.
                $changelog_blocks = preg_split('/^\s*(?=v\s*\d+(?:\.\d+)+\s*[-\x{2013}\x{2014}])/mu', $changelog_raw, -1, PREG_SPLIT_NO_EMPTY);

                foreach ($changelog_blocks as $changelog_block) {
                    $changelog_lines = preg_split('/\r\n|\r|\n/', trim($changelog_block));
                    $changelog_header = trim(array_shift($changelog_lines));

                    if (!preg_match('/^v\s*(\d+(?:\.\d+)+)\s*[-\x{2013}\x{2014}]\s*(.+)$/u', $changelog_header, $changelog_matches)) continue;

                    $changelog_entries = array();
                    foreach ($changelog_lines as $changelog_line) {
                        $changelog_line = trim($changelog_line);
                        if ($changelog_line === '') continue;

                        // Normal entries start with "- "; keep any other line as-is so nothing is silently dropped.
                        $changelog_entries[] = (strpos($changelog_line, '-') === 0) ? ltrim($changelog_line, "- \t") : $changelog_line;
                    }

                    if (!empty($changelog_entries)) $changelog_versions[] = array('version' => $changelog_matches[1], 'date' => $changelog_matches[2], 'entries' => $changelog_entries);
                }
            }

            // The key already carries MEC_VERSION/file mtime/parser revision,
            // so a long TTL is safe.
            set_transient($changelog_cache_key, $changelog_versions, WEEK_IN_SECONDS);
        }

        // How many recent versions render expanded; older ones go into <details>.
        $changelog_latest_count = 3;
        ?>
        <div class="w-box change-log mec-intro-card">
            <h2 class="w-box-head">
                <?php echo esc_html__('Changelog', 'modern-events-calendar-lite'); ?>
                <?php
                // Additive sales surface (v2): most changelog entries describe
                // Pro-only features - on Lite, link them to the upgrade page.
                // No <span> here: a legacy ".w-box-head span" rule absolutely
                // positions every span inside a box head.
                if (!$this->isProBuild()): ?>
                    <a class="mec-changelog-pro" href="<?php echo esc_url($this->get_pro_link()); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Pro - Unlock all features', 'modern-events-calendar-lite'); ?> <i class="mec-sl-action-redo" aria-hidden="true"></i></a>
                <?php endif; ?>
            </h2>
            <div class="w-box-content">
                <?php if (!empty($changelog_versions)): ?>
                <div class="mec-changelog">
                    <?php foreach ($changelog_versions as $changelog_i => $changelog_version): ?>
                        <?php if ($changelog_i === $changelog_latest_count): ?>
                        <details class="mec-changelog-more">
                            <summary><?php echo esc_html(sprintf(__('Show older versions (%d)', 'modern-events-calendar-lite'), count($changelog_versions) - $changelog_latest_count)); ?></summary>
                        <?php endif; ?>
                        <div class="mec-changelog-entry<?php echo ($changelog_i === 0) ? ' mec-changelog-entry--latest' : ''; ?>">
                            <div class="mec-changelog-entry-head">
                                <span class="mec-changelog-entry-ver">v<?php echo esc_html($changelog_version['version']); ?></span>
                                <span class="mec-changelog-entry-date"><?php echo esc_html($changelog_version['date']); ?></span>
                            </div>
                            <ul>
                                <?php foreach ($changelog_version['entries'] as $changelog_entry): ?>
                                <li><?php
                                // Historical entries contain occasional <a> links. Everything is
                                // escaped; only a bare <a href/target/rel> is allowed through, so
                                // wp_kses strips any script/on* vector and javascript: URLs.
                                echo wp_kses($changelog_entry, array('a' => array('href' => true, 'target' => true, 'rel' => true)));
                                ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <?php if (count($changelog_versions) > $changelog_latest_count && $changelog_i + 1 === count($changelog_versions)): ?>
                        </details>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <p class="mec-changelog-empty"><?php esc_html_e('No changelog available.', 'modern-events-calendar-lite'); ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
// Icon hydration for the .mec-ds components on this screen (header logo,
// stat chips, buttons). Same self-contained registry as support/addons.
$this->getFactory()->params('footer', function () {
?>
<script>
(function () {
    var P = {
        home: '<path d="m3 10 9-7 9 7v9a2 2 0 0 1-2 2h-4v-7h-6v7H5a2 2 0 0 1-2-2z"/>',
        plus: '<path d="M12 5v14M5 12h14"/>',
        refresh: '<path d="M20 11a8 8 0 0 0-14.9-3M4 4v4h4M4 13a8 8 0 0 0 14.9 3M20 20v-4h-4"/>',
        calendar: '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M8 3v4M16 3v4M3 10h18"/>',
        layers: '<path d="m12 2 10 5-10 5L2 7z"/><path d="m2 12 10 5 10-5"/><path d="m2 17 10 5 10-5"/>',
        map: '<path d="M9 4 3 6v14l6-2 6 2 6-2V4l-6 2-6-2z"/><path d="M9 4v14M15 6v14"/>',
        users: '<circle cx="9" cy="8" r="3.5"/><path d="M2.5 20a6.5 6.5 0 0 1 13 0M16 4.5a3.5 3.5 0 0 1 0 7M18 14a6 6 0 0 1 3.5 6"/>',
        arrow: '<path d="M5 12h14M13 6l6 6-6 6"/>',
        ext: '<path d="M14 4h6v6M20 4l-9 9M18 14v5a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h5"/>',
        check: '<path d="m5 12.5 4.5 4.5L19 7.5"/>',
        x: '<path d="M6 6l12 12M18 6 6 18"/>',
        alert: '<path d="M12 9v4M12 17h.01M10.3 3.9 2.4 18a2 2 0 0 0 1.7 3h15.8a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0z"/>',
        info: '<circle cx="12" cy="12" r="9"/><path d="M12 11v6M12 7.5h.01"/>'
    };

    var els = document.querySelectorAll('span[data-i]:not([data-done])');
    for (var i = 0; i < els.length; i++) {
        var name = els[i].getAttribute('data-i');
        if (!P[name]) continue;
        els[i].innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' + P[name] + '</svg>';
        els[i].setAttribute('data-done', '1');
    }
})();
</script>
<?php });
