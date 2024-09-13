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

class Campaign_Data extends \Thrive\Automator\Items\Data_Object {

	public static function get_id() {
		return 'campaign_data';
	}

	public static function get_fields() {
		return array(
			'campaign_id',
			'campaign_name',
			'campaign_type',
			'campaign_start_date',
			'campaign_trigger_type',
			'campaign_event_id',
			'campaign_user_email',
		);
	}

	public static function create_object( $param ) {
		return array(
			'campaign_id'           => $param['campaign_id'],
			'campaign_name'         => $param['campaign_name'],
			'campaign_type'         => $param['campaign_type'],
			'campaign_trigger_type' => $param['campaign_trigger_type'],
			'campaign_start_date'   => $param['campaign_start_date'],
			'campaign_event_id'     => $param['countdown_event_id'],
			'campaign_user_email'   => $param['user_email'],
		);
	}

	public function can_provide_email() {
		return true;
	}

	public function get_provided_email() {
		return $this->get_value( 'campaign_user_email' );
	}
}
