<?php

namespace TVA\Reporting\Events;

use TVE\Reporting\Event;
use TVE\Reporting\Traits\Report;

class All_Free_Lessons_Completed extends Event {
	use Report;

	public static function key(): string {
		return 'tva_all_free_lessons_completed';
	}

	public static function label(): string {
		return __( 'All free lessons completed', 'thrive-apprentice' );
	}

	public static function register_action() {
		add_action( 'thrive_apprentice_all_free_lessons_completed', static function ( $user_details, $course_id ) {
			$event = new static( [
				'item_id' => $course_id,
				'user_id' => empty( $user_details['user_id'] ) ? 0 : $user_details['user_id'],
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
		return ' completed all the free lessons.';
	}
}
