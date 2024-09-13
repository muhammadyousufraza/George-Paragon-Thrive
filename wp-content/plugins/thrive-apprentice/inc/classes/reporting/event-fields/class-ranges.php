<?php
/**
 * Thrive Apprentice - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

namespace TVA\Reporting\EventFields;

use TVE\Reporting\EventFields\Event_Field;

class Ranges extends Event_Field {
	public static function key(): string {
		return 'ranges';
	}

	public static function get_label( $singular = true ): string {
		return __( 'Ranges', 'thrive-apprentice' );
	}

	public function get_title(): string {
		return __( 'Ranges', 'thrive-apprentice' );
	}
}
