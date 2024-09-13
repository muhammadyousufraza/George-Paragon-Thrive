<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

namespace TVA\Architect\Visual_Builder;

use TCB_Utils;
use TVA\Access\Expiry\Base;
use TVD_Global_Shortcodes;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Shortcodes
 *
 * @project: thrive-apprentice
 */
class Shortcodes {

	/**
	 * Contains the List of Shortcodes
	 *
	 * @var array
	 */
	private $shortcodes = [
		'tva_content_post_title'         => 'post_title',
		'tva_content_course_title'       => 'course_title',
		'tva_content_post_summary'       => 'post_summary',
		'tva_content_difficulty_name'    => 'difficulty',
		'tva_content_course_type'        => 'course_type',
		'tva_content_course_type_icon'   => 'course_type_icon',
		'tva_content_course_progress'    => 'course_progress',
		'tva_content_course_topic_title' => 'course_topic_title',
		'tva_content_course_topic_icon'  => 'course_topic_icon',
		'tva_content_course_label_title' => 'course_label_title',
	];

	/**
	 * Strings that is shown if the shortcode is placed in a non course context
	 * Ex: save a shortcode from a lesson page and place it into a page
	 *
	 * @var string
	 */
	private $could_not_determine_course = 'Could not determine course';

	/**
	 * Shortcodes constructor.
	 */
	public function __construct() {
		foreach ( $this->shortcodes as $shortcode => $function ) {
			add_shortcode( $shortcode, [ $this, $function ] );
		}
	}

	/**
	 * Apprentice Content Post Title Shortcode callback
	 *
	 * @param array  $attr
	 * @param string $content
	 *
	 * @return string
	 */
	public function post_title( $attr = [], $content = '' ) {

		if ( empty( tcb_tva_visual_builder()->get_active_course() ) ) {
			/**
			 * In case this shortcode ends up to be on a non course context content, we output an error string
			 * we need to be inside a course context content so the shortcode can render properly
			 */
			return $this->could_not_determine_course;
		}

		return TVD_Global_Shortcodes::maybe_link_wrap( tcb_tva_visual_builder()->get_title(), $attr );
	}

	/**
	 * Apprentice Content Course Title Shortcode callback
	 *
	 * @param array  $attr
	 * @param string $content
	 *
	 * @return string
	 */
	public function course_title( $attr = [], $content = '' ) {

		if ( empty( tcb_tva_visual_builder()->get_active_course() ) ) {
			/**
			 * In case this shortcode ends up to be on a non course context content, we output an error string
			 * we need to be inside a course context content so the shortcode can render properly
			 */
			return $this->could_not_determine_course;
		}

		$course_title = tcb_tva_visual_builder()->get_active_course()->name;

		if ( ! empty( $attr['link'] ) ) {

			$attributes = array_filter( [
				'href'     => tcb_tva_visual_builder()->get_active_course()->get_link(),
				'target'   => ! empty( $attr['target'] ) ? '_blank' : '',
				'rel'      => ! empty( $attr['rel'] ) ? 'nofollow' : '',
				'data-css' => ! empty( $attr['link-css-attr'] ) ? 'link-css-attr' : '',
			], 'strlen' );

			$course_title = TCB_Utils::wrap_content( $course_title, 'a', '', array(), $attributes );
		} else {
			$course_title = TVD_Global_Shortcodes::maybe_link_wrap( $course_title, $attr );
		}

		return $course_title;
	}

	/**
	 * Apprentice Content Post Summary Shortcode callback
	 *
	 * @param array  $attr
	 * @param string $content
	 *
	 * @return string
	 */
	public function post_summary( $attr = [], $content = '' ) {

		if ( empty( tcb_tva_visual_builder()->get_active_course() ) ) {
			/**
			 * In case this shortcode ends up to be on a non course context content, we output an error string
			 * we need to be inside a course context content so the shortcode can render properly
			 */
			return $this->could_not_determine_course;
		}

		$summary = nl2br( tcb_tva_visual_builder()->get_summary() );

		return TVD_Global_Shortcodes::maybe_link_wrap( $summary, $attr );
	}

	/**
	 * Apprentice Content Difficulty Shortcode callback
	 *
	 * @param array  $attr
	 * @param string $content
	 *
	 * @return string
	 */
	public function difficulty( $attr = [], $content = '' ) {

		if ( empty( tcb_tva_visual_builder()->get_active_course() ) ) {
			/**
			 * In case this shortcode ends up to be on a non course context content, we output an error string
			 * we need to be inside a course context content so the shortcode can render properly
			 */
			return $this->could_not_determine_course;
		}

		return TVD_Global_Shortcodes::maybe_link_wrap( tcb_tva_visual_builder()->get_difficulty_name(), $attr );
	}

	/**
	 * Apprentice Course Type Shortcode Callback
	 *
	 * @param array  $attr
	 * @param string $content
	 *
	 * @return string
	 */
	public function course_type( $attr = [], $content = '' ) {

		if ( empty( tcb_tva_visual_builder()->get_active_course() ) ) {
			/**
			 * In case this shortcode ends up to be on a non course context content, we output an error string
			 * we need to be inside a course context content so the shortcode can render properly
			 */
			return $this->could_not_determine_course;
		}

		return TVD_Global_Shortcodes::maybe_link_wrap( tcb_tva_visual_builder()->get_course_type(), $attr );
	}

