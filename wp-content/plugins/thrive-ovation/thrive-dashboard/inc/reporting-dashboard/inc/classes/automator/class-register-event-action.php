<?php

namespace TVE\Reporting\Automator;

use Thrive\Automator\Items\Action;
use Thrive\Automator\Items\Automation_Data;
use Thrive\Automator\Items\User_Data;
use TVE\Reporting\EventFields\User_Id;
use TVE\Reporting\Traits\Event;

class Register_Event_Action extends Action {

	use Event;

	/**
	 * Get the action identifier
	 *
	 * @return string
	 */
	public static function get_id() {
		return 'thrive/reporting-event';
	}

	/**
	 * Get the action name/label
	 *
	 * @return string
	 */
	public static function get_name() {
		return 'Register user event';
	}

	/**
	 * Get the action description
	 *
	 * @return string
	 */
	public static function get_description() {
		return static::get_name();
	}

	/**
	 * Get the action logo
	 *
	 * @return string
	 */
	public static function get_image() {
		return 'tap-unlock-content';
	}

	/**
	 * Get an array of keys with the required data-objects
	 *
	 * @return array
	 */
	public static function get_required_data_objects() {
		return [ 'user_data' ];
	}

	public function prepare_data( $data = array() ) {
		/** @var $automation_data Automation_Data */
		global $automation_data;

		$automation_data->set( 'reporting_event_key', empty( $data['reporting-event-key']['value'] ) ? null : $data['reporting-event-key']['value'] );
	}

	public function do_action( $data ) {
		/** @var $data Automation_Data */
		$user_data = $data->get( User_Data::get_id() );
		if ( ! empty( $user_data ) ) {
			$user_id = (int) $user_data->get_value( User_Id::key() );

			if ( ! empty( $user_id ) ) {
				$this->fields = [
					User_Id::key() => $user_id,
				];

				$this->log();
			}
		}
	}

	public static function key() {
		/** @var $automation_data Automation_Data */
		global $automation_data;

		return $automation_data->get( 'reporting_event_key' );
	}

	public static function get_required_action_fields() {
		return [ 'reporting-event-key' ];
	}
}
