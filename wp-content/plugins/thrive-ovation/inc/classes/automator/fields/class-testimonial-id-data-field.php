<?php

namespace TVO\Automator;

use Thrive\Automator\Items\Data_Field;
use function tvo_get_testimonials;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Testimonial_Id_Data_Field
 */
class Testimonial_Id_Data_Field extends Data_Field {
	/**
	 * Field name
	 */
	public static function get_name() {
		return 'Testimonial post';
	}

	/**
	 * Field description
	 */
	public static function get_description() {
		return 'Filter testimonials by ovation post';
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
		$testimonials = [];
		foreach ( tvo_get_testimonials() as $testimonial ) {
			$testimonials[ $testimonial->ID ] = [
				'label' => $testimonial->post_name,
				'id'    => $testimonial->ID,
			];
		}

		return $testimonials;
	}

	public static function get_id() {
		return 'testimonial_id';
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
}
