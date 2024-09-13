<?php
/**
 * Thrive Dashboard - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */

namespace TVD\Coming_Soon;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Main
 *
 * @package TVD\Coming_Soon
 */
class Main {

	const OPTION = 'tvd_coming_soon_page_id';

	const MENU_SLUG = 'tve_dash_coming_soon';

	public static function init() {
		static::includes();

		Hooks::actions();
		Hooks::filters();
	}

	public static function includes() {
		require_once __DIR__ . '/class-hooks.php';
	}

	/**
	 * call my by my name
	 *
	 * @return string|void
	 */
	public static function title() {
		return __( 'Coming Soon Mode', 'thrive-dash' );
	}

	/**
	 * Check if we've enabled coming soon
	 *
	 * @return bool
	 */
	public static function is_coming_soon_enabled() {
		$option = get_option( static::OPTION, false );

		return ! empty( $option );
	}

	/**
	 * Get ID of the Coming Soon Page
	 *
	 * @return false|mixed|void
	 */
	public static function get_page_id() {
		return get_option( static::OPTION, 0 );
	}

	/**
	 * Get name of the Coming Soon Page
	 *
	 * @return string
	 */
	public static function get_page_name() {
		return get_the_title( static::get_page_id() );
	}

	/**
	 * Get edit url of the Coming Soon Page
	 *
	 * @return string
	 */
	public static function get_edit_url() {
		return tcb_get_editor_url( static::get_page_id() );
	}

	/**
	 * Get preview url of the Coming Soon Page
	 *
	 * @return string
	 */
	public static function get_preview_url() {
		return get_permalink( static::get_page_id() );
	}

	/**
	 * Check if the Coming Soon page has content
	 *
	 * @return bool
	 */
	public static function is_empty_page() {
		$page_id = static::get_page_id();

		return empty( $page_id ) || empty( tve_get_post_meta( $page_id, 'tve_updated_post' ) );
	}

	/**
	 * Check if we're on a edit page for the coming soon
	 *
	 * @return bool
	 */
	public static function is_edit_screen() {
		$post_id = get_the_ID();

		if ( empty( $post_id ) && isset( $_GET['post'] ) && is_admin() ) {
			$post_id = $_GET['post'];
		}

		return is_numeric( $post_id ) && (int) $post_id === (int) Main::get_page_id();
	}
}
