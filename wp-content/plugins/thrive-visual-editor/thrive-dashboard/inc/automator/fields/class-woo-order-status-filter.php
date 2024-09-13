<?php

namespace TVE\Dashboard\Automator;

use Thrive\Automator\Items\Data_Field;
use function wc_get_order_statuses;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Woo_Order_Status_Field
 */
class Woo_Order_Status_Filter extends Data_Field {
	/**
	 * Field name
	 */
	public static function get_name() {
		return 'Order status';
	}

	/**
	 * Field description
	 */
	public static function get_description() {
		return 'Filter by the status of the order';
	}

	/**
	 * Field input placeholder
	 */
	public static function get_placeholder() {
		return '';
	}

	public static function get_dummy_value() {
		return 'COMPLETED';
	}

	/**
	 * For multiple option inputs, name of the callback function called through ajax to get the options
	 */
	public static function get_options_callback() {
		$statuses = array();

		foreach ( wc_get_order_statuses() as $key => $label ) {
			$status              = strpos( $key, 'wc-' ) === 0 ? substr( $key, 3 ) : $key;
			$statuses[ $status ] = array(
				'label' => $label,
				'id'    => $status,
			);
		}

		return $statuses;
	}

	public static function get_id() {
		return 'order_status';
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
