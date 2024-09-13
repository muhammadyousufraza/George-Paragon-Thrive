<?php

namespace TVA\Reporting\Events;

use TVE\Reporting\Event;
use TVE\Reporting\Traits\Report;

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * File Download Event
 */
class File_Download extends Event {
	use Report;

	public static function key(): string {
		return 'tva_protected_file_download';
	}

	public static function label(): string {
		return __( 'File Download', 'thrive-apprentice' );
	}

	public static function register_action() {
		/**
		 * @param \TVA_Protected_File $file
		 * @param array               $user_details
		 */
		add_action( 'tva_protected_file_download', static function ( $file, $user_details ) {
			$event = new self( [
				'item_id' => $file->ID,
				'user_id' => empty( $user_details['user_id'] ) ? 0 : $user_details['user_id'],
				'post_id' => get_the_ID(),
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

		return " downloaded \"$item\" protected file";
	}
}
