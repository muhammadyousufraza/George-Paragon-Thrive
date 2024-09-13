<?php

namespace TVE\Dashboard\Automator;

use Thrive\Automator\Items\Data_Field;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Woo_Order_Currency
 */
class Woo_Order_Currency extends Data_Field {
	/**
	 * Field name
	 */
	public static function get_name() {
		return 'Order Currency';
	}

	/**
	 * Field description
	 */
	public static function get_description() {
		return 'Filter by order currency';
	}

	/**
	 * Field input placeholder
	 */
	public static function get_placeholder() {
		return '';
	}

	public static function get_dummy_value() {
		return 'USD';
	}

	/**
	 * For multiple option inputs, name of the callback function called through ajax to get the options
	 */
	public static function get_options_callback() {
		$currencies = array();

		foreach ( get_woocommerce_currencies() as $code => $name ) {
			if ( ! empty( $name ) ) {
				$currencies[ $code ] = array(
					'label' => $name,
					'id'    => $code,
				);
			}

		}

		return $currencies;
	}

	public static function get_id() {
		return 'order_currency';
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

	public static function get_field_value_type() {
		return static::TYPE_STRING;
	}
}
