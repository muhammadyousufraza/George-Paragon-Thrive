<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

namespace TCB\ConditionalDisplay\Fields\Referral;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

if ( ! class_exists( 'Referral_Post', false ) ) {
	require_once __DIR__ . '/class-post.php';
}

class Page extends Post {
	/**
	 * @return string
	 */
	public static function get_key() {
		return 'referral_page_id';
	}

	public static function get_label() {
		return esc_html__( 'Page', 'thrive-cb' );
	}

	public static function get_post_type() {
		return 'page';
	}

	/**
	 * @return string
	 */
	public static function get_placeholder_text() {
		return esc_html__( 'Search pages', 'thrive-cb' );
	}

	/**
	 * Determines the display order in the modal field select
	 *
	 * @return int
	 */
	public static function get_display_order() {
		return 15;
	}
}
