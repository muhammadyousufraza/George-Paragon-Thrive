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

class Campaign_Event_Id_Data_Field extends \Thrive\Automator\Items\Data_Field {
	/**
	 * @inheritDoc
	 */
	public static function get_id() {
		return 'campaign_event_id';
	}

	/**
	 * @inheritDoc
	 */
	public static function get_supported_filters() {
		return array( 'number_comparison' );
	}

	/**
	 * @inheritDoc
	 */
	public static function get_name() {
		return __( 'ID of the event that triggered the countdown', 'thrive-ult' );
	}

	/**
	 * @inheritDoc
	 */
	public static function get_description() {
		return __( 'User targets by the specific event ID that triggered the campaign', 'thrive-ult' );
	}

	/**
	 * @inheritDoc
	 */
	public static function get_placeholder() {
		return '';
	}

	public static function get_field_value_type() {
		return static::TYPE_NUMBER;
	}

	public static function get_dummy_value() {
		return 34;
	}
}
