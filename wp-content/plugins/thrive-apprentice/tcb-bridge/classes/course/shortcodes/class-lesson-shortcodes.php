<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

namespace TVA\Architect\Course\Shortcodes;

use TVA\Architect\Course\Main;
use TVA_Const;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

require_once __DIR__ . '/class-shortcodes.php';

class Lesson_Shortcodes extends Shortcodes {

	public function __construct( $type = '' ) {
		parent::__construct( $type );
		/**
		 * Init assessment subitems too
		 */
		$this->init_items_shortcodes( 'assessment' );
	}

	/**
	 * Override the item state shortcode to prevent displaying the assessment is some cases
	 *
	 * @param $attr
	 * @param $content
	 * @param $shortcode
	 *
	 * @return string
	 */
	public function item_state( $attr = array(), $content = '', $shortcode = '' ) {
		$prevent_display = false;

		if ( ! empty( $this->active_item ) ) {
			/**
			 * If the active item is a lesson and the shortcode is an assessment, then don't display it
			 */
			if ( $this->active_item->post_type === TVA_Const::LESSON_POST_TYPE && strpos( $shortcode, 'assessment' ) !== false ) {
				$prevent_display = true;
			}
			/**
			 * If the active item is an assessment and the shortcode is a lesson, then don't display it
			 */
			if ( $this->active_item->post_type === TVA_Const::ASSESSMENT_POST_TYPE && strpos( $shortcode, 'lesson' ) !== false ) {
				$prevent_display = true;
			}
			/**
			 * If the active item is an assessment and the shortcode is an assessment but the user chose to hide it, then don't display it
			 */
			if ( ! Main::$show_assessments && $this->active_item->post_type === TVA_Const::ASSESSMENT_POST_TYPE && strpos( $shortcode, 'assessment' ) !== false ) {
				$prevent_display = true;
			}
		}


		return $prevent_display ? '' : parent::item_state( $attr, $content, $shortcode );
	}

	/**
	 * Returns the Lesson Post Type
	 *
	 * @return array
	 */
	protected function get_post_type() {
		return [ TVA_Const::LESSON_POST_TYPE, TVA_Const::ASSESSMENT_POST_TYPE ];
	}

	/*
	 * @param string $content
	 *
	 * @return mixed
	 */
	public function get_default_content( $content ) {
		return static::get_default_template( 'lesson' );
	}


	/**
	 * Class is different for assessments
	 *
	 * @param $post_type
	 *
	 * @return string
	 */
	protected function get_item_class( $post_type = '' ) {
		return 'tva-course-' . ( $post_type === TVA_Const::ASSESSMENT_POST_TYPE ? 'assessment' : $this->type );
	}
}
