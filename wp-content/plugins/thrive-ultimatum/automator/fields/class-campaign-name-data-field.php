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

class Campaign_Name_Data_Field extends \Thrive\Automator\Items\Data_Field {
	/**
	 * @inheritDoc
	 */
	public static function get_id() {
		return 'campaign_name';
	}

	/**
	 * @inheritDoc
	 */
	public static function get_supported_filters() {
		return array( 'autocomplete' );
	}

	/**
	 * @inheritDoc
	 */
	public static function get_name() {
		return __( 'Name of the campaign', 'thrive-ult' );
	}

	/**
	 * @inheritDoc
	 */
	public static function get_description() {
		return __( 'User targets by one or more Ultimatum campaigns', 'thrive-ult' );
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
		$campaigns = array();

		$running_campaigns = tve_ult_get_campaigns( array(
			'get_designs'  => false,
			'only_running' => true,
			'get_logs'     => false,
			'lockdown'     => true,
		) );

		foreach ( $running_campaigns as $campaign ) {
			if ( $campaign->type === \TVE_Ult_Const::CAMPAIGN_TYPE_EVERGREEN ) {
				$name               = $campaign->post_title;
				$campaigns[ $name ] = array(
					'id'    => $name,
					'label' => $name,
				);
			}
		}

		return $campaigns;
	}

	public static function is_ajax_field() {
		return true;
	}

	public static function get_field_value_type() {
		return static::TYPE_STRING;
	}

	public static function get_dummy_value() {
		return 'An Example Ultimatum Countdown';
	}
}
