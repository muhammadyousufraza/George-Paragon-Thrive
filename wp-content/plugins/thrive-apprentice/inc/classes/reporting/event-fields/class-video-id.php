<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

namespace TVA\Reporting\EventFields;

use TVE\Reporting\EventFields\Event_Field;

class Video_Id extends Event_Field {
	public static function key(): string {
		return 'video_id';
	}

	public static function can_group_by(): bool {
		return true;
	}

	public static function get_label( $singular = true ): string {
		return $singular ? __( 'Video', 'thrive-apprentice' ) : __( 'Videos', 'thrive-apprentice' );
	}

	public static function format_value( $value ) {
		return (int) $value;
	}
}
