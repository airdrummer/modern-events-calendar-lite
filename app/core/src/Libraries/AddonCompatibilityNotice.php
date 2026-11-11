<?php

namespace MEC\Libraries;

use MEC\Singleton;

/**
 * Class AddonCompatibilityNotice
 *
 * Renders a single, combined admin notice when active plugins declare a
 * "Requires MEC" header that the running MEC version cannot satisfy.
 *
 * Add-ons only carry a "Requires MEC" header in their main plugin file; this
 * central check reads those headers so a site with several outdated add-ons
 * sees exactly one warning instead of one notice per add-on. Headers are the
 * single source of truth — any plugin (regardless of folder-name prefix) that
 * declares the header is picked up.
 */
class AddonCompatibilityNotice extends Singleton {

	/**
	 * Transient prefix for the plugin-header scan cache.
	 *
	 * @var string
	 */
	const CACHE_PREFIX = 'mec_addon_compat_';

	/**
	 * Cache lifetime for the scan result.
	 *
	 * @var int
	 */
	const CACHE_LIFETIME = 12 * HOUR_IN_SECONDS;

	/**
	 * Init
	 *
	 * @return void
	 */
	public function init() {

		add_action( 'admin_notices', array( $this, 'display_notice' ), 11 );

		// Purge the scan cache whenever WordPress upgrades a plugin, so a
		// freshly-updated add-on is re-evaluated immediately.
		add_action( 'upgrader_process_complete', array( $this, 'purge_cache' ) );
	}

	/**
	 * Display the combined compatibility notice.
	 *
	 * @return void
	 */
	public function display_notice() {

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$incompatible = $this->incompatible_addons();

		if ( empty( $incompatible ) ) {
			return;
		}

		$update_url = current_user_can( 'update_core' )
			? admin_url( 'update-core.php' )
			: admin_url( 'plugins.php' );
		?>
		<div class="notice mec-admin-notice notice-warning">
			<p><strong><?php esc_html_e( 'Modern Events Calendar update required', 'modern-events-calendar-lite' ); ?></strong></p>
			<p>
				<?php
				printf(
					/* translators: %s: Current MEC version. */
					esc_html__( 'You are running Modern Events Calendar %s. The following active addons need a newer version and may not work correctly:', 'modern-events-calendar-lite' ),
					esc_html( MEC_VERSION )
				);
				?>
			</p>
			<ul style="list-style:disc;padding-inline-start:20px;margin-top:4px">
				<?php foreach ( $incompatible as $addon ) : ?>
					<li>
						<?php
						printf(
							/* translators: 1: Addon name, 2: Required MEC version. */
							esc_html__( '%1$s — requires MEC %2$s or later', 'modern-events-calendar-lite' ),
							esc_html( $addon['name'] ),
							esc_html( $addon['requires'] )
						);
						?>
					</li>
				<?php endforeach; ?>
			</ul>
			<p>
				<a class="button button-small" href="<?php echo esc_url( $update_url ); ?>">
					<?php esc_html_e( 'Update MEC', 'modern-events-calendar-lite' ); ?>
				</a>
			</p>
		</div>
		<?php
	}

	/**
	 * Collect active plugins whose "Requires MEC" header is newer than MEC_VERSION.
	 *
	 * @return array[] Each item: { name, requires, plugin }.
	 */
	public function incompatible_addons() {

		$plugins = wp_get_active_and_valid_plugins();

		if ( empty( $plugins ) ) {
			return array();
		}

		// Cache key covers the two inputs that change the outcome: the MEC
		// version and the set of active plugin files.
		$cache_key = self::CACHE_PREFIX . md5( MEC_VERSION . '|' . implode( '|', $plugins ) );

		$cached = get_transient( $cache_key );

		if ( is_array( $cached ) ) {
			return $cached;
		}

		$incompatible = array();

		foreach ( $plugins as $file ) {

			// Never flag MEC itself.
			if ( plugin_basename( $file ) === MEC_BASENAME ) {
				continue;
			}

			$headers = get_file_data(
				$file,
				array(
					'Name'        => 'Plugin Name',
					'RequiresMEC' => 'Requires MEC',
				),
				'plugin'
			);

			// Plugins without the header (i.e. everything except MEC add-ons).
			if ( '' === trim( (string) $headers['RequiresMEC'] ) ) {
				continue;
			}

			if ( version_compare( MEC_VERSION, $headers['RequiresMEC'], '>=' ) ) {
				continue;
			}

			$incompatible[] = array(
				'name'     => '' !== trim( (string) $headers['Name'] ) ? $headers['Name'] : plugin_basename( $file ),
				'requires' => trim( (string) $headers['RequiresMEC'] ),
				'plugin'   => plugin_basename( $file ),
			);
		}

		// Stable, alphabetical listing.
		usort(
			$incompatible,
			static function ( $a, $b ) {
				return strcasecmp( $a['name'], $b['name'] );
			}
		);

		set_transient( $cache_key, $incompatible, self::CACHE_LIFETIME );

		return $incompatible;
	}

	/**
	 * Delete all cached scan results (any plugin update invalidates them).
	 *
	 * @return void
	 */
	public function purge_cache() {

		global $wpdb;

		$like = $wpdb->esc_like( '_transient_' . self::CACHE_PREFIX ) . '%';

		$transients = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s",
				$like
			)
		);

		foreach ( $transients as $transient ) {
			delete_transient( str_replace( '_transient_', '', $transient ) );
		}
	}
}
