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

class Wordpress_Remove_Post_Tags extends Action {

	protected $tags;

	public static function get_id(): string {
		return 'wordpress/remove_post_tags';
	}

	public static function get_name(): string {
		return __( 'Remove post tags', 'thrive-automator' );
	}

	public static function get_description(): string {
		return __( 'Remove a list of post tags from the current post', 'thrive-automator' );
	}

	public static function get_app_id(): string {
		return Wordpress_App::get_id();
	}

	public static function get_image(): string {
		return 'tap-wordpress-logo';
	}

	public static function get_required_action_fields(): array {
		return [ Post_Tags_Field::get_id() ];
	}

	public static function get_required_data_objects(): array {
		return [ Post_Data::get_id() ];
	}

	public function prepare_data( $data = [] ) {
		if ( ! empty( $data[ Post_Tags_Field::get_id() ]['value'] ) ) {
			$this->tags = $data[ Post_Tags_Field::get_id() ]['value'];
		}
	}

	public function do_action( $data ) {
		global $automation_data;
		$post_data = $automation_data->get( Post_Data::get_id() );
		if ( ! empty( $post_data ) ) {
			wp_remove_object_terms( $post_data->get_value( 'wp_post_id' ), $this->tags, 'post_tag' );
		}

		return true;
	}
}
