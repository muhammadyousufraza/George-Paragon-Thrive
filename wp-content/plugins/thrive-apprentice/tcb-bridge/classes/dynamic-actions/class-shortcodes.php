<?php

namespace TVA\Architect\Dynamic_Actions;

use TCB_Utils;
use TVA\TTB\Apprentice_Wizard;
use TVA_Dynamic_Labels;
use TVA_Post;
use TVA_Product;
use TVD_Global_Shortcodes;

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Handles dynamic actions shortcodes
 *
 * Class Shortcodes
 *
 * @package TVA\Architect\Dynamic_Actions
 */
class Shortcodes {

	/**
	 * @var string[]
	 */
	private static $_shortcodes = array(
		'tva_dynamic_actions_next_lesson_text'           => 'next_lesson_text',
		'tva_dynamic_actions_previous_lesson_text'       => 'prev_lesson_text',
		'tva_dynamic_actions_call_to_action_text'        => 'call_to_action_text',
		'tva_dynamic_actions_buy_now_text'               => 'buy_now_text',
		'tva_dynamic_actions_mark_as_complete_text'      => 'mark_as_complete_text',
		'tva_dynamic_actions_mark_as_complete_next_text' => 'mark_as_complete_next_text',
		'tva_dynamic_actions_link'                       => 'dynamic_action_link',
		'tva_content_dynamic_element'                    => 'dynamic_element',
		'tva_dynamic_actions_module_count_with_label'    => 'module_count_with_label',
		'tva_dynamic_actions_chapter_count_with_label'   => 'chapter_count_with_label',
		'tva_dynamic_actions_lesson_count_with_label'    => 'lesson_count_with_label',
		'tva_dynamic_actions_resources_label'            => 'resources_label',
		'tva_dynamic_actions_resources_download_label'   => 'resources_download_label',
		'tva_dynamic_actions_resources_open_label'       => 'resources_open_label',
		'tva_dynamic_actions_download_certificate_text'  => 'download_certificate_text',
		'tva_dynamic_actions_completion_page_text'       => 'completion_page_text',

	);

	private static $_dynamic_shortcodes = array(
		'tva_dynamic_actions_%s_progress'                => '%s_progress',
		'tva_dynamic_actions_%s_count_lessons'           => '%s_count_items',
		'tva_dynamic_actions_%s_count_lessons_completed' => '%s_count_items_completed',
	);
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
		foreach ( array( 'course', 'module', 'chapter' ) as $type ) {
			foreach ( self::$_dynamic_shortcodes as $shortcode => $function ) {
				self::$_shortcodes[ sprintf( $shortcode, $type ) ] = sprintf( $function, $type );
			}
		}

