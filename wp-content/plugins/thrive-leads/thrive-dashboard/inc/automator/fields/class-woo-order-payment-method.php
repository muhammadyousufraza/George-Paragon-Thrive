<?php

namespace TVE\Dashboard\Automator;

use Thrive\Automator\Items\Data_Field;
use function WC;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Woo_Order_Payment_Method
 */
class Woo_Order_Payment_Method extends Data_Field {
	/**
	 * Field name
	 */
	public static function get_name() {
		return 'Payment method';
	}

	/**
	 * Field description
	 */
	public static function get_description() {
		return 'Filter by payment method';
	}

	/**
	 * Field input placeholder
	 */
	public static function get_placeholder() {
		return '';
	}

	public static function get_dummy_value() {
		return 'PayPal';
	}

	/**
	 * For multiple option inputs, name of the callback function called through ajax to get the options
	 */
	public static function get_options_callback() {
		$methods = array();

		foreach ( WC()->payment_gateways->get_available_payment_gateways() as $method ) {
			if ( ! empty( $method ) ) {
				$methods[ $method->id ] = array(
					'label' => $method->get_method_title(),
					'id'    => $method->id,
				);
			}

		}

		return $methods;
	}

	public static function get_id() {
		return 'payment_method';
	}

	public static function get_supported_filters() {
		return array( 'checkbox' );
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
