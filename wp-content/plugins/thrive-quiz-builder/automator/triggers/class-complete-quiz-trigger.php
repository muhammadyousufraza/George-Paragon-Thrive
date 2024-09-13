<?php

namespace TQB\Automator;


use Thrive\Automator\Items\Trigger;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Complete_Quiz extends Trigger {
	/**
	 * Get the trigger identifier
	 *
	 * @return string
	 */
	public static function get_id() {
		return 'thrive/completequiz';
	}

	/**
	 * Get the trigger hook
	 *
	 * @return string
	 */
	public static function get_wp_hook() {
		return 'thrive_quizbuilder_quiz_completed';
	}

	/**
	 * Get the trigger provided params
	 *
	 * @return array
	 */
	public static function get_provided_data_objects() {
		return array( 'quiz_data', 'user_data', 'form_data' );
	}

	/**
	 * Get the number of params
	 *
	 * @return int
	 */
	public static function get_hook_params_number() {
		return 3;
	}

	/**
	 * Get the id of the app to which the hook belongs
	 *
	 * @return string
	 */
	public static function get_app_id() {
		return Quiz_App::get_id();
	}

	/**
	 * Get the trigger name
	 *
	 * @return string
	 */
	public static function get_name() {
		return 'User completes quiz';
	}

	/**
	 * Get the trigger description
	 *
	 * @return string
	 */
	public static function get_description() {
		return 'This trigger will be fired whenever results are submitted for a quiz. The trigger will not be fired if the user starts the quiz but does not complete all the questions';
	}

	/**
	 * Get the trigger logo
	 *
	 * @return string
	 */
	public static function get_image() {
		return 'tap-quiz-builder-logo';
	}
}
