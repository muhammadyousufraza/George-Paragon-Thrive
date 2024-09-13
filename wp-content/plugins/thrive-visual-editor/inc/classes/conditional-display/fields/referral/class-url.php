<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

namespace TCB\ConditionalDisplay\Fields\Referral;

use TCB\ConditionalDisplay\Field;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Url extends Field {
	/**
	 * @return string
	 */
	public static function get_entity() {
		return 'referral_data';
	}

	/**
	 * @return string
	 */
	public static function get_key() {
		return 'referral_url';
	}

	public static function get_label() {
		return esc_html__( 'URL', 'thrive-cb' );
	}

	public static function get_conditions() {
		return [ 'url_comparison' ];
	}

	public function get_value( $referral_data ) {
		return empty( $referral_data['url'] ) ? '' : $referral_data['url'];
	}

	/**
	 * Determines the display order in the modal field select
	 *
	 * @return int
	 */
	public static function get_display_order() {
		return 0;
	}

	/**
	 * @return string
	 */
	public static function get_placeholder_text() {
		return esc_html__( 'Enter a URL. Use an asterisk as a wildcard', 'thrive-cb' );
	}
}
