<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-automator
 */

namespace Thrive\Automator\Items;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Post_Categories_Data_Field extends Data_Field {

	public static function get_id() {
		return 'post_categories';
	}

	public static function get_supported_filters() {
		return [ Autocomplete_Contains::get_id() ];
	}

	public static function get_name() {
		return __( 'Post categories', 'thrive-automator' );
	}

	public static function get_description() {
		return __( 'Filter by post categories', 'thrive-automator' );
	}

	public static function get_placeholder() {
		return '';
	}

	public static function get_field_value_type() {
		return static::TYPE_STRING;
	}

	public static function get_dummy_value() {
		return 'Category';
	}

	public static function is_ajax_field() {
		return true;
	}

	public static function get_field_values( $filters = [] ) {
		$categories = \get_categories( [
			'hide_empty' => false,

		] );
		$data       = [];

		foreach ( $categories as $category ) {
			$data[] = [
				'id'    => $category->name,
				'label' => $category->name,
			];
		}


		return $data;
	}
}
