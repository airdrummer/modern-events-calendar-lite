<?php

namespace MEC;

use MEC\Libraries\FlushNotices;
use MEC\Attendees\AttendeesTable;

/**
 * Core Class in Plugin
 */
final class Base
{

	/**
	 * Plugin Version
	 *
	 * @var string
	 */
	public static $version = '1.0.0';

	/**
	 * Session instance
	 *
	 * @var bool
	 */
	protected static $instance;

	/**
	 * MEC Constructor
	 */
	public function __construct()
	{

		$this->define();
		$this->includes();
		$this->init_hooks();
		$this->admin();
		$this->enqueue_scripts();
	}

	/**
	 * MEC Instance
	 *
	 * @return self()
	 */
	public static function instance()
	{

		if (is_null(self::$instance)) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Set Constants
	 *
	 * @return void
	 */
	public function define()
	{

		define('MEC_CORE_PD', plugin_dir_path(MEC_CORE_FILE));
		define('MEC_CORE_PDI', plugin_dir_path(MEC_CORE_FILE) . 'src/');
		define('MEC_CORE_PU_JS', plugins_url('assets/js/', MEC_CORE_FILE));
		define('MEC_CORE_PU_CSS', plugins_url('assets/css/', MEC_CORE_FILE));
		define('MEC_CORE_PU_IMG', plugins_url('assets/img/', MEC_CORE_FILE));
		define('MEC_CORE_PU_FONTS', plugins_url('assets/fonts/', MEC_CORE_FILE));
		define('MEC_CORE_TEMPLATES', plugin_dir_path(MEC_CORE_FILE) . 'templates/');
	}

	/**
	 * Include Files
	 *
	 * @return void
	 */
	public function includes() {}


	/**
	 * Include Files If is Admin
	 *
	 * @return void
	 */
	public function admin()
	{

		if (!is_admin()) {
			return;
		}

		FlushNotices::getInstance()->init();
	}


	/**
	 * Register actions enqueue scripts
	 *
	 * @return void
	 */
	public function enqueue_scripts() {}

	/**
	 * Add Hooks - Actions and Filters
	 *
	 * @return void
	 */
	public function init_hooks()
	{

		add_action('admin_notices', array($this, 'upgrade_notice'));
		add_action('admin_notices', array($this, 'marketing_notice'));
		add_action('wp_ajax_mec_dismiss_marketing_notice', array($this, 'dismiss_marketing_notice'));
		add_action('wp_ajax_mec-upgrade-transactions-in-db', array(__CLASS__, 'upgrade_transactions_db_by_ajax'));

		add_action('init', [$this, 'init']);

		// PostHog product tracking. Registered here (init_hooks runs once) rather
		// than in init(), which mec.php calls directly AND hooks to 'init' — so
		// init() runs twice per request and would double-register these hooks.
		add_action('init', [$this, 'init_tracking']);

		// Activation lifecycle analytics (mec_lite_activated / mec_pro_activated).
		// Registered against the REAL main plugin file: MEC_CORE_FILE points at
		// app/core/mec.php, whose basename never matches the "activate_{plugin}"
		// action, so a hook on it would never fire on a normal activation.
		register_activation_hook(MEC_ABSPATH . MEC_FILENAME, array('\MEC\Tracking\Activator', 'activate'));

		// Content lifecycle events (publish/update, FES submissions). Registered
		// unconditionally — unlike init_tracking() — because FES submissions
		// arrive on front-end requests.
		\MEC\Tracking\ContentEvents::register();

		// Booking lifecycle (mec_booking_completed) — gateway confirmations
		// arrive on front-end / webhook requests, so also unconditional.
		\MEC\Tracking\BookingEvents::register();

		register_activation_hook(MEC_CORE_FILE, __CLASS__ . '::register_activation');
		$db_version = get_option('mec_core_db', '1.0.0');
		if (version_compare($db_version, '6.10.0', '<')) {

			static::register_activation();
		}
	}

	/**
	 * Active Plugin
	 *
	 * @return void
	 */
	public static function register_activation()
	{

		AttendeesTable::create_table();

		update_option('mec_core_db', MEC_VERSION);
	}


	/**
	 * Init MEC after WordPress
	 *
	 * @return void
	 */
	public function init() {}

	/**
	 * Initialize PostHog product tracking. Runs once on the WordPress `init`
	 * action. Only needed in wp-admin (settings save, consent popup, cron
	 * scheduling) and during WP-Cron (the snapshot job); front-end visitor
	 * requests are skipped entirely so page loads are untouched.
	 *
	 * @return void
	 */
	public function init_tracking()
	{
		static $done = false;
		if ($done) return;
		$done = true;

		if (!is_admin() && !(defined('DOING_CRON') && DOING_CRON)) return;

		(new \MEC\Tracking\PostHog())->init();
		(new \MEC\Tracking\Consent())->init();
		(new \MEC\Tracking\Snapshot())->init();
		(new \MEC\Tracking\SettingsControl())->init();
		(new \MEC\Tracking\ClientRelay())->init();
	}

	public static function should_include_assets()
	{

		$factory = \MEC::getInstance('app.libraries.factory');

		return $factory->should_include_assets('frontend');
	}

	public static function is_include_assets_in_footer()
	{

		return '1' == \MEC\Settings\Settings::getInstance()->get_settings('assets_in_footer_status') ? true : false;
	}

	public static function get_main()
	{

		global $MEC_Main;
		if (is_null($MEC_Main)) {

			$MEC_Main = new \MEC_main();
		}

		return $MEC_Main;
	}

	/**
	 * Upgrade transactions in db
	 *
	 * @return void
	 */
	public static function upgrade_transactions()
	{

		$db_version = get_option('mec_transaction_version', '1.0.0');
		if (version_compare($db_version, MEC_VERSION, '<')) {

			if (current_user_can('activate_plugins')) {

				\MEC\Transactions\Transaction::upgrade_db();
			}
		}
	}

	/**
	 * Upgrade transactions in db by ajax
	 *
	 * @return void
	 */
	public static function upgrade_transactions_db_by_ajax()
	{

		if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'mec-upgrade-transactions-in-db')) {

			return;
		}

