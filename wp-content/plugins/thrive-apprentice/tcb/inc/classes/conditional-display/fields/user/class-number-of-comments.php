<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

namespace TCB\ConditionalDisplay\Fields\User;

use TCB\ConditionalDisplay\Field;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Number_Of_Comments extends Field {
	/**
	 * @return string
	 */
	public static function get_entity() {
		return 'user_data';
	}

	/**
	 * @return string
	 */
	public static function get_key() {
		return 'number_of_comments';
	}

	public static function get_label() {
		return esc_html__( 'Number of comments', 'thrive-cb' );
	}

	public static function get_conditions() {
		return [ 'number_comparison' ];
	}

	public function get_value( $user_data ) {
		$comments_count = 0;

		if ( ! empty( $user_data ) ) {
			$args = [
				'author_email'  => $user_data->user_email,
				'no_found_rows' => false,
				'number'        => 10,
				'status'        => 'all,spam,trash',
			];

			$query = new \WP_Comment_Query;
			$query->query( $args );

			$comments_count = (int) $query->found_comments;
		}

		return $comments_count;
	}

	/**
	 * Determines the display order in the modal field select
	 *
	 * @return int
	 */
	public static function get_display_order() {
		return 25;
	}
}
