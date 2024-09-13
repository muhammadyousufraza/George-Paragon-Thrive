<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */

namespace TVE\Dashboard\Metrics;

use function apply_filters;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Main {
	const SCRIPT_HANDLE = 'td-metrics';

	const APP_ID = 'td-metrics-wrapper';

	const NONCE = 'td-metrics-nonce';

	public static function init() {
		static::includes();
		static::hooks();
	}

	public static function get_class_name_from_filename( $filename ) {
		$name = str_replace( 'class-', '', basename( $filename, '.php' ) );

		return str_replace( '-', '_', ucwords( $name, '-' ) );
	}

	/**
	 * Function that requires all the files needed for the metrics
	 *
	 * @return void
	 */
	public static function includes() {
		foreach ( glob( __DIR__ . '/*.php' ) as $file ) {
			if ( strpos( $file, 'class-main.php' ) !== false ) {
				continue;
			}
			require_once $file;
			$class = 'TVE\Dashboard\Metrics\\' . static::get_class_name_from_filename( $file );
			if ( method_exists( $class, 'init' ) ) {
				$class::init();
			}
		}
	}


	public static function hooks() {
		add_action( 'admin_footer', [ __CLASS__, 'print_wrapper' ] );
		add_action( 'rest_api_init', [ __CLASS__, 'rest_api_init' ] );
		add_action( 'admin_enqueue_scripts', [ __CLASS__, 'enqueue_scripts' ] );
	}

	/**
	 * Print the wrapper for the Vue app
	 *
	 * @return void
	 */
	public static function print_wrapper() {
		if ( static::should_enqueue() ) {
			echo '<div id="' . static::APP_ID . '"></div>';
		}
	}

	/**
	 * Enqueue the scripts for the metrics app
	 *
	 * @return void
	 */
	public static function enqueue_scripts() {
		if ( static::should_enqueue() ) {
			tve_dash_enqueue_vue();

			tve_dash_enqueue_script( static::SCRIPT_HANDLE, TVE_DASH_URL . '/assets/dist/js/metrics.js', [], TVE_DASH_VERSION, true );

			if ( is_file( TVE_DASH_PATH . '/assets/dist/css/metrics.css' ) ) {
				tve_dash_enqueue_style( static::SCRIPT_HANDLE, TVE_DASH_URL . '/assets/dist/css/metrics.css' );
			}

			wp_localize_script( static::SCRIPT_HANDLE, 'TD_Metrics', static::localize_data() );
		}
	}

	/**
	 * Whether we should enqueue the scripts for the metrics app
	 *
	 * @return mixed|null
	 */
	public static function should_enqueue() {
		return apply_filters( 'tve_dash_metrics_should_enqueue', Deactivate::should_enqueue() || Tracking::should_enqueue() );
	}

	/**
	 * Data to be passed to the frontend
	 *
	 * @return mixed|null
	 */
	public static function localize_data() {
		$data = [
			'app_id'                  => static::APP_ID,
			'tracking_consent_notice' => Tracking::TRACKING_NOTICE_ID,
			'routes'                  => get_rest_url( get_current_blog_id(), Rest_Controller::REST_NAMESPACE ),
			'wp_rest_nonce'           => wp_create_nonce( 'wp_rest' ),
			'td_metrics_nonce'        => wp_create_nonce( static::NONCE ),
			'is_plugin_screen'        => Utils::is_plugins_screen(),
			'plugins'                 => Utils::get_products(),
			'i18n'                    => [],
			'tracking_setting_id'     => Tracking::SETTING_ID,
			'tracking_enabled'        => Tracking::get_tracking_allowed(),
		];

		return apply_filters( 'tve_metrics_localize_data', $data );
	}

	/**
	 * Initialize the REST API routes
	 *
	 * @return void
	 */
	public static function rest_api_init() {
		$rest = new Rest_Controller();
		$rest->register_routes();
	}
}
