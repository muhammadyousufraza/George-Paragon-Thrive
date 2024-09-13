<?php

namespace TVE\Dashboard\Automator;

use Thrive\Automator\Items\Data_Field;
use function WC;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Woo_Order_Shipping_Country
 */
class Woo_Order_Shipping_Country extends Data_Field {
	/**
	 * Field name
	 */
	public static function get_name() {
		return 'Shipping country';
	}

	/**
	 * Field description
	 */
	public static function get_description() {
		return 'Filter by shipping country';
	}

	/**
	 * Field input placeholder
	 */
	public static function get_placeholder() {
		return '';
	}

	public static function get_dummy_value() {
		return 'United States';
	}

	/**
	 * For multiple option inputs, name of the callback function called through ajax to get the options
	 */
	public static function get_options_callback() {
		$countries = array();

		foreach ( WC()->countries->get_shipping_countries() as $key => $country ) {
			if ( ! empty( $country ) ) {
				$countries[ $key ] = array(
					'label' => $country,
					'id'    => $key,
				);
			}

		}

		return $countries;
	}

	public static function get_id() {
		return 'shipping_country';
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
