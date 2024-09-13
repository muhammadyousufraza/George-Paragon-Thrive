<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package plugins
 */

namespace TQB\Reporting\EventFields;

use TVE\Reporting\EventFields\Event_Field;

/**
 * Used also in thrive apprentice plugin
 */
class Quiz_Id extends Event_Field {

	public static function key(): string {
		return 'quiz_id';
	}

	public static function get_label( $singular = true ): string {
		return __( 'Quiz id', 'thrive-quiz-builder' );
	}

	public static function format_value( $value ) {
		return (int) $value;
	}
}
