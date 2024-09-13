<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Thrive_Video_Post_Bunnynet extends Thrive_Video_Post_Format {

	const EMBED_SRC = 'https://iframe.mediadelivery.net/embed/';

	public function get_defaults() {
		$defaults = [
			'url'        => [
				'type'        => 'input',
				'label'       => __( 'Video Url', 'thrive-theme' ),
				'value'       => '',
				'placeholder' => 'e.g. https://video.bunnycdn.com/play/[video_id]/[library_id]',
				'default'     => '',
			],
			'responsive' => [
				'type'     => 'checkbox',
				'label'    => __( 'Responsive', 'thrive-theme' ),
				'value'    => '',
				'default'  => '',
				'alias'    => 'responsive',
				'inverted' => 0,
			],
			'autoplay'   => [
				'type'     => 'checkbox',
				'label'    => __( 'Autoplay', 'thrive-theme' ),
				'class'    => 'thrive-autoplay-checkbox',
				'alias'    => 'autoplay',
				'value'    => '',
				'default'  => '',
				'notice'   => __( 'Note: Autoplay is muted by default.', 'thrive-theme' ),
				'inverted' => 0,
			],
			'preload'    => [
				'type'     => 'checkbox',
				'label'    => __( 'Preload', 'thrive-theme' ),
				'value'    => '',
				'default'  => '',
				'alias'    => 'preload',
				'inverted' => 0,
			],
			'loop'       => [
				'type'     => 'checkbox',
				'label'    => __( 'Loop', 'thrive-theme' ),
				'value'    => '',
				'default'  => '',
				'alias'    => 'loop',
				'inverted' => 0,
			],
			'muted'      => [
				'type'     => 'checkbox',
				'label'    => __( 'Muted', 'thrive-theme' ),
				'value'    => '',
				'default'  => '',
				'alias'    => 'muted',
				'inverted' => 0,
			],
			'lazy_load'  => [
				'type'     => 'checkbox',
				'label'    => __( 'Lazy load', 'thrive-theme' ),
				'value'    => '',
				'default'  => '',
				'alias'    => 'lazy_load',
				'inverted' => 0,
			],
		];

		return $defaults;
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
			'data-src'        => $this->get_bunny_embed_code( $src, $options ),
			'class'           => 'tcb-video',
			'data-provider'   => Thrive_Video_Post_Format_Main::BUNNYNET,
			'allowfullscreen' => 'true',
			'url-params'      => '',
			'allow'           => 'accelerometer; gyroscope; autoplay; encrypted-media; picture-in-picture;',
			'data-code'       => $this->get_bunny_id( $src ),
			'loading'         => 'lazy',
		];

		if ( empty( $main_attr['lazy-load'] ) ) {
			$attr['src'] = $attr['data-src'];
		}
		$attr['style'] = isset( $attr['style'] ) ? rtrim( $attr['style'], ';' ) . '; border: none' : 'border: none';

		if ( ! empty( $options['responsive']['value'] ) ) {
			$attr['class'] .= ' bunny-style-iframe';
			$iframe        = TCB_Utils::wrap_content( '', 'iframe', '', '', $attr );
			$content       = TCB_Utils::wrap_content( $iframe, 'div', '', 'tcb-bunny-responsive-container' );
		} else {
			$content = TCB_Utils::wrap_content( '', 'iframe', '', '', $attr );
		}

		return $content;
	}

	/**
	 * @param $src
	 * @param $options
	 *
	 * @return string
	 */
	private function get_bunny_embed_code( $src, $options ) {
		if ( ! preg_match( '/\/play\/(\d+)\/([a-zA-Z0-9-]+)/', $src, $m ) ) {
			return '';
		}

		$video_id   = $m[2];
		$library_id = $m[1];

		$src          = static::EMBED_SRC . $library_id . '/' . $video_id;
		$query_string = $this->parse_query_attributes( $options );
		$src          .= empty( $query_string ) ? '?' : ( '?' . $query_string );

		return $src;
	}

	private function get_bunny_id( $src ) {
		if ( ! preg_match( '/\/play\/(\d+)\/([a-zA-Z0-9-]+)/', $src, $m ) ) {
			return '';
		}

		return $m[2];
	}

	/**
	 * Build the URL query string out of the options.
	 *
	 * @param        $options
	 * @param string $video_hash
	 *
	 * @return string
	 */
	private function parse_query_attributes( $options ) {
		$video_query_attr               = [];
		$video_query_attr['responsive'] = empty ( $options['responsive']['value'] ) ? 'false' : 'true';
		$video_query_attr['autoplay']   = empty ( $options['autoplay']['value'] ) ? 'false' : 'true';
		$video_query_attr['preload']    = empty ( $options['preload']['value'] ) ? 'false' : 'true';
		$video_query_attr['muted']      = ! empty ( $options['muted']['value'] ) ? 'true' : 'false';
		$video_query_attr['loop']       = ! empty ( $options['loop']['value'] ) ? 'true' : 'false';
		$video_query_attr['lazy_load']  = 'true';

		if ( $video_query_attr['autoplay'] === 'true' && $video_query_attr['preload'] === 'false' ) {
			unset( $video_query_attr['preload'] );
		}

		return http_build_query( $video_query_attr, '', '&' );
	}

	public function render_options() {
		include THEME_PATH . '/inc/templates/admin/video-post-format/bunnynet.php';
	}
}
