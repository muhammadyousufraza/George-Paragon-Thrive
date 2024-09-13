<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-ultimatum
 */

namespace TU\Automator;
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Campaign_Start_Date_Data_Field extends \Thrive\Automator\Items\Data_Field {
	/**
	 * Field name
	 */
	public static function get_name() {
		return 'Campaign start date';
	}

	/**
	 * Field description
	 */
	public static function get_description() {
		return 'User targets by the start date of an Ultimatum campaign';
	}

	/**
	 * Field input placeholder
	 */
	public static function get_placeholder() {
		return '';
	}

	public static function get_id() {
		return 'campaign_start_date';
	}

	public static function get_supported_filters() {
		return array( 'time_date' );
	}

	public static function get_field_value_type() {
		return static::TYPE_DATE;
	}

	public static function get_dummy_value() {
		return '2021-09-06 17:18:57';
	}
}

