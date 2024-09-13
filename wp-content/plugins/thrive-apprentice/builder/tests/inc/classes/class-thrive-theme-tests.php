<?php

/**
 * Class Thrive_Theme_Tests
 */
class Thrive_Theme_Tests {

	const FLAG = 'jstest';

	public static function init() {
		if ( isset( $_GET[ static::FLAG ] ) ) {
			add_action( 'tcb_editor_iframe_before', [ __CLASS__, 'tcb_editor_iframe_before' ] );

			add_action( 'tcb_main_frame_enqueue', [ __CLASS__, 'tcb_main_frame_enqueue' ] );

			add_filter( 'tcb_editor_edit_link_query_args', [ __CLASS__, 'tcb_editor_edit_link_query_args' ] );

			/* no need for this kind of negativity in our lives */
			add_filter( 'doing_it_wrong_trigger_error', '__return_false' );

			/* the earliest hook we could find so we can login an admin */
			add_filter( 'secure_auth_redirect', [ __CLASS__, 'admin_login' ] );
		}
	}

	/**
	 * We're not doing anything related to the filter, we just use it because it's before auth
	 * Get the first admin from db and login with it
	 *
	 * @param $secure
	 *
	 * @return mixed
	 */
	public static function admin_login( $secure ) {
		if ( ! is_user_logged_in() ) {

			defined( 'WP_ADMIN' ) || define( 'WP_ADMIN', true );

			/* set logged user */
			$admin = get_users( [
				'role'  => 'Administrator',
				'order' => 'login',
			] )[0];

			add_action( 'set_logged_in_cookie', function ( $cookie_value ) {
				$_COOKIE[ LOGGED_IN_COOKIE ] = $cookie_value;

			} );

			add_action( 'set_auth_cookie', function ( $cookie_value ) {
				$_COOKIE[ AUTH_COOKIE ]        = $cookie_value;
				$_COOKIE[ SECURE_AUTH_COOKIE ] = $cookie_value;
			} );

			wp_set_current_user( $admin->ID, $admin->user_login );

			wp_set_auth_cookie( $admin->ID );

			do_action( 'wp_login', $admin->user_login, $admin );
		}

		return $secure;
	}

	/**
	 * Inserts container into main frame for qunit to display test results
	 */
	public static function tcb_editor_iframe_before() {
		echo '<div id="qunit"></div><div id="qunit-fixture"></div>';
	}

	/**
	 * Enqueue qunit scripts and test sources in architect pages that don't have
	 */
	public static function tcb_main_frame_enqueue() {
		wp_enqueue_script( 'q-unit-js', 'https://code.jquery.com/qunit/qunit-2.11.3.js' );
		wp_enqueue_style( 'q-unit-css', 'https://code.jquery.com/qunit/qunit-2.11.3.css' );

		wp_enqueue_script( 'thrive-theme-tests', THEME_URL . '/tests/js/all.js', [ 'jquery', 'underscore' ], time() );
		wp_enqueue_style( 'test-main-frame', THEME_URL . '/tests/inc/assets/style.css' );
	}

	/**
	 * @param $params
	 *
	 * @return string
	 */
	public static function tcb_editor_edit_link_query_args( $params ) {

		if ( isset( $_GET[ static::FLAG ] ) ) {
			$params [ static::FLAG ] = $_GET[ static::FLAG ];
		}

		return $params;
	}
}
