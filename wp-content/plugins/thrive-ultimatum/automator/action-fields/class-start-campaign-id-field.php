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

class Start_Campaign_Id_Field extends \Thrive\Automator\Items\Action_Field {
	/**
	 * Field name
	 */
	public static function get_name() {
		return __( 'Running campaign', 'thrive-ult');
	}

	/**
	 * Field description
	 */
	public static function get_description() {
		return __( 'Which lockdown campaign do you want to trigger?', 'thrive-ult');
	}

	/**
	 * Field input placeholder
	 */
	public static function get_placeholder() {
		return __( 'Choose campaign', 'thrive-ult');
	}

	/**
	 * $$value will be replaced by field value
	 * $$length will be replaced by value length
	 *
	 * @var string
	 */
	public static function get_preview_template() {
		return 'Campaign: $$value';
	}

	/**
	 * For multiple option inputs, name of the callback function called through ajax to get the options
	 */
	public static function get_options_callback( $action_id, $action_data ) {
		$campaigns = array();

		$running_campaigns = tve_ult_get_campaigns( array(
			'get_designs'  => false,
			'only_running' => true,
			'get_logs'     => false,
			'lockdown'     => true,
		) );

		foreach ( $running_campaigns as $campaign ) {
			if ( $campaign->type === \TVE_Ult_Const::CAMPAIGN_TYPE_EVERGREEN ) {
				$id               = $campaign->ID;
				$campaigns[ $id ] = array(
					'id'    => $id,
					'label' => $campaign->post_title,
				);
			}
		}

		return $campaigns;
	}

	public static function get_id() {
		return 'start_campaign_id';
	}

	public static function get_type() {
		return 'select';
	}

	public static function is_ajax_field() {
		return true;
	}

	public static function get_validators() {
		return array( 'required' );
	}
}