		$db_version = get_option('mec_transaction_version', '1.0.0');
		if (version_compare($db_version, '6.10.0', '<')) {

			static::upgrade_transactions();
			wp_send_json(array(
				'done' => false,
			));
		} else {

			wp_send_json(array(
				'done' => true,
			));
		}
	}

	public function upgrade_notice($type = false)
	{

		$booking_module_status = (bool)\MEC\Settings\Settings::getInstance()->get_settings('booking_status');
		$db_version = get_option('mec_transaction_version', '1.0.0');
		if (version_compare($db_version, '6.10.0', '<') && $booking_module_status) {

			if (!current_user_can('activate_plugins')) {
				return;
			}

			$upgrade_url = admin_url('?mec_upgrade_db=true');
			$message        = '<p>'
				. __('Your booking database needs updating. Click the button below and wait for it to complete. Do not refresh the page.', 'modern-events-calendar-lite')
				. '<br><b>' . __('Note: If you have many bookings, this may take longer. Please be patient.', 'modern-events-calendar-lite') . '</b>'
				. '</p>';
			$message       .= '<p>' . sprintf('<a href="%s" class="button-primary mec-upgrade-db">%s</a>', $upgrade_url, __('Upgrade Database Now', 'modern-events-calendar-lite')) . '</p>';

?>
			<script>
				jQuery(document).ready(function($) {
					$('.mec-upgrade-db').on('click', function(e) {
						e.preventDefault();

						var $btn = $(this);
						$btn.html("<?php echo __('Updating Database...', 'modern-events-calendar-lite'); ?>");
						$.post(
							"<?php echo admin_url('admin-ajax.php'); ?>", {
								action: 'mec-upgrade-transactions-in-db',
								nonce: "<?php echo wp_create_nonce('mec-upgrade-transactions-in-db') ?>",
							},
							function(r) {

								if (false == r.done) {

									$('.mec-upgrade-db').trigger('click');
								} else {

									$btn.html("<?php echo __('Database has been upgraded.', 'modern-events-calendar-lite'); ?>");
								}
							}
						)
					});
				});
			</script>
			<div class="notice notice-error is-dismissible">
				<p><?php echo $message; ?></p>
			</div>
			<?php
		}
	}

	public function marketing_notice()
	{
		$debug = isset($_GET['mec-debug-notice']);

		// Admins only — this never faces editors or subscribers.
		if (!current_user_can('activate_plugins')) {
			if ($debug) {
				echo '<div class="notice notice-error"><p><strong>MEC notice debug:</strong> skipped: current user lacks activate_plugins (user ' . esc_html(get_current_user_id()) . ').</p></div>';
			}
			return;
		}

		// Category 4 targets Lite installs, 5 targets Pro.
		$factory = \MEC::getInstance('app.libraries.factory');
		$category = $factory->isProBuild() ? 5 : 4;

		if ($debug) {
			echo '<div class="notice notice-warning"><p><strong>MEC notice debug:</strong> code v5 is running: querying category ' . esc_html($category) . '.</p></div>';
		}

		$response_lite = wp_remote_get(
			add_query_arg(
				array(
					'per_page' => 1,
					'page' => 1,
					'categories' => $category,
				),
				'https://notifications.webnus.site/wp-json/wp/v2/posts'
			),
			array(
				'timeout' => 50, // Fix for: cURL error 28: Operation timed out after...
			)
		);

		if (is_wp_error($response_lite) || wp_remote_retrieve_response_code($response_lite) !== 200) {
			// ?mec-debug-notice=1 surfaces the otherwise-silent failure reasons.
			if (isset($_GET['mec-debug-notice'])) {
				$reason = is_wp_error($response_lite) ? $response_lite->get_error_message() : 'HTTP ' . wp_remote_retrieve_response_code($response_lite);
				echo '<div class="notice notice-error"><p><strong>MEC notice debug:</strong> posts request failed: ' . esc_html($reason) . '</p></div>';
			}
			return;
		}

		$body = json_decode(wp_remote_retrieve_body($response_lite));

		if (!is_countable($body) || count($body) === 0) {
			if ($debug) {
				echo '<div class="notice notice-error"><p><strong>MEC notice debug:</strong> API returned 0 posts for category ' . esc_html($category) . '.</p></div>';
			}
			return;
		}

		// Per-user, per-post dismissal: a newly published post shows again.
		// ?mec-show-notice=1 bypasses it (developer escape hatch).
		$post_id = (int) $body[0]->id;
		$dismissed = (int) get_user_meta(get_current_user_id(), 'mec_dismissed_marketing_notice', true);

		if (!isset($_GET['mec-show-notice']) && $dismissed === $post_id) {
			if ($debug) {
				echo '<div class="notice notice-error"><p><strong>MEC notice debug:</strong> dismissed for post ' . esc_html($post_id) . ' (user ' . esc_html(get_current_user_id()) . '): use ?mec-show-notice=1 to bypass.</p></div>';
			}
			return;
		}

		$featured_media = $body[0]->featured_media;
		$title = $body[0]->title->rendered;
		$content = $body[0]->content->rendered;

		$featured_image = wp_remote_get(
			'https://notifications.webnus.site/wp-json/wp/v2/media/' . $featured_media,
			array(
				'timeout' => 50, // Fix for: cURL error 28: Operation timed out after...
			)
		);

		if (is_wp_error($featured_image) || wp_remote_retrieve_response_code($featured_image) !== 200) {
			if ($debug) {
				$reason = is_wp_error($featured_image) ? $featured_image->get_error_message() : 'HTTP ' . wp_remote_retrieve_response_code($featured_image);
				echo '<div class="notice notice-error"><p><strong>MEC notice debug:</strong> media request failed: ' . esc_html($reason) . '</p></div>';
			}
			return;
		}

		$body_featured_image = json_decode(wp_remote_retrieve_body($featured_image));
		$lite_featured_image = $body_featured_image->guid->rendered;
		?>
				<style>
					.notice.mec-marketing-notice {
						padding: 0;
						border: 1px solid #dcdcdc;
						border-radius: 11px;
						background: #fff;
						overflow: hidden;
						font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Oxygen, Roboto, sans-serif;
					}
					.mec-marketing-notice-body {
						display: flex;
						align-items: flex-start;
						flex-wrap: wrap;
						padding: 20px 30px 30px;
						line-height: 24px;
						font-size: 14px;
					}
					.mec-marketing-notice-body .mec-marketing-notice-image {
						width: 240px;
						max-width: 100%;
						height: auto;
						flex-shrink: 0;
						margin-right: 30px;
					}
					.mec-marketing-notice-content { flex: 1; min-width: 0; }
					.mec-marketing-notice-label {
						text-align: center;
						background: rgba(186, 240, 252, 0.34);
						border-radius: 6px;
						letter-spacing: 4.4px;
						color: #00CAE6;
						text-transform: uppercase;
						padding: 10px 5px;
						font-weight: bold;
						margin-bottom: 40px;
					}
					.mec-marketing-notice-content p { font-size: 14px; }
					.mec-marketing-notice-content a {
						background: #1fcae4;
						color: #fff !important;
						padding: 6px 20px;
						margin-top: 3px;
						margin-bottom: 12px;
						display: inline-block;
						border-radius: 60px;
						font-size: 13px;
						letter-spacing: .4px;
						font-weight: 600;
						text-decoration: none !important;
						box-shadow: 0 0 0 3px rgb(56 213 237 / 8%) !important;
						outline: none;
						border: 0;
					}
					.mec-marketing-notice-content a:hover {
						box-shadow: 0 0 0 4px rgb(56 213 237 / 15%) !important;
					}
					.mec-marketing-notice-content a:focus {
						border-radius: 60px !important;
						box-shadow: 0 0 0 4px rgb(56 213 237 / 15%) !important;
					}
				</style>
				<div class="notice is-dismissible mec-marketing-notice" data-mec-post-id="<?php echo esc_attr($post_id); ?>">
					<div class="mec-marketing-notice-body">
						<img class="mec-marketing-notice-image" src="<?php echo esc_url($lite_featured_image); ?>" alt="<?php echo esc_attr(wp_strip_all_tags($title)); ?>" />
						<div class="mec-marketing-notice-content">
							<div class="mec-marketing-notice-label"><?php echo $title; ?></div>
							<?php echo $content ?>
						</div>
					</div>
				</div>
				<script>
					jQuery(function($) {
						$('.mec-marketing-notice').on('click', '.notice-dismiss', function() {
							$.post('<?php echo admin_url('admin-ajax.php'); ?>', {
								action: 'mec_dismiss_marketing_notice',
								post_id: $('.mec-marketing-notice').data('mec-post-id'),
								nonce: '<?php echo wp_create_nonce('mec_dismiss_marketing_notice'); ?>'
							});
						});
					});
				</script>
<?php
	}

	/**
	 * AJAX handler persisting the marketing-notice dismissal for the
	 * current user.
	 *
	 * @return void
	 */
	public function dismiss_marketing_notice()
	{
		check_ajax_referer('mec_dismiss_marketing_notice', 'nonce');

		if (!current_user_can('activate_plugins')) {
			wp_die(0, 403);
		}

		update_user_meta(get_current_user_id(), 'mec_dismissed_marketing_notice', absint($_POST['post_id'] ?? 0));
		wp_die(1);
	}
}