	/**
	 * Apprentice Course Type Icon Shortcode Callback
	 *
	 * @param array  $attr
	 * @param string $content
	 *
	 * @return string
	 */
	public function course_type_icon( $attr = [], $content = '' ) {

		if ( empty( tcb_tva_visual_builder()->get_active_course() ) ) {
			/**
			 * In case this shortcode ends up to be on a non course context content, we output an error string
			 * we need to be inside a course context content so the shortcode can render properly
			 */
			return $this->could_not_determine_course;
		}

		return tcb_tva_visual_builder()->get_course_type_icon();
	}

	/**
	 * Apprentice Course Progress Callback
	 *
	 * @param array  $attr
	 * @param string $content
	 *
	 * @return string
	 */
	public function course_progress( $attr = [], $content = '' ) {

		if ( empty( tcb_tva_visual_builder()->get_active_course() ) ) {
			/**
			 * In case this shortcode ends up to be on a non course context content, we output an error string
			 * we need to be inside a course context content so the shortcode can render properly
			 */
			return $this->could_not_determine_course;
		}

		return TVD_Global_Shortcodes::maybe_link_wrap( tcb_tva_visual_builder()->get_course_progress(), $attr );
	}

	/**
	 * Apprentice Course Topic title callback
	 *
	 * @param array  $attr
	 * @param string $content
	 *
	 * @return string
	 */
	public function course_topic_title( $attr = [], $content = '' ) {

		if ( empty( tcb_tva_visual_builder()->get_active_course() ) ) {
			/**
			 * In case this shortcode ends up to be on a non course context content, we output an error string
			 * we need to be inside a course context content so the shortcode can render properly
			 */
			return $this->could_not_determine_course;
		}

		return TVD_Global_Shortcodes::maybe_link_wrap( tcb_tva_visual_builder()->get_course_topic_title(), $attr );
	}

	/**
	 * Course Topic Icon Element shortcode callback
	 *
	 * @param array  $attr
	 * @param string $content
	 *
	 * @return string
	 */
	public function course_topic_icon( $attr = array(), $content = '' ) {

		if ( empty( tcb_tva_visual_builder()->get_active_course() ) ) {
			/**
			 * In case this shortcode ends up to be on a non course context content, we output an error string
			 * we need to be inside a course context content so the shortcode can render properly
			 */
			return $this->could_not_determine_course;
		}

		$icon = tcb_tva_visual_builder()->get_course_topic_icon();

		if ( strpos( $icon, '<svg' ) !== false ) {
			$color = tcb_tva_visual_builder()->get_active_course_topic()->overview_icon_color;
			$html  = str_replace( '<svg', '<svg style="fill:' . $color . ';color:' . $color . ';"', $icon );
		} elseif ( wp_http_validate_url( $icon ) ) { //Returns false if not a valid URL or true if is valid URL
			$html = '<div class="tva-course-topic-icon-bg" style="background-image: url(' . $icon . ');"></div>';
		} else {
			$html = $icon;
		}

		return $html;
	}

	/**
	 * Course Label Title shortcode implementation
	 *
	 * @param array  $attr
	 * @param string $content
	 *
	 * @return string
	 */
	public function course_label_title( $attr = array(), $content = '' ) {

		if ( empty( tcb_tva_visual_builder()->get_active_course() ) ) {
			/**
			 * In case this shortcode ends up to be on a non course context content, we output an error string
			 * we need to be inside a course context content so the shortcode can render properly
			 */
			return $this->could_not_determine_course;
		}

		$label_data = tcb_tva_visual_builder()->get_course_label();

		if ( ( ! empty( $label_data['default_label'] ) || $label_data['ID'] < 0 ) && ! Main::$is_editor_page ) {
			return '';
		}

		if ( isset( $label_data['ID'] ) && $label_data['ID'] === 'access_about_to_expire' ) {
			$label_data['title'] = str_replace( '[days]', Base::get_days_until_expiration( get_current_user_id(), tcb_tva_visual_builder()->get_active_course()->get_product() ), $label_data['title'] );
		}

		$is_tar_element = ! empty( $attr['tar-element'] );

		if ( $is_tar_element ) {
			$shortcodeContentHTML = '<span class="thrive-shortcode-content" data-shortcode="tva_content_course_label_title" data-shortcode-name="Course Label" contenteditable="false" data-extra_key="">' . $label_data['title'] . '</span>';
			$content              = '<div class="thrv_wrapper thrv_text_element"><p><span class="thrive-inline-shortcode" contenteditable="false">' . $shortcodeContentHTML . '</span></p></div>';

			$attributes = array(
				'data-css' => $attr['css'],
			);

			$return = TCB_Utils::wrap_content( $content, 'div', '', [ 'thrv_wrapper', 'tva-course-label-title' ], $attributes );
		} else {
			$return = TVD_Global_Shortcodes::maybe_link_wrap( $label_data['title'], $attr );
		}

		return $return;
	}

	/**
	 * Return the shortcode keys
	 *
	 * @return array
	 */
	public function get_shortcodes() {
		return array_keys( $this->shortcodes );
	}
}
