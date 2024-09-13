<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-automator
 */

namespace TVE\Dashboard\Automator;
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}
use Thrive\Automator\Items\Connection_Test;

class Slack_Test_Notification_Field extends Connection_Test {

	public static function get_name() {
		return '';
	}

	public static function get_description() {
		return '';
	}

	public static function get_placeholder() {
		return '';
	}

	public static function get_id() {
		return 'slack_test_notification_button';
	}

	public static function get_type() {
		return 'action_test';
	}

	public static function get_extra_options() {
		return [
			'success_message' => __( 'Slack notification sent successfully', 'thrive-automator' ),
		];
	}
}
