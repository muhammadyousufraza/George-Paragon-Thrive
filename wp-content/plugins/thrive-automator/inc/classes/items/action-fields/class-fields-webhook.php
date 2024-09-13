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

class Fields_Webhook extends Action_Field {

	public static function get_name() {
		return __( 'Fields', 'thrive-automator' );
	}

	public static function get_description() {
		return __( 'List of fields to be sent to the webhook.', 'thrive-automator' );
	}

	public static function get_placeholder() {
		return __( 'Send test', 'thrive-automator' );
	}

	public static function get_id() {
		return 'fields_webhook';
	}

	public static function get_validators() {
		return [ 'key_value_pair' ];
	}

	public static function get_type() {
		return 'key_value_pair';
	}

	public static function get_preview_template() {
		return '';
	}

	public static function allow_dynamic_data() {
		return true;
	}
}
