<?php

/** no direct access **/
defined('MECEXEC') or die();

/** @var MEC_feature_mec $this */

wp_enqueue_style('mec-lity-style');
wp_enqueue_script('mec-lity-script');

global $wp_version;

$dox = 'https://webnus.net/dox/modern-events-calendar/';

/* ════════════════════════════════════════════════════════════════════
   State engine. Everything the page shows is decided here from cheap
   option / transient reads: package, licence, update availability and
   server facts. The template below only renders, it never decides.
   ════════════════════════════════════════════════════════════════════ */

// Package (which files shipped), never licence state.
$is_pro_build = $this->isProBuild();

// Licence: the signed token governs support entitlement. The legacy
// mec_license_status option only records what the activation endpoint
// replied and governs auto-updates alone.
$mec_options = get_option('mec_options');
$product_license = (is_array($mec_options) and !empty($mec_options['purchase_code'])) ? $mec_options['purchase_code'] : '';

$mec_license_token = ($is_pro_build and class_exists('MEC_license')) ? MEC_license::instance()->licensed() : false;
$mec_token_exp = ($mec_license_token and class_exists('MEC_license')) ? MEC_license::instance()->token_expiry() : null;
$mec_partner = ($is_pro_build and class_exists('MEC_license')) ? MEC_license::instance()->partner_licence() : null;
$mec_license_active = ($mec_license_token or is_array($mec_partner));

// active | expired | inactive | lite
$mec_lic = !$is_pro_build ? 'lite' : ($mec_license_active ? 'active' : ($product_license !== '' ? 'expired' : 'inactive'));
$mec_has_support = ($mec_lic === 'active');

// Update state: cheap read of the WP updates transient (wp.org for Lite,
// MEC's update server for Pro - both register in the same place).
$mec_plugin_basename = plugin_basename(MEC_ABSPATH . 'mec.php');
$mec_updates_transient = get_site_transient('update_plugins');
$mec_update_available = (is_object($mec_updates_transient) and isset($mec_updates_transient->response[$mec_plugin_basename]));
$mec_latest_version = ($mec_update_available and isset($mec_updates_transient->response[$mec_plugin_basename]->new_version)) ? $mec_updates_transient->response[$mec_plugin_basename]->new_version : MEC_VERSION;

/* Server facts for the health list + system report */
$mec_memory = (string) ini_get('memory_limit');
$mec_memory_mb = 0;
if (preg_match('/^(\d+)\s*([KMG]?)$/i', $mec_memory, $mm))
{
    $mec_memory_mb = (int) $mm[1];
    $unit = strtoupper($mm[2]);
    if ($unit === 'K') $mec_memory_mb = (int) round($mec_memory_mb / 1024);
    elseif ($unit === 'G') $mec_memory_mb *= 1024;
}
$mec_memory_ok = ($mec_memory === '-1' or $mec_memory_mb >= 256);
$mec_php = (string) phpversion();
$mec_php_ok = version_compare($mec_php, '7.4', '>=');
$mec_curl = function_exists('curl_version');
$mec_multisite = (function_exists('is_multisite') and is_multisite());
$mec_debug = (defined('WP_DEBUG') and WP_DEBUG);

/* URLs */
$update_url = admin_url('update-core.php');
$activation_url = admin_url('admin.php?page=mec-intro') . '#mec-activation-box';
$renew_url = 'https://my.webnus.net/';
$ticket_url = 'https://my.webnus.net/support-tickets/';
$pricing_url = $this->main->get_pro_link();
$memory_doc_url = 'https://developer.wordpress.org/advanced-administration/wordpress/wp-config/';
$debug_doc_url = 'https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/';
$search_url = $dox;
$community_url = 'https://wordpress.org/support/plugin/modern-events-calendar-lite/';
$changelog_url = 'https://webnus.net/modern-events-calendar/change-log/';
$feature_request_url = 'https://my.webnus.net/feature-request/';

$pkg_label = $is_pro_build ? esc_html__('Pro', 'modern-events-calendar-lite') : esc_html__('Lite', 'modern-events-calendar-lite');

$mec_lic_labels = [
    'active' => esc_html__('Active', 'modern-events-calendar-lite'),
    'expired' => esc_html__('Expired', 'modern-events-calendar-lite'),
    'inactive' => esc_html__('Not activated', 'modern-events-calendar-lite'),
    'lite' => esc_html__('Lite', 'modern-events-calendar-lite'),
];
$mec_lic_label = $mec_lic_labels[$mec_lic];

/* Health checks: label, value, state (ok|warn|bad|info), optional fix link */
$health = [
    ['l' => esc_html__('MEC version', 'modern-events-calendar-lite'), 'v' => MEC_VERSION, 'st' => $mec_update_available ? 'warn' : 'ok', 'fix' => $mec_update_available ? '<a class="fix" href="' . esc_url($update_url) . '">' . esc_html__('Update', 'modern-events-calendar-lite') . '</a>' : ''],
    ['l' => esc_html__('License', 'modern-events-calendar-lite'), 'v' => $mec_lic_label, 'st' => $mec_lic === 'active' ? 'ok' : ($mec_lic === 'lite' ? 'info' : 'bad'), 'fix' => $mec_lic === 'expired' ? '<a class="fix" href="' . esc_url($renew_url) . '" target="_blank" rel="noopener">' . esc_html__('Renew', 'modern-events-calendar-lite') . '</a>' : ($mec_lic === 'inactive' ? '<a class="fix" href="' . esc_url($activation_url) . '">' . esc_html__('Activate', 'modern-events-calendar-lite') . '</a>' : ($mec_lic === 'lite' ? '<a class="fix" href="' . esc_url($pricing_url) . '" target="_blank" rel="noopener">' . esc_html__('Upgrade', 'modern-events-calendar-lite') . '</a>' : ''))],
    ['l' => esc_html__('WordPress', 'modern-events-calendar-lite'), 'v' => $wp_version, 'st' => 'ok', 'fix' => ''],
    ['l' => esc_html__('PHP', 'modern-events-calendar-lite'), 'v' => $mec_php, 'st' => $mec_php_ok ? 'ok' : 'bad', 'fix' => ''],
    ['l' => esc_html__('Memory limit', 'modern-events-calendar-lite'), 'v' => $mec_memory, 'st' => $mec_memory_ok ? 'ok' : 'warn', 'fix' => $mec_memory_ok ? '' : '<a class="fix" href="' . esc_url($memory_doc_url) . '" target="_blank" rel="noopener">' . esc_html__('Raise to 256M', 'modern-events-calendar-lite') . '</a>'],
    ['l' => esc_html__('cURL', 'modern-events-calendar-lite'), 'v' => $mec_curl ? esc_html__('Yes', 'modern-events-calendar-lite') : esc_html__('No', 'modern-events-calendar-lite'), 'st' => $mec_curl ? 'ok' : 'bad', 'fix' => ''],
    ['l' => esc_html__('Debug mode', 'modern-events-calendar-lite'), 'v' => $mec_debug ? esc_html__('On', 'modern-events-calendar-lite') : esc_html__('Off', 'modern-events-calendar-lite'), 'st' => 'info', 'fix' => ''],
];

$mec_issues = 0;
foreach ($health as $h) if ($h['st'] === 'warn' or $h['st'] === 'bad') $mec_issues++;

