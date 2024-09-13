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

class Post_Tags_Field extends Action_Field {
	public static function get_name() {
		return __( 'Post tag', 'thrive-automator' );
	}

	public static function get_description() {
		return __( 'WordPress post tag', 'thrive-automator' );
	}

	public static function get_placeholder() {
		return __( 'Post tag', 'thrive-automator' );
	}

	public static function get_id() {
		return 'post_tags';
	}

	public static function get_type() {
		return Utils::FIELD_TYPE_TAGS;
	}

	public static function get_preview_template() {
		return __( 'Tags:', 'thrive-automator' ).' $$value';
	}

	public static function get_validators() {
		return [ 'required' ];
	}

	public static function allow_dynamic_data() {
		return true;
	}
}
