<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

use SiteGround_Helper\Helper_Service;
use SiteGround_Optimizer\Options\Options;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Thrive_Sg_Optimizer
 */
class Thrive_Sg_Optimizer implements Thrive_Plugin_Contract {
	/**
	 * Use general singleton methods
	 */
	use Thrive_Singleton;

	/**
	 * Plugin directory
	 */
	const DIR = 'sg-cachepress';

	/**
	 * Plugin Main File
	 */
	const FILE = 'sg-cachepress/sg-cachepress.php';

	/**
	 * Our default settings that performed the best
	 */
	public static function get_thrive_recommended_settings() {
		$default_options = [
			'siteground_optimizer_file_caching'               => '1',
			'siteground_optimizer_autoflush_cache'            => '1',
			'siteground_optimizer_ssl_enabled'                => '0',
			'siteground_optimizer_fix_insecure_content'       => '0',
			'siteground_optimizer_enable_browser_caching'     => '0',
			'siteground_optimizer_lazyload_images'            => '1',
			'siteground_optimizer_purge_rest_cache'           => '0',
			'siteground_optimizer_backup_media'               => '0',
			'siteground_optimizer_image_compression_level'    => '0',
			'siteground_optimizer_webp_support'               => '0',
			'siteground_optimizer_optimize_html'              => '1',
			'siteground_optimizer_optimize_javascript'        => '1',
			'siteground_optimizer_optimize_javascript_async'  => '1',
			'siteground_optimizer_optimize_css'               => '1',
			'siteground_optimizer_combine_css'                => '1',
			'siteground_optimizer_combine_javascript'         => '1',
			'siteground_optimizer_combine_javascript_exclude' => static::get_combine_js_blacklist(),
		];

		/* Settings specific only for sites Hosted on SiteGround */
		if ( Helper_Service::is_siteground() ) {
			$siteground_options = [
				'siteground_optimizer_enable_cache'     => '1',
				'siteground_optimizer_enable_memcached' => '1',
			];
			$default_options    = array_merge( $default_options, $siteground_options );
		}

		return $default_options;
	}

	/**
	 * Update SG Optimizer settings taking into account the existing ones
	 *
	 * @param array $data
	 * @param bool  $keep_existing - whether or not to keep the existing settings for the plugin
	 *
	 * @return bool
	 */
	public function update_settings( $data = [], $keep_existing = false ) {
		if ( ! is_plugin_active( static::FILE ) ) {
			return false;
		}

		$settings = empty( $data ) ? static::get_thrive_recommended_settings() : $data;

		foreach ( $settings as $key => $value ) {
			Options::change_option( $key, $value );
		}

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
			if ( Options::is_enabled( $key ) != $value ) {
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
			'tag'        => 'sg-optimizer',
			'slug'       => 'sg-cachepress',
			'name'       => 'SG Optimizer',
			'file'       => static::FILE,
			'installed'  => is_dir( WP_PLUGIN_DIR . '/' . static::DIR ),
			'active'     => is_plugin_active( static::FILE ),
			'configured' => $this->is_configured(),
			'premium'    => false,
		];
	}


	/**
	 * Returns an array on scripts that will not be combined
	 *
	 * @return string[]
	 */
	public static function get_combine_js_blacklist() {
		$blacklist = [
			'jquery-migrate',
			'jquery-core',
		];

		/* add the main frontend file manually, since it's not included in the module data */
		$blacklist[] = 'tve_frontend';

		/* wildcards are not accepted by total cache, so we have to add each JS module manually */
		foreach ( array_keys( \TCB\Lightspeed\JS::get_module_data() ) as $module ) {
			$blacklist[] = 'tve_frontend_' . $module;
		}

		return $blacklist;
	}
}

/**
 * Return Thrive_Sg_Optimizer instance
 *
 * @return Thrive_Sg_Optimizer
 */
function thrive_sg_optimizer() {
	return Thrive_Sg_Optimizer::instance();
}
