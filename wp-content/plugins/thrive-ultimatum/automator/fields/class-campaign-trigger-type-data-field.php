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

class Campaign_Trigger_Type_Data_Field extends \Thrive\Automator\Items\Data_Field {
	/**
	 * @inheritDoc
	 */
	public static function get_id() {
		return 'campaign_trigger_type';
	}

	/**
	 * @inheritDoc
	 */
	public static function get_supported_filters() {
		return array( 'checkbox' );
	}

	/**
	 * @inheritDoc
	 */
	public static function get_name() {
		return __( 'Campaign trigger type', 'thrive-ult' );
	}

	/**
	 * @inheritDoc
	 */
	public static function get_description() {
		return __( 'User targets by one or more trigger types of an Ultimatum campaign', 'thrive-ult' );
	}

	/**
	 * @inheritDoc
	 */
	public static function get_placeholder() {
		return '';
	}

	/**
	 * For multiple option inputs, name of the callback function called through ajax to get the options
	 */
	public static function get_options_callback() {
		$trigger_types = array();

		foreach ( \TVE_Ult_Const::trigger_types() as $type ) {
			$trigger_types[ $type ] = array(
				'id'    => $type,
				'label' => ucfirst( $type ),
			);
		}

		return $trigger_types;
	}

	public static function is_ajax_field() {
		return true;
	}

	public static function get_field_value_type() {
		return static::TYPE_STRING;
	}

	public static function get_dummy_value() {
		return 'Thrive Leads Form';
	}
}
