<?php

namespace Thrive\Automator\Items;

use Thrive\Automator\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Wordpress_Schedule_Single extends Trigger {

	/**
	 * @param array $params
	 *
	 * @return array
	 */
	public function process_params( $params = [] ) {

		$data = false;
		/**
		 *  only init the global data object if the current automation is the one from the cron, so we have some data to work with
		 */

		// handle the old way
		if ( ! empty( $params[0] ) && $params[0] instanceof \WP_Post && $this->get_automation_id() === $params[0]->ID ) {
			$data = [ TAP_GLOBAL_DATA_OBJECT => new Global_Data( [], $params[0]->ID ) ];
		}
		// handle the improved way
		if ( ! empty( $params[0] ) && $this->get_automation_id() === $params[0] ) {
			Automation::register_automation_meta_table();
			$data = [ TAP_GLOBAL_DATA_OBJECT => new Global_Data( [], $params[0] ) ];
			delete_metadata( Automation::AUTOMATION_TABLE, $params[0], Automation::AUTOMATION_SINGLE_EVENT_META );
		}

		return $data;
	}

	/**
	 * Get the trigger identifier
	 *
	 * @return string
	 */
	public static function get_id() {
		return 'wordpress/schedule_single';
	}

	/**
	 * Get the trigger hook
	 *
	 * @return string
	 */
	public static function get_wp_hook() {
		return 'tap_run_single_event_trigger';
	}

	/**
	 * Get the trigger provided params
	 *
	 * @return array
	 */
	public static function get_provided_data_objects() {
		return [];
	}

	/**
	 * Get the number of params
	 *
	 * @return int
	 */
	public static function get_hook_params_number() {
		return 1;
	}


	/**
	 * Get the trigger name
	 *
	 * @return string
	 */
	public static function get_name() {
		return __( 'Specific date and time', 'thrive-automator' );
	}

	/**
	 * Get the trigger description
	 *
	 * @return string
	 */
	public static function get_description() {
		return __( 'Trigger will be fired at the specified date and time. The timezone is inherited from the WordPress “General” settings screen.', 'thrive-automator' );
	}

	/**
	 * Get the trigger logo
	 *
	 * @return string
	 */
	public static function get_image() {
		return 'tap-date-time-logo';
	}

	public static function get_required_trigger_fields() {
		return [ Date_And_Time_Field::get_id() ];
	}

	public static function is_single_scheduled_event() {
		return true;
	}

	public function prepare_data( $data = [] ) {
		$value = '';
		if ( ! empty( $this->data['date_and_time'] ) ) {
			$value = strtotime( $this->data['date_and_time']['value'] . ' ' . Utils::calculate_timezone_offset() );
		}

		return $value;
	}
}
