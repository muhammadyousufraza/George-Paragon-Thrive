<?php

namespace TVE\Dashboard\Automator;

use Thrive\Automator\Items\Action;
use Thrive\Automator\Items\Action_Field;
use Thrive\Automator\Items\Connection_Test;
use Thrive\Automator\Items\Fields_Webhook;
use Thrive\Automator\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Slack_Send_Notification
 */
class Slack_Send_Notification extends Action {

	protected $data;

	/**
	 * Get the action identifier
	 *
	 * @return string
	 */
	public static function get_id() {
		return 'slack/sendnotification';
	}

	/**
	 * Get the action name/label
	 *
	 * @return string
	 */
	public static function get_name() {
		return __( 'Send notification', 'thrive-dash' );
	}

	/**
	 * Get the action description
	 *
	 * @return string
	 */
	public static function get_description() {
		return __( 'Send a notification to a Slack channel', 'thrive-dash' );
	}

	/**
	 * Get the action logo
	 *
	 * @return string
	 */
	public static function get_image() {
		return 'tap-slack-logo';
	}

	/**
	 * Get the name of app to which action belongs
	 *
	 * @return string
	 */
	public static function get_app_id() {
		return Slack_App::get_id();
	}

	/**
	 * Array of action-field keys, required for the action to be setup
	 *
	 * @return array
	 */
	public static function get_required_action_fields() {
		return [
			Slack_Channel_Field::get_id(),
			Slack_Message_Title_Field::get_id(),
			Fields_Webhook::get_id(),
			Slack_Test_Notification_Field::get_id(),
		];
	}

	/**
	 * Get an array of keys with the required data-objects
	 *
	 * @return array
	 */
	public static function get_required_data_objects() {
		return [];
	}

	public function do_action( $data = [] ) {

		$channel      = $this->get_automation_data_value( Slack_Channel_Field::get_id() );
		$api_instance = \Thrive_Dash_List_Manager::connection_instance( 'slack' );
		$response     = false;
		if ( $api_instance && $api_instance->is_connected() ) {
			$args = array(
				'fields' => $this->get_automation_data_value( 'fields_webhook', [] ),
				'text'   => $this->get_automation_data_value( Slack_Message_Title_Field::get_id() ),
			);
			$response = $api_instance->post_message( $channel, $args );
		}
		if ( empty( $response->ok ) ) {
			$result = [
				'status_code' => 400,
				'message'     => __( 'Request failed with error message', 'thrive-dash' ) . ': ' . $response->error
			];
		} else {
			$result = [ 'status_code' => 200 ];
		}

		return $result;

	}

	public function prepare_data( $data = array() ) {
		$this->data = $data;
	}
}
