<?php

namespace TVA\Reporting\Events;

use TVA\Reporting\EventFields\Course_Id;
use TVA\Reporting\EventFields\Lesson_Id;
use TVA\Reporting\EventFields\Member_Id;
use TVE\Reporting\Event;
use TVE\Reporting\Traits\Report;

class Lesson_Start extends Event {

	use Report {
		Report::get_group_by as _get_group_by;
	}

	public static function key(): string {
		return 'tva_lesson_start';
	}

	public static function label(): string {
		return __( 'Lesson started', 'thrive-apprentice' );
	}

	public static function get_tooltip_text(): string {
		return '<strong>{number}</strong> ' . __( 'lessons started', 'thrive-apprentice' );
	}

	public static function get_item_id_field(): string {
		return Lesson_Id::class;
	}

	public static function get_user_id_field(): string {
		return Member_Id::class;
	}

	public static function get_extra_int_field_1() {
		return Course_Id::class;
	}

	public static function register_action() {
		add_action( 'thrive_apprentice_lesson_start', static function ( $lesson_details, $user_details ) {
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
		$lesson = $this->get_field( 'item_id' )->get_title();
		$course = $this->get_field( 'course_id' )->get_title();

		return " started lesson \"$lesson\" in the course \"$course\".";
	}
}
