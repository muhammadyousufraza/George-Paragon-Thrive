<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */

namespace TVE\Reporting\EventFields;

use TVE\Reporting\Logs;

class Created extends Event_Field {

	public static function key(): string {
		return 'date';
	}

	public static function can_group_by(): bool {
		return true;
	}

	/**
	 * Subtract one day from date
	 *
	 * @param string $date
	 * @param string $format
	 *
	 * @return string
	 */
	public static function minus_one_day( $date, $format = 'Y-m-d' ) {
		$timestamp = strtotime( $date );

		$timestamp -= DAY_IN_SECONDS;

		return date( $format, $timestamp );
	}

	public static function get_query_select_field( $db_col = '' ): string {
		global $reports_query;

		switch ( $reports_query['date_format'] ) {
			case 'year':
				$format = 'DATE_FORMAT(`created`, "%Y")';
				break;
			case 'month':
				$format = 'DATE_FORMAT(`created`, "%M %Y")';
				break;
			case 'week':
				$format = 'DATE_FORMAT(`created`, "%YW%v")';
				break;
			case 'day':
			default:
				$format = 'DATE(`created`)';
				break;
		}

		return "$format AS date";
	}

	public static function get_label( $singular = true ): string {
		return $singular ? 'Date' : 'Dates';
	}

	public static function format_value( $value ) {
		return strtotime( $value );
	}

	public function get_title(): string {
		return $this->value === null ? 'Date' : static::format_value( $this->value );
	}

	public static function get_filter_type(): string {
		return 'date-range-picker';
	}
}
