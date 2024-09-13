<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */

namespace TVE\Reporting;

use TVE\Reporting\Traits\Report;

abstract class Report_Type {

	use Report;

	abstract public static function key(): string;

	abstract public static function label(): string;

	public static function get_registered_fields(): array {
		return [];
	}

	public static function get_registered_field() {
		return null;
	}

	public static function get_filters(): array {
		return [];
	}
}
