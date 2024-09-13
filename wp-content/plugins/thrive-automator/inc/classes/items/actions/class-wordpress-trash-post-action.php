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

class Wordpress_Trash_Post extends Action {

	public static function get_id(): string {
		return 'wordpress/trash_post';
	}

	public static function get_name(): string {
		return __( 'Move post to trash', 'thrive-automator' );
	}

	public static function get_description(): string {
		return '';
	}

	public static function get_image(): string {
		return 'tap-wordpress-logo';
	}

	public static function get_app_id() {
		return Wordpress_App::get_id();
	}

	public static function get_required_action_fields(): array {
		return [];
	}

	public static function get_required_data_objects(): array {
		return [ Post_Data::get_id() ];
	}

	public function prepare_data( $data = [] ) {
		return [];
	}

	public function do_action( $data ) {
		global $automation_data;
		if ( ! empty( $automation_data->get( Post_Data::get_id() ) ) ) {
			wp_trash_post( $automation_data->get( Post_Data::get_id() )->get_value( 'wp_post_id' ) );
		}

		return true;
	}
}
