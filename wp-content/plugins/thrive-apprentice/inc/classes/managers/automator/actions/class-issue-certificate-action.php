<?php

namespace TVA\Automator;


use Thrive\Automator\Items\Action;
use Thrive\Automator\Items\Automation_Data;
use Thrive\Automator\Utils;
use TVA_Course_V2;
use TVA_Customer;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Issue_Certificate extends Action {
	protected $courses;

	public static function get_id() {
		return 'thrive/issuecertificate';
	}

	public static function get_name() {
		return 'Issue course certificate';
	}

	public static function get_description() {
		return 'Issue course certificate to user';
	}

	public static function get_image() {
		return 'tap-product-enroll';
	}

	public static function get_app_id() {
		return Apprentice_App::get_id();
	}

	public static function get_required_action_fields() {
		return [ Course_Certificate_Field::get_id() ];
	}

	public static function get_required_data_objects() {
		return [ 'user_data' ];
	}

	public function prepare_data( $data = array() ) {
		if ( ! empty( $data[ Course_Certificate_Field::get_id() ]['value'] ) ) {
			$this->courses = $data[ Course_Certificate_Field::get_id() ]['value'];
		} else {
			$this->courses = [];
		}
	}

	/**
	 * @param Automation_Data $data
	 *
	 * @return void
	 */
	public function do_action( $data ) {
		$user_id  = $data->get( 'user_data' )->get_value( 'user_id' );
		$customer = new TVA_Customer( $user_id );

		/**
		 * In case the action was setup up before the course certificate field was added, we need to get the course id from the course_data object
		 */
		if ( empty( $this->courses ) && ! empty( $data->get( 'course_data' ) ) ) {
			$course_id     = $data->get( 'course_data' )->get_value( 'course_id' );
			$this->courses = [ $course_id ];
		}

		foreach ( $this->courses as $course_id ) {
			$course_id = Utils::get_dynamic_data_object_from_automation( $course_id, 'course_id' );
			$course    = new TVA_Course_V2( $course_id );
			if ( $course->has_certificate() ) {
				$certificate = $course->get_certificate();
				$certificate->download( $customer );

				$certificate->send_email( $user_id );
			}
		}
	}
}
