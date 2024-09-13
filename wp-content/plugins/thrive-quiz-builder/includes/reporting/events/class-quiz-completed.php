<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-quiz-builder
 */

namespace TQB\Reporting\Events;

use TQB\Reporting\EventFields\Quiz_Id;
use TVE\Reporting\Event;
use TQB\Reporting\EventFields\Quiz_Score;
use TVE\Reporting\Traits\Report;

class Quiz_Completed extends Event {

	use Report;

	public static function key(): string {
		return 'tqb_quiz_completed';
	}

	public static function label(): string {
		return __( 'Quiz Completed', 'thrive-quiz-builder' );
	}

	public static function get_item_id_field(): string {
		return Quiz_Id::class;
	}

	public static function get_extra_int_field_1() {
		return Quiz_Score::class;
	}

	public static function register_action() {
		add_action( 'thrive_quizbuilder_quiz_completed', static function ( $quiz_details, $user_details ) {
			$event = new self( [
				'item_id'    => $quiz_details['quiz_id'],
				'user_id'    => empty( $user_details['user_id'] ) ? 0 : $user_details['user_id'],
				'post_id'    => empty( $_REQUEST['tqb-post-id'] ) ? 0 : (int) $_REQUEST['tqb-post-id'],
				'quiz_score' => empty( $quiz_details['result'] ) ? 0 : $quiz_details['result'],
			] );

			$event->log();
		}, 10, 2 );
	}

	/**
	 * Event description - used for user timeline
	 *
	 * @return string
	 */
	public function get_event_description(): string {
		$item  = $this->get_field( 'item_id' )->get_title();
		$score = $this->get_field_value( 'quiz_score' );

		return " finished quiz `$item` with $score points.";
	}
}
