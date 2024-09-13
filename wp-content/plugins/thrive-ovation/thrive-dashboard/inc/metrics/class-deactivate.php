<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */

namespace TVE\Dashboard\Metrics;

use function add_filter;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Deactivate {
	const TRACKING_URL = 'https://service-api.thrivethemes.com/plugin-deactivate';

	public static function init() {
		static::hooks();
	}

	public static function hooks() {
		add_filter( 'tve_metrics_localize_data', [ __CLASS__, 'localize_data' ] );
	}

	/**
	 * Log the deactivation reason
	 *
	 * @param array $data
	 *
	 * @return void
	 */
	public static function log_data( $data ) {
		$default_data = [
			'site_id'   => Utils::hash_256( get_site_url() ),
			'timestamp' => time(),
			'ttw_id'    => class_exists( '\TD_TTW_Connection', false ) && \TD_TTW_Connection::get_instance()->is_connected() ? \TD_TTW_Connection::get_instance()->ttw_id : 0,
		];
		$data         = array_merge( $default_data, $data );

		Utils::send_request( static::TRACKING_URL, $data );
	}

	/**
	 * Extra data to be passed to the frontend
	 *
	 * @param array $data
	 *
	 * @return array
	 */
	public static function localize_data( $data ) {
		if ( Utils::is_plugins_screen() ) {
			$data['deactivate_plugins'] = static::get_products();
			$data['deactivate_reasons'] = static::get_reasons();
			$data['i18n']               = array_merge( $data['i18n'], static::get_i18n() );
		}

		return $data;
	}

	/**
	 * Extra i18n strings for the deactivation popup
	 *
	 * @return array
	 */
	public static function get_i18n() {
		return [
			'deactivate_title'  => __( 'Please share why you are deactivating', 'thrive-dash' ),
			'submit_deactivate' => __( 'Submit & Deactivate', 'thrive-dash' ),
			'skip_deactivate'   => __( 'Skip & Deactivate', 'thrive-dash' ),
			'deactivate_reason' => __( 'Reason for deactivation', 'thrive-dash' ),
		];
	}

	/**
	 * Should enqueue the scripts for the deactivation popup only on the plugins page
	 *
	 * @return bool
	 */
	public static function should_enqueue() {
		return Utils::is_plugins_screen();
	}

	/**
	 * Array of reasons for deactivation
	 *
	 * @return array[]
	 */
	public static function get_reasons() {
		return [
			[
				'key'   => 'plugin_not_working',
				'label' => __( "I couldn't get the plugin to work", 'thrive-dash' ),
			],
			[
				'key'   => 'temporary_deactivation',
				'label' => __( "It's a temporary deactivation", 'thrive-dash' ),
			],
			[
				'key'   => 'requesting_refund',
				'label' => __( "I'm requesting a refund", 'thrive-dash' ),
			],
			[
				'key'   => 'cancel_subscription',
				'label' => __( "I'm canceling my subscription", 'thrive-dash' ),
			],
			[
				'key'   => 'found_a_better_plugin',
				'label' => __( 'I found a better plugin', 'thrive-dash' ),
			],
			[
				'key'   => 'no_longer_need',
				'label' => __( 'I no longer need the plugin', 'thrive-dash' ),
			],
			[
				'key'   => 'other',
				'label' => __( 'Other', 'thrive-dash' ),
			],
		];
	}

	/**
	 * List of plugins that should be tracked on deactivation
	 *
	 * @return mixed|null
	 */
	public static function get_products() {
		$products = Utils::get_products();
		foreach ( $products as $key => $data ) {
			if ( $data['type'] === 'theme' ) {
				unset( $products[ $key ] );
			}
		}

		return apply_filters( 'tve_metrics_deactivate_products', $products );
	}
}