/* Full system information table */
$sys_rows = [
    [esc_html__('Home URL', 'modern-events-calendar-lite'), esc_url(get_home_url())],
    [esc_html__('Site URL', 'modern-events-calendar-lite'), esc_url(get_site_url())],
    [esc_html__('Locale', 'modern-events-calendar-lite'), esc_html(get_locale())],
    [esc_html__('Character set', 'modern-events-calendar-lite'), esc_html(get_option('blog_charset'))],
    [esc_html__('MEC version', 'modern-events-calendar-lite'), esc_html(MEC_VERSION)],
    [esc_html__('Package', 'modern-events-calendar-lite'), $pkg_label],
    [esc_html__('License', 'modern-events-calendar-lite'), $mec_lic_label],
    [esc_html__('WordPress version', 'modern-events-calendar-lite'), esc_html($wp_version)],
    [esc_html__('Multisite', 'modern-events-calendar-lite'), $mec_multisite ? esc_html__('Yes', 'modern-events-calendar-lite') : esc_html__('No', 'modern-events-calendar-lite')],
    [esc_html__('WordPress memory limit', 'modern-events-calendar-lite'), esc_html($mec_memory)],
    [esc_html__('PHP version', 'modern-events-calendar-lite'), esc_html($mec_php)],
    [esc_html__('PHP cURL', 'modern-events-calendar-lite'), $mec_curl ? esc_html__('Yes', 'modern-events-calendar-lite') : esc_html__('No', 'modern-events-calendar-lite')],
    [esc_html__('WP debug', 'modern-events-calendar-lite'), $mec_debug ? esc_html__('On', 'modern-events-calendar-lite') : esc_html__('Off', 'modern-events-calendar-lite')],
];

/* Plain-text system report for the Copy button / ticket portal (leading blank lines for typing above). */
$sys_report = "\n\n" . implode("\n", [
    '### ' . esc_html__('System report (MEC Support page)', 'modern-events-calendar-lite'),
    'Home URL: ' . esc_url(get_home_url()),
    'Site URL: ' . esc_url(get_site_url()),
    'MEC: ' . MEC_VERSION . ' (' . $pkg_label . ') - ' . esc_html__('License', 'modern-events-calendar-lite') . ': ' . $mec_lic_label . ($mec_update_available ? ' - ' . esc_html__('Latest', 'modern-events-calendar-lite') . ': ' . $mec_latest_version : ''),
    'WordPress: ' . $wp_version . ' - ' . esc_html__('Multisite', 'modern-events-calendar-lite') . ': ' . ($mec_multisite ? 'Yes' : 'No') . ' - Locale: ' . get_locale(),
    'PHP: ' . $mec_php . ' - Memory: ' . $mec_memory . ' - cURL: ' . ($mec_curl ? 'Yes' : 'No') . ' - WP_DEBUG: ' . ($mec_debug ? 'On' : 'Off'),
]);

/* Contextual banner: one message, the most important one */
if ($mec_lic === 'expired')
{
    $banner = ['k' => 'bad', 'i' => 'lock', 't' => esc_html__('Your license has expired. Updates and support are paused.', 'modern-events-calendar-lite'), 'd' => sprintf(esc_html__("Renew to get security updates, live chat and ticket support back. You're still on MEC %s.", 'modern-events-calendar-lite'), MEC_VERSION), 'a' => '<a class="btn btn-bad" href="' . esc_url($renew_url) . '" target="_blank" rel="noopener"><span class="ico" data-i="refresh"></span>' . esc_html__('Renew license', 'modern-events-calendar-lite') . '</a>'];
}
elseif ($mec_lic === 'inactive')
{
    $banner = ['k' => 'warn', 'i' => 'key', 't' => esc_html__('Activate your license to unlock updates and support.', 'modern-events-calendar-lite'), 'd' => esc_html__("You're running the Pro package. Add your purchase code to turn on auto-updates, live chat and ticket support.", 'modern-events-calendar-lite'), 'a' => '<a class="btn btn-warn" href="' . esc_url($activation_url) . '"><span class="ico" data-i="key"></span>' . esc_html__('Activate now', 'modern-events-calendar-lite') . '</a>'];
}
elseif ($mec_update_available)
{
    $banner = ['k' => 'warn', 'i' => 'alert', 't' => sprintf(esc_html__("MEC %1\$s is available. You're on %2\$s.", 'modern-events-calendar-lite'), $mec_latest_version, MEC_VERSION), 'd' => esc_html__('Updates include security fixes. Attackers are using AI tools to exploit outdated plugins faster than ever. Updating is free and takes under a minute.', 'modern-events-calendar-lite'), 'a' => '<a class="btn btn-sec btn-sm" href="' . esc_url($changelog_url) . '" target="_blank" rel="noopener">' . esc_html__("What's new", 'modern-events-calendar-lite') . '</a><a class="btn btn-warn" href="' . esc_url($update_url) . '"><span class="ico" data-i="refresh"></span>' . esc_html__('Update now', 'modern-events-calendar-lite') . '</a>'];
}
elseif ($mec_lic === 'lite')
{
    $banner = ['k' => 'ok', 'i' => 'shield', 't' => sprintf(esc_html__("You're on the latest version of MEC Lite (%s).", 'modern-events-calendar-lite'), MEC_VERSION), 'd' => esc_html__('Upgrade to Pro for bookings, payments, 20+ add-ons and direct help from our team.', 'modern-events-calendar-lite'), 'a' => '<a class="btn btn-sec btn-sm" href="' . esc_url($pricing_url) . '" target="_blank" rel="noopener">' . esc_html__('Compare plans', 'modern-events-calendar-lite') . '</a>'];
}
else
{
    $banner = ['k' => 'ok', 'i' => 'shield', 't' => sprintf(esc_html__("You're up to date. MEC %s is the latest version.", 'modern-events-calendar-lite'), MEC_VERSION), 'd' => esc_html__('Security fixes and new features install automatically while your license is active.', 'modern-events-calendar-lite'), 'a' => ''];
}

/* Header meta tags */
$mec_tags = '<span class="mec-tag">MEC ' . esc_html(MEC_VERSION) . '</span>';
if ($is_pro_build) $mec_tags .= '<span class="mec-tag pro">' . esc_html__('Pro', 'modern-events-calendar-lite') . '</span>';
if ($mec_lic === 'active')
{
    $mec_tags .= '<span class="mec-tag ok"><span class="d"></span>' . ($mec_token_exp ? sprintf(esc_html__('License active · valid until %s', 'modern-events-calendar-lite'), esc_html(date_i18n(get_option('date_format') ?: 'Y-m-d', $mec_token_exp))) : esc_html__('License active', 'modern-events-calendar-lite')) . '</span>';
}
elseif ($mec_lic === 'expired') $mec_tags .= '<span class="mec-tag bad"><span class="d"></span>' . esc_html__('License expired', 'modern-events-calendar-lite') . '</span>';
elseif ($mec_lic === 'inactive') $mec_tags .= '<span class="mec-tag warn"><span class="d"></span>' . esc_html__('License not activated', 'modern-events-calendar-lite') . '</span>';
else $mec_tags .= '<span class="mec-tag">' . esc_html__('Lite (free)', 'modern-events-calendar-lite') . '</span>';
$mec_tags .= $mec_update_available
    ? '<span class="mec-tag warn"><span class="d"></span>' . esc_html__('Update available', 'modern-events-calendar-lite') . '</span>'
    : '<span class="mec-tag ok"><span class="d"></span>' . esc_html__('Up to date', 'modern-events-calendar-lite') . '</span>';

