<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-graph-editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

class TGE_Ajax {

	const AJAX_NONCE_NAME = 'tge_ajax_controller';

	public static function init() {
		self::add_ajax_events();
	}

	public static function add_ajax_events() {

		$ajax_events = array(
			'tge_admin_ajax_controller' => false,
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

	public static function tge_admin_ajax_controller() {
		if ( empty( $_REQUEST['_nonce'] ) || ! wp_verify_nonce( $_REQUEST['_nonce'], self::AJAX_NONCE_NAME ) ) {
			exit( 0 );
		}
		/**
		 * User needs to have TQB capability to use the graph editor
		 */
		if ( ! TQB_Product::has_access() ) {
			exit( 1 );
		}

		$response = TGE_Ajax_Controller::instance()->handle();

		self::clear_cache();

		wp_send_json( $response );
	}
}

TGE_Ajax::init();
