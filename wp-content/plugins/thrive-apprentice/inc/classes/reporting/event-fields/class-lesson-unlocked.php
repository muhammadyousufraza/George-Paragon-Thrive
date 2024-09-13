<?php

namespace TVA\Reporting\EventFields;

use TVE\Reporting\EventFields\Event_Field;

class Lesson_Unlocked extends Event_Field {

	public static function key(): string {
		return 'lesson_unlocked';
	}

	public static function get_label( $singular = true ): string {
		return __( 'Lesson unlocked', 'thrive-apprentice' );
	}

	public function get_title(): string {
		return __( 'Lesson unlocked', 'thrive-apprentice' );
	}

	public static function format_value( $value ) {
		return (string) $value;
	}
}
