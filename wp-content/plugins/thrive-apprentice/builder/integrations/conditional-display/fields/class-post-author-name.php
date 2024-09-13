<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

namespace Thrive\Theme\ConditionalDisplay\Fields;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Post_Author_Name extends \TCB\ConditionalDisplay\Field {
	/**
	 * @return string
	 */
	public static function get_entity() {
		return 'post_data';
	}

	/**
	 * @return string
	 */
	public static function get_key() {
		return 'post_author_name';
	}

	public static function get_label() {
		return __( 'Author name', 'thrive-theme' );
	}

	public static function get_conditions() {
		return [ 'autocomplete' ];
	}

	public function get_value( $post ) {
		return empty( $post ) ? '' : get_the_author_meta( 'ID', $post->post_author );
	}

	public static function get_options( $selected_values = [], $searched_keyword = '' ) {
		$users = [];

		foreach ( get_users() as $user ) {
			if (
				static::filter_options( $user->ID, $user->data->display_name, $selected_values, $searched_keyword ) &&
				user_can( $user, 'publish_posts' )
			) {
				$users[] = [
					'value' => (string) $user->ID,
					'label' => $user->data->display_name,
				];
			}
		}

		return $users;
	}

	/**
	 * @return string
	 */
	public static function get_autocomplete_placeholder() {
		return __( 'Search authors', 'thrive-theme' );
	}

	/**
	 * Determines the display order in the modal field select
	 *
	 * @return int
	 */
	public static function get_display_order() {
		return 35;
	}
}