		foreach ( self::$_shortcodes as $shortcode => $function ) {
			add_shortcode( $shortcode, array( $this, $function ) );
		}
	}

	/**
	 * Return the shortcode keys
	 *
	 * @return array
	 */
	public static function get() {
		return array_keys( self::$_shortcodes );
	}

	/**
	 * @param array $attr
	 *
	 * @return string
	 */
	public function dynamic_action_link( $attr = array() ) {
		$url = 'javascript:void(0);';

		if ( ! empty( $attr['id'] ) && method_exists( tcb_tva_dynamic_actions(), 'get_' . $attr['id'] . '_link' ) ) {

			if ( empty( tcb_tva_dynamic_actions()->get_active_course() ) && ! in_array( $attr['id'], [ 'index_page' ] ) ) {
				/**
				 * In case someone saves the apprentice course based shortcode and drops it into a page
				 */
				return $url;
			}

			$method = 'get_' . $attr['id'] . '_link';

			$url = tcb_tva_dynamic_actions()->$method();
		}

		return $url;
	}

	/**
	 * @param array  $attr
	 * @param string $content
	 *
	 * @return string
	 */
	public function next_lesson_text( $attr = array() ) {

		if ( empty( tcb_tva_dynamic_actions()->get_active_course() ) ) {
			/**
			 * In case someone saves the apprentice course based shortcode and drops it into a page
			 */
			return $this->could_not_determine_course;
		}

		$title = tcb_tva_dynamic_actions()->get_next_item_text();

		if ( ! empty( $attr['link'] ) ) {
			$attributes = array(
				'href' => tcb_tva_dynamic_actions()->get_next_lesson_link(),
			);

			if ( ! empty( $attr['link-css-attr'] ) ) {
				$attributes['data-css'] = $attr['link-css-attr'];
			}

			$title = TCB_Utils::wrap_content( $title, 'a', '', array(), $attributes );
		} else {
			$title = TVD_Global_Shortcodes::maybe_link_wrap( $title, $attr );
		}

		return $title;
	}

	/**
	 * @param array $attr
	 *
	 * @return string
	 */
	public function prev_lesson_text( $attr = array() ) {

		if ( empty( tcb_tva_dynamic_actions()->get_active_course() ) ) {
			/**
			 * In case someone saves the apprentice course based shortcode and drops it into a page
			 */
			return $this->could_not_determine_course;
		}

		$title = tcb_tva_dynamic_actions()->get_prev_lesson_text();

		if ( ! empty( $attr['link'] ) ) {
			$attributes = array(
				'href' => tcb_tva_dynamic_actions()->get_previous_lesson_link(),
			);

			if ( ! empty( $attr['link-css-attr'] ) ) {
				$attributes['data-css'] = $attr['link-css-attr'];
			}

			$title = TCB_Utils::wrap_content( $title, 'a', '', array(), $attributes );
		} else {
			$title = TVD_Global_Shortcodes::maybe_link_wrap( $title, $attr );
		}

		return $title;
	}


	public function buy_now_text( $attr = [] ) {
		$text = TVA_Dynamic_Labels::get_cta_label( 'buy_now' );

		if ( ! empty( $attr['link'] ) ) {
			$attributes = array(
				'href' => tcb_tva_dynamic_actions()->get_call_to_action_link(),
			);

			if ( ! empty( $attr['link-css-attr'] ) ) {
				$attributes['data-css'] = $attr['link-css-attr'];
			}

			$text = TCB_Utils::wrap_content( $text, 'a', '', array(), $attributes );
		} else {
			$text = TVD_Global_Shortcodes::maybe_link_wrap( $text, $attr );
		}

		return $text;
	}

	/**
	 * @param array $attr
	 *
	 * @return string
	 */
	public function call_to_action_text( $attr = array() ) {
		if ( empty( tcb_tva_dynamic_actions()->get_active_course() ) ) {
			/**
			 * In case someone saves the apprentice course based shortcode and drops it into a page
			 */
			return $this->could_not_determine_course;
		}

		$text = tcb_tva_dynamic_actions()->get_call_to_action_text();

		if ( ! empty( $attr['link'] ) ) {
			$attributes = array(
				'href' => tcb_tva_dynamic_actions()->get_call_to_action_link(),
			);

			if ( ! empty( $attr['link-css-attr'] ) ) {
				$attributes['data-css'] = $attr['link-css-attr'];
			}

			$text = TCB_Utils::wrap_content( $text, 'a', '', array(), $attributes );
		} else {
			$text = TVD_Global_Shortcodes::maybe_link_wrap( $text, $attr );
		}

		return $text;
	}

	/**
	 * @param array $attr
	 *
	 * @return mixed|string
	 */
	public function mark_as_complete_text( $attr = array() ) {

		if ( empty( tcb_tva_dynamic_actions()->get_active_course() ) ) {
			/**
			 * In case someone saves the apprentice course based shortcode and drops it into a page
			 */
			return $this->could_not_determine_course;
		}

		if ( ! Main::$is_editor_page && tcb_tva_dynamic_actions()->get_active_item()->is_completed() ) {
			/**
			 * Mark as complete text must be hidden if the user has completed the lesson
			 */
			return '';
		}

		$text = tcb_tva_dynamic_actions()->get_mark_as_complete_text();

		if ( ! empty( $attr['link'] ) ) {
			$attributes = array(
				'href' => 'javascript:void(0);',
			);

			if ( ! empty( $attr['link-css-attr'] ) ) {
				$attributes['data-css'] = $attr['link-css-attr'];
			}

			$text = TCB_Utils::wrap_content( $text, 'a', '', array(), $attributes );
		}

		return $text;
	}

	/**
	 * @param array $attr
	 *
	 * @return mixed|string
	 */
	public function mark_as_complete_next_text( $attr = array() ) {

		if ( empty( tcb_tva_dynamic_actions()->get_active_course() ) ) {
			/**
			 * In case someone saves the apprentice course based shortcode and drops it into a page
			 */
			return $this->could_not_determine_course;
		}

		$text = tcb_tva_dynamic_actions()->get_mark_as_complete_next_text();

		if ( ! empty( $attr['link'] ) ) {
			$attributes = array(
				'href' => 'javascript:void(0);',
			);

			if ( ! empty( $attr['link-css-attr'] ) ) {
				$attributes['data-css'] = $attr['link-css-attr'];
			}

			$text = TCB_Utils::wrap_content( $text, 'a', '', array(), $attributes );
		}

		return $text;
	}

	/**
	 * Callback for dynamic apprentice elements
	 * Such as Call to action button, next and previous lesson button
	 *
	 * @param array  $attr
	 * @param string $content
	 *
	 * @return string
	 */
	public function dynamic_element( $attr = array(), $content = '' ) {

		if ( empty( tcb_tva_dynamic_actions()->get_active_course() ) ) {
			/**
			 * In case someone saves the apprentice course based shortcode and drops it into a page
			 */
			return $this->could_not_determine_course;
		}

		$has_access           = TVA_Product::has_access();
		$not_internal_request = ! Main::$is_editor_page && ! Apprentice_Wizard::is_during_preview();

		$should_hide = empty( $attr['tva-dynamic-element'] );
		$should_hide = $should_hide || ( ! Main::$is_editor_page && $attr['tva-dynamic-element'] === 'tva_element_previous_lesson' && tcb_tva_dynamic_actions()->is_first_visible_item() );
		$should_hide = $should_hide || ( ! Main::$is_editor_page && in_array( $attr['tva-dynamic-element'], [ 'tva_element_mark_complete', 'tva_element_download_certificate', 'tva_element_completion_page' ] ) && ! tva_access_manager()->has_access() );
		$should_hide = $should_hide || ( $not_internal_request && $attr['tva-dynamic-element'] === 'tva_element_download_certificate' && ( ! is_user_logged_in() || ! tcb_tva_dynamic_actions()->get_active_course()->has_certificate() || ( ! $has_access && ! tva_customer()->has_completed_course( tcb_tva_dynamic_actions()->get_active_course() ) ) ) );
		$should_hide = $should_hide || ( $not_internal_request && $attr['tva-dynamic-element'] === 'tva_element_completion_page' && ( ! is_user_logged_in() || ! tcb_tva_dynamic_actions()->get_active_course()->has_completed_post() || ( ! $has_access && ! tva_customer()->has_completed_course( tcb_tva_dynamic_actions()->get_active_course() ) ) ) );

		if ( $should_hide ) {
			return '';
		}

		$data  = array();
		$id    = empty( $attr['id'] ) ? '' : $attr['id'];
		$class = empty( $attr['class'] ) ? '' : $attr['class'];

		$should_disable = ! Main::$is_editor_page && ! empty( $attr['tva-dynamic-element'] ) && $attr['tva-dynamic-element'] === 'tva_element_mark_complete' && tcb_tva_dynamic_actions()->get_active_object() instanceof TVA_Post && ! tcb_tva_dynamic_actions()->get_active_object()->can_be_marked_as_completed();

		if ( $should_disable ) {
			$class .= ' tva-disabled-mark-as-complete';
		}

		foreach ( $attr as $key => $value ) {
			if ( $key !== 'class' && $key !== 'id' ) {
				$data[ 'data-' . $key ] = esc_attr( $value );
			}
		}

		$content = strtr( $content, array(
			'&#091;' => '[',
			'&#093;' => ']',
			'&#91;'  => '[',
			'&#93;'  => ']',
		) );

		return TCB_Utils::wrap_content( do_shortcode( $content ), 'div', $id, $class, $data );
	}

	public function module_count_with_label( $attr = array(), $content = '' ) {
		if ( empty( tcb_tva_dynamic_actions()->get_active_course() ) ) {
			/**
			 * In case someone saves the apprentice course based shortcode and drops it into a page
			 */
			return $this->could_not_determine_course;
		}

		$label = tcb_tva_dynamic_actions()->get_children_count_with_label( tcb_tva_dynamic_actions()->get_active_course()->get_published_modules() );

		return TVD_Global_Shortcodes::maybe_link_wrap( $label, $attr );
	}

	/**
	 * @param array $attr
	 *
	 * @return string
	 */
	public function chapter_count_with_label( $attr = array() ) {
		if ( empty( tcb_tva_dynamic_actions()->get_active_course() ) ) {
			/**
			 * In case someone saves the apprentice course based shortcode and drops it into a page
			 */
			return $this->could_not_determine_course;
		}

		$label = tcb_tva_dynamic_actions()->get_children_count_with_label( tcb_tva_dynamic_actions()->get_active_course()->get_published_chapters() );

		return TVD_Global_Shortcodes::maybe_link_wrap( $label, $attr );
	}

	/**
	 * @param array $attr
	 *
	 * @return string
	 */
	public function lesson_count_with_label( $attr = array() ) {
		if ( empty( tcb_tva_dynamic_actions()->get_active_course() ) ) {
			/**
			 * In case someone saves the apprentice course based shortcode and drops it into a page
			 */
			return $this->could_not_determine_course;
		}

		$label = tcb_tva_dynamic_actions()->get_children_count_with_label( tcb_tva_dynamic_actions()->get_active_course()->get_published_items() );

		return TVD_Global_Shortcodes::maybe_link_wrap( $label, $attr );
	}

	/**
	 * @param array $attr
	 *
	 * @return string
	 */
	public function course_progress( $attr = array() ) {
		if ( empty( tcb_tva_dynamic_actions()->get_active_course() ) ) {
			/**
			 * In case someone saves the apprentice course based shortcode and drops it into a page
			 */
			return $this->could_not_determine_course;
		}

		return TVD_Global_Shortcodes::maybe_link_wrap( tcb_tva_dynamic_actions()->get_progress_by_type( 'course' ), $attr );
	}

	public function course_count_items( $attr = array() ) {
		if ( empty( tcb_tva_dynamic_actions()->get_active_course() ) ) {
			/**
			 * In case someone saves the apprentice course based shortcode and drops it into a page
			 */
			return $this->could_not_determine_course;
		}

		return TVD_Global_Shortcodes::maybe_link_wrap( tcb_tva_dynamic_actions()->course_count_items, $attr );
	}

	public function course_count_items_completed( $attr = array() ) {
		if ( empty( tcb_tva_dynamic_actions()->get_active_course() ) ) {
			/**
			 * In case someone saves the apprentice course based shortcode and drops it into a page
			 */
			return $this->could_not_determine_course;
		}

		return TVD_Global_Shortcodes::maybe_link_wrap( tcb_tva_dynamic_actions()->course_count_items_completed, $attr );
	}

	public function module_progress( $attr = array() ) {
		if ( empty( tcb_tva_dynamic_actions()->get_active_course() ) ) {
			/**
			 * In case someone saves the apprentice course based shortcode and drops it into a page
			 */
			return $this->could_not_determine_course;
		}

		return TVD_Global_Shortcodes::maybe_link_wrap( tcb_tva_dynamic_actions()->get_progress_by_type( 'module' ), $attr );
	}

	public function module_count_items( $attr = array() ) {
		if ( empty( tcb_tva_dynamic_actions()->get_active_course() ) ) {
			/**
			 * In case someone saves the apprentice course based shortcode and drops it into a page
			 */
			return $this->could_not_determine_course;
		}

		return TVD_Global_Shortcodes::maybe_link_wrap( tcb_tva_dynamic_actions()->module_count_items, $attr );
	}

	public function module_count_items_completed( $attr = array() ) {
		if ( empty( tcb_tva_dynamic_actions()->get_active_course() ) ) {
			/**
			 * In case someone saves the apprentice course based shortcode and drops it into a page
			 */
			return $this->could_not_determine_course;
		}

		return TVD_Global_Shortcodes::maybe_link_wrap( tcb_tva_dynamic_actions()->module_count_items_completed, $attr );
	}

	public function chapter_progress( $attr = array() ) {
		if ( empty( tcb_tva_dynamic_actions()->get_active_course() ) ) {
			/**
			 * In case someone saves the apprentice course based shortcode and drops it into a page
			 */
			return $this->could_not_determine_course;
		}

		return TVD_Global_Shortcodes::maybe_link_wrap( tcb_tva_dynamic_actions()->get_progress_by_type( 'chapter' ), $attr );
	}

	public function chapter_count_items( $attr = array() ) {
		if ( empty( tcb_tva_dynamic_actions()->get_active_course() ) ) {
			/**
			 * In case someone saves the apprentice course based shortcode and drops it into a page
			 */
			return $this->could_not_determine_course;
		}

		return TVD_Global_Shortcodes::maybe_link_wrap( tcb_tva_dynamic_actions()->chapter_count_items, $attr );
	}

	public function chapter_count_items_completed( $attr = array() ) {
		if ( empty( tcb_tva_dynamic_actions()->get_active_course() ) ) {
			/**
			 * In case someone saves the apprentice course based shortcode and drops it into a page
			 */
			return $this->could_not_determine_course;
		}

		return TVD_Global_Shortcodes::maybe_link_wrap( tcb_tva_dynamic_actions()->chapter_count_items_completed, $attr );
	}

	public function resources_label( $attr = array() ) {
		return TVD_Global_Shortcodes::maybe_link_wrap( tcb_tva_dynamic_actions()->get_course_structure_label( 'course_resources', 'plural' ), $attr );
	}

	public function resources_download_label( $attr ) {
		return TVD_Global_Shortcodes::maybe_link_wrap( tcb_tva_dynamic_actions()->get_course_structure_label( 'resources_download', 'singular' ), $attr );
	}

	public function resources_open_label( $attr ) {
		return TVD_Global_Shortcodes::maybe_link_wrap( tcb_tva_dynamic_actions()->get_course_structure_label( 'resources_open', 'singular' ), $attr );
	}

	public function download_certificate_text( $attr ) {
		return TVD_Global_Shortcodes::maybe_link_wrap( tcb_tva_dynamic_actions()->get_course_structure_label( 'certificate_download', 'singular' ), $attr );
	}

	public function completion_page_text( $attr ) {
		return TVD_Global_Shortcodes::maybe_link_wrap( tcb_tva_dynamic_actions()->get_course_nav_label( 'to_completion_page' ), $attr );
	}
}
