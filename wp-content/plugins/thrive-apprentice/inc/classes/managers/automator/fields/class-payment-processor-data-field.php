<?php

namespace TVA\Automator;

use Thrive\Automator\Items\Data_Field;
use TVA_Const;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Payment_Processor_Field
 */
class Payment_Processor_Data_Field extends Data_Field {
	/**
	 * Field name
	 */
	public static function get_name() {
		return 'Payment processor';
	}

	/**
	 * Field description
	 */
	public static function get_description() {
		return 'Filter by payment processor';
	}

	/**
	 * Field input placeholder
	 */
	public static function get_placeholder() {
		return '';
	}

	/**
	 * For multiple option inputs, name of the callback function called through ajax to get the options
	 */
	public static function get_options_callback() {
		$processors = [];
		foreach ( TVA_Const::PAYMENT_PROCESSORS as $key => $processor ) {
			$processors[ $key ] = [
				'label' => $processor,
				'id'    => $key,
			];
		}

		return $processors;
	}

	public static function get_id() {
		return 'payment_processor';
	}

	public static function get_supported_filters() {
		return [ 'autocomplete' ];
	}

	public static function is_ajax_field() {
		return true;
	}

	public static function get_validators() {
		return [ 'required' ];
	}

	public static function get_field_value_type() {
		return static::TYPE_STRING;
	}

	public static function get_dummy_value() {
		return 'WooCommerce';
	}
}
