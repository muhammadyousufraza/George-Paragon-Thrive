<?php

namespace TVA\Automator;

use Thrive\Automator\Items\Trigger;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Start_Module extends Trigger {

	/**
	 * Get the trigger identifier
	 *
	 * @return string
	 */
	public static function get_id() {
		return 'thrive/start_module';
	}

	/**
	 * Get the trigger hook
	 *
	 * @return string
	 */
	public static function get_wp_hook() {
		return 'thrive_apprentice_module_start';
	}

	/**
	 * Get the trigger provided params
	 *
	 * @return array
	 */
	public static function get_provided_data_objects() {
		return [ 'module_data', 'user_data' ];
	}

	/**
	 * Get the number of params
	 *
	 * @return int
	 */
	public static function get_hook_params_number() {
		return 2;
	}

	/**
	 * Get the name of the app to which the hook belongs
	 *
	 * @return string
	 */
	public static function get_app_id() {
		return Apprentice_App::get_id();
	}

	/**
	 * Get the trigger name
	 *
	 * @return string
	 */
	public static function get_name() {
		return 'User starts a module';
	}

	/**
	 * Get the trigger description
	 *
	 * @return string
	 */
	public static function get_description() {
		return 'This trigger will be fired the first time a user views a lesson from a module. This trigger will not fire if the user does not have access rights to view the course content.';
	}

	/**
	 * Get the trigger logo
	 *
	 * @return string
	 */
	public static function get_image() {
		return 'tap-apprentice-logo';
	}
}
