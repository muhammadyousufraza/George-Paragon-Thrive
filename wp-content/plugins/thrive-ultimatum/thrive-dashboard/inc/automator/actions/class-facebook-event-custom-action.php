<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */

namespace TVE\Dashboard\Automator;

use Thrive\Automator\Items\Action;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Facebook_Event_Custom extends Action {

	protected $event_name;
	protected $custom_fields;

	public static function get_id() {
		return 'fb/custom_event';
	}

	public static function get_app_id() {
		return Facebook_App::get_id();
	}

	public static function get_name() {
		return __( 'Fire Facebook custom event', 'thrive-dash' );
	}

	public static function get_description() {
		return __( 'Send a custom event to Facebook', 'thrive-dash' );
	}

	public static function get_image() {
		return 'tap-facebook-logo';
	}

	public static function get_required_action_fields() {
		return [
			Facebook_Event_Name_Field::get_id(),
			Facebook_Custom_Options_Field::get_id(),
		];
	}

	public function prepare_data( $data = array() ) {
		$this->event_name    = $data[ Facebook_Event_Name_Field::get_id() ]['value'];
		$this->custom_fields = Main::extract_mapping( $data[ Facebook_Custom_Options_Field::get_id() ]['value'] );
	}

	public static function get_required_data_objects() {
		return [];
	}

	public function do_action( $data ) {
		/**
		 * @var \Thrive_Dash_Api_FacebookPixel $api_instance
		 */
		$api_instance = Facebook::get_api();
		if ( $api_instance ) {
			$event_fields = [
				'event_name' => $this->event_name,
			];

			$user_data                 = $api_instance->prepare_user_data();
			$event_fields['user_data'] = $user_data;

			$custom_properties = [];
			if ( ! empty( $this->custom_fields ) ) {
				$custom_properties['custom_properties'] = $this->custom_fields;
			}
			$event_fields['custom_data'] = $api_instance->prepare_custom_data( $custom_properties );
			$event                       = $api_instance->prepare_event_data( $event_fields );

			$response = $api_instance->send_events( $event );

			if ( $response['success'] !== true ) {
				Facebook::log_error_request( $this->get_automation_id(), $response );
			}
		}
	}
}
