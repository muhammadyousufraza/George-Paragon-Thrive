<?php

namespace TVE\Dashboard\Automator;

use Thrive\Automator\Items\Data_Field;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Woo_Product_Shipping_Class_Ids
 */
class Woo_Product_Shipping_Class_Ids extends Data_Field {
	/**
	 * Field name
	 */
	public static function get_name() {
		return 'Shipping class';
	}

	/**
	 * Field description
	 */
	public static function get_description() {
		return 'Filter by shipping class ID';
	}

	/**
	 * Field input placeholder
	 */
	public static function get_placeholder() {
		return '';
	}

	public static function get_dummy_value() {
		return 99;
	}

	/**
	 * For multiple option inputs, name of the callback function called through ajax to get the options
	 */
	public static function get_options_callback() {
		$classes = array();

		$cat_args = array(
			'orderby' => 'name',
			'order'   => 'asc',
		);

		$product_classes = get_terms( 'product_shipping_class', $cat_args );

		foreach ( $product_classes as $class ) {
			if ( ! empty( $class ) ) {
				$classes[ $class->term_id ] = array(
					'label' => $class->name,
					'id'    => $class->term_id,
				);
			}

		}

		return $classes;
	}

	public static function get_id() {
		return 'product_shipping_class_ids';
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
