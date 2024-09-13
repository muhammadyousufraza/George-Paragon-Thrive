<?php

namespace TVA\Automator;

use Thrive\Automator\Items\Data_Field;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Premium_Content_Data_Field
 */
class Premium_Content_Data_Field extends Data_Field {
	/**
	 * Field name
	 */
	public static function get_name() {
		return 'Has content for purchase';
	}

	/**
	 * Field description
	 */
	public static function get_description() {
		return 'Filter products that have content available only by purchase';
	}

	/**
	 * Field input placeholder
	 */
	public static function get_placeholder() {
		return '';
	}

	public static function get_id() {
		return 'premium_content';
	}

	public static function get_supported_filters() {
		return [ 'boolean' ];
	}

	public static function get_field_value_type() {
		return static::TYPE_BOOLEAN;
	}
}
