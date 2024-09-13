<?php

namespace TVA\Reporting\Events;

use TVA\Assessments\TVA_User_Assessment;
use TVE\Reporting\Event;
use TVE\Reporting\Traits\Report;

class Assessment_Submitted extends Event {
	use Report;

	public static function key(): string {
		return 'tva_assessment_submitted';
	}

	public static function label(): string {
		return __( 'Assessment submits', 'thrive-apprentice' );
	}

	/**
	 * @return void
	 */
	public static function register_action() {
		add_action( 'tva_assessment_submitted', static function ( $assessment_data ) {
			$event = new self( [
				'user_id' => $assessment_data->post_author,
				'item_id' => $assessment_data->post_parent,
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

		return " submitted an assessment for $item.";
	}
}
