<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

namespace TVA\Reporting\EventFields;

use TVE\Reporting\EventFields\Event_Field;

class Campaign_Id extends Event_Field {

	public static function key(): string {
		return 'campaign_id';
	}

	public static function can_group_by(): bool {
		return true;
	}

	public static function get_label( $singular = true ): string {
		return $singular ? __( 'Campaign', 'thrive-apprentice' ) : __( 'Campaigns', 'thrive-apprentice' );
	}

	public static function format_value( $value ) {
		return (int) $value;
	}
}
