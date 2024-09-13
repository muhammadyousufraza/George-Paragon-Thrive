<?php

namespace TVA\Automator;

use Thrive\Automator\Items\Data_Field;
use TVA_Course_V2;
use TVA_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Module_Title_Field
 */
class Module_Title_Data_Field extends Data_Field {
	/**
	 * Field name
	 */
	public static function get_name() {
		return 'Module title';
	}

	/**
	 * Field description
	 */
	public static function get_description() {
		return 'Filter by module title';
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
		$data    = [];
		$courses = TVA_Course_V2::get_items( [ 'status' => 'publish' ] );
		foreach ( $courses as $course ) {
			$course_id = $course->get_id();

			$modules = TVA_Manager::get_course_modules( $course->get_wp_term() );
			if ( ! empty( $modules ) ) {
				$data[ $course_id ] = [
					'label' => $course->name,
					'items' => [],
				];
				foreach ( $modules as $module ) {
					$data[ $course_id ]['items'][ $module->ID ] = [
						'label' => $module->post_title,
						'value' => $module->post_title,
					];
				}
			}
		}

		return $data;
	}

	public static function get_id() {
		return 'module_title';
	}

	public static function get_supported_filters() {
		return [ 'autocomplete_toggle' ];
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
		return 'Example Module';
	}
}