/* Header actions */
if ($mec_has_support)
{
    $head_actions = '<button type="button" class="btn btn-sec" data-action="ticket"><span class="ico" data-i="ticket"></span>' . esc_html__('Open a ticket', 'modern-events-calendar-lite') . '</button>'
        . '<button type="button" class="btn btn-pri" data-action="chat"><span class="ico" data-i="chat"></span>' . esc_html__('Chat with Support', 'modern-events-calendar-lite') . '</button>';
}
elseif ($mec_lic === 'expired')
{
    $head_actions = '<a class="btn btn-sec" href="' . esc_url($community_url) . '" target="_blank" rel="noopener"><span class="ico" data-i="users"></span>' . esc_html__('Community', 'modern-events-calendar-lite') . '</a>'
        . '<a class="btn btn-pri" href="' . esc_url($renew_url) . '" target="_blank" rel="noopener"><span class="ico" data-i="refresh"></span>' . esc_html__('Renew license', 'modern-events-calendar-lite') . '</a>';
}
elseif ($mec_lic === 'inactive')
{
    $head_actions = '<a class="btn btn-sec" href="' . esc_url($community_url) . '" target="_blank" rel="noopener"><span class="ico" data-i="users"></span>' . esc_html__('Community', 'modern-events-calendar-lite') . '</a>'
        . '<a class="btn btn-pri" href="' . esc_url($activation_url) . '"><span class="ico" data-i="key"></span>' . esc_html__('Activate license', 'modern-events-calendar-lite') . '</a>';
}
else
{
    $head_actions = '<a class="btn btn-sec" href="' . esc_url($community_url) . '" target="_blank" rel="noopener"><span class="ico" data-i="users"></span>' . esc_html__('Community', 'modern-events-calendar-lite') . '</a>'
        . '<a class="btn btn-pri" href="' . esc_url($pricing_url) . '" target="_blank" rel="noopener"><span class="ico" data-i="rocket"></span>' . esc_html__('Upgrade to Pro', 'modern-events-calendar-lite') . '</a>';
}

/* Channel locks */
$ticket_lock = '';
if (!$mec_has_support)
{
    if ($mec_lic === 'expired') $ticket_lock = '<span class="ico" data-i="lock"></span>' . sprintf(esc_html__('Needs an active license · %s', 'modern-events-calendar-lite'), '<a href="' . esc_url($renew_url) . '" target="_blank" rel="noopener">' . esc_html__('Renew', 'modern-events-calendar-lite') . '</a>');
    elseif ($mec_lic === 'inactive') $ticket_lock = '<span class="ico" data-i="lock"></span>' . sprintf(esc_html__('Needs an active license · %s', 'modern-events-calendar-lite'), '<a href="' . esc_url($activation_url) . '">' . esc_html__('Activate now', 'modern-events-calendar-lite') . '</a>');
    else $ticket_lock = '<span class="ico" data-i="lock"></span>' . sprintf(esc_html__('Pro feature · %s', 'modern-events-calendar-lite'), '<a href="' . esc_url($pricing_url) . '" target="_blank" rel="noopener">' . esc_html__('Upgrade to Pro', 'modern-events-calendar-lite') . '</a>');
}

$chat_lock_text = '';
if (!$mec_has_support)
{
    if ($mec_lic === 'expired') $chat_lock_text = esc_html__('For renewal & billing questions only', 'modern-events-calendar-lite');
    elseif ($mec_lic === 'inactive') $chat_lock_text = esc_html__('For activation help only', 'modern-events-calendar-lite');
    else $chat_lock_text = esc_html__('For pre-sales & upgrade questions only', 'modern-events-calendar-lite');
}

/* Debug log block (admins only) */
$mec_debug_txt = '';
$mec_debug_btn = '';
if (current_user_can('manage_options'))
{
    if ($mec_debug)
    {
        $log_file = WP_CONTENT_DIR . '/debug.log';
        if (defined('WP_DEBUG_LOG') and is_string(WP_DEBUG_LOG)) $log_file = WP_DEBUG_LOG;
        $log_size = file_exists($log_file) ? filesize($log_file) : 0;

        if ($log_size)
        {
            $mec_debug_txt = sprintf(esc_html__('WP debug mode is on. Log size: %s KB.', 'modern-events-calendar-lite'), round($log_size / 1024, 1));
            $mec_debug_btn = '<a class="btn btn-sec btn-sm" href="' . esc_url($this->main->URL('admin') . '?mec-download-log-file=1') . '"><span class="ico" data-i="copy"></span>' . esc_html__('Download log', 'modern-events-calendar-lite') . '</a>';
        }
        else $mec_debug_txt = esc_html__('WP debug mode is on but there is no log to download yet.', 'modern-events-calendar-lite');
    }
    else $mec_debug_txt = esc_html__("WP debug mode is off. Turn it on to download a debug log for your ticket.", 'modern-events-calendar-lite');
}

/* Step 1 resource cards */
$resources = [
    ['u' => $dox, 'i' => 'book', 's' => '', 't' => esc_html__('Documentation', 'modern-events-calendar-lite'), 'd' => esc_html__('Step-by-step guides covering every MEC feature and setting.', 'modern-events-calendar-lite'), 'c' => esc_html__('Browse docs', 'modern-events-calendar-lite')],
    ['u' => 'https://www.youtube.com/playlist?list=PLOuD0zVFWiE5DdYyjZnKhSM2jK9QfExeY', 'i' => 'play', 's' => 'background:#FFF0F0;color:#E5484D', 't' => esc_html__('Video tutorials', 'modern-events-calendar-lite'), 'd' => esc_html__('Over-the-shoulder walkthroughs. Get started in minutes.', 'modern-events-calendar-lite'), 'c' => esc_html__('Watch videos', 'modern-events-calendar-lite')],
    ['u' => $dox . 'category/knowledge/', 'i' => 'bulb', 's' => 'background:#FFF8EB;color:#DC6803', 't' => esc_html__('Knowledgebase', 'modern-events-calendar-lite'), 'd' => esc_html__('Short answers to the questions people ask most.', 'modern-events-calendar-lite'), 'c' => esc_html__('Find answers', 'modern-events-calendar-lite')],
    ['u' => $dox . 'category/developer-document/', 'i' => 'code', 's' => 'background:#F0EEFF;color:#6E62F0', 't' => esc_html__('Developer docs', 'modern-events-calendar-lite'), 'd' => esc_html__('Hooks, filters, theme integration and template overrides.', 'modern-events-calendar-lite'), 'c' => esc_html__('Open reference', 'modern-events-calendar-lite')],
];

