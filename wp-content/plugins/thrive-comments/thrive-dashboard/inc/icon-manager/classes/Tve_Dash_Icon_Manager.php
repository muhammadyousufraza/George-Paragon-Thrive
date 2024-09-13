<?php


class Tve_Dash_Icon_Manager {
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
		$icon_data = get_option( 'thrive_icon_pack' );
		if ( empty( $icon_data['icons'] ) ) {
			$icon_data['icons'] = array();
		}
		$icon_data['icons'] = apply_filters( 'tcb_get_extra_icons', $icon_data['icons'], $post_id ); //

		$data = array(
			'style'  => 'icomoon',
			'prefix' => 'icon',
			'icons'  => $icon_data['icons'],
		);

		return $data;
	}

	/**
	 * Enqueue Fontawesome and Material icons css styles
	 * Needed for icon modal to use fonts instead of svgs
	 */
	public static function enqueue_fontawesome_styles() {
		$license = 'use';
		if ( get_option( 'tvd_fa_kit' ) ) {
			$license = 'pro';
		}

		wp_enqueue_style( 'tvd_material', '//fonts.googleapis.com/css?family=Material+Icons+Two+Tone' );
		wp_enqueue_style( 'tvd_material_community', '//cdn.materialdesignicons.com/5.3.45/css/materialdesignicons.min.css' );
		wp_enqueue_style( 'tvd_fa', "//$license.fontawesome.com/releases/v6.4.2/css/all.css" );
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
		$css_version = isset( $icon_pack['css_version'] ) ? $icon_pack['css_version'] : TVE_DASH_VERSION;

		$_url = tve_dash_url_no_protocol( $css_url );
		wp_enqueue_style( $handle, $_url, array(), $css_version );


		return $_url . '?ver=' . $css_version;
	}
}
