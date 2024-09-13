<?php

namespace TVO\Automator;

use Thrive\Automator\Items\Data_Field;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Testimonial_Author_Image_Data_Field
 */
class Testimonial_Author_Image_Data_Field extends Data_Field {
	/**
	 * Field name
	 */
	public static function get_name() {
		return 'Testimonial author image';
	}

	/**
	 * Field description
	 */
	public static function get_description() {
		return 'Filter testimonials by author image';
	}

	/**
	 * Field input placeholder
	 */
	public static function get_placeholder() {
		return '';
	}

	public static function get_id() {
		return 'testimonial_author_image';
	}

	public static function get_supported_filters() {
		return [ 'string_ec' ];
	}

	public static function get_field_value_type() {
		return static::TYPE_STRING;
	}
}
