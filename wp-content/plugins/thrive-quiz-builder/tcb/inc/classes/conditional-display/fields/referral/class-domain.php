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

class Domain extends Field {
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
		return 'referral_domain';
	}

	public static function get_label() {
		return esc_html__( 'Domain', 'thrive-cb' );
	}

	public static function get_conditions() {
		return [ 'string_contains' ];
	}

	public function get_value( $referral_data ) {
		return empty( $referral_data['url'] ) ? '' : parse_url( $referral_data['url'], PHP_URL_HOST );
	}

	/**
	 * Determines the display order in the modal field select
	 *
	 * @return int
	 */
	public static function get_display_order() {
		return 5;
	}

	/**
	 * @return string
	 */
	public static function get_placeholder_text() {
		return esc_html__( 'Enter a domain URL', 'thrive-cb' );
	}
}
