<?php

namespace TQB\TVA;

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-quiz-builder
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Main entry-endpoint for TQB-TVA integration
 */
class Main {
	/**
	 * @var Main
	 */
	private static $instance;

	/**
	 * Singleton implementation for Main
	 *
	 * @return Main
	 */
	public static function get_instance() {
		if ( self::$instance === null ) {
			self::$instance = new static();
		}

		return self::$instance;
	}

	/**
	 * Main constructor
	 */
	private function __construct() {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}

		$is_active = is_plugin_active( 'thrive-apprentice/thrive-apprentice.php' );

		/**
		 * If Thrive Apprentice is active, include the dependencies
		 */
		if ( $is_active ) {
			require_once __DIR__ . '/class-hooks.php';
		}
	}
}

/**
 * @return Main
 */
function tqb_tva_integration() {
	return Main::get_instance();
}

tqb_tva_integration();
