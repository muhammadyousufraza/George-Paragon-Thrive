<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

use W3TC\Dispatcher;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Thrive_Total_Cache
 */
class Thrive_Total_Cache implements Thrive_Plugin_Contract {

	/**
	 * Use general singleton methods
	 */
	use Thrive_Singleton;

	/**
	 * Directory plugin
	 */
	const DIR = 'w3-total-cache';

	/**
	 * Plugins Main File
	 */
	const FILE = 'w3-total-cache/w3-total-cache.php';

	/**
	 * Our default settings for total cache
	 *
	 * @return array
	 */
	public static function get_thrive_recommended_settings() {
		return [
			'minify.reject.logged'                  => true,
			'minify.js.enable'                      => true,
			'minify.js.header.embed_type'           => 'nb-defer',
			'minify.js.body.embed_type'             => 'nb-defer',
			'minify.js.combine.header'              => true,
			'minify.js.http2push'                   => true,
			'minify.reject.files.js'                => static::get_js_minify_blacklist(),
			'minify.reject.files.css'               => [
				'wp-content/plugins/woocommerce/assets/css/woocommerce-smallscreen.css',
			],
			'pgcache.enabled'                       => true,
			'minify.enabled'                        => true,
			'minify.error.notification'             => 'admin',
			'minify.html.enable'                    => true,
			'minify.html.inline.css'                => true,
			'minify.html.inline.js'                 => true,
			'minify.html.strip.crlf'                => true,
			'minify.css.combine'                    => true,
			'minify.css.http2push'                  => true,
			'minify.css.imports'                    => 'bubble',
			'browsercache.cssjs.cache.control'      => true,
			'browsercache.html.expires'             => true,
			'browsercache.html.cache.control'       => true,
			'browsercache.security.referrer.policy' => true,
			'common.track_usage'                    => true,
			'objectcache.enabled_for_wp_admin'      => false,
		];
	}

	/**
	 * @return string[]
	 */
	public static function get_js_minify_blacklist() {
		$blacklist = [
			'wp-includes/js/jquery/jquery.js',
			'wp-includes/js/jquery/jquery.min.js',
		];

		$file_path = 'wp-content/plugins/thrive-visual-editor/editor/js/dist/modules/';

		/* add the main frontend file manually, since it's not included in the module data */
		$blacklist[] = $file_path . 'general.min.js';

		/* wildcards are not accepted by total cache, so we have to add each JS module manually */
		foreach ( array_keys( \TCB\Lightspeed\JS::get_module_data() ) as $module ) {
			$blacklist[] = $file_path . $module . '.min.js';
		}

		return $blacklist;
	}

	/**
	 * Update W3 Total Cache settings
	 *
	 * @param array $data
	 * @param bool  $keep_existing - whether or not to keep the existing settings for the plugin
	 *
	 * @return bool
	 */
	public function update_settings( $data = [], $keep_existing = false ) {
		$config   = Dispatcher::config();
		$settings = empty( $data ) ? static::get_thrive_recommended_settings() : $data;

		/** Maybe reset the existing settings */
		if ( ! $keep_existing && method_exists( $config, 'set_defaults' ) ) {
			$config->set_defaults();
		}

		foreach ( $settings as $key => $value ) {
			$config->set( $key, $value );
		}

		$config->save();

		return true;
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

		$configured = true;
		foreach ( static::get_thrive_recommended_settings() as $key => $value ) {
			if ( Dispatcher::config()->get( $key ) !== $value ) {
				$configured = false;
			}
		}

		return $configured;
	}

	/**
	 * Return general information about the plugin
	 *
	 * @return array
	 */
	public function get_info() {
		return [
			'tag'        => 'total-cache',
			'slug'       => 'w3-total-cache',
			'name'       => 'W3 Total Cache',
			'file'       => static::FILE,
			'installed'  => is_dir( WP_PLUGIN_DIR . '/' . static::DIR ),
			'active'     => is_plugin_active( static::FILE ),
			'configured' => $this->is_configured(),
			'premium'    => false,
		];
	}
}

/**
 * Return Thrive_Total_Cache instance
 *
 * @return Thrive_Total_Cache
 */
function thrive_total_cache() {
	return Thrive_Total_Cache::instance();
}
