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

class Wordpress_Add_Post_Categories extends Action {

	protected $categories;

	public static function get_id(): string {
		return 'wordpress/add_post_categories';
	}

	public static function get_name(): string {
		return __( 'Add post categories', 'thrive-automator' );
	}

	public static function get_description(): string {
		return __( 'Add a list of post categories to the current post, if they do not already exist they will be created', 'thrive-automator' );
	}

	public static function get_app_id(): string {
		return Wordpress_App::get_id();
	}

	public static function get_image(): string {
		return 'tap-wordpress-logo';
	}

	public static function get_required_action_fields(): array {
		return [ Post_Categories_Field::get_id() ];
	}

	public static function get_required_data_objects(): array {
		return [ Post_Data::get_id() ];
	}

	public function prepare_data( $data = [] ) {
		if ( ! empty( $data[ Post_Categories_Field::get_id() ]['value'] ) ) {
			$this->categories = $data[ Post_Categories_Field::get_id() ]['value'];
		}
	}

	public function do_action( $data ) {
		global $automation_data;
		$post_data = $automation_data->get( Post_Data::get_id() );

		$categories = [];
		foreach ( $this->categories as $category ) {
			$term_exists = term_exists( $category, 'category' );
			if ( $term_exists ) {
				$categories[] = (int) $term_exists['term_id'];
			} else {
				$inserted_term = wp_insert_term( $category, 'category' );
				$categories[]  = is_array( $inserted_term ) ? $inserted_term['term_id'] : $inserted_term;
			}
		}

		if ( ! empty( $post_data ) ) {
			wp_set_post_terms( $post_data->get_value( 'wp_post_id' ), $categories, 'category', true );
		}

		return true;
	}
}
