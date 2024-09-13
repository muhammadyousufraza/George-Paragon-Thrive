<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */

namespace TVE\Dashboard\Metrics;

use WP_Theme;
use function is_plugin_active;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Tracking {

	const SETTING_ID = 'tve_metrics_consent';

	const CONSENT_RESET = 'tve-metrics-consent-reset';

	const TRACKING_OPTION = 'tve-tracking-allowed';

	const TRACKING_NOTICE_ID = 'tve-tracking-notice';

	const ALLOWED_VIEWS = [ 'admin_page_tve_dash_general_settings_section' ];
	/**
	 * List of our plugins that should be checked for tracking
	 * Do it like this because on some hooks plugins files might not be loaded yet
	 */
	const PLUGINS = [
		'thrive-apprentice/thrive-apprentice.php',
		'thrive-visual-editor/thrive-visual-editor.php',
		'thrive-comments/thrive-comments.php',
		'thrive-leads/thrive-leads.php',
		'thrive-ultimatum/thrive-ultimatum.php',
		'thrive-quiz-builder/thrive-quiz-builder.php',
		'thrive-ovation/thrive-ovation.php',
		'thrive-optimize/thrive-optimize.php',
		'ab-page-testing/ab-page-testing.php',
	];

	const THEME = 'thrive-theme';

	public static function init() {
		static::hooks();
	}

	public static function hooks() {
		add_action( 'admin_notices', [ __CLASS__, 'admin_notices' ], 1 );
		add_action( 'activated_plugin', [ __CLASS__, 'after_plugin_activate' ], 10, 2 );
		add_action( 'switch_theme', [ __CLASS__, 'after_theme_activate' ], 10, 3 );
		add_filter( 'tve_dash_general_settings_filter', [ __CLASS__, 'add_settings' ] );
	}

	/**
	 * Add a settings in the dashboard settings page
	 *
	 * @param $settings
	 *
	 * @return mixed
	 */
	public static function add_settings( $settings ) {
		$settings[] = [
			'name'        => static::SETTING_ID,
			'id'          => static::SETTING_ID,
			'value'       => static::get_tracking_allowed(),
			'type'        => 'checkbox',
			'description' => __( 'Help us improve Thrive Themes products by sharing anonymized data with us.', 'thrive-dash' ),
			'multiple'    => false,
			'link'        => '//help.thrivethemes.com/en/articles/6796332-thrive-themes-data-collection',

		];

		return $settings;
	}

	/**
	 * Display the admin notice if needed
	 *
	 * @return void
	 *
	 */
	public static function admin_notices() {
		if ( static::should_display_ribbon() ) {
			echo sprintf( '<div id="%s" class="notice notice-success tve-metrics-consent-notice" style="display: none"></div>', static::TRACKING_NOTICE_ID );
		}
	}

	/**
	 * Check if the tracking is allowed
	 *
	 * @return bool
	 */
	public static function get_tracking_allowed() {
		return (bool) get_option( static::TRACKING_OPTION, 0 );
	}

	/**
	 * Whether we should display the ribbon
	 * We also need at least one license
	 *
	 * @return bool
	 */
	public static function should_display_ribbon() {
		$products = array_filter( Utils::get_products(), static function ( $product ) {
			return $product['is_activated'];
		} );

		return get_option( static::TRACKING_OPTION, '' ) === '' && count( $products ) > 0;
	}

	/**
	 * Allow enqueuing of the tracking script only on General settings page or if the user hasn't made a choice yet
	 *
	 * @return bool
	 */
	public static function should_enqueue() {
		$screen = \tve_get_current_screen_key();

		return ( $screen && in_array( $screen, static::ALLOWED_VIEWS ) ) || static::should_display_ribbon();
	}

	/**
	 * After plugin activation, clear the tracking consent option if needed
	 *
	 * @param $plugin
	 * @param $network_activation
	 *
	 * @return void
	 */
	public static function after_plugin_activate( $plugin, $network_activation ) {
		if ( in_array( $plugin, static::PLUGINS, true ) && ! static::get_tracking_allowed() ) {
			static::clear_tracking_consent();
		}
	}

	/**
	 * After theme activation, clear the tracking consent if needed
	 *
	 * @param          $theme_name
	 * @param WP_Theme $theme_object
	 * @param          $old_theme_name
	 *
	 * @return void
	 */
	public static function after_theme_activate( $theme_name, WP_Theme $theme_object, $old_theme_name ) {
		if ( ! empty( $theme_object ) && ! static::get_tracking_allowed() && $theme_object->get_stylesheet() === static::THEME ) {
			static::clear_tracking_consent();
		}
	}

	/**
	 * Get number of activated products
	 */
	public static function activated_products() {
		if ( ! function_exists( '\is_plugin_active' ) ) {
			include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}
		$active_products = array_filter( static::PLUGINS, static function ( $product ) {
			return is_plugin_active( $product );
		} );
		$active_count    = count( $active_products );
		if ( \tve_dash_is_ttb_active() ) {
			$active_count ++;
		}

		return $active_count;
	}

	/**
	 * Delete the tracking consent option if there is more than one product active
	 */
	public static function clear_tracking_consent() {
		if ( static::activated_products() === 1 && ! get_option( static::CONSENT_RESET, false ) ) {
			delete_option( static::TRACKING_OPTION );
			update_option( static::CONSENT_RESET, true, 'no' );
		}
	}

	/**
	 * Save user tracking preference
	 *
	 * @param $value
	 *
	 * @return void
	 */
	public static function set_tracking_allowed( $value ) {
		update_option( static::TRACKING_OPTION, $value, 'no' );
		/**
		 * Action triggered when the user changes the tracking preference
		 */
		do_action( 'tve_tracking_consent_changed', $value );
	}
}
