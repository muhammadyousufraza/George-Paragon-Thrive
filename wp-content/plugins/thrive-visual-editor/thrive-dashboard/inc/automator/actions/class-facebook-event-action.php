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

class Facebook_Event extends Action {

	protected $event;
	protected $standard_fields;
	protected $custom_fields;

	public static function get_id() {
		return 'fb/standard_event';
	}

	public static function get_name() {
		return __( 'Fire Facebook standard event', 'thrive-dash' );
	}

	public static function get_app_id() {
		return Facebook_App::get_id();
	}

	public static function get_description() {
		return __( 'Send a standard event to Facebook', 'thrive-dash' );
	}

	public static function get_image() {
		return 'tap-facebook-logo';
	}

	public function prepare_data( $data = array() ) {
		$this->event           = $data[ Facebook_Event_Field::get_id() ]['value'];
		$this->standard_fields = Main::extract_mapping( $data[ Facebook_Standard_Options_Field::get_id() ]['value'] );
		$this->custom_fields   = Main::extract_mapping( $data[ Facebook_Custom_Options_Field::get_id() ]['value'] );
	}

	public static function get_required_action_fields() {
		return [
			Facebook_Event_Field::get_id(),
			Facebook_Standard_Options_Field::get_id(),
			Facebook_Custom_Options_Field::get_id(),
		];
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
			$user_fields   = Facebook::extract_user_fields( $this->standard_fields );
			$event_options = Facebook::extract_event_fields( $this->standard_fields );

			$event_fields = [
				'event_name' => $this->event,
			];
			if ( ! empty( $event_options ) ) {
				$event_fields = array_merge( $event_fields, $event_options );
			}

			$event_fields['user_data'] = $api_instance->prepare_user_data( $user_fields );

			$custom_data_details = array_diff( $this->standard_fields, $user_fields, $event_options );

			$this->custom_fields = array_filter( $this->custom_fields );

			if ( ! empty( $this->custom_fields ) ) {
				$custom_data_details['custom_properties'] = $this->custom_fields;
			}

			if ( ! empty( $custom_data_details ) ) {
				$event_fields['custom_data'] = $api_instance->prepare_custom_data( $custom_data_details );
			}

			$event = $api_instance->prepare_event_data( $event_fields );

			$response = $api_instance->send_events( $event );

			if ( $response['success'] !== true ) {
				Facebook::log_error_request( $this->get_automation_id(), $response );
			}
		}
	}
}
