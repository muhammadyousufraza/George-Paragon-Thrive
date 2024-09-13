<?php

namespace TVA\Reporting\Events;

class Module_Finish extends Module_Start {

	public static function key(): string {
		return 'tva_module_finish';
	}

	public static function label(): string {
		return __( 'Modules finished', 'thrive-apprentice' );
	}

	public static function register_action() {
		add_action( 'thrive_apprentice_module_finish', static function ( $module_details, $user_details ) {
			$event = new static( [
				'item_id'   => $module_details['module_id'],
				'user_id'   => empty( $user_details['user_id'] ) ? 0 : $user_details['user_id'],
				'post_id'   => $module_details['module_id'],
				'course_id' => $module_details['course_id'],
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
		$item   = $this->get_field( 'item_id' )->get_title();
		$course = $this->get_field( 'course_id' )->get_title();

		return " finished module \"$item\" in the course \"$course\".";
	}
}
