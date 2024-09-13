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

class Post_Type_Field extends Trigger_Field {
	public static function get_name() {
		return __( 'Select specific post type(s)', 'thrive-automator' );
	}

	public static function get_description() {
		return __( 'You must select at least one post type', 'thrive-automator' );
	}

	public static function get_placeholder() {
		return __( 'Select at least post type', 'thrive-automator' );
	}

	public static function get_id() {
		return 'post_type_trigger_field';
	}

	public static function get_type() {
		return Utils::FIELD_TYPE_AUTOCOMPLETE;
	}

	public static function is_ajax_field() {
		return true;
	}

	public static function get_validators() {
		return [ 'required' ];
	}

	public static function get_options_callback( $trigger_id, $trigger_data ) {
		$post_types = [];

		foreach ( get_post_types( array( 'public' => true ) ) as $key => $post_type ) {
			$post_types[ $key ] = [
				'label' => $post_type,
				'id'    => $key,
			];
		}

		return $post_types;
	}
}
