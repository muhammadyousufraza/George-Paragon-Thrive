<?php

namespace TVA\Reporting\Events;

use TVA\Reporting\EventFields\Course_Id;
use TVA\Reporting\EventFields\Lesson_Unlocked;
use TVE\Reporting\Event;
use TVE\Reporting\Traits\Report;

class Lesson_Unlocked_By_Quiz extends Event {
	use Report;

	public static function key(): string {
		return 'tva_lesson_unlocked_by_quiz';
	}

	public static function label(): string {
		return __( 'Lesson unlocked by quiz', 'thrive-apprentice' );
	}

	public static function get_extra_int_field_1() {
		return Course_Id::class;
	}

	public static function get_extra_int_field_2() {
		if ( class_exists( '\TQB\Reporting\EventFields\Quiz_Id', false ) ) {
			return \TQB\Reporting\EventFields\Quiz_Id::class;
		}

		return null;
	}

	public static function get_extra_float_field() {
		return Lesson_Unlocked::class;
	}

	public static function register_action() {

		add_action( 'thrive_quizbuilder_quiz_completed', static function ( $quiz_details = array(), $user_details = array(), $form_data = array(), $post_id = 0 ) {

			if ( empty( $post_id ) || get_post_type( $post_id ) !== \TVA_Const::LESSON_POST_TYPE ) {
				return false;
			}

			$lesson = new \TVA_Lesson( $post_id );

			$quiz_id    = (int) $quiz_details['quiz_id'];
			$user_id    = empty( $user_details['user_id'] ) ? 0 : (int) $user_details['user_id'];
			$conditions = $lesson->get_complete_conditions();

			if ( ! empty( $conditions ) && ! empty( $conditions['tqb'] ) && is_array( $conditions['tqb'] ) ) {
				foreach ( $conditions['tqb'] as $condition ) {
					if ( ! empty( $condition['quiz_id'] ) && (int) $condition['quiz_id'] === $quiz_id ) {
						$can_be_unlocked = $lesson->can_be_marked_as_completed();

						$event = new static( [
							'item_id'         => $lesson->ID,
							'user_id'         => $user_id,
							'post_id'         => $lesson->ID,
							'course_id'       => $lesson->get_course_v2()->get_id(),
							'quiz_id'         => $quiz_id,
							'lesson_unlocked' => (int) $can_be_unlocked,
						] );

						$event->log();
					}
				}
			}
		}, 10, 4 );
	}

	/**
	 * @return string
	 */
	public function get_event_description(): string {
		$item = $this->get_field( 'item_id' )->get_title();
		$quiz = $this->get_field( 'quiz_id' )->get_title();

		return " unlocked lesson \"$item\" in the quiz \"$quiz\".";
	}
}
