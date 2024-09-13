<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */

namespace TVE\Reporting\EventFields;

class Post_Id extends Event_Field {

	public static function key(): string {
		return 'post_id';
	}

	public static function can_group_by(): bool {
		return true;
	}

	public static function get_label( $singular = true ): string {
		return $singular ? 'Post' : 'Posts';
	}

	public static function format_value( $value ) {
		return (int) $value;
	}

	public static function can_filter_by(): bool {
		return false;
	}
}
