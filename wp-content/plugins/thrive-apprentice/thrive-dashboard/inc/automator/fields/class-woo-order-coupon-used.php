<?php

namespace TVE\Dashboard\Automator;

use Thrive\Automator\Items\Data_Field;
use WP_Query;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Woo_Order_Coupon_Used
 */
class Woo_Order_Coupon_Used extends Data_Field {
	/**
	 * Field name
	 */
	public static function get_name() {
		return 'Coupon used';
	}

	/**
	 * Field description
	 */
	public static function get_description() {
		return 'Filter by coupon used';
	}

	/**
	 * Field input placeholder
	 */
	public static function get_placeholder() {
		return '';
	}

	public static function get_dummy_value() {
		return 'XMAS2021';
	}

	/**
	 * For multiple option inputs, name of the callback function called through ajax to get the options
	 */
	public static function get_options_callback() {
		$coupons = array();
		// set base query arguments
		$query_args = array(
			'post_type'   => 'shop_coupon',
			'post_status' => 'publish',
		);

		$query = new WP_Query( $query_args );

		foreach ( $query->posts as $post ) {
			if ( ! empty( $post ) ) {
				$code = \wc_format_coupon_code( $post->post_title );
				$coupons[ $code ] = array(
					'label' => $post->post_title,
					'id'    => $code,
				);
			}

		}

		return $coupons;
	}

	public static function get_field_value_type() {
		return static::TYPE_STRING;
	}

	public static function get_id() {
		return 'coupon_used';
	}

	public static function get_supported_filters() {
		return array( 'autocomplete' );
	}

	public static function is_ajax_field() {
		return true;
	}

	public static function get_validators() {
		return array( 'required' );
	}
}
