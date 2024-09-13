<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Thrive_Fastest_Cache
 */
class Thrive_Fastest_Cache implements Thrive_Plugin_Contract {
	/**
	 * Use general singleton methods
	 */
	use Thrive_Singleton;

	/**
	 * Plugin directory
	 */
	const DIR = 'wp-fastest-cache';

	/**
	 * Plugin Main File
	 */
	const FILE = 'wp-fastest-cache/wpFastestCache.php';

	/**
	 * Option name where WP Fastest Cache save his settings
	 */
	const SETTINGS_OPTION = 'WpFastestCache';

	/**
	 * Our default settings that performed the best
	 */
	const THRIVE_RECOMMENDED
		= [
			'wpFastestCacheStatus'          => 'on',
			'wpFastestCachePreload_number'  => '4',
			'wpFastestCacheNewPost'         => 'on',
			'wpFastestCacheNewPost_type'    => 'all',
			'wpFastestCacheUpdatePost'      => 'on',
			'wpFastestCacheUpdatePost_type' => 'all',
			'wpFastestCacheMinifyHtml'      => 'on',
			'wpFastestCacheMinifyCss'       => 'on',
			'wpFastestCacheCombineCss'      => 'on',
			'wpFastestCacheCombineJs'       => 'on',
			'wpFastestCacheGzip'            => 'on',
			'wpFastestCacheLBC'             => 'on',
		];

	/**
	 * Update Fastest Cache settings taking into account the existing ones
	 *
	 * @param array $data
	 * @param bool  $keep_existing - whether or not to keep the existing settings for the plugin
	 *
	 * @return bool
	 */
	public function update_settings( $data = [], $keep_existing = false ) {
		$settings = empty( $data ) ? static::THRIVE_RECOMMENDED : $data;

		if ( $keep_existing ) {
			$existing_settings = get_option( static::SETTINGS_OPTION );

			if ( $existing_settings ) {
				$existing_settings = json_decode( $existing_settings, true );

				/* Change the settings with the new ones */
				if ( ! empty( $existing_settings ) ) {
					$settings = array_merge( $existing_settings, $settings );
				}
			}
		}

		update_option( static::SETTINGS_OPTION, json_encode( $settings ) );

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

		$configured        = true;
		$existing_settings = get_option( static::SETTINGS_OPTION );

		if ( $existing_settings ) {
			$existing_settings = json_decode( $existing_settings, true );

			foreach ( static::THRIVE_RECOMMENDED as $key => $value ) {
				if ( empty( $existing_settings[ $key ] ) || $existing_settings[ $key ] !== $value ) {
					$configured = false;
				}
			}
		} else {
			$configured = false;
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
			'tag'        => 'fastest-cache',
			'slug'       => 'wp-fastest-cache',
			'name'       => 'WP Fastest Cache',
			'file'       => static::FILE,
			'installed'  => is_dir( WP_PLUGIN_DIR . '/' . static::DIR ),
			'active'     => is_plugin_active( static::FILE ),
			'configured' => $this->is_configured(),
			'premium'    => false,
		];
	}
}

/**
 * Return Thrive_Fastest_Cache instance
 *
 * @return Thrive_Fastest_Cache
 */
function thrive_fastest_cache() {
	return Thrive_Fastest_Cache::instance();
}
