<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Thrive_Video_Post_Wistia extends Thrive_Video_Post_Format {

	const EMBED_SRC = '//fast.wistia.net/embed/iframe/';

	public function get_defaults() {
		$defaults = [
			'url'                => [
				'type'        => 'input',
				'label'       => __( 'Video Url', 'thrive-theme' ),
				'value'       => '',
				'placeholder' => 'e.g. http://fast.wistia.net/embed/iframe/[video_id]',
				'default'     => '',
			],
			'autoplay'           => [
				'type'    => 'checkbox',
				'label'   => __( 'Autoplay', 'thrive-theme' ),
				'class'   => 'thrive-autoplay-checkbox',
				'value'   => '',
				'default' => '',
				'notice'  => __( 'Note: Autoplay is muted by default.', 'thrive-theme' ),
				'mute'    => 'silentAutoPlay',
			],
			'disable_play_bar'   => [
				'type'     => 'checkbox',
				'label'    => __( 'Disable the Play bar', 'thrive-theme' ),
				'value'    => '',
				'default'  => '',
				'alias'    => 'play-bar',
				'inverted' => true,
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
		];

		return array_merge( Thrive_Video_Post_Format::get_general_defaults(), $defaults );
	}

	public function render_options() {
		include THEME_PATH . '/inc/templates/admin/video-post-format/wistia.php';
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
			'data-src'        => $this->get_wistia_embed_code( $src, $options ),
			'class'           => 'tcb-video',
			'data-provider'   => Thrive_Video_Post_Format_Main::WISTIA,
			'allowfullscreen' => null,
			'frameborder'     => 0,
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
	 *
	 * @return string
	 */
	private function get_wistia_embed_code( $src, $options ) {
		if ( preg_match( '/https?:\/\/(.+)?fast\.wistia\.\w*\/embed\/(.+?)\/(.+)/', $src, $m ) ) {
			$video_id = $m[3];
		} elseif ( preg_match( '/https?:\/\/(.+)?(wistia\.com|wi\.st)\/(medias|embed)\/(.+)/', $src, $m ) ) {
			$video_id = $m[4];
		} else {
			return '';
		}

		$src = static::EMBED_SRC . $video_id;

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

		if ( ! empty( $options['disable_play_bar']['value'] ) ) {
			$video_query_attr['playbar'] = 'false';
		}
		if ( ! empty( $options['hide_fullscreen']['value'] ) ) {
			$video_query_attr['fullscreenButton'] = 'false';
		}

		/* calculate the start time (format is &time=1m2s) */
		$time = Thrive_Video_Post_Format::get_start_time( $options );

		if ( ! empty( $time ) ) {
			$video_query_attr['time'] = $time;
		}

		$query_string = http_build_query( $video_query_attr, '', '&' );

		return $query_string;
	}
}
