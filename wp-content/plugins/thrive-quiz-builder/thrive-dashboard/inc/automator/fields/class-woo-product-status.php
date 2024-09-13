<?php

namespace TVE\Dashboard\Automator;

use Thrive\Automator\Items\Data_Field;
use function get_post_stati;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Product_Status
 */
class Woo_Product_Status extends Data_Field {
	/**
	 * Field name
	 */
	public static function get_name() {
		return 'Product status';
	}

	/**
	 * Field description
	 */
	public static function get_description() {
		return 'Filter by the status of the product';
	}

	/**
	 * Field input placeholder
	 */
	public static function get_placeholder() {
		return '';
	}

	public static function get_dummy_value() {
		return 'Published';
	}

	/**
	 * For multiple option inputs, name of the callback function called through ajax to get the options
	 */
	public static function get_options_callback() {
		$statuses = array();

		foreach ( get_post_stati() as $key => $status ) {
			$statuses[ $key ] = array(
				'label' => $status,
				'id'    => $key,
			);
		}

		return $statuses;
	}

	public static function get_id() {
		return 'product_status';
	}

	public static function get_supported_filters() {
		return array( 'dropdown' );
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
