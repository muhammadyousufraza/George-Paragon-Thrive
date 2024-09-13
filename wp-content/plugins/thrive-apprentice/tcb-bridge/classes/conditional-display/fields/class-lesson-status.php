<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

namespace TVA\Architect\ConditionalDisplay\Fields;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Lesson_Status extends \TCB\ConditionalDisplay\Field {
	/**
	 * @return string
	 */
	public static function get_entity() {
		return 'lesson_data';
	}

	/**
	 * @return string
	 */
	public static function get_key() {
		return 'lesson_status';
	}

	public static function get_label() {
		return __( 'Lesson status', 'thrive-apprentice' );
	}

	public static function get_conditions() {
		return [ 'checkbox' ];
	}

	/**
	 * @param \TVA_Lesson $lesson
	 *
	 * @return string
	 */
	public function get_value( $lesson ) {
		$status = '';

		if ( ! empty( $lesson ) ) {
			/* only 'complete' and 'in_progress' make sense as statuses in the conditional display context */
			if ( $lesson->is_completed() ) {
				$status = 'complete';
			} elseif ( $lesson->is_in_progress() ) {
				$status = 'in_progress';
			}
		}

		return $status;
	}

	public static function get_options( $selected_values = [], $search = '' ) {
		return [
			[
				'label' => __( 'Complete ', 'thrive-apprentice' ),
				'value' => 'complete',
			],
			[
				'label' => __( 'Not Yet Complete ', 'thrive-apprentice' ),
				'value' => 'in_progress',
			],
		];
	}

	/**
	 * Determines the display order in the modal field select
	 *
	 * @return int
	 */
	public static function get_display_order() {
		return 10;
	}
}
