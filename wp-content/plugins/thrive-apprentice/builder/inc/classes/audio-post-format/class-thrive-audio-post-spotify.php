<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Thrive_Audio_Post_Spotify extends Thrive_Audio_Post_Format {

	public function get_defaults() {
		$defaults = [
			'url' => [
				'type'        => 'input',
				'label'       => __( 'Audio Spotify Url', 'thrive-theme' ),
				'value'       => '',
				'placeholder' => 'Input a Spotify audio url.',
				'default'     => '',
			],
		];

		return $defaults;
	}

	public function render() {
		$classes = [ Thrive_Audio_Post_Format::AUDIO_CONTAINER_CLS ];

		/* check if we should hide this element from the page ( by returning nothing or by adding classes to hide it ) */
		if ( ! thrive_post()->is_element_visible( 'featured_audio', $classes ) ) {
			return '';
		}

		$options = $this->get_audio_options_meta();
		$src     = $options['url']['value'];

		/* if no src is set, return empty */
		if ( empty( $src ) ) {
			return Thrive_Audio_Post_Format_Main::render_placeholder();
		}

		if ( strpos( $src, 'open.spotify.com/embed/' ) === false ) {
			$src = str_replace( 'open.spotify.com/', 'open.spotify.com/embed/', $src );
		}

		$attr = [
			'src'               => $src,
			'allowtransparency' => 'true',
			'frameborder'       => 0,
			'allow'             => 'encrypted-media',
			'width'             => '100%',
			'height'            => '100%',
			'scrolling'         => 'no',
			'data-provider'     => 'spotify',
		];

		$content = TCB_Utils::wrap_content( '', 'iframe', '', 'tcb-audio', $attr );
		$content = TCB_Utils::wrap_content( $content, 'div', '', $classes );

		return $content;

	}

	public function render_options() {
		include THEME_PATH . '/inc/templates/admin/audio-post-format/spotify.php';
	}
}
