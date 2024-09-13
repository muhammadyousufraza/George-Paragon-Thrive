<?php

namespace TVA\Reporting\Events;

use TCB\VideoReporting\Video;
use TVA\Reporting\EventFields\Course_Id;
use TVA\Reporting\EventFields\Lesson_Id;
use TVA\Reporting\EventFields\Member_Id;
use TVA\Reporting\EventFields\Ranges;
use TVA\Reporting\EventFields\Video_Id;
use TVE\Reporting\Event;
use TVE\Reporting\Traits\Report;

if ( ! trait_exists( '\TCB\Traits\Has_Ranges', false ) ) {
	/**
	 * Fixes for dev websites where TCB versions are the same and probably the active plugin is not up-to-date
	 */
	require_once \TVA_Const::plugin_path() . '/tcb/inc/traits/trait-has-ranges.php';
}

class Video_Data extends Event {
	use Report {
		Report::get_group_by as _get_group_by;
	}
	use \TCB\Traits\Has_Ranges;

	//todo prevent this from being shown in the latest activity tab, somehow
	public static function key(): string {
		return 'tva_video_watch_data';
	}

	public static function label(): string {
		return __( 'Video watch data', 'thrive-apprentice' );
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

	public static function get_extra_text_field_1() {
		return Ranges::class;
	}

	public static function register_action() {
		add_action( 'thrive_video_update_watch_data', static function ( $data ) {
			if ( false && ! get_post( $data['item_id'] ) instanceof \TVA_Lesson ) {
				return;
			}

			if ( isset( $data['range_start'] ) && isset( $data['range_end'] ) ) {
				$start = $data['range_start'];
				$end   = $data['range_end'];

				$data['text_field_1'] = json_encode( [ [ 'start' => $start, 'end' => $end ] ] );

				unset( $data['range_start'], $data['range_end'] );
			} else {
				return;
			}

			$event = new static( $data );

			$row = $event->get_entry_row( [ 'item_id', 'user_id', 'post_id', 'int_field_1' ] );

			if ( $row === null ) {
				$event->log();
			} else {
				$ranges = $row->text_field_1;
				$ranges = empty( $ranges ) ? [] : json_decode( $ranges, true );

				$ranges = static::add_range( $ranges, $start, $end );

				/**
				 * Check if we reached the percentage required to consider the video as completed
				 */
				if ( Video::get_instance_with_id( $data['item_id'] )->is_completed( static::get_duration( $ranges ) ) ) {
					do_action( 'thrive_video_completed', [
						'item_id'   => $data['item_id'],
						'user_id'   => $data['user_id'],
						'post_id'   => $data['post_id'],
						'course_id' => ( new \TVA_Lesson( $data['post_id'] ) )->get_course_v2()->get_id(),
					] );
				}

				$event->fields['text_field_1'] = json_encode( $ranges );

				$event->update_log( $row->id, [ 'text_field_1' ] );
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

		return " continued watching video \"$video\" from lesson \"$lesson\", course \"$course\".";
	}
}
