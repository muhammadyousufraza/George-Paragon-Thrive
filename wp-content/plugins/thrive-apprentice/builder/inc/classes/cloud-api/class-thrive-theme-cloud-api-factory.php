<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

require_once __DIR__ . '/class-thrive-theme-cloud-api-base.php';
require_once __DIR__ . '/class-thrive-theme-cloud-api-sections.php';
require_once __DIR__ . '/class-thrive-theme-cloud-api-templates.php';
require_once __DIR__ . '/class-thrive-theme-cloud-api-skins.php';

/**
 * Class Thrive_Theme_Cloud_Api_Factory
 */
class Thrive_Theme_Cloud_Api_Factory {
	/**
	 * Build an instance for an api class
	 *
	 * @param $type
	 *
	 * @return Thrive_Theme_Cloud_Api_Sections|Thrive_Theme_Cloud_Api_Skins
	 * @throws Exception
	 */
	public static function build( $type ) {
		if ( empty( $type ) ) {
			throw new Exception( 'Please provide an element type' );
		}

		$class_name = 'Thrive_Theme_Cloud_Api_' . ucfirst( $type );

		if ( class_exists( $class_name, false ) ) {
			$instance = call_user_func( [ $class_name, 'getInstance' ] );
		} else {
			throw new Exception( 'Invalid element type' );
		}

		return $instance;
	}
}
