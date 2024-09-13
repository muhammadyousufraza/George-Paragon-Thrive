<?php

namespace TVA\Reporting\Events;

use TCB\VideoReporting\Video;
use TVA\Reporting\EventFields\Course_Id;
use TVA\Reporting\EventFields\Lesson_Id;
use TVA\Reporting\EventFields\Video_Id;
use TVA\Reporting\EventFields\Member_Id;
use TVE\Reporting\Event;
use TVE\Reporting\Traits\Report;

class Video_Completed extends Event {
	use Report {
		Report::get_group_by as _get_group_by;
	}

	public static function key(): string {
		return 'tva_video_completed';
	}

	public static function label(): string {
		return __( 'Video completed', 'thrive-apprentice' );
	}

	public static function get_item_id_field(): string {
		return Video_Id::class;
	}

	public static function get_user_id_field(): string {
		return Member_Id::class;
	}

	public static function get_post_id_field(): string {
		return Lesson_Id::class;
	}

	public static function get_extra_int_field_1() {
		return Course_Id::class;
	}

	public static function register_action() {
		add_action( 'thrive_video_completed', static function ( $data ) {
			//todo remove the 'false' when testing from lessons -> and uncomment course_id
			if ( false && ! get_post( $data['item_id'] ) instanceof \TVA_Lesson ) {
				return;
			}

			$event = new static( [
				'item_id'   => $data['item_id'],
				'user_id'   => $data['user_id'],
				'post_id'   => $data['post_id'],
				'course_id' => ( new \TVA_Lesson( $data['post_id'] ) )->get_course_v2()->get_id(),
				'intervals' => [],
			] );

			if ( $event->get_entry_row( [ 'item_id', 'user_id', 'post_id' ] ) === null ) {
				$event->log();
			}
		}, 10, 2 );
	}

	/**
	 * Event description - used for user timeline
	 *
	 * @return string
	 */
	public function get_event_description(): string {
		$video_id = $this->get_field( 'item_id' )->get_value();

		$video  = Video::get_instance_with_id( $video_id )->get_title();
		$lesson = $this->get_field( 'post_id' )->get_title();
		$course = $this->get_field( 'course_id' )->get_title();

		return " completed video \"$video\" from lesson \"$lesson\", course \"$course\".";
	}
}
