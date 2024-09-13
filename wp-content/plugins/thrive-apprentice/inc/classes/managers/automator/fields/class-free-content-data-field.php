<?php

namespace TVA\Automator;

use Thrive\Automator\Items\Data_Field;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Free_Content_Field
 */
class Free_Content_Data_Field extends Data_Field {
	/**
	 * Field name
	 */
	public static function get_name() {
		return 'Has free content';
	}

	/**
	 * Field description
	 */
	public static function get_description() {
		return 'Filter products that have free content available';
	}

	/**
	 * Field input placeholder
	 */
	public static function get_placeholder() {
		return '';
	}

	public static function get_id() {
		return 'free_content';
	}

	public static function get_supported_filters() {
		return [ 'boolean' ];
	}

	public static function get_field_value_type() {
		return static::TYPE_BOOLEAN;
	}
}
