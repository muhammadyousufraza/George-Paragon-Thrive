<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

namespace TVA\Reporting\EventFields;

use TVE\Reporting\EventFields\Event_Field;

class Lesson_Id extends Event_Field {

	public static function key(): string {
		return 'lesson_id';
	}

	public static function can_group_by(): bool {
		return true;
	}

	public static function get_label( $singular = true ): string {
		return $singular ? __( 'Lesson', 'thrive-apprentice' ) : __( 'Lessons', 'thrive-apprentice' );
	}

	public static function format_value( $value ) {
		return (int) $value;
	}
}
