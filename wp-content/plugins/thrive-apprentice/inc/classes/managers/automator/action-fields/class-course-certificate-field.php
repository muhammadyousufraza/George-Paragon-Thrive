<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

namespace TVA\Automator;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

use Thrive\Automator\Items\Action_Field;
use TVA_Course_V2;

class Course_Certificate_Field extends Action_Field {

	/**
	 * Field name
	 */
	public static function get_name() {
		return '';
	}

	/**
	 * Field description
	 */
	public static function get_description() {
		return __( 'Select a course with a certificate', 'thrive-apprentice' );
	}

	/**
	 * Field input placeholder
	 */
	public static function get_placeholder() {
		return __( 'Select a course with a certificate', 'thrive-apprentice' );
	}

	/**
	 * Field input type
	 */
	public static function get_type() {
		return 'autocomplete';
	}

	/**
	 * Field input options
	 */
	public static function get_options_callback( $action_id, $action_data ) {
		$courses = TVA_Course_V2::get_items( [ 'status' => 'publish' ] );

		$options = [];

		foreach ( $courses as $course ) {
			if ( $course->has_certificate() ) {
				$course_id             = $course->get_id();
				$options[ $course_id ] = [
					'label' => $course->name,
					'id'    => $course_id,
				];
			}
		}

		return $options;
	}

	/**
	 * $$value will be replaced by field value
	 * $$length will be replaced by value length
	 *
	 * @var string
	 */
	public static function get_preview_template() {
		return 'Course(s): $$value';
	}

	public static function is_ajax_field() {
		return true;
	}

	public static function get_validators() {
		return [ 'required' ];
	}

	public static function get_id() {
		return 'tva/course/certificate';
	}

	public static function allowed_data_set_values() {
		return [ Course_Data::get_id() ];
	}
}
