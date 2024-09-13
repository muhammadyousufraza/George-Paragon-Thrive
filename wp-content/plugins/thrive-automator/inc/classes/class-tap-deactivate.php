<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-automator
 */

namespace Thrive\Automator;
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

use function get_current_screen;

class Deactivate {
	const TRACKING_URL = 'https://service-api.thrivethemes.com/plugin-deactivate';

	public static function init() {
		add_action( 'admin_enqueue_scripts', [ __CLASS__, 'enqueue_scripts' ] );
		add_action( 'admin_footer', [ __CLASS__, 'print_wrapper' ] );
		add_action( 'tve_metrics_deactivate_products', [ __CLASS__, 'deactivate_products' ] );
	}

	public static function deactivate_products( $products ) {
		if ( isset( $products['tap'] ) ) {
			unset( $products['tap'] );
		}

		return $products;
	}

	public static function enqueue_scripts() {
		if ( static::is_plugins_screen() ) {
			Utils::enqueue_assets( 'deactivate-feedback', array_merge( Hooks::get_localize_data(), static::localize_data() ) );
		}
	}

	/**
	 * Extra data to be passed to the frontend
	 *
	 * @return \array[][]
	 */
	public static function localize_data(): array {
		return [
			'deactivate_reasons' => [
				[
					'key'   => 'plugin_not_working',
					'label' => __( "I couldn't get the plugin to work", 'thrive-automator' ),
				],
				[
					'key'   => 'temporary_deactivation',
					'label' => __( "It's a temporary deactivation", 'thrive-automator' ),
				],
				[
					'key'   => 'found_a_better_plugin',
					'label' => __( 'I found a better plugin', 'thrive-automator' ),
				],
				[
					'key'   => 'no_longer_need',
					'label' => __( 'I no longer need the plugin', 'thrive-automator' ),
				],
				[
					'key'   => 'other',
					'label' => __( 'Other', 'thrive-automator' ),
				],
			],
		];
	}

	public static function print_wrapper() {
		if ( static::is_plugins_screen() ) {
			echo '<div id="tap-deactivate-feedback"></div>';
		}
	}

	/**
	 * Send details to service-api
	 *
	 * @param $data
	 *
	 * @return void
	 */
	public static function log_data( $data ) {
		$default_data = [
			'plugin_name'    => TAP_PLUGIN_NAME,
			'plugin_version' => TAP_VERSION,
			'site_id'        => Utils::hash_256( get_site_url() ),
			'timestamp'      => time(),
			'ttw_id'         => class_exists( '\TD_TTW_Connection', false ) && \TD_TTW_Connection::get_instance()->is_connected() ? \TD_TTW_Connection::get_instance()->ttw_id : 0,
		];
		$data         = array_merge( $default_data, $data );

		$url = add_query_arg( [
			'p' => Utils::calc_thrive_hash( $data ),
		], static::TRACKING_URL );

		try {
			wp_remote_post( $url, [
				'body'      => json_encode( $data ),
				'headers'   => [
					'Content-Type' => 'application/json',
				],
				'sslverify' => false,
				'timeout'   => 30,
			] );
		} catch ( \Exception $e ) {
			// do nothing
		}
	}

	/**
	 * Whether we are on plugin screen
	 *
	 * @return bool
	 */
	public static function is_plugins_screen(): bool {
		$screen = get_current_screen();

		return $screen && in_array( $screen->id, [ 'plugins', 'plugins-network' ] );
	}

}
