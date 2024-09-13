<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

namespace TVA\Reporting\EventFields;

use TVE\Reporting\EventFields\Event_Field;

class Course_Id extends Event_Field {

	public static function key(): string {
		return 'course_id';
	}

	public static function can_group_by(): bool {
		return true;
	}

	public static function get_label( $singular = true ): string {
		return $singular ? __( 'Course', 'thrive-apprentice' ) : __( 'Courses', 'thrive-apprentice' );
	}

	public function get_title(): string {
		if ( ! empty( $this->value ) ) {
			$course = new \TVA_Course_V2( (int) $this->value );
		}

		return $course->name ?? __( 'Course', 'thrive-apprentice' );
	}

	public static function format_value( $value ) {
		return (int) $value;
	}

	public static function get_filter_options(): array {
		$publish = [];
		$draft   = [];

		foreach ( \TVA_Course_V2::get_items() as $key => $course ) {
			if ( $course->is_published() ) {

				$publish[$key] = [ 'id' => $course->get_id(), 'label' => $course->name ];
			} else {

				$draft[$key] = [ 'id' => $course->get_id(), 'label' => $course->name . ' (draft)' ];
			}
		}

		return array_values( $publish + $draft );
	}
}
