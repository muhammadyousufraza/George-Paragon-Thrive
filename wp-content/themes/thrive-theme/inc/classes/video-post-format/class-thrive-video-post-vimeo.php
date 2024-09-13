<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Thrive_Video_Post_Vimeo extends Thrive_Video_Post_Format {

	const EMBED_SRC = 'https://player.vimeo.com/video/';

	public function get_defaults() {
		$defaults = [
			'url'                => [
				'type'        => 'input',
				'label'       => __( 'Video Url', 'thrive-theme' ),
				'value'       => '',
				'placeholder' => 'e.g. https://vimeo.com/[video_id]',
				'default'     => '',
			],
			'autoplay'           => [
				'type'    => 'checkbox',
				'label'   => __( 'Autoplay', 'thrive-theme' ),
				'class'   => 'thrive-autoplay-checkbox',
				'value'   => '',
				'default' => '',
				'notice'  => __( 'Note: Autoplay is muted by default.', 'thrive-theme' ),
			],
			'hide_user_image'    => [
				'type'     => 'checkbox',
				'label'    => __( 'Hide the user image', 'thrive-theme' ),
				'value'    => '',
				'default'  => '',
				'alias'    => 'modestbranding',
				'inverted' => true,
			],
			'hide_byline'        => [
				'type'     => 'checkbox',
				'label'    => __( 'Hide the "by" line', 'thrive-theme' ),
				'value'    => '',
				'default'  => '',
				'alias'    => 'byline',
				'inverted' => true,
			],
			'hide_title'         => [
				'type'     => 'checkbox',
				'label'    => __( 'Hide title bar', 'thrive-theme' ),
				'value'    => '',
				'default'  => '',
				'alias'    => 'showinfo',
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


	/**
	 * Make sure the video attributes are parsed correctly inside editor.
	 *
	 * @param $video_attrs
	 *
	 * @return mixed
	 */
	public function parse_video_attributes( $video_attrs ) {
		if ( is_editor_page_raw( true ) ) {
			$defaults = $this->get_defaults();
			$keys     = [ 'hide_user_image', 'hide_byline', 'hide_title' ];

			foreach ( $keys as $key ) {
				$video_attrs[ 'data-' . $defaults[ $key ]['alias'] ] = empty( $video_attrs[ 'data-' . $key ] ) ? 1 : 0;

				unset( $video_attrs[ $key ] );
			}
		}

		return $video_attrs;
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
			'data-src'        => $this->get_vimeo_embed_code( $src, $options ),
			'class'           => 'tcb-video',
			'data-provider'   => Thrive_Video_Post_Format_Main::VIMEO,
			'allowfullscreen' => null,
			'frameborder'     => 0,
			'data-autoplay'   => $has_thumbnail || empty( $options['autoplay']['value'] ) ? 0 : 1,
			'data-code'       => '39129233',
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
	private function get_vimeo_embed_code( $src, $options ) {
		if ( ! preg_match( '/.com\/\D*(\d+)\/?([a-zA-Z0-9]+)?/', $src, $m ) ) {
			return '';
		}
		$video_hash = empty( $m[2] ) ? '' : $m[2];
		$video_id   = $m[1];

		$src          = static::EMBED_SRC . $video_id;
		$query_string = $this->parse_query_attributes( $options, $video_hash );

		$src .= empty( $query_string ) ? '?' : ( '?' . $query_string );

		return $src;
	}

	/**
	 * Build the URL query string out of the options.
	 *
	 * @param        $options
	 * @param string $video_hash
	 *
	 * @return string
	 */
	private function parse_query_attributes( $options, $video_hash = '' ) {
		$video_query_attr = [];

		if ( $video_hash !== '' ) {
			$video_query_attr['h'] = $video_hash;
		}
		if ( ! empty ( $options['hide_user_image']['value'] ) ) {
			$video_query_attr['portait'] = 0;
		}
		if ( ! empty ( $options['hide_byline']['value'] ) ) {
			$video_query_attr['byline'] = 0;
		}
		if ( ! empty ( $options['hide_title']['value'] ) ) {
			$video_query_attr['title'] = 0;
		}

		$query_string = http_build_query( $video_query_attr, '', '&' );

		/* calculate the start time (format is #t=1m2s); This cannot be added as a normal query arg, it does not work when added with '&'. */
		$time = Thrive_Video_Post_Format::get_start_time( $options );

		if ( ! empty( $time ) ) {
			$query_string .= '#t=' . $time;
		}

		return $query_string;
	}

	public function render_options() {
		include THEME_PATH . '/inc/templates/admin/video-post-format/vimeo.php';
	}
}
