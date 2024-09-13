<?php

namespace TVA\Architect\DynamicVideo;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Main
 * - for dynamic video shortcode
 *
 * @package TVA\Architect\DynamicVideo
 */
class Main {

	/**
	 * @var self
	 */
	protected static $instance;

	/** @var string[] */
	protected $shortcodes = array(
		'tva_dynamic_video' => 'render_dynamic_video',
	);

	/**
	 * Singleton implementation
	 *
	 * @return self
	 */
	public static function instance() {

		if ( null === self::$instance ) {
			self::$instance = new static();
		}

		return self::$instance;
	}

	/**
	 * And do not allow this class to be instantiated multiple times
	 * - register the shortcodes
	 */
	private function __construct() {
		foreach ( $this->shortcodes as $shortcode => $function ) {
			add_shortcode( $shortcode, array( $this, $function ) );
		}
	}

	/**
	 * Transforms a shortcode string into HTML
	 * - for course overview page, in frontend or content editor
	 *
	 * @param array  $attr
	 * @param string $content
	 *
	 * @return string
	 */
	public function render_dynamic_video( $attr, $content ) {

		$classes = array( 'tve_responsive_video_container' );

		if ( \TVA_Course_Overview_Post::POST_TYPE === get_post_type() ) {
			if ( tva_course()->has_video() ) {
				$video   = tva_course()->get_video();
				$content = $video->get_embed_code();

				/* If the video is floating, the thumbnail should be wrapped inside the floating container*/
				if ( ! empty( $attr['is-floating'] ) || ( isset( $attr['float'] ) && 'true' === $attr['float'] ) ) {
					$content = \TCB_Utils::wrap_content( $content, 'div', '', 'tcb-video-float-container' );
				}

				$content = \TCB_Utils::wrap_content( $content, 'div', '', $classes, $attr );
			} elseif ( false === tva_course()->has_video() && is_editor_page() ) {
				ob_start();
				include \TVA\Architect\Utils::get_integration_path( 'editor-layouts/elements/dynamic-video/course-overview-overlay.php' );
				$content = ob_get_clean();
				$content = \TCB_Utils::wrap_content( $content, 'div', '', $classes, $attr );
			}
		}

		return $content;
	}
}
