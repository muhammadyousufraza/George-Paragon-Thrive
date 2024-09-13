<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

namespace TCB\ConditionalDisplay\Entities;

use TCB\ConditionalDisplay\Entity;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Referral extends Entity {

	/**
	 * @return string
	 */
	public static function get_key() {
		return 'referral_data';
	}

	public static function get_label() {
		return esc_html__( 'Referral', 'thrive-cb' );
	}

	/**
	 * The referrer is normally taken from $_SERVER['HTTP_REFERER'].
	 * When it's not available ( this happens during ajax requests ), it is sent as a parameter from there.
	 *
	 * @param $param
	 *
	 * @return array|mixed
	 */
	public function create_object( $param ) {
		$referrer_data = [];

		if ( wp_doing_ajax() && ! empty( $_REQUEST['referrer'] ) ) {
			$url = $_REQUEST['referrer'];
		} else if ( ! empty( $_SERVER['HTTP_REFERER'] ) ) {
			$url = $_SERVER['HTTP_REFERER'];
		}

		if ( ! empty( $url ) ) {
			$referrer_data['url'] = $url;

			/* if the URL is from this site, store the post data */
			if ( strpos( $url, home_url() ) !== false ) {
				$referrer_data['post'] = get_post( url_to_postid( $url ) );
			}
		}

		return $referrer_data;
	}

	/**
	 * Determines the display order in the modal entity select
	 *
	 * @return int
	 */
	public static function get_display_order() {
		return 25;
	}
}
