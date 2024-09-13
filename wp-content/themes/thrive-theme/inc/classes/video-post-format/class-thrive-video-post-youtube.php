<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Thrive_Video_Post_Youtube extends Thrive_Video_Post_Format {

	const EMBED_SRC           = '//www.youtube.com/embed/';
	const EMBED_NO_COOKIE_SRC = '//www.youtube-nocookie.com/embed/';

	public function get_defaults() {
		$defaults = [
			'url'                => [
				'type'        => 'input',
				'label'       => __( 'Video Url', 'thrive-theme' ),
				'value'       => '',
				'placeholder' => 'e.g. https://www.youtube.com/watch?v=[video_id]',
				'default'     => '',
			],
			'hide_logo'          => [
				'type'    => 'checkbox',
				'label'   => __( 'Auto-hide Youtube logo', 'thrive-theme' ),
				'value'   => '',
				'default' => '',
				'alias'   => 'modestbranding',
			],
			'hide_controls'      => [
				'type'     => 'checkbox',
				'label'    => __( 'Auto-hide player controls ', 'thrive-theme' ),
				'value'    => '',
				'default'  => '',
				'alias'    => 'controls',
				'inverted' => true,
			],
			'show_related'       => [
				'type'     => 'checkbox',
				'label'    => __( 'Optimize related videos', 'thrive-theme' ),
				'value'    => '',
				'default'  => '',
				'alias'    => 'rel',
				'inverted' => true,
			],
			'autoplay'           => [
				'type'    => 'checkbox',
				'label'   => __( 'Autoplay', 'thrive-theme' ),
				'class'   => 'thrive-autoplay-checkbox',
				'value'   => '',
				'default' => '',
				'notice'  => __( 'Note: Autoplay is muted by default.', 'thrive-theme' ),
				'mute'    => 'mute',
			],
			'hide_fullscreen'    => [
				'type'     => 'checkbox',
				'label'    => __( 'Hide full-screen button', 'thrive-theme' ),
				'value'    => '',
				'default'  => '',
				'alias'    => 'fs',
				'inverted' => true,
			],
			'start_time_minutes' => [
				'type'    => 'input',
				'label'   => '',
				'value'   => '',
				'default' => 0,
			],
			'start_time_seconds' => [
				'type'    => 'input',
				'label'   => '',
				'value'   => '',
				'default' => 0,
			],
			'no-cookie'          => [
				'value'   => '',
				'type'    => '',
				'default' => false,
			],
		];

		return array_merge( Thrive_Video_Post_Format::get_general_defaults(), $defaults );
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
		$options = $this->get_video_options_meta();
		$src     = $options['url']['value'];

		/* if no src is set, return empty */
		if ( empty( $src ) ) {
			return Thrive_Video_Post_Format_Main::render_placeholder();

		}

		$attr = [
			'allowfullscreen' => 'allowfullscreen',
			'frameborder'     => 0,
			'class'           => 'tcb-video',
			'data-provider'   => Thrive_Video_Post_Format_Main::YOUTUBE,
			'data-src'        => $this->get_youtube_embed_code( $src, $options ),
			'data-autoplay'   => $has_thumbnail || empty( $options['autoplay']['value'] ) ? 0 : 1,
		];

		if ( empty( $main_attr['lazy-load'] ) ) {
			$attr['src'] = $attr['data-src'];
		}

		return TCB_Utils::wrap_content( '', 'iframe', '', '', $attr );
	}

	/**
	 * @param $src
	 * @param $options
	 * @param $has_thumbnail
	 *
	 * @return string
	 */
	private function get_youtube_embed_code( $src, $options ) {
		if ( preg_match( '/^((?:https?:)?\/\/)?((?:www|m)\.)?((?:youtube\.com|youtu.be))(\/(?:[\w\-]+\?v=|embed\/|shorts\/|\/|v\/)?)([\w\-]+)(\S+)?$/', $src, $m ) ) {
			$video_id = $m[5];
		} else {
			return '';
		}

		$embed_src    = empty( $options['no-cookie']['value'] ) ? static::EMBED_SRC : static::EMBED_NO_COOKIE_SRC;
		$src          = $embed_src . $video_id;
		$query_string = $this->parse_query_attributes( $options );

		$src .= empty( $query_string ) ? '?' : ( '?' . $query_string );

		return $src;
	}

	/**
	 * Build the URL query string out of the options.
	 *
	 * @param $options
	 *
	 * @return string
	 */
	private function parse_query_attributes( $options ) {
		$video_query_attr = [];

		if ( ! empty( $options['hide_logo']['value'] ) ) {
			$video_query_attr['modestbranding'] = 1;
		}
		if ( ! empty( $options['hide_controls']['value'] ) ) {
			$video_query_attr['controls'] = 0;
		}
		if ( empty( $options['show_related']['value'] ) ) {
			$video_query_attr['rel'] = 0;
		}
		if ( ! empty( $options['hide_fullscreen']['value'] ) ) {
			$video_query_attr['fs'] = 0;
		}

		$time = Thrive_Video_Post_Format::get_start_time( $options, 'int' );

		if ( $time !== 0 ) {
			$video_query_attr['start'] = $time;
		}

		$query_string = http_build_query( $video_query_attr, '', '&' );

		return $query_string;
	}

	public function render_options() {
		include THEME_PATH . '/inc/templates/admin/video-post-format/youtube.php';
	}
}