/* Browse-by-topic cards */
$topics = [
    ['t' => esc_html__('Getting started', 'modern-events-calendar-lite'), 'i' => 'rocket', 's' => 'background:#E8F7F4;color:#00A88F', 'l' => [
        [esc_html__('Install MEC', 'modern-events-calendar-lite'), $dox . 'installation/'],
        [esc_html__('Activate your license', 'modern-events-calendar-lite'), $dox . 'activation/'],
        [esc_html__('Add your first event', 'modern-events-calendar-lite'), $dox . 'add-event/'],
        [esc_html__('Frontend event submission', 'modern-events-calendar-lite'), $dox . 'frontend-event-submission/'],
        [esc_html__('Import & export events', 'modern-events-calendar-lite'), $dox . 'import-and-export-events/'],
    ]],
    ['t' => esc_html__('Settings & shortcodes', 'modern-events-calendar-lite'), 'i' => 'gear', 's' => 'background:#EEF6FF;color:#00BEEB', 'l' => [
        [esc_html__('General settings', 'modern-events-calendar-lite'), $dox . 'general-settings/'],
        [esc_html__('Appearance & styling', 'modern-events-calendar-lite'), $dox . 'appearance-settings/'],
        [esc_html__('Single event page', 'modern-events-calendar-lite'), $dox . 'single-event-settings/'],
        [esc_html__('Making advanced shortcodes', 'modern-events-calendar-lite'), $dox . 'making-advanced-shortcodes/'],
        [esc_html__('Integrations', 'modern-events-calendar-lite'), $dox . 'mec-integrations/'],
    ]],
    ['t' => esc_html__('Booking & payments', 'modern-events-calendar-lite'), 'i' => 'card', 's' => 'background:#F0EEFF;color:#6E62F0', 'l' => [
        [esc_html__('Add a booking system', 'modern-events-calendar-lite'), $dox . 'add-a-booking-system/'],
        [esc_html__('Booking settings & tickets', 'modern-events-calendar-lite'), $dox . 'booking-settings/'],
        [esc_html__('Payment gateways', 'modern-events-calendar-lite'), $dox . 'booking-settings/#Payment_Gateways'],
        [esc_html__('Organizer payment', 'modern-events-calendar-lite'), $dox . 'organizer-payment-settings/'],
        [esc_html__('Emails & notifications', 'modern-events-calendar-lite'), $dox . 'event-notifications/'],
    ]],
    ['t' => esc_html__('Update, translate & fix', 'modern-events-calendar-lite'), 'i' => 'shield', 's' => 'background:#FFF8EB;color:#DC6803', 'l' => [
        [esc_html__('Auto-update MEC', 'modern-events-calendar-lite'), $dox . 'auto-update/'],
        [esc_html__('Update manually (FTP)', 'modern-events-calendar-lite'), $dox . 'manual-update/'],
        [esc_html__('Change license domain', 'modern-events-calendar-lite'), $dox . 'change-license-domain/'],
        [esc_html__('Translate MEC', 'modern-events-calendar-lite'), $dox . 'translate-mec/'],
        [esc_html__('Troubleshooting', 'modern-events-calendar-lite'), $dox . 'knowledgebase/troubleshooting-other/'],
    ]],
];

/* Video tutorial cards (lity lightbox) */
$videos = [
    ['t' => esc_html__('Activate your license', 'modern-events-calendar-lite'), 'u' => 'https://youtu.be/V8DAZXuVxrQ', 'l' => [
        esc_html__('Log in to your Webnus dashboard', 'modern-events-calendar-lite'),
        esc_html__('Copy your license key', 'modern-events-calendar-lite'),
        esc_html__('Activate MEC in one click', 'modern-events-calendar-lite'),
    ]],
    ['t' => esc_html__('Build a booking form', 'modern-events-calendar-lite'), 'u' => 'https://youtu.be/difbDGz6blU', 'l' => [
        esc_html__('Enable booking from settings', 'modern-events-calendar-lite'),
        esc_html__('Set up your booking form', 'modern-events-calendar-lite'),
        esc_html__('Customize fields & tickets', 'modern-events-calendar-lite'),
    ]],
    ['t' => esc_html__('MEC settings overview', 'modern-events-calendar-lite'), 'u' => 'https://youtu.be/mdXWngl4Lso', 'l' => [
        esc_html__('Tour every settings panel', 'modern-events-calendar-lite'),
        esc_html__('Configure MEC for your site', 'modern-events-calendar-lite'),
        esc_html__('Use the advanced options', 'modern-events-calendar-lite'),
    ]],
];

/* Ticket modal: topics + doc suggestions per topic */
$mec_topics_select = [
    esc_html__('Booking & payments', 'modern-events-calendar-lite'),
    esc_html__('Display / shortcodes / styling', 'modern-events-calendar-lite'),
    esc_html__('Settings & configuration', 'modern-events-calendar-lite'),
    esc_html__('Emails & notifications', 'modern-events-calendar-lite'),
    esc_html__('Import / export', 'modern-events-calendar-lite'),
    esc_html__('License & updates', 'modern-events-calendar-lite'),
    esc_html__('Add-on issue', 'modern-events-calendar-lite'),
    esc_html__('Something else', 'modern-events-calendar-lite'),
];
$mec_suggest = [
    0 => [esc_html__('Booking settings & tickets', 'modern-events-calendar-lite'), $dox . 'booking-settings/'],
    1 => [esc_html__('Making advanced shortcodes', 'modern-events-calendar-lite'), $dox . 'making-advanced-shortcodes/'],
    3 => [esc_html__('Emails & notifications', 'modern-events-calendar-lite'), $dox . 'event-notifications/'],
    4 => [esc_html__('Import & export events', 'modern-events-calendar-lite'), $dox . 'import-and-export-events/'],
    5 => [esc_html__('Activate your license', 'modern-events-calendar-lite'), $dox . 'activation/'],
];

