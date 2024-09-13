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
 * Class Thrive_Optimole_Wp
 */
class Thrive_Optimole_Wp {

	/**
	 * These are all the recommended settings that are saved each time users will click the "auto-configure optimole"
	 */
	const THRIVE_RECOMMENDED = [
		'image_replacer'       => 'enabled',
		'lazyload'             => 'enabled',
		'lazyload_placeholder' => 'enabled',
		'network_optimization' => 'disabled',
		'img_to_video'         => 'disabled',
		'autoquality'          => 'disabled',
		'quality'              => 90,
		'resize_smart'         => 'enabled',
		'max_width'            => 1000,
		'max_height'           => 1000,
		'bg_replacer'          => 'enabled',
		'watchers'             => '.tve-content-box-background,.tve-page-section-out,.thrv_text_element,.section-background',
		'filters'              => [
			'lazyload' => [
				'extension' => [],
				'filename'  => [ 'thrive-quiz-builder' => true ],
				'page_url'  => [],
				'class'     => [],
			],
			'optimize' => [
				'extension' => [],
				'filename'  => [ 'thrive-quiz-builder' => true ],
				'page_url'  => [],
				'class'     => [],
			],
		],
	];

	use Thrive_Singleton;

	/**
	 * Whether or not the plugin exists and is loaded
	 *
	 * @return bool
	 */
	public function exists() {
		return class_exists( 'Optml_Settings', false );
	}

	/**
	 * Set Optimole recommended settings
	 *
	 * @return bool
	 */
	public function update_settings() {
		if ( ! $this->exists() ) {
			return false;
		}

		$rest = new Optml_Rest();

		$request = new WP_REST_Request();
		$request->set_param( 'settings', static::THRIVE_RECOMMENDED );
		if ( method_exists( $rest, 'update_option' ) ) {
			$rest->update_option( $request );

			/**
			 * Filters array needs to be treated separately
			 */
			$settings = new Optml_Settings();
			$settings->update( 'filters', static::THRIVE_RECOMMENDED['filters'] );
		}

		return true;
	}

	/**
	 * Whether or not the Optimole plugin is registered (this happens after user enters a valid registration key)
	 *
	 * @return bool
	 */
	public function is_registered() {
		if ( ! $this->exists() ) {
			return false;
		}

		/* first, check to see if optimole is configured / connected */
		$settings   = new Optml_Settings();
		$configured = $settings->get( 'service_data' );

		return ! empty( $configured );
	}

	/**
	 * Whether or not the plugin is configured with Thrive's recommended settings
	 *
	 * @return bool
	 */
	public function is_configured() {
		if ( ! $this->exists() ) {
			return false;
		}

		$all = $this->get_settings();
		foreach ( static::THRIVE_RECOMMENDED as $key => $value ) {
			if ( isset( $all[ $key ] ) ) {
				if ( is_array( $all[ $key ] ) && is_array( $value ) ) {
					if ( strcmp( json_encode( $all[ $key ] ), json_encode( $value ) ) ) {
						return false;
					}
				} else if ( $all[ $key ] !== $value ) {
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Get settings saved for optimole
	 *
	 * @return array
	 */
	public function get_settings() {
		$settings = new Optml_Settings();
		if ( method_exists( $settings, 'get_site_settings' ) ) {
			return $settings->get_site_settings();
		}

		return [];
	}
}

/**
 *
 * @return Thrive_Optimole_Wp
 */
function thrive_optimole_wp() {
	return Thrive_Optimole_Wp::instance();
}
