<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

if ( ! class_exists( 'TCB_Post_List_Featured_Image', false ) ) {
	require_once TVE_TCB_ROOT_PATH . 'inc/classes/post-list/class-tcb-post-list-featured-image.php';
}

/**
 * Class Thrive_Featured_Image
 */
class Thrive_Featured_Image extends TCB_Post_List_Featured_Image {

	/**
	 * Get the html for the default featured image set in the thrive theme dashboard
	 * If this doesn't exists we will set a default one existent in the theme
	 * As of 2023, I don't think the functionality to change the default featured image exists, so this logic will always return the default placeholder.
	 *
	 * @param string $size
	 *
	 * @return string
	 */
	public static function get_default( $size = 'full' ) {
		$attachment_id = get_option( THRIVE_FEATURED_IMAGE_OPTION );

		if ( empty( $attachment_id ) ) {
			$default_sizes = static::get_default_sizes();

			$image = TCB_Utils::wrap_content( '', 'img', '', '', [
				'src'     => THRIVE_FEATURED_IMAGE_PLACEHOLDER,
				'width'   => $default_sizes['width'],
				'height'  => $default_sizes['height'],
				'loading' => 'lazy',
			] );
		} else {
			$image = wp_get_attachment_image( $attachment_id, $size, false, [ 'alt' => THRIVE_FEATURED_IMAGE_PLACEHOLDER ] );
		}

		return $image;
	}

	/**
	 * Get the default featured image url set in the thrive theme dashboard
	 * If this doesn't exists return the default placeholder
	 *
	 * @param string $size
	 *
	 * @return string
	 */
	public static function get_default_url( $size = 'full' ) {
		$attachment_id = (int) get_option( THRIVE_FEATURED_IMAGE_OPTION );

		if ( empty( $attachment_id ) || ! is_int( $attachment_id ) ) {
			$url = THRIVE_FEATURED_IMAGE_PLACEHOLDER;
		} else {
			$url = wp_get_attachment_image_url( $attachment_id, $size );
		}

		return $url;
	}

	/**
	 * Returns the default sizes of THRIVE_FEATURED_IMAGE_OPTION ('/inc/assets/images/featured_image.png')
	 *
	 * @return string[]
	 */
	public static function get_default_sizes() {
		return [
			'width'  => '743',
			'height' => '385',
		];
	}
}
