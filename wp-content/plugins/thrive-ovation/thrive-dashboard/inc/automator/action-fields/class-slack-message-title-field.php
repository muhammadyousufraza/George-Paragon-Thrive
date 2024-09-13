<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-automator
 */

namespace TVE\Dashboard\Automator;

use Thrive\Automator\Items\Action_Field;

use Thrive\Automator\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Slack_Message_Title_Field extends Action_Field {

	public static function get_name() {
		return __( 'Message title', 'thrive-dash' );
	}

	public static function get_description() {
		return __( 'Message title', 'thrive-dash' );
	}

	public static function get_placeholder() {
		return __( 'Enter the title of your notification', 'thrive-dash' );
	}

	public static function get_id() {
		return 'slack_message_title';
	}

	public static function get_type() {
		return Utils::FIELD_TYPE_TEXT;
	}

	public static function get_validators() {
		return [ 'required' ];
	}

	public static function is_ajax_field() {
		return false;
	}

	public static function get_preview_template() {
		return __( 'Message title:', 'thrive-dash' ) . '$$value';
	}

	public static function allow_dynamic_data() {
		return true;
	}
}
