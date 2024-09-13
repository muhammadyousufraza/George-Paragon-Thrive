<?php

namespace TVA\Architect\Assessment;

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Main {
	const IDENTIFIER      = '.tva-assessment';
	const TYPE_IDENTIFIER = '.tva-assessment-type';

	const TYPE_UPLOAD        = 'upload';
	const TYPE_QUIZ          = 'tqb';
	const TYPE_YOUTUBE_LINK  = 'youtube_link';
	const TYPE_EXTERNAL_LINK = 'external_link';
	const TYPE_CONFIRMATION  = 'confirmation';
	const TYPE_RESULTS       = 'results';

	const STATE_AUTO    = 'auto';
	const STATE_SUBMIT  = 'submit';
	const STATE_RESULTS = 'results';

	/**
	 * @var bool
	 */
	public static $is_editor_page = false;

	/**
	 * Initialize the hooks and adds shortcodes
	 */
	public static function init() {
		static::$is_editor_page = is_editor_page_raw( true );

		Hooks::init();
		Shortcodes::init();
	}

	/**
	 * Fetches the default content
	 *
	 * @return string
	 */
	public static function get_default_content() {
		ob_start();

		include_once __DIR__ . '/assessment-default-content.php';

		$content = ob_get_contents();

		ob_end_clean();

		return $content;
	}

	/**
	 * Computes the editor element name
	 *
	 * @param $type
	 *
	 * @return string
	 */
	public static function get_editor_sub_element_name( $type ) {

		$name = esc_html__( 'Assessment submit type', 'thrive-apprentice' );

		switch ( $type ) {
			case static::TYPE_UPLOAD:
				$name = esc_html__( 'File Upload Container', 'thrive-apprentice' );
				break;
			case static::TYPE_QUIZ:
				$name = esc_html__( 'Quiz', 'thrive-apprentice' );
				break;
			case static::TYPE_YOUTUBE_LINK:
				$name = esc_html__( 'Youtube Link', 'thrive-apprentice' );
				break;
			case static::TYPE_EXTERNAL_LINK:
				$name = esc_html__( 'External Link', 'thrive-apprentice' );
				break;
			case static::TYPE_CONFIRMATION:
				$name = esc_html__( 'Grading Pending', 'thrive-apprentice' );
				break;
			case static::TYPE_RESULTS:
				$name = esc_html__( 'Grading Completed', 'thrive-apprentice' );
				break;
		}

		return $name;
	}

	/**
	 * Decide what state should show for the submit element
	 *
	 * @return string
	 */
	public static function get_assessment_type( $assessment ) {
		$type = '';

		if ( $assessment instanceof \TVA_Assessment ) {
			$type = $assessment->get_type();
		}

		if ( empty( $type ) ) {
			/**
			 * Compatibility with builder website
			 */
			$type = static::TYPE_UPLOAD;
		}

		return $type;
	}

	/**
	 * Returns the assessment quiz ID needed to render in frontend
	 *
	 * @return int
	 */
	public static function get_assessment_quiz_id( $assessment = '' ) {
		if ( get_post_type() === \TVA_Const::ASSESSMENT_POST_TYPE ) {
			$assessment = new \TVA_Assessment( get_post() );
		}

		return $assessment instanceof \TVA_Assessment ? (int) $assessment->get_quiz() : 0;
	}
}
