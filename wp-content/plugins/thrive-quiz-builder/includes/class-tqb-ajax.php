<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-quiz-builder
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

class TQB_Ajax {

	const AJAX_NONCE_NAME = 'tie_ajax_controller';

	public static function init() {
		self::add_ajax_events();
	}

	public static function add_ajax_events() {

		$ajax_events = array(
			'tqb_admin_ajax_controller'    => false,
			'tqb_frontend_ajax_controller' => true,
		);

		foreach ( $ajax_events as $action => $nopriv ) {
			add_action( 'wp_ajax_' . $action, array( __CLASS__, $action ) );

			if ( $nopriv ) {
				add_action( 'wp_ajax_nopriv_' . $action, array( __CLASS__, $action ) );
			}
		}
	}

	/**
	 * Clear cache of some cache plugins to make sure that changes are visible
	 */
	public static function clear_cache() {
		/**
		 * Clear WP_Rocket on ajax otherwise progressbar and scroll settings will no take effect
		 */
		if ( function_exists( 'rocket_clean_domain' ) ) {
			rocket_clean_domain();
		}
	}

	public static function tqb_admin_ajax_controller() {
		require_once __DIR__ . '/admin/classes/class-tqb-admin-ajax-controller.php';

		$response = TQB_Admin_Ajax_Controller::instance()->handle();
		self::clear_cache();

		wp_send_json( $response );
	}

	public static function tqb_frontend_ajax_controller() {
		require_once __DIR__ . '/class-tqb-frontend-ajax-controller.php';

		$response = TQB_Frontend_Ajax_Controller::instance()->handle();
		self::clear_cache();

		wp_send_json( $response );
	}
}

TQB_Ajax::init();
