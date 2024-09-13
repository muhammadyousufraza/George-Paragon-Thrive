<?php

namespace TVA\Automator;

use Thrive\Automator\Items\Trigger;
use Thrive\Automator\Items\User_Data;
use TVA\Assessments\TVA_User_Assessment;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Assessment_Failed extends Trigger {

	public static function get_id() {
		return 'thrive/assessment_failed';
	}

	public static function get_wp_hook() {
		return 'tva_assessment_failed';
	}

	public static function get_provided_data_objects() {
		return [
			User_Assessment_Data::get_id(),
			Assessment_Data::get_id(),
			User_Data::get_id(),
			Course_Data::get_id(),
		];
	}

	public static function get_hook_params_number() {
		return 1;
	}
	public static function get_app_id() {
		return Apprentice_App::get_id();
	}

	public static function get_name() {
		return 'Assessment has been marked and failed';
	}

	public static function get_description() {
		return 'Fires when an assessment is marked as failed';
	}

	public static function get_image() {
		return 'tap-apprentice-logo';
	}

	/**
	 * Override default method so we manually init user data, assessment_data, course_data
	 *
	 * @param array $params
	 *
	 * @return array
	 * @see Automation::start()
	 */
	public function process_params( $params = array() ) {
		$data_objects = [];

		if ( $params[0] instanceof TVA_User_Assessment ) {
			$user_assessment                      = $params[0];
			$data_objects['user_assessment_data'] = new User_Assessment_Data( $user_assessment );
			$data_objects['assessment_data']      = new Assessment_Data( $user_assessment->post_parent );
			$data_objects['user_data']            = new User_Data( $user_assessment->post_author );
			$data_objects['course_data']          = new Course_Data( $user_assessment->get_course_id() );
		}

		return $data_objects;
	}
}