/* JS config handed to the footer script */
$mec_js = [
    'ajaxurl' => admin_url('admin-ajax.php'),
    'nonce' => wp_create_nonce('mec_support'),
    'urls' => ['ticket' => $ticket_url, 'search' => $search_url, 'renew' => $renew_url],
    'site' => [
        'mec' => MEC_VERSION,
        'pkg' => $is_pro_build ? 'Pro' : 'Lite',
        'lic' => $mec_lic,
        'wp' => $wp_version,
        'php' => $mec_php,
        'home' => get_home_url(),
    ],
    'report' => $sys_report,
    'suggest' => $mec_suggest,
    'i18n' => [
        'copied' => esc_html__('System report copied to clipboard', 'modern-events-calendar-lite'),
        'copyFail' => esc_html__('Copy failed. Select the system table manually.', 'modern-events-calendar-lite'),
        'ticketCopied' => esc_html__('System info copied. Paste it into your ticket.', 'modern-events-calendar-lite'),
        'ticketOpened' => esc_html__('Opened the ticket portal.', 'modern-events-calendar-lite'),
        'chatLoading' => esc_html__('The chat is still loading. Please try again in a moment.', 'modern-events-calendar-lite'),
        'suggest' => esc_html__('Before you send it, this guide might help:', 'modern-events-calendar-lite'),
        'topic' => esc_html__('Topic', 'modern-events-calendar-lite'),
        'updated' => esc_html__('Updated to latest', 'modern-events-calendar-lite'),
        'renew' => esc_html__('Your license has expired. Renew to open a support ticket.', 'modern-events-calendar-lite'),
        'popupBlocked' => esc_html__('Please allow pop-ups for this site to open the ticket portal.', 'modern-events-calendar-lite'),
        'ctxFail' => esc_html__('Could not attach system info automatically. Portal opened without context — check license / my.webnus API.', 'modern-events-calendar-lite'),
        'rateLimited' => esc_html__('Too many ticket attempts for this license. Please try again in an hour.', 'modern-events-calendar-lite'),
    ],
];
?>
<div class="wrap" id="mec-wrap">
<div id="webnus-dashboard" class="mec-support-page">
    <div class="mec-support mec-ds" id="mecSupport">

        <!-- Header: who you are + the two primary actions, always visible -->
        <div class="mec-page-head mec-page-head--split">
            <div class="mec-page-head__main">
                <span class="mec-page-eyebrow"><?php esc_html_e('Modern Events Calendar', 'modern-events-calendar-lite'); ?></span>
                <h1><span class="mec-page-mark" aria-hidden="true"><i class="mec-sl-support"></i></span><?php esc_html_e('Support', 'modern-events-calendar-lite'); ?></h1>
                <p class="mec-page-lead"><?php esc_html_e('Find answers fast, check your setup, and reach our team when you need to.', 'modern-events-calendar-lite'); ?></p>
                <div class="mec-settings-meta"><?php echo $mec_tags; // phpcs:ignore ?></div>
            </div>
            <div class="mec-page-head__actions ms-actions"><?php echo $head_actions; // phpcs:ignore ?></div>
        </div>
        <hr class="wp-header-end" />

        <!-- Contextual banner: update / license state -->
        <div class="banner <?php echo esc_attr($banner['k']); ?>">
            <span class="bi"><span class="ico" data-i="<?php echo esc_attr($banner['i']); ?>"></span></span>
            <div class="bt"><b><?php echo $banner['t']; // phpcs:ignore ?></b><span><?php echo $banner['d']; // phpcs:ignore ?></span></div>
            <?php if (trim($banner['a']) !== ''): ?><div class="acts"><?php echo $banner['a']; // phpcs:ignore ?></div><?php endif; ?>
        </div>

        <?php if (current_user_can('read')): ?>

        <!-- Search at the top: most visits start with a question -->
        <section class="ms-search">
            <h2><?php esc_html_e('What do you need help with?', 'modern-events-calendar-lite'); ?></h2>
            <form id="msKbForm" role="search" action="<?php echo esc_url($search_url); ?>" method="get" target="_blank">
                <span class="ico" data-i="search"></span>
                <label for="msKbQ" class="sr"><?php esc_html_e('Search the knowledgebase', 'modern-events-calendar-lite'); ?></label>
                <input id="msKbQ" type="search" name="s" autocomplete="off" placeholder="<?php esc_attr_e('Search docs: try "booking form", "taxes", "shortcode"&hellip;', 'modern-events-calendar-lite'); ?>" />
                <button class="btn btn-pri" type="submit"><?php esc_html_e('Search', 'modern-events-calendar-lite'); ?></button>
            </form>
            <div class="chips" id="msChips"><span><?php esc_html_e('Popular:', 'modern-events-calendar-lite'); ?></span>
                <button type="button"><?php esc_html_e('Activate license', 'modern-events-calendar-lite'); ?></button><button type="button"><?php esc_html_e('Booking form', 'modern-events-calendar-lite'); ?></button><button type="button"><?php esc_html_e('Payment gateways', 'modern-events-calendar-lite'); ?></button><button type="button"><?php esc_html_e('Shortcodes', 'modern-events-calendar-lite'); ?></button><button type="button"><?php esc_html_e('Translate', 'modern-events-calendar-lite'); ?></button><button type="button"><?php esc_html_e('Emails not sending', 'modern-events-calendar-lite'); ?></button>
            </div>
        </section>

        <!-- Step 1: self-serve -->
        <section class="sec">
            <div class="sec-h"><h2><span class="num">1</span><?php esc_html_e('Find an answer yourself', 'modern-events-calendar-lite'); ?></h2><a class="lk" href="<?php echo esc_url($dox); ?>" target="_blank" rel="noopener"><?php esc_html_e('All documentation', 'modern-events-calendar-lite'); ?> <span class="ico" data-i="ext"></span></a></div>
            <div class="g4">
                <?php foreach ($resources as $r): ?>
                <a class="ds-card res" href="<?php echo esc_url($r['u']); ?>" target="_blank" rel="noopener">
                    <div class="ic"<?php if ($r['s']): ?> style="<?php echo esc_attr($r['s']); ?>"<?php endif; ?>><span class="ico" data-i="<?php echo esc_attr($r['i']); ?>"></span></div>
                    <h3><?php echo $r['t']; // phpcs:ignore ?></h3>
                    <p><?php echo $r['d']; // phpcs:ignore ?></p>
                    <span class="lk"><?php echo $r['c']; // phpcs:ignore ?> <span class="ico" data-i="arrow"></span></span>
                </a>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Browse by topic -->
        <section class="sec">
            <div class="sec-h"><h2><?php esc_html_e('Browse by topic', 'modern-events-calendar-lite'); ?></h2></div>
            <div class="g4">
                <?php foreach ($topics as $tp): ?>
                <div class="ds-card topic">
                    <h3><span class="ic" style="<?php echo esc_attr($tp['s']); ?>"><span class="ico" data-i="<?php echo esc_attr($tp['i']); ?>"></span></span><?php echo $tp['t']; // phpcs:ignore ?></h3>
                    <ul>
                        <?php foreach ($tp['l'] as $li): ?>
                        <li><a href="<?php echo esc_url($li[1]); ?>" target="_blank" rel="noopener"><span class="ico" data-i="arrow"></span><?php echo $li[0]; // phpcs:ignore ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Videos -->
        <section class="sec">
            <div class="sec-h"><h2><?php esc_html_e('Video tutorials', 'modern-events-calendar-lite'); ?></h2><a class="lk" href="https://www.youtube.com/playlist?list=PLOuD0zVFWiE5DdYyjZnKhSM2jK9QfExeY" target="_blank" rel="noopener"><?php esc_html_e('View channel', 'modern-events-calendar-lite'); ?> <span class="ico" data-i="ext"></span></a></div>
            <div class="g3">
                <?php foreach ($videos as $v): ?>
                <div class="ds-card vid">
                    <a class="th" href="<?php echo esc_url($v['u']); ?>" data-lity><span class="ico" data-i="playc"></span></a>
                    <h3><?php echo $v['t']; // phpcs:ignore ?></h3>
                    <ul>
                        <?php foreach ($v['l'] as $vl): ?>
                        <li><span class="ico" data-i="check"></span><?php echo $vl; // phpcs:ignore ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <a class="lk" href="<?php echo esc_url($v['u']); ?>" data-lity><?php esc_html_e('Watch video', 'modern-events-calendar-lite'); ?> <span class="ico" data-i="arrow"></span></a>
                </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Step 2: diagnose -->
        <section class="sec">
            <div class="sec-h"><h2><span class="num">2</span><?php esc_html_e('Check your setup &amp; common questions', 'modern-events-calendar-lite'); ?></h2></div>
            <div class="g2">
                <div class="ds-card faq">
                    <h3 class="card-h"><?php esc_html_e('Frequently asked questions', 'modern-events-calendar-lite'); ?></h3>
                    <details open>
                        <summary><?php esc_html_e('How should I update the plugin?', 'modern-events-calendar-lite'); ?><span class="ico" data-i="chev"></span></summary>
                        <div class="a"><?php esc_html_e('You have two options:', 'modern-events-calendar-lite'); ?>
                            <ol>
                                <li><b><?php esc_html_e('Auto-update (recommended):', 'modern-events-calendar-lite'); ?></b> <?php echo sprintf(esc_html__('activate your license in %1$s and update from the Plugins page. %2$s', 'modern-events-calendar-lite'), '<em>' . esc_html__('M.E. Calendar → Settings', 'modern-events-calendar-lite') . '</em>', '<a href="' . esc_url($dox . 'auto-update/') . '" target="_blank" rel="noopener">' . esc_html__('Guide', 'modern-events-calendar-lite') . '</a>'); ?></li>
                                <li><b><?php esc_html_e('Manual:', 'modern-events-calendar-lite'); ?></b> <?php echo sprintf(esc_html__('download the latest zip from your Webnus account and upload it via FTP. %s', 'modern-events-calendar-lite'), '<a href="' . esc_url($dox . 'manual-update/') . '" target="_blank" rel="noopener">' . esc_html__('Guide', 'modern-events-calendar-lite') . '</a>'); ?></li>
                            </ol>
                        </div>
                    </details>
                    <details>
                        <summary><?php esc_html_e('Do I lose my data or customizations if I update?', 'modern-events-calendar-lite'); ?><span class="ico" data-i="chev"></span></summary>
                        <div class="a"><?php esc_html_e('No. Events, bookings and settings are stored in your database and stay as they are. If you edited plugin files directly, move those edits to your theme first.', 'modern-events-calendar-lite'); ?> <a href="<?php echo esc_url($dox . 'including-mec-in-your-theme/'); ?>" target="_blank" rel="noopener"><?php esc_html_e('Template overrides', 'modern-events-calendar-lite'); ?></a> <?php esc_html_e('survive updates.', 'modern-events-calendar-lite'); ?></div>
                    </details>
                    <details>
                        <summary><?php esc_html_e('Can I customize the event pages?', 'modern-events-calendar-lite'); ?><span class="ico" data-i="chev"></span></summary>
                        <div class="a"><?php esc_html_e('Yes. Use the Single Event settings for layout, or override the template in your theme for full control.', 'modern-events-calendar-lite'); ?> <a href="<?php echo esc_url($dox . 'single-event-settings/'); ?>" target="_blank" rel="noopener"><?php esc_html_e('Single event settings', 'modern-events-calendar-lite'); ?></a> · <a href="<?php echo esc_url($dox . 'including-mec-in-your-theme/'); ?>" target="_blank" rel="noopener"><?php esc_html_e('Theme integration', 'modern-events-calendar-lite'); ?></a></div>
                    </details>
                    <details>
                        <summary><?php esc_html_e('Does MEC come translated, or do I need to translate it?', 'modern-events-calendar-lite'); ?><span class="ico" data-i="chev"></span></summary>
                        <div class="a"><?php esc_html_e('MEC ships with several community translations and works with WPML, Polylang and Loco Translate.', 'modern-events-calendar-lite'); ?> <a href="<?php echo esc_url($dox . 'translate-mec/'); ?>" target="_blank" rel="noopener"><?php esc_html_e('Translation guide', 'modern-events-calendar-lite'); ?></a></div>
                    </details>
                    <details>
                        <summary><?php esc_html_e('Can I have more than one calendar on one website?', 'modern-events-calendar-lite'); ?><span class="ico" data-i="chev"></span></summary>
                        <div class="a"><?php esc_html_e('Yes. Create as many shortcodes as you like, each with its own view, filters and categories.', 'modern-events-calendar-lite'); ?> <a href="<?php echo esc_url($dox . 'making-advanced-shortcodes/'); ?>" target="_blank" rel="noopener"><?php esc_html_e('Making shortcodes', 'modern-events-calendar-lite'); ?></a></div>
                    </details>
                    <details>
                        <summary><?php esc_html_e('Can I import/export events to/from MEC?', 'modern-events-calendar-lite'); ?><span class="ico" data-i="chev"></span></summary>
                        <div class="a"><?php echo sprintf(esc_html__('Yes. Go to %s to use iCal, CSV, Google Calendar, Meetup and more.', 'modern-events-calendar-lite'), '<em>' . esc_html__('M.E. Calendar → Import / Export', 'modern-events-calendar-lite') . '</em>'); ?> <a href="<?php echo esc_url($dox . 'import-and-export-events/'); ?>" target="_blank" rel="noopener"><?php esc_html_e('Guide', 'modern-events-calendar-lite'); ?></a></div>
                    </details>
                </div>

                <div class="ds-card">
                    <div class="sec-h"><h3 class="card-h"><?php esc_html_e('System status', 'modern-events-calendar-lite'); ?></h3><button type="button" class="btn btn-sec btn-sm" data-action="copy"><span class="ico" data-i="copy"></span><?php esc_html_e('Copy report', 'modern-events-calendar-lite'); ?></button></div>
                    <div class="score">
                        <?php if ($mec_issues): ?>
                        <span class="st warn"><span class="ico" data-i="alert"></span></span>
                        <div><b><?php echo sprintf(esc_html(_n('%s item needs attention', '%s items need attention', $mec_issues, 'modern-events-calendar-lite')), number_format_i18n($mec_issues)); ?></b><span><?php esc_html_e('Fix these first. It often solves the problem by itself.', 'modern-events-calendar-lite'); ?></span></div>
                        <?php else: ?>
                        <span class="st ok"><span class="ico" data-i="check"></span></span>
                        <div><b><?php esc_html_e('Everything looks healthy', 'modern-events-calendar-lite'); ?></b><span><?php esc_html_e('Your server meets all MEC requirements.', 'modern-events-calendar-lite'); ?></span></div>
                        <?php endif; ?>
                    </div>
                    <ul class="health">
                        <?php
                        $st_icons = ['ok' => 'check', 'warn' => 'alert', 'bad' => 'x', 'info' => 'info'];
                        foreach ($health as $h): ?>
                        <li>
                            <span class="st <?php echo esc_attr($h['st']); ?>"><span class="ico" data-i="<?php echo esc_attr($st_icons[$h['st']]); ?>"></span></span>
                            <span class="l"><?php echo $h['l']; // phpcs:ignore ?></span>
                            <span class="v"><?php echo $h['v']; // phpcs:ignore ?></span>
                            <?php echo $h['fix']; // phpcs:ignore ?>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    <div class="sysinfo">
                        <details>
                            <summary><span class="ico" data-i="chev"></span><?php esc_html_e('Show full system information', 'modern-events-calendar-lite'); ?></summary>
                            <table>
                                <?php foreach ($sys_rows as $sr): ?>
                                <tr><td><?php echo $sr[0]; // phpcs:ignore ?></td><td><?php echo $sr[1]; // phpcs:ignore ?></td></tr>
                                <?php endforeach; ?>
                            </table>
                        </details>
                    </div>
                    <?php if ($mec_debug_txt !== ''): ?>
                    <div class="sys-acts">
                        <div class="t"><b><?php esc_html_e('Debug log', 'modern-events-calendar-lite'); ?></b><span><?php echo $mec_debug_txt; // phpcs:ignore ?></span></div>
                        <?php echo $mec_debug_btn; // phpcs:ignore ?>
                        <?php if (!$mec_debug): ?><a class="btn btn-sec btn-sm" href="<?php echo esc_url($debug_doc_url); ?>" target="_blank" rel="noopener"><?php esc_html_e('How to enable', 'modern-events-calendar-lite'); ?></a><?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <!-- Step 3: contact (gated by license) -->
        <section class="sec">
            <div class="sec-h">
                <h2><span class="num">3</span><?php esc_html_e('Still need a hand? Talk to us', 'modern-events-calendar-lite'); ?></h2>
                <span class="hint"><?php echo $mec_has_support ? esc_html__('Quick question → chat · Something broken → ticket', 'modern-events-calendar-lite') : esc_html__('Lite and expired licenses get help from the community', 'modern-events-calendar-lite'); ?></span>
            </div>
            <div class="g3">
                <div class="ds-card ch<?php echo $mec_has_support ? ' rec' : ' locked'; ?>" data-ch="chat">
                    <span class="badge"><?php esc_html_e('Fastest', 'modern-events-calendar-lite'); ?></span>
                    <div class="top"><div class="ic"><span class="ico" data-i="chat"></span></div><div><h3><?php esc_html_e('Live chat', 'modern-events-calendar-lite'); ?></h3><div class="s"><?php esc_html_e('Real-time help from our team', 'modern-events-calendar-lite'); ?></div></div></div>
                    <div class="d"><?php esc_html_e('Best for quick how-to questions, license and billing help. Your MEC version and setup are shared automatically, so you won&#39;t have to explain them.', 'modern-events-calendar-lite'); ?></div>
                    <div class="meta"><span class="ico" data-i="clock"></span><?php esc_html_e('The chat window shows when agents are online', 'modern-events-calendar-lite'); ?></div>
                    <button type="button" class="btn btn-pri" data-action="chat"><span class="ico" data-i="chat"></span><?php esc_html_e('Chat with Support', 'modern-events-calendar-lite'); ?></button>
                    <?php if ($chat_lock_text !== ''): ?><div class="lock"><?php echo $chat_lock_text; // phpcs:ignore ?></div><?php endif; ?>
                </div>
                <div class="ds-card ch<?php echo $mec_has_support ? '' : ' locked'; ?>" data-ch="ticket">
                    <span class="badge"><?php esc_html_e('For bugs', 'modern-events-calendar-lite'); ?></span>
                    <div class="top"><div class="ic alt"><span class="ico" data-i="ticket"></span></div><div><h3><?php esc_html_e('Support ticket', 'modern-events-calendar-lite'); ?></h3><div class="s"><?php esc_html_e('We investigate &amp; follow up by email', 'modern-events-calendar-lite'); ?></div></div></div>
                    <div class="d"><?php esc_html_e('Best for errors, conflicts and anything specific to your site. We&#39;ll walk you through it and add your system report for you.', 'modern-events-calendar-lite'); ?></div>
                    <div class="meta"><span class="ico" data-i="clock"></span><?php esc_html_e('Typical first reply: 12–24 hours', 'modern-events-calendar-lite'); ?></div>
                    <button type="button" class="btn btn-sec" data-action="ticket"><span class="ico" data-i="ticket"></span><?php esc_html_e('Open a ticket', 'modern-events-calendar-lite'); ?></button>
                    <p class="ms-gdpr note"><?php esc_html_e('Your system info (versions and site URL) will be attached to the ticket.', 'modern-events-calendar-lite'); ?></p>
                    <?php if ($ticket_lock !== ''): ?><div class="lock"><?php echo $ticket_lock; // phpcs:ignore ?></div><?php endif; ?>
                </div>
                <div class="ds-card ch<?php echo $mec_has_support ? '' : ' rec'; ?>" data-ch="community">
                    <span class="badge"><?php esc_html_e('Open to all', 'modern-events-calendar-lite'); ?></span>
                    <div class="top"><div class="ic alt2"><span class="ico" data-i="users"></span></div><div><h3><?php esc_html_e('Community', 'modern-events-calendar-lite'); ?></h3><div class="s"><?php esc_html_e('Ask other MEC users in the WordPress forums', 'modern-events-calendar-lite'); ?></div></div></div>
                    <div class="d"><?php esc_html_e('Share setups, get tips and search solved threads. Open to Lite users and anyone without an active license.', 'modern-events-calendar-lite'); ?></div>
                    <div class="meta"><span class="ico" data-i="users"></span><?php esc_html_e('No license needed', 'modern-events-calendar-lite'); ?></div>
                    <a class="btn btn-sec" href="<?php echo esc_url($community_url); ?>" target="_blank" rel="noopener"><span class="ico" data-i="users"></span><?php esc_html_e('Join the community', 'modern-events-calendar-lite'); ?></a>
                </div>
            </div>
            <p class="ms-foot-links"><?php esc_html_e('Have an idea for MEC?', 'modern-events-calendar-lite'); ?> <a href="<?php echo esc_url($feature_request_url); ?>" target="_blank" rel="noopener"><?php esc_html_e('Request a feature', 'modern-events-calendar-lite'); ?></a> · <a href="<?php echo esc_url($changelog_url); ?>" target="_blank" rel="noopener"><?php esc_html_e("See what's new in the changelog", 'modern-events-calendar-lite'); ?></a></p>
        </section>

        <?php endif; ?>

        <!-- Ticket modal disabled: Open a ticket → portal with system report only; user writes subject/message in Crisp. -->

        <!-- Toast -->
        <div class="toast" id="msToast"></div>
    </div>
