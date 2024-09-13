<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package TCB2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

/**
 * Class TCB_Icon_Manager
 *
 * Holds the whole logic of icons
 * All types of icons
 */
class TCB_Icon_Manager {

	/**
	 * Main entry point and executed at inclusion
	 */
	static public function init() {
		self::init_hooks();
	}

	private static function init_hooks() {

		/**
		 * enqueue icon pack for editor or
		 * if the post has in post meta flag set
		 */
		if ( is_editor_page() && tve_is_post_type_editable( get_post_type( get_the_ID() ) ) || tve_get_post_meta( get_the_ID(), 'thrive_icon_pack' ) ) {
			add_action( 'wp_enqueue_scripts', [ 'TCB_Icon_Manager', 'enqueue_icon_pack' ] );
		}
	}

	/**
	 * Get retina icons from dashboard and also if the imported page had retina icons too
	 *
	 * @param null $post_id - edited page id
	 *
	 * @return array
	 */
	public static function get_custom_icons( $post_id = null ) {
		if ( empty( $post_id ) ) {
			$post_id = get_the_ID();
		}
		$icon_data = get_option( 'thrive_icon_pack', [] );
		if ( empty( $icon_data['icons'] ) ) {
			$icon_data['icons'] = [];
		}
		$icon_data['icons'] = apply_filters( 'tcb_get_extra_icons', $icon_data['icons'], $post_id ); //

		return [
			'style'  => 'icomoon',
			'prefix' => 'icon',
			'icons'  => $icon_data['icons'],
		];
	}

	/**
	 * Enqueue Fontawesome and Material icons css styles
	 * Needed for icon modal to use fonts instead of svgs
	 */
	public static function enqueue_fontawesome_styles() {
		$license = 'use';
		if ( get_option( 'tve_fa_kit', '' ) ) {
			$license = 'pro';
		}

		tve_enqueue_style( 'tve_material', '//fonts.googleapis.com/css?family=Material+Icons+Two+Tone' );
		tve_enqueue_style( 'tve_material_community', '//cdn.materialdesignicons.com/5.3.45/css/materialdesignicons.min.css' );
		tve_enqueue_style( 'tve_fa', "//$license.fontawesome.com/releases/v6.4.2/css/all.css" );
	}

	/**
	 * Enqueue the CSS for the icon pack used by the user
	 *
	 * @return false|string url
	 */
	public static function enqueue_icon_pack() {

		$handle = 'thrive_icon_pack';

		if ( wp_style_is( $handle, 'enqueued' ) ) {
			return false;
		}

		$icon_pack = get_option( 'thrive_icon_pack' );
		if ( empty( $icon_pack['css'] ) ) {
			return false;
		}

		$css_url     = $icon_pack['css'];
		$css_version = isset( $icon_pack['css_version'] ) ? $icon_pack['css_version'] : TVE_VERSION;

		$_url = tve_url_no_protocol( $css_url );
		wp_enqueue_style( $handle, $_url, [], $css_version );


		return $_url . '?ver=' . $css_version;
	}
}

add_action( 'wp', [ 'TCB_Icon_Manager', 'init' ] );
