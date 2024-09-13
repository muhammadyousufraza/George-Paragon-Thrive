<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package plugins
 */

namespace TQB\Reporting\EventFields;

use TVE\Reporting\EventFields\Event_Field;

class Quiz_Score extends Event_Field {

	public static function key(): string {
		return 'quiz_score';
	}

	public static function get_label( $singular = true ): string {
		return __( 'Quiz score', 'thrive-quiz-builder' );
	}

	public static function format_value( $value ) {
		return (int) $value;
	}

	public function get_title(): string {
		return $this->value;
	}

	public static function get_filter_type(): string {
		return 'input';
	}
}
