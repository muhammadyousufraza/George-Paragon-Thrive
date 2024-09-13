<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Thrive_Video_Post_Custom extends Thrive_Video_Post_Format {

	public function get_defaults() {
		$defaults = [
			'url'      => [
				'type'        => 'textarea',
				'label'       => __( 'Video Custom Url or Embed Code', 'thrive-theme' ),
				'value'       => '',
				'placeholder' => __( 'Add a video URL from the Media tab, or upload a video.', 'thrive-theme' ),
				'default'     => '',
			],
			'autoplay' => [
				'type'    => 'checkbox',
				'label'   => __( 'Autoplay', 'thrive-theme' ),
				'class'   => 'thrive-autoplay-checkbox',
				'value'   => '',
				'default' => '',
				'notice'  => __( 'Note: Autoplay is muted by default.', 'thrive-theme' ),
			],
			'loop'     => [
				'type'    => 'checkbox',
				'label'   => __( 'Loop', 'thrive-theme' ),
				'value'   => '',
				'default' => '',
				'notice'  => '',
			],
			'controls' => [
				'type'     => 'checkbox',
				'label'    => __( 'Hide Controls', 'thrive-theme' ),
				'value'    => '',
				'default'  => 0,
				'notice'   => '',
				'inverted' => true,
			],
		];

		return $defaults;
	}

	/**
	 * Escape the video URL. Strips all the tags from the embed code except the allowed tags.
	 * This is done to prevent users from adding <script> or <style> or other fun tags.
	 *
	 * @param $url
	 *
	 * @return string
	 */
	public function sanitize_url( $url ) {
		$allowed_tags = [ '<iframe>', '<embed>', '<video>', '<source>' ];

		return strip_tags( $url, implode( $allowed_tags ) );
	}

	/**
	 * See the parent function for description.
	 *
	 * @param $has_thumbnail
	 * @param $main_attr
	 *
	 * @return mixed|string
	 */
	public function render( $has_thumbnail, $main_attr ) {

		/**
		 * Allow other visual builders to hook into here:
		 *
		 * Used in TA Visual Builder to hook into here and handle video lesson content
		 */
		$content = apply_filters( 'thrive_theme_video_post_custom_content', '', $this->post_id, $has_thumbnail );

		if ( ! empty( $content ) ) {
			return $content;
		}

		$options = $this->get_video_options_meta();
		$src     = $options['url']['value'];
		/* if no src is set, return empty */
		if ( empty( $src ) ) {
			return Thrive_Video_Post_Format_Main::render_placeholder();

		}

		/* if the src contains < or [, it's already a video code -> call do_shortcode on it  */
		if ( strpos( $src, '<' ) !== false || strpos( $src, '[' ) !== false ) { //if embeded code or shortcode
			$content = do_shortcode( $src );
		} else {
			$attachment_id = attachment_url_to_postid( $src );

			$attr = [
				'style'         => 'width:100%',
				'data-title'    => empty( $attachment_id ) ? get_the_title() : get_the_title( $attachment_id ),
				'data-id'       => $attachment_id,
				'controls'      => 'controls', /* this enables the control bar */
				'playsinline'   => null, /* these are added as a single attributes, without a corresponding value */
				'class'         => 'tcb-video',
				'data-src'      => $src,
				'data-provider' => Thrive_Video_Post_Format_Main::CUSTOM,
			];

			/* autoplay only if there is no thumbnail and the autoplay value is set to 1 */
			if ( ! $has_thumbnail && ! empty( $options['autoplay']['value'] ) ) {
				/* we set these to null so they are displayed inline inside the HTML tag. It does not mean they are not active!! */
				$attr['data-autoplay'] = 1;
				$attr['muted']         = 1;
			}
			if ( ! empty( $options['loop']['value'] ) ) {
				$attr['loop'] = null;
			}

			$type = empty( $attachment_id ) ? 'video/mp4' : get_post_mime_type( $attachment_id );

			$source_html = '<source src="' . $src . '" type="' . $type . '">';
			$content     = TCB_Utils::wrap_content( $source_html, 'video', '', 'tcb-video tcb-responsive-video', $attr );
		}

		return $content;
	}

	public function render_options() {
		include THEME_PATH . '/inc/templates/admin/video-post-format/custom.php';
	}
}
