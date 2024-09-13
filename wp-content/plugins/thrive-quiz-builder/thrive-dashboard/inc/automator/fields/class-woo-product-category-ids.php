<?php

namespace TVE\Dashboard\Automator;

use Thrive\Automator\Items\Data_Field;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Woo_Product_Category_Ids
 */
class Woo_Product_Category_Ids extends Data_Field {
	/**
	 * Field name
	 */
	public static function get_name() {
		return 'Category ID';
	}

	/**
	 * Field description
	 */
	public static function get_description() {
		return 'Filter by specific product category';
	}

	/**
	 * Field input placeholder
	 */
	public static function get_placeholder() {
		return '';
	}

	public static function get_dummy_value() {
		return 'Example';
	}

	/**
	 * For multiple option inputs, name of the callback function called through ajax to get the options
	 */
	public static function get_options_callback() {
		$categories = array();

		$cat_args = array(
			'orderby' => 'name',
			'order'   => 'asc',
		);

		$product_categories = get_terms( 'product_cat', $cat_args );

		foreach ( $product_categories as $category ) {
			if ( ! empty( $category ) ) {
				$categories[ $category->term_id ] = array(
					'label' => $category->name,
					'id'    => $category->term_id,
				);
			}

		}

		return $categories;
	}

	public static function get_id() {
		return 'product_category_ids';
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
		return static::TYPE_ARRAY;
	}
}
