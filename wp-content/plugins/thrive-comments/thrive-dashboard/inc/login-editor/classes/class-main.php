<?php
/**
 * Thrive Dashboard - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */

namespace TVD\Login_Editor;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Main
 *
 * @package TVD\Login_Editor
 */
class Main {

	const OPTION = 'tvd_enable_login_design';

	const MENU_SLUG = 'tve_dash_login_editor';

	const EDIT_FLAG = 'tve-login-edit';

	public static $elements = array();

	public static function init() {
		if ( static::is_architect_active() ) {
			static::includes();
			static::register_elements();

			Hooks::actions();
			Hooks::filters();

			Post_Type::init();
		}
	}

	public static function includes() {
		require_once __DIR__ . '/class-hooks.php';
		require_once __DIR__ . '/class-post-type.php';
	}

	/**
	 * Load elements needed for the login editor
	 */
	public static function register_elements() {
		$path  = __DIR__ . '/elements';
		$items = array_diff( scandir( $path ), array( '.', '..' ) );

		static::$elements = array();

		foreach ( $items as $item ) {
			$item_path = $path . '/' . $item;

			/* if the item is a file, include it */
			if ( is_file( $item_path ) && substr( $item_path, - 3 ) === 'php' ) {
				$element = include $item_path;

				if ( ! empty( $element ) ) {
					static::$elements[ $element->tag() ] = $element;
				}
			}
		}
	}

	/**
	 * Check if architect is active
	 *
	 * @return bool
	 */
	public static function is_architect_active() {
		return defined( 'TVE_VERSION' );
	}

	/**
	 * call my by my name
	 *
	 * @return string|void
	 */
	public static function title() {
		return __( 'WordPress Login Screen Branding', 'thrive-dash' );
	}

	/**
	 * Check if we're on a edit page for the login screen
	 *
	 * @return bool
	 */
	public static function is_edit_screen() {
		$post_type = get_post_type();

		if ( empty( $post_type ) && is_admin() && isset( $_GET['post'] ) ) {
			$post_type = get_post_type( sanitize_text_field( $_GET['post'] ) );
		}

		return isset( $_GET[ Main::EDIT_FLAG ] ) || $post_type === Post_Type::NAME;
	}

	/**
	 * Return the css part which includes the main logo from the site
	 *
	 * @return string
	 */
	public static function get_main_logo_image() {
		$logo_image = '';

		if ( class_exists( 'TCB_Logo' ) ) {
			$logos = \TCB_Logo::get_logos();

			if ( ! empty( $logos ) ) {
				$active_logos = array_filter( $logos, static function ( $logo ) {
					return (int) $logo['active'] === 1 && (int) $logo['default'] === 1;
				} );

				/* get the src for each attachment ID */
				foreach ( $active_logos as $key => $logo ) {
					$active_logos[ $key ]['src'] = \TCB_Logo::get_src( $logo['id'] );
				}
			}

			$logo_image = "{$active_logos[0]['src']}?default=1&login_logo";
		}

		return $logo_image;
	}

	/**
	 * Check if we've enabled login screen design
	 *
	 * @return bool
	 */
	public static function is_login_design_enabled() {
		$option = get_option( static::OPTION, false );

		return ! empty( $option );
	}

	/**
	 * Update the content with the current logo
	 *
	 * @param string $content
	 *
	 * @return string
	 */
	public static function update_logo_in_content( $content = '' ) {
		$logo_image = static::get_main_logo_image();

		if ( ! empty( $logo_image ) ) {
			$content = preg_replace( '/url\("([^"]*)&login_logo/', 'url("' . $logo_image . '&login_logo', $content );
		}

		return $content;
	}
}
