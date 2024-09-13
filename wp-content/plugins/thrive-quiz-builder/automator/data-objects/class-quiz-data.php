<?php

namespace TQB\Automator;

use Exception;
use Thrive\Automator\Items\Data_Object;
use TQB_Post_meta;
use function html_entity_decode;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Quiz_Data
 */
class Quiz_Data extends Data_Object {
	/**
	 * Get the data-object identifier
	 *
	 * @return string
	 */
	public static function get_id() {
		return 'quiz_data';
	}

	/**
	 * Array of field object keys that are contained by this data-object
	 *
	 * @return array
	 */
	public static function get_fields() {
		return array( 'quiz_title', 'quiz_type', 'quiz_number_result', 'quiz_text_result', 'quiz_user_email', 'quiz_questions_answers' );
	}

	public static function create_object( $param ) {
		if ( empty( $param ) ) {
			throw new Exception( 'No parameter provided for Quiz_Data object' );
		}

		if ( ! empty( $param['quiz_id'] ) ) {
			$param['quiz_name'] = str_replace( '&#8211;', '-', $param['quiz_name'] );

			return array(
				'quiz_id'                => $param['quiz_id'],
				'quiz_title'             => html_entity_decode( $param['quiz_name'] ),
				'quiz_type'              => TQB_Post_meta::get_quiz_type_meta( $param['quiz_id'], true ),
				'quiz_number_result'     => (float) str_replace( '%', '', $param['result'] ),
				'quiz_text_result'       => $param['result'],
				'quiz_user_email'        => $param['user_email'],
				'quiz_questions_answers' => $param['answers'],
			);
		}

		return null;
	}

	public function can_provide_email() {
		return true;
	}

	public function get_provided_email() {
		return $this->get_value( 'quiz_user_email' );
	}
}
