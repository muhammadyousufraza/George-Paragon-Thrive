<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class TCB_Post_List_Featured_Image
 */
class TCB_Post_List_Featured_Image {

	const PLACEHOLDER_URL = 'editor/css/images/featured_image.png';

	/**
	 * Get the html for the placeholder of the featured image or the real featured image if we send the post id
	 *
	 * @param $post_id
	 *
	 * @return string
	 */
	public static function get_default_url( $post_id = null ) {

		$featured_image = tve_editor_url( static::PLACEHOLDER_URL );

		if ( ! empty( $post_id ) && has_post_thumbnail( $post_id ) ) {
			$post_featured_image = get_the_post_thumbnail_url( $post_id, 'full' );
			if ( ! empty( $post_featured_image ) ) {
				$featured_image = $post_featured_image;
			}
		}

		return $featured_image;
	}

	/**
	 * Get the sizes of the post featured image existent in the website
	 *
	 * @param $post_id
	 *
	 * @return array
	 */
	public static function get_sizes( $post_id ) {
		$sizes = [];

		if ( has_post_thumbnail( $post_id ) ) {
			$post_thumbnail_id = get_post_thumbnail_id( $post_id );
			$sizes             = static::get_image_sizes( $post_thumbnail_id );
		}

		return $sizes;
	}


	/**
	 * Get the available sizes for a certain image
	 *
	 * @param $thumb_id
	 *
	 * @return array
	 */
	public static function get_image_sizes( $thumb_id ) {
		$sizes        = [];
		$filter_sizes = static::filter_available_sizes();

		$post_thumbnail        = get_post( $thumb_id );
		$data['media_details'] = wp_get_attachment_metadata( $thumb_id );

		if ( ! empty( $data['media_details']['sizes'] ) ) {
			foreach ( $data['media_details']['sizes'] as $size => $size_data ) {
				/**
				 * Avoid calling `wp_get_attachment_image_src` for each size if the result is not taken into account
				 */
				if ( ! isset( $filter_sizes[ $size ] ) ) {
					continue;
				}

				$image_src = wp_get_attachment_image_src( $thumb_id, $size );
				if ( ! $image_src ) {
					continue;
				}

				$size_data['url'] = $image_src[0];
				$sizes[ $size ]   = $size_data;
			}
		}

		/**
		 * We should always have the full size of the uploaded image
		 */
		$full_src = wp_get_attachment_image_src( $thumb_id, 'full' );

		if ( ! empty( $full_src ) ) {
			$sizes['full'] = array(
				'file'      => wp_basename( $full_src[0] ),
				'width'     => $full_src[1],
				'height'    => $full_src[2],
				'mime_type' => $post_thumbnail->post_mime_type,
				'ID'        => $post_thumbnail->ID,
				'url'       => $full_src[0],
				'title'     => $post_thumbnail->post_name,
				'caption'   => $post_thumbnail->post_excerpt,
				'alt'       => get_post_meta( $post_thumbnail->ID, '_wp_attachment_image_alt', true ),
			);
		}

		return $sizes;
	}


	/**
	 * Return only this specific values from the available sizes options
	 *
	 * @return array
	 */
	public static function filter_available_sizes() {
		$sizes = apply_filters( 'image_size_names_choose', array(
			'thumbnail' => __( 'Thumbnail', 'thrive-cb' ),
			'medium'    => __( 'Medium', 'thrive-cb' ),
			'large'     => __( 'Large', 'thrive-cb' ),
			'full'      => __( 'Full size', 'thrive-cb' ),
		) );

		/* MailPoet 3 adds an extra image size, but we're not using it in our products so we must remove it */
		if ( ! empty( $sizes['mailpoet_newsletter_max'] ) ) {
			unset ( $sizes['mailpoet_newsletter_max'] );
		}

		return $sizes;
	}

	/**
	 * Returns a normalized list of all currently registered image sub-sizes.
	 * wp_get_registered_image_subsizes it's available only from 5.3 so we are offering an alternative if the function it's not available
	 *
	 * @return array
	 */
	public static function get_registered_image_subsizes() {
		$sizes = [];

		if ( function_exists( 'wp_get_registered_image_subsizes' ) ) {
			$sizes = wp_get_registered_image_subsizes();
		} else {
			$additional_sizes = wp_get_additional_image_sizes();

			foreach ( get_intermediate_image_sizes() as $size_name ) {
				$size_data = [
					'width'  => 0,
					'height' => 0,
					'crop'   => false,
				];

				if ( isset( $additional_sizes[ $size_name ]['width'] ) ) {
					// For sizes added by plugins and themes.
					$size_data['width'] = intval( $additional_sizes[ $size_name ]['width'] );
				} else {
					// For default sizes set in options.
					$size_data['width'] = intval( get_option( "{$size_name}_size_w" ) );
				}

				if ( isset( $additional_sizes[ $size_name ]['height'] ) ) {
					$size_data['height'] = intval( $additional_sizes[ $size_name ]['height'] );
				} else {
					$size_data['height'] = intval( get_option( "{$size_name}_size_h" ) );
				}

				if ( empty( $size_data['width'] ) && empty( $size_data['height'] ) ) {
					// This size isn't set.
					continue;
				}

				$sizes[ $size_name ] = $size_data;
			}
		}

		return $sizes;
	}
}
