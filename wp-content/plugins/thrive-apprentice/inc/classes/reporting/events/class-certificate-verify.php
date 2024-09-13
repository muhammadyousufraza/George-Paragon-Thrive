<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

namespace TVA\Reporting\Events;

use TVA\Reporting\EventFields\Course_Id;
use TVE\Reporting\Event;
use TVE\Reporting\Traits\Report;

class Certificate_Verify extends Event {

	use Report;

	public static function key(): string {
		return 'tva_certificate_verify';
	}

	public static function label(): string {
		return __( 'Certificate verifications', 'thrive-apprentice' );
	}

	public static function get_item_id_field(): string {
		return Course_Id::class;
	}

	/**
	 * @return void
	 */
	public static function register_action() {
		add_action( 'tva_certificate_verified', static function ( $certificate_data, $user_data, $course_data ) {
			$event = new self( [
				'item_id'            => $course_data['course_id'],
				'user_id'            => empty( $user_data['user_id'] ) ? 0 : $user_data['user_id'],
				'certificate_number' => $certificate_data['certificate_number'],
			] );

			$event->log();
		}, 10, 3 );
	}

	/**
	 * Event description - used for user timeline
	 *
	 * @return string
	 */
	public function get_event_description(): string {
		$item = $this->get_field( 'item_id' )->get_title();

		return " verified course $item's certificate.";
	}
}
