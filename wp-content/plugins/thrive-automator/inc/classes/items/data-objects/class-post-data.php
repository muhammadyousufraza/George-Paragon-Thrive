<?php

namespace Thrive\Automator\Items;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Post_Data
 */
class Post_Data extends Data_Object {

	/**
	 * Get the data-object identifier
	 *
	 * @return string
	 */
	public static function get_id() {
		return 'post_data';
	}

	public static function get_nice_name() {
		return __( 'WordPress post', 'thrive-automator' );
	}

	/**
	 * Array of field object keys that are contained by this data-object
	 *
	 * @return array
	 */
	public static function get_fields() {
		return [
			Post_Id_Data_Field::get_id(),
			Post_Author_Email_Field::get_id(),
			Post_Author_Display_Name_Field::get_id(),
			Post_Content_Data_Field::get_id(),
			Post_Status_Data_Field::get_id(),
			Post_Type_Data_Field::get_id(),
			Post_Title_Data_Field::get_id(),
			Post_Date_Data_Field::get_id(),
			Post_Parent_Data_Field::get_id(),
			Post_Categories_Data_Field::get_id(),
			Post_Tags_Data_Field::get_id(),
			Post_Url_Data_Field::get_id(),
		];
	}

	public static function create_object( $param ) {
		$post = null;
		if ( $param instanceof \WP_Post ) {
			$post = $param;
		} elseif ( is_numeric( $param ) ) {
			$post = get_post( $param );
		} elseif ( ! empty( $param['post_id'] ) && is_numeric( $param['post_id'] ) ) {
			$post = get_post( $param['post_id'] );
		} elseif ( is_array( $param ) ) {
			$post = get_post( $param[0] );
		}

		if ( $post ) {
			$author = get_userdata( $post->post_author );

			return [
				'wp_post_id'               => $post->ID,
				'post_author_email'        => $author->user_email ?? '',
				'post_author_display_name' => $author->display_name ?? '',
				'post_content'             => $post->post_content,
				'post_status'              => $post->post_status,
				'post_type'                => $post->post_type,
				'post_title'               => $post->post_title,
				'post_date'                => $post->post_date,
				'post_parent'              => $post->post_parent,
				'post_categories'          => implode( ',', wp_get_post_categories( $post->ID, [ 'fields' => 'names' ] ) ),
				'post_tags'                => implode( ',', wp_get_post_tags( $post->ID, [ 'fields' => 'names' ] ) ),
				'post_url'                 => get_permalink( $post->ID ),
			];
		}

		return $post;
	}

	public static function get_data_object_options() {
		$posts   = get_posts();
		$options = [];

		foreach ( $posts as $post ) {
			$options[ $post->ID ] = [ 'id' => $post->ID, 'label' => $post->post_title ];
		}

		return $options;
	}
}
