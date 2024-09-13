<?php

namespace TVA\Reporting\Events;

use TVA\Reporting\EventFields\Course_Id;
use TVE\Reporting\Event;
use TVE\Reporting\Traits\Report;

class Free_Lesson_Complete extends Event {

	use Report;

	public static function key(): string {
		return 'tva_free_lesson_completed';
	}

	public static function label(): string {
		return __( 'Free lessons completed', 'thrive-apprentice' );
	}

	public static function get_extra_int_field_1() {
		return Course_Id::class;
	}

	public static function register_action() {
		add_action( 'thrive_apprentice_free_lesson_completed', static function ( $lesson_details, $user_details ) {
			$event = new static( [
				'item_id'   => $lesson_details['lesson_id'],
				'user_id'   => empty( $user_details['user_id'] ) ? 0 : $user_details['user_id'],
				'post_id'   => $lesson_details['lesson_id'],
				'course_id' => $lesson_details['course_id'],
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
		$item = $this->get_field( 'item_id' )->get_title();

		return " completed the free lesson $item.";
	}
}
