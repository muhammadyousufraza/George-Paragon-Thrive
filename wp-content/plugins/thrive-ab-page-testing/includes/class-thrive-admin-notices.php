<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-ab-page-testing
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

class Thrive_Admin_Notices {

	private static $_notices = array();

	public static function init() {

		add_action( 'admin_print_styles', array( __CLASS__, 'add_notices' ) );
	}

	public static function get_notices() {

		self::$_notices = array_unique( array_merge( self::$_notices, get_option( 'thrive_ab_page_testing_notifications', array() ) ) );

		return self::$_notices;
	}

	public static function remove_notices() {

		delete_option( 'thrive_ab_page_testing_notifications' );
		self::$_notices = array();
	}

	public static function push_notice( $notice ) {

		self::$_notices = array_unique( array_merge( self::get_notices(), array( $notice ) ) );
		self::save_notifications();
	}

	protected static function save_notifications() {

		update_option( 'thrive_ab_page_testing_notifications', self::$_notices );
	}

	/**
	 * Call this function for a hook
	 */
	public static function push_notice_active() {

		self::push_notice( 'active' );
	}

	public static function add_notices() {

		$notices = self::get_notices();

		if ( ! empty( $notices ) ) {

			foreach ( $notices as $notice ) {

				$callback = $notice . '_notice';

				if ( method_exists( 'Thrive_Admin_Notices', $callback ) ) {
					add_action( 'admin_notices', array( __CLASS__, $callback ) );
				}
			}
		}
	}

	/**
	 * Display an error notice and deactivate the plugin
	 */
	public static function active_notice() {

		include dirname( __FILE__ ) . '/views/admin/notices/html-active.php';

		thrive_ab()->deactivate();
	}

	/**
	 * Display an error notice and deactivate the plugin
	 */
	public static function min_version_notice() {

		include dirname( __FILE__ ) . '/views/admin/notices/html-version.php';

		thrive_ab()->deactivate();
	}
}

Thrive_Admin_Notices::init();
