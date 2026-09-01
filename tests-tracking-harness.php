<?php
/**
 * Standalone harness for the tracking logic (no WordPress).
 * Run: php tests-tracking-harness.php
 */

// CLI only — never executable through a web request.
if (PHP_SAPI !== 'cli') exit;

error_reporting(E_ALL);

// ---- Minimal WordPress stubs -------------------------------------------------
$GLOBALS['__options'] = array();
$GLOBALS['__sent'] = array();

function get_option($k, $d = false) { return array_key_exists($k, $GLOBALS['__options']) ? $GLOBALS['__options'][$k] : $d; }
function update_option($k, $v, $autoload = null) { $GLOBALS['__options'][$k] = $v; return true; }
function delete_option($k) { unset($GLOBALS['__options'][$k]); return true; }
function add_option($k, $v, $autoload = null) { return update_option($k, $v); }
function apply_filters($tag, $value) { return $value; }
function home_url() { return 'https://example.com'; }
function get_option_admin_email() { return 'a@b.c'; }
function wp_remote_post($url, $args) { $GLOBALS['__sent'][] = array('url' => $url, 'body' => json_decode($args['body'], true)); return array('response' => array('code' => 200)); }
function is_wp_error($x) { return false; }
function wp_json_encode($v) { return json_encode($v); }
function wp_generate_uuid4() { return sprintf('%04x%04x', mt_rand(), mt_rand()); }
function untrailingslashit($s) { return rtrim($s, '/'); }
if (!defined('HOUR_IN_SECONDS')) define('HOUR_IN_SECONDS', 3600);
if (!defined('MEC_VERSION')) define('MEC_VERSION', '7.35.1');
if (!defined('MEC_EDITION')) define('MEC_EDITION', 'pro');

// Pretend the site has an admin email (real WP always does).
$GLOBALS['__options']['admin_email'] = 'admin@example.com';

/** Find the sent body of a named event (identify merges share the channel). */
function sent_event($name) { foreach ($GLOBALS['__sent'] as $s) if ($s['body']['event'] === $name) return $s['body']; return null; }

require __DIR__ . '/app/core/src/Tracking/PostHog.php';
require __DIR__ . '/app/core/src/Tracking/Util.php';

use MEC\Tracking\PostHog;
use MEC\Tracking\Util;

$pass = 0; $fail = 0;
function check($label, $cond) { global $pass, $fail; if ($cond) { $pass++; echo "  OK  $label\n"; } else { $fail++; echo "  FAIL $label\n"; } }

echo "== Util buckets ==\n";
check('bucket 0', Util::bucket(0) === '0');
check('bucket 3', Util::bucket(3) === '1_4');
check('bucket 20', Util::bucket(20) === '5_20');
check('bucket 100', Util::bucket(100) === '21_100');
check('bucket 5000', Util::bucket(5000) === '101_plus');
check('price 0', Util::price_bucket(0) === '0');
check('price 499', Util::price_bucket(499) === '100_499');

echo "\n== capture() consent gate + retroactive queue ==\n";
$t = new PostHog();
$GLOBALS['__sent'] = array();

// No consent: activation event must be QUEUED, nothing sent.
$t->capture('mec_pro_activated', array('activation_type' => 'new_install'));
check('nothing sent without consent', count($GLOBALS['__sent']) === 0);
$queue = get_option('mec_posthog_queue');
check('activation queued', is_array($queue) && count($queue) === 1 && $queue[0]['event'] === 'mec_pro_activated');

// A non-queueable event must be dropped entirely (not queued).
$t->capture('mec_event_published', array('x' => 1));
$queue = get_option('mec_posthog_queue');
check('non-lifecycle NOT queued', is_array($queue) && count($queue) === 1);

// Accept consent + replay.
update_option(PostHog::CONSENT_OPTION, 'accepted');
$t->flush_queue();
check('queued event replayed after consent', count($GLOBALS['__sent']) === 1);
check('replayed event name', $GLOBALS['__sent'][0]['body']['event'] === 'mec_pro_activated');

echo "\n== common properties on every event ==\n";
$GLOBALS['__sent'] = array();
$t->capture('mec_event_published', array('publish_action' => 'created'));
$body = $GLOBALS['__sent'][0]['body'];
$p = $body['properties'];
check('site_instance_id present', !empty($p['site_instance_id']));
check('edition = pro', $p['edition'] === 'pro');
check('event_source = php', $p['event_source'] === 'php');
check('plugin_version present', $p['plugin_version'] === '7.35.1');
check('NO admin_email in event properties', !isset($p['admin_email']));
check('email only in person $set', isset($p['$set']['email']));

echo "\n== identity: install UUID -> EDD account ==\n";
$install_id = get_option('mec_posthog_install_id');
check('distinct_id starts as install UUID', $body['distinct_id'] === $install_id);

$GLOBALS['__sent'] = array();
update_option('mec_license_account_id', 'edd_customer_18372');
$t->capture('mec_event_published', array('publish_action' => 'updated'));
$body2 = sent_event('mec_event_published');
check('distinct_id switches to account', $body2['distinct_id'] === 'edd_customer_18372');
check('edd_customer_id property present', $body2['properties']['edd_customer_id'] === 'edd_customer_18372');
$identify = null;
foreach ($GLOBALS['__sent'] as $s) if ($s['body']['event'] === '$identify') $identify = $s['body'];
check('$identify merge sent once', $identify !== null && $identify['properties']['$anon_distinct_id'] === $install_id);
$GLOBALS['__sent'] = array();
$t->capture('mec_event_published', array());
$second_identify = 0;
foreach ($GLOBALS['__sent'] as $s) if ($s['body']['event'] === '$identify') $second_identify++;
check('$identify sent exactly once', $second_identify === 0);

echo "\n== js source flag ==\n";
$GLOBALS['__sent'] = array();
$t->capture('mec_calendar_view_rendered', array('view_type' => 'list'), array(), 'js');
check('event_source = js relayed', sent_event('mec_calendar_view_rendered')['properties']['event_source'] === 'js');

echo "\n{$pass} passed, {$fail} failed\n";
exit($fail ? 1 : 0);
