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
 * Class Thrive_Image_Post_Format
 */
class Thrive_Image_Post_Format {

	public $post_id;

	/**
	 * Thrive_Image_Post_Format constructor.
	 *
	 * @param $post_id
	 */
	public function __construct( $post_id = null ) {
		$this->post_id = $post_id ? $post_id : get_the_ID();
	}

	/**
	 * Get the individual post featured image.
	 *
	 * @return array|mixed
	 */
	public function get_image() {

		$attachment_id = get_post_thumbnail_id( $this->post_id );
		$image_data    = wp_get_attachment_metadata( $attachment_id );
		if ( $image_data && is_array( $image_data ) ) {
			$image_data['src'] = wp_get_attachment_image_url( $attachment_id, 'full' );
		} else {
			$image_data = [ 'src' => THRIVE_FEATURED_IMAGE_PLACEHOLDER ];
		}

		return $image_data;
	}

	/**
	 * Save individual post featured image.
	 *
	 * @param int $attachment_id
	 *
	 * @return array|mixed
	 */
	public function save_image( $attachment_id ) {
		return set_post_thumbnail( $this->post_id, $attachment_id );
	}

	/**
	 * Render featured image element.
	 *
	 * @return array|mixed
	 */
	public function render() {
		$image = '';
		if ( has_post_thumbnail( $this->post_id ) ) {
			$image = get_the_post_thumbnail( $this->post_id, 'full' );
		}

		/* add the post url only when the option is selected */
		$url_attr = [
			'href'  => get_permalink( $this->post_id ),
			'title' => get_the_title( $this->post_id ),
		];

		$attr['post_id'] = $this->post_id;

		return Thrive_Shortcodes::before_wrap( [
			'content' => $image,
			'tag'     => 'a',
			'class'   => TCB_POST_THUMBNAIL_IDENTIFIER . ' ' . TCB_SHORTCODE_CLASS,
			'attr'    => $url_attr,
		], $attr );
	}
}


/**
 * @param $post_id
 *
 * @return Thrive_Image_Post_Format
 */
function thrive_image_post_format( $post_id = null ) {
	return new Thrive_Image_Post_Format( $post_id );
}

