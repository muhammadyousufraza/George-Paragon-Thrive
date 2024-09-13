<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-automator
 */

namespace Thrive\Automator\Items;

use Thrive\Automator\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Webhook_Headers extends Action_Field {

	public static function get_name() {
		return __( 'Additional headers', 'thrive-automator' );
	}

	public static function get_description() {
		return '';
	}

	public static function get_placeholder() {
		return __( 'Send test', 'thrive-automator' );
	}

	public static function get_id() {
		return 'webhook_headers';
	}

	public static function get_validators() {
		return [ 'key_value_pair', 'http_headers' ];
	}

	public static function get_type() {
		return Utils::FIELD_TYPE_KEY_PAIR;
	}

	public static function get_preview_template() {
		return '';
	}

	public static function allow_dynamic_data() {
		return true;
	}
}
