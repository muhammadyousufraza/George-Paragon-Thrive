<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-automator
 */

namespace Thrive\Automator\Items;
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Date_And_Time_Field extends Trigger_Field {
	public static function get_name() {
		return 'Date and time';
	}

	public static function get_description() {
		return '';
	}

	public static function get_placeholder() {
		return '';
	}

	public static function get_id() {
		return 'date_and_time';
	}

	public static function get_type() {
		return 'date_time';
	}

	public static function get_preview_template() {
		return '$$value';
	}

	public static function is_ajax_field() {
		return false;
	}
}