</div>
</div>

<?php
$this->factory->params('footer', function () use ($mec_js) {
?>
<script>
window.MECSupport = <?php echo wp_json_encode($mec_js); ?>;
</script>
<script>
(function () {
    var CFG = window.MECSupport || {};
    var I18N = CFG.i18n || {};

    /* ---- Icons: self-contained SVG set, hydrated client-side ---- */
    var P = {
        search: '<circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>',
        arrow: '<path d="M5 12h14M13 6l6 6-6 6"/>',
        ext: '<path d="M14 4h6v6M20 4l-9 9M18 14v5a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h5"/>',
        book: '<path d="M4 19.5V5a2 2 0 0 1 2-2h13v16H6.5A2.5 2.5 0 0 0 4 21.5"/><path d="M8 7h7M8 11h7"/>',
        play: '<rect x="3" y="5" width="18" height="14" rx="3"/><path d="m10 9 5 3-5 3z"/>',
        playc: '<circle cx="12" cy="12" r="10"/><path d="m10 8 6 4-6 4z" fill="currentColor"/>',
        bulb: '<path d="M9 18h6M10 21h4M12 3a6 6 0 0 0-3.5 10.9c.6.5 1 1.2 1 2V16h5v-.1c0-.8.4-1.5 1-2A6 6 0 0 0 12 3z"/>',
        code: '<path d="m8 8-4 4 4 4M16 8l4 4-4 4M13.5 5l-3 14"/>',
        rocket: '<path d="M5 15c-1.5 1.5-2 5-2 5s3.5-.5 5-2M9 12l3 3M14.5 4.5c2.5-1 5-1 5.5-.5.5.5.5 3-.5 5.5L13 16l-5-5z"/><circle cx="15" cy="9" r="1.5"/>',
        gear: '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .3 1.8l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.7 1.7 0 0 0-1.8-.3 1.7 1.7 0 0 0-1 1.5V21a2 2 0 1 1-4 0v-.1a1.7 1.7 0 0 0-1.1-1.5 1.7 1.7 0 0 0-1.8.3l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1a1.7 1.7 0 0 0 .3-1.8 1.7 1.7 0 0 0-1.5-1H3a2 2 0 1 1 0-4h.1a1.7 1.7 0 0 0 1.5-1.1 1.7 1.7 0 0 0-.3-1.8l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1a1.7 1.7 0 0 0 1.8.3H9a1.7 1.7 0 0 0 1-1.5V3a2 2 0 1 1 4 0v.1a1.7 1.7 0 0 0 1 1.5 1.7 1.7 0 0 0 1.8-.3l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.7 1.7 0 0 0-.3 1.8V9a1.7 1.7 0 0 0 1.5 1H21a2 2 0 1 1 0 4h-.1a1.7 1.7 0 0 0-1.5 1z"/>',
        card: '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 10h18M7 15h3"/>',
        shield: '<path d="M12 3 4 6v6c0 5 3.5 8 8 9 4.5-1 8-4 8-9V6z"/><path d="m9 12 2 2 4-4"/>',
        chat: '<path d="M21 12a8 8 0 0 1-11.6 7.1L4 20l1-4.6A8 8 0 1 1 21 12z"/>',
        ticket: '<path d="M3 8a2 2 0 0 0 0 4 2 2 0 0 1 0 4v2h18v-2a2 2 0 0 1 0-4 2 2 0 0 1 0-4V6H3z"/><path d="M14 6v12" stroke-dasharray="2 2"/>',
        check: '<path d="m5 12.5 4.5 4.5L19 7.5"/>',
        x: '<path d="M6 6l12 12M18 6 6 18"/>',
        alert: '<path d="M12 9v4M12 17h.01M10.3 3.9 2.4 18a2 2 0 0 0 1.7 3h15.8a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0z"/>',
        info: '<circle cx="12" cy="12" r="9"/><path d="M12 11v6M12 7.5h.01"/>',
        clock: '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
        users: '<circle cx="9" cy="8" r="3.5"/><path d="M2.5 20a6.5 6.5 0 0 1 13 0M16 4.5a3.5 3.5 0 0 1 0 7M18 14a6 6 0 0 1 3.5 6"/>',
        chev: '<path d="m6 9 6 6 6-6"/>',
        copy: '<rect x="8" y="8" width="13" height="13" rx="2"/><path d="M16 8V5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h3"/>',
        life: '<circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="4"/><path d="m5.6 5.6 3.6 3.6M14.8 14.8l3.6 3.6M18.4 5.6l-3.6 3.6M9.2 14.8l-3.6 3.6"/>',
        refresh: '<path d="M20 11a8 8 0 0 0-14.9-3M4 4v4h4M4 13a8 8 0 0 0 14.9 3M20 20v-4h-4"/>',
        lock: '<rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/>',
        key: '<circle cx="8" cy="15" r="4"/><path d="m11 12 9-9M17 6l3 3"/>'
    };

    function icons(root) {
        var els = (root || document).querySelectorAll('span[data-i]:not([data-done])');
        for (var i = 0; i < els.length; i++) {
            var name = els[i].getAttribute('data-i');
            if (!P[name]) continue;
            els[i].innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' + P[name] + '</svg>';
            els[i].setAttribute('data-done', '1');
        }
    }
    icons();

    /* ---- Toast ---- */
    var toastEl = document.getElementById('msToast'), toastT;
    function toast(msg) {
        if (!toastEl) return;
        toastEl.innerHTML = '<span class="ico" data-done="1">' + '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' + P.check + '</svg>' + '</span><span></span>';
        toastEl.querySelectorAll('span')[1].textContent = msg;
        toastEl.classList.add('on');
        clearTimeout(toastT);
        toastT = setTimeout(function () { toastEl.classList.remove('on'); }, 3400);
    }

    /* ---- Clipboard ---- */
    function legacyCopy(text) {
        var ta = document.createElement('textarea');
        ta.value = text;
        ta.style.position = 'fixed';
        ta.style.opacity = '0';
        document.body.appendChild(ta);
        ta.focus();
        ta.select();
        var ok = false;
        try { ok = document.execCommand('copy'); } catch (e) {}
        ta.remove();
        return ok;
    }
    function copy(text) {
        if (navigator.clipboard && navigator.clipboard.writeText) {
            return navigator.clipboard.writeText(text).then(function () { return true; }, function () { return legacyCopy(text); });
        }
        return Promise.resolve(legacyCopy(text));
    }

    /* ---- Live chat (Crisp): agents see the user's setup without asking ---- */
    function openChat() {
        if (window.$crisp && window.$crisp.push) {
            var s = CFG.site || {};
            try {
                window.$crisp.push(['set', 'session:data', [[
                    ['mec_version', s.mec], ['mec_package', s.pkg], ['license', s.lic],
                    ['wp', s.wp], ['php', s.php], ['site', s.home], ['source', 'mec-dashboard']
                ]]]);
            } catch (e) {}
            window.$crisp.push(['do', 'chat:show']);
            window.$crisp.push(['do', 'chat:open']);
        } else if (I18N.chatLoading) {
            toast(I18N.chatLoading);
        }
    }

    /* ---- Open ticket: no modal — blank tab + ctx; report only via ctx (never in URL) ---- */
    function openTicketPortal() {
        var portal = (CFG.urls && CFG.urls.ticket) ? CFG.urls.ticket : 'https://my.webnus.net/support-tickets/';
        var emptyCreate = portal + (portal.indexOf('?') >= 0 ? '&' : '?') + 'view=create';
        var tab = window.open('about:blank', '_blank');
        if (!tab) {
            toast(I18N.popupBlocked || 'Please allow pop-ups.');
            return;
        }
        try { tab.opener = null; } catch (err) {}

        var body = new URLSearchParams();
        body.append('action', 'mec_support_context');
        body.append('nonce', CFG.nonce || '');
        body.append('topic', '');
        body.append('message', '');
        body.append('updated', '0');
        body.append('include_sys', '1');

        fetch(CFG.ajaxurl || window.ajaxurl || '', {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
            body: body.toString()
        }).then(function (res) {
            return res.json().then(function (json) {
                return { ok: res.ok, status: res.status, json: json };
            }, function () {
                return { ok: false, status: res.status, json: null };
            });
        }).then(function (r) {
            var json = r.json || {};
            var inner = (json && json.data && typeof json.data === 'object') ? json.data : {};
            var url = json.url || inner.url || '';
            var reason = inner.reason || json.reason || '';

            var apiMsg = inner.message || json.message || '';

            if (reason === 'expired') {
                tab.close();
                toast(I18N.renew || 'License expired.');
                if (CFG.urls && CFG.urls.renew) window.location.href = CFG.urls.renew;
                return;
            }

            if (reason === 'rate_limited') {
                tab.close();
                toast(I18N.rateLimited || 'Too many ticket attempts. Please try again in an hour.');
                return;
            }

            if (url && url.indexOf('ctx=') !== -1) {
                tab.location = url;
                toast(I18N.ticketOpened || 'Opened the ticket portal.');
                return;
            }

            // API failed: open empty create form — never put system report in the query string.
            tab.location = emptyCreate;
            toast(
                (I18N.ctxFail || 'Could not attach system info.')
                + (reason ? ' [' + reason + ']' : '')
                + (apiMsg ? ' ' + apiMsg : '')
            );
        }).catch(function () {
            tab.location = emptyCreate;
            toast(I18N.ctxFail || 'Could not attach system info.');
        });
    }

    /* ---- Delegated actions ---- */
    document.addEventListener('click', function (e) {
        var el = e.target.closest ? e.target.closest('[data-action]') : null;
        if (!el) return;
        var act = el.getAttribute('data-action');
        if (act === 'chat') { e.preventDefault(); openChat(); }
        else if (act === 'ticket') { e.preventDefault(); openTicketPortal(); }
        else if (act === 'copy') {
            copy(CFG.report || '').then(function (ok) { toast(ok ? I18N.copied : I18N.copyFail); });
        }
    });
    /* ---- Search ---- */
    var form = document.getElementById('msKbForm');
    if (form) form.addEventListener('submit', function (e) {
        e.preventDefault();
        var q = document.getElementById('msKbQ').value.trim();
        if (!q) { document.getElementById('msKbQ').focus(); return; }
        window.open(((CFG.urls || {}).search || 'https://webnus.net/dox/modern-events-calendar/') + '?s=' + encodeURIComponent(q), '_blank', 'noopener');
    });

    var chips = document.getElementById('msChips');
    if (chips) chips.addEventListener('click', function (e) {
        var b = e.target.closest ? e.target.closest('button') : null;
        if (!b) return;
        var q = document.getElementById('msKbQ');
        q.value = b.textContent.trim();
        q.focus();
    });
})();
</script>
<script type="text/javascript">window.$crisp=[];window.CRISP_WEBSITE_ID="1ec276e5-9da2-4a16-859f-eae7bcbb4ae9";(function(){d=document;s=d.createElement("script");s.src="https://client.crisp.chat/l.js";s.async=1;d.getElementsByTagName("head")[0].appendChild(s);})();</script>
<?php });
