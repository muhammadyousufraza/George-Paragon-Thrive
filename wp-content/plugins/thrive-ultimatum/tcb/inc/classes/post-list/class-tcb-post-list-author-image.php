<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class TCB_Post_List_Author_Image
 */
class TCB_Post_List_Author_Image {
	const PLACEHOLDER_URL = 'editor/css/images/author_image.png';

	/**
	 * Get the default author image url, or the author image url for a specific post
	 *
	 * @param $post_id
	 *
	 * @return string
	 */
	public static function get_default_url( $post_id = null ) {
		$url = tve_editor_url( static::PLACEHOLDER_URL );

		if ( ! empty( $post_id ) ) {
			$post = get_post( $post_id );

			if ( $post ) {
				$avatar_data = get_avatar_data( $post->post_author, [ 'size' => 256 ] );

				if ( ! empty( $avatar_data['url'] ) && ! is_wp_error( $avatar_data['url'] ) ) {
					$url = $avatar_data['url'];
				}
			}
		}

		return $url;
	}

	/**
	 * Returns post author avatar
	 *
	 * @return false|string
	 */
	public static function author_avatar() {
		global $post;

		if ( empty( $post ) ) {
			$avatar_url = '';
		} else {
			/**
			 * Allow dynamic implementations for post_authors.
			 *
			 * @param string $post_author
			 */
			$post_author = apply_filters( 'tcb_dynamic_field_author', $post->post_author );
			$avatar      = get_avatar( $post_author, 256 );
			preg_match( '/src=\'([^\']*)\'/m', $avatar, $matches );

			if ( empty( $matches[1] ) ) {
				$avatar_url = get_avatar_url( $post->post_author, [ 'size' => 256 ] );
			} else {
				$avatar_url = html_entity_decode( $matches[1] );
			}

			/* if we're in the editor, append a dynamic flag at the end so we can recognize that the URL is dynamic */
			if ( TCB_Utils::in_editor_render( true ) ) {
				$avatar_url = add_query_arg( [
					'dynamic_author' => 1,
				], $avatar_url );
			}
		}

		return $avatar_url;
	}
}
