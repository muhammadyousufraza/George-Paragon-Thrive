<?php

namespace TVA\Automator;

use Thrive\Automator\Items\Data_Field;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Drip_Enabled_Data_Field
 */
class Drip_Enabled_Data_Field extends Data_Field {
	/**
	 * Field name
	 */
	public static function get_name() {
		return 'Has drip feed enabled';
	}

	/**
	 * Field description
	 */
	public static function get_description() {
		return 'Filter products that have the drip feed feature available';
	}

	/**
	 * Field input placeholder
	 */
	public static function get_placeholder() {
		return '';
	}

	public static function get_id() {
		return 'drip_enabled';
	}

	public static function get_supported_filters() {
		return [ 'boolean' ];
	}

	public static function get_field_value_type() {
		return static::TYPE_BOOLEAN;
	}
}
