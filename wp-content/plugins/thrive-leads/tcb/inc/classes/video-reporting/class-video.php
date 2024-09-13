<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

namespace TCB\VideoReporting;

use TCB\Traits\Is_Singleton;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Video {
	use Is_Singleton;
	use Has_Post_Type;

	/**
	 * @var int
	 */
	private $ID;

	/**
	 * @param $id
	 */
	public function __construct( $id = null ) {
		$this->ID = $id;
	}

	public function on_video_start( $user_id, $post_id ) {
		do_action( 'thrive_video_start', [
			'item_id' => $this->ID,
			'user_id' => $user_id,
			'post_id' => $post_id,
		] );
	}

	public function save_range( $user_id, $post_id, $range_start, $range_end ) {
		do_action( 'thrive_video_update_watch_data', [
			'item_id'     => $this->ID,
			'user_id'     => $user_id,
			'post_id'     => $post_id,
			'range_start' => $range_start,
			'range_end'   => $range_end,
		] );
	}

	public function is_completed( $current_duration ) {
		$percentage_to_complete = $this->get_percentage_to_complete();
		if ( ! $percentage_to_complete ) {
			$percentage_to_complete = 100;
		}
		$duration_to_complete = (int) $this->get_full_duration() * (int) $percentage_to_complete / 100;

		return $current_duration >= $duration_to_complete;
	}
}
