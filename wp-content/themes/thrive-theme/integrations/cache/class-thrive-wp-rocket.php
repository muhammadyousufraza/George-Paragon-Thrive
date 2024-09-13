<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

use WP_Rocket\Admin\Options;
use WP_Rocket\Admin\Options_Data;

/**
 * Class Thrive_Wp_Rocket
 */
class Thrive_Wp_Rocket implements Thrive_Plugin_Contract {
	/**
	 * Use general singleton methods
	 */
	use Thrive_Singleton;

	/**
	 * Directory plugin
	 */
	const DIR = 'wp-rocket';

	/**
	 * Plugins Main File
	 */
	const FILE = 'wp-rocket/wp-rocket.php';

	private static $options_api_instance = null;

	/**
	 * Our default settings for WP Rocket
	 */
	public static function get_thrive_recommended_settings() {
		return [
			'cache_logged_user'      => 0,
			'minify_css'             => 1,
			'minify_concatenate_css' => 1,
			'async_css'              => 1,
			'minify_js'              => 1,
			'minify_concatenate_js'  => 0,
			'defer_all_js'           => 1,
			'exclude_css'            => [
				'wp-content/plugins/woocommerce/assets/css/woocommerce-smallscreen.css',
			],
			'exclude_js'             => [
				'wp-includes/js/jquery/jquery.js',
				'wp-includes/js/jquery/jquery.min.js',
				'wp-content/plugins/thrive-visual-editor/editor/js/dist/modules/(.*).js',
				'wp-content/plugins/thrive-visual-editor/editor/js/dist/carousel-libs.min.js',
			],
			'exclude_defer_js'       => [
				'wp-includes/js/jquery/jquery.js',
				'wp-includes/js/jquery/jquery.min.js',
				'wp-content/plugins/thrive-visual-editor/editor/js/dist/modules/(.*).js',
				'wp-content/plugins/thrive-visual-editor/editor/js/dist/carousel-libs.min.js',
			],
			'delay_js'               => 1,
			'delay_js_exclusions'    => static::get_delayed_js_exclusions(),
		];
	}

	const DELAYED_JS_EXCLUSIONS = [
			'/wp-content/plugins/thrive-visual-editor/editor/js/dist/modules/(.*).js',
			'/wp-content/plugins/thrive-visual-editor/editor/js/dist/carousel-libs.min.js',
			'TVE_Event_Manager_Registered_Callbacks',
			'ThriveGlobal',
			'TCB_Front',
			'TL_Front',
			'TVE_Ult',
			'thrive-',
			'thrive_',
			'tve_',
			'tve-',
		];

	/**
	 * Remove the previously added Thrive exclusions since they are no longer needed in the new WP Rocket version
	 * @return mixed
	 */
	public static function get_delayed_js_exclusions() {
		$options = static::get_options_data()->get( 'delay_js_exclusions', [] );

		if ( ! empty( $options ) && is_array( $options ) ) {
			foreach ( $options as $index => $option ) {
				if ( in_array( $option, static::DELAYED_JS_EXCLUSIONS, true ) ) {
					unset( $options[ $index ] );
				}
			}

			$options = array_values( $options );
		}

		return $options;
	}

	/**
	 * Check if the plugin has the configuration suggested by thrive
	 *
	 * @return bool
	 */
	public function is_configured() {

		if ( ! is_plugin_active( static::FILE ) ) {
			return false;
		}

		$options    = static::get_options_data();
		$configured = true;

		foreach ( static::get_thrive_recommended_settings() as $key => $value ) {
			if ( $options->get( $key ) !== $value ) {
				$configured = false;
			}
		}

		return $configured;
	}

	/**
	 * @return Options_Data
	 */
	public static function get_options_data() {
		$options_api = static::get_options_api_instance();

		return new Options_Data( $options_api->get( 'settings', [] ) );
	}

	public static function get_options_api_instance() {
		if ( empty( static::$options_api_instance ) ) {
			static::$options_api_instance = new Options( 'wp_rocket_' );
		}

		return static::$options_api_instance;
	}

	/**
	 * Update WP Rocket settings
	 *
	 * @param array $data
	 * @param bool  $keep_existing - whether or not to keep the existing settings for the plugin
	 *
	 * @return bool
	 */
	public function update_settings( $data = [], $keep_existing = false ) {
		$options_data = static::get_options_data();

		$options_data->set_values( static::get_thrive_recommended_settings() );
		static::get_options_api_instance()->set( 'settings', $options_data->get_options() );

		return true;
	}

	/**
	 * Return general information about the plugin
	 *
	 * @return array
	 */
	public function get_info() {
		return [
			'tag'        => 'wp-rocket',
			'slug'       => 'wp-rocket',
			'name'       => 'WP Rocket',
			'file'       => static::FILE,
			'installed'  => is_dir( WP_PLUGIN_DIR . '/' . static::DIR ),
			'active'     => is_plugin_active( static::FILE ),
			'configured' => $this->is_configured(),
			'premium'    => true,
			'redirect'   => 'https://help.thrivethemes.com/en/articles/4741848-setting-up-and-using-wp-rocket-with-thrive-theme-builder',
		];
	}
}

/**
 * Return Thrive_Total_Cache instance
 *
 * @return Thrive_Wp_Rocket
 */
function thrive_wp_rocket() {
	return Thrive_Wp_Rocket::instance();
}
