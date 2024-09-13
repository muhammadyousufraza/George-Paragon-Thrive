<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

namespace TVA\Architect\Course_List;

use TCB_Utils;
use TVA\Access\Expiry\Base;
use TVA_Course_V2;
use TVA_Dynamic_Labels;
use TVD_Global_Shortcodes;
use function TVA\Architect\Dynamic_Actions\tcb_tva_dynamic_actions;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Shortcodes
 *
 * @package  TVA\Architect\Course_List
 * @project  : thrive-apprentice
 */
class Shortcodes {

	/**
	 * Contains the List of Shortcodes
	 *
	 * @var array
	 */
	private $shortcodes = [
		'tva_course_list_item'                           => 'course_item',
		'tva_course_list_item_label'                     => 'course_item_label',
		'tva_course_list_dynamic_variables'              => 'dynamic_variables',
		'tva_course_list_item_name'                      => 'course_name',
		'tva_course_list_item_author_name'               => 'course_author_name',
		'tva_course_list_item_description'               => 'course_description',
		'tva_course_list_item_type'                      => 'course_type',
		'tva_course_list_item_type_icon'                 => 'course_type_icon',
		'tva_course_list_item_topic_title'               => 'course_topic_title',
		'tva_course_list_item_topic_icon'                => 'course_topic_icon',
		'tva_course_list_item_lessons_number'            => 'course_lessons_number',
		'tva_course_list_item_difficulty_name'           => 'course_difficulty',
		'tva_course_list_item_label_title'               => 'course_label_title',
		'tva_course_list_item_progress'                  => 'course_progress',
		'tva_course_list_item_progress_percentage'       => 'course_progress_percentage',
		'tva_course_list_item_action_label'              => 'course_action_label',
		'tva_course_list_item_permalink'                 => 'course_permalink',
		'tva_course_list_item_dynamic_image'             => 'course_dynamic_image',
		'tva_course_list_item_module_number_with_label'  => 'module_number_with_label',
		'tva_course_list_item_chapter_number_with_label' => 'chapter_number_with_label',
		'tva_course_list_item_lesson_number_with_label'  => 'lesson_number_with_label',
	];

	/**
	 * Shortcodes constructor.
	 */
	public function __construct() {
		foreach ( $this->shortcodes as $shortcode => $function ) {
			add_shortcode( $shortcode, [ $this, $function ] );
		}
	}

	/**
	 * Renders the Course List Item
	 *
	 * @param array  $attr
	 * @param string $content
	 *
	 * @return string
	 */
	public function course_item( $attr = [], $content = '' ) {
		$attributes = [
			'data-id'       => $this->get_active_course()->get_id(),
			'data-selector' => '.tva-course-list-item',
		];

		if ( ( ! Main::$is_editor_page && Main::$disabled_links ) || ( isset( $_REQUEST['disable_links'] ) && (int) $_REQUEST['disable_links'] === 1 ) ) {
			/**
			 * This is only for frontend if the course list has disabled the links,
			 * we add the item link as a data attribute
			 */
			$attributes['data-permalink'] = tva_get_course_url( $this->get_active_course() );
		}

		if ( isset( $attr['tcb_hover_state_parent'] ) ) {
			$attributes['tcb_hover_state_parent'] = '';
		}

		if ( ! empty( $attr['tcb-events'] ) ) {
			$attributes['data-tcb-events'] = str_replace( [ '|{|', '|}|' ], [ '[', ']' ], $attr['tcb-events'] );
		}

		$classes = [ 'tva-course-list-item', 'thrv_wrapper', 'thrive-animated-item' ];

		if ( ! empty( $attr['class'] ) ) {
			$classes[] = $attr['class'];
		}

		$content = $this->prepare_content( $content );

		$content = do_shortcode( $content );

		return TCB_Utils::wrap_content( trim( $content ), 'div', '', $classes, $attributes );
	}

	/**
	 * Course List Item - Label Element
	 *
	 * @param array  $attr
	 * @param string $content
	 *
	 * @return string
	 */
	public function course_item_label( $attr = array(), $content = '' ) {
		$label_data = $this->get_active_course()->get_label_data();

		if ( ( ! empty( $label_data['default_label'] ) || $label_data['ID'] < 0 ) && ! Main::$is_editor_page ) {
			return '';
		}

		if ( $label_data['ID'] === 'access_about_to_expire' ) {
			$label_data['title'] = str_replace( '[days]', Base::get_days_until_expiration( get_current_user_id(), $this->get_active_course()->get_product() ), $label_data['title'] );
		}

		$shortcodeContentHTML = '<span class="thrive-shortcode-content" data-shortcode="tva_course_list_item_label_title" data-shortcode-name="Course Label" contenteditable="false" data-extra_key="">' . $label_data['title'] . '</span>';
		$content              = '<div class="thrv_wrapper thrv_text_element"><p><span class="thrive-inline-shortcode" contenteditable="false">' . $shortcodeContentHTML . '</span></p></div>';

		$attributes = array(
			'data-css' => $attr['css'],
		);

		return TCB_Utils::wrap_content( $content, 'div', '', array( 'thrv_wrapper', 'tva-course-list-item-label' ), $attributes );
	}

	/**
	 * Callback for dynamic style shortcode
	 *
	 * @param array  $attr
	 * @param string $style_css
	 *
	 * @return string
	 */
	public function dynamic_variables( $attr = array(), $style_css = '' ) {
		$css = tcb_course_list_shortcode()->get_dynamic_variables( $this->get_active_course() );

		return TCB_Utils::wrap_content( $css, 'style', '', 'tva-course-list-dynamic-variables', array( 'type' => 'text/css' ) );
	}

	/**
	 * Course List Item Name shortcode callback
	 *
	 * @param array  $attr
	 * @param string $content
	 *
	 * @return string
	 */
	public function course_name( $attr = array(), $content = '' ) {
		$name = $this->get_active_course()->name;

		if ( ! empty( $attr['link'] ) ) {
			$attributes = array(
				'href' => tva_get_course_url( $this->get_active_course() ),
			);

			if ( ! empty( $attr['target'] ) ) {
				$attributes['target'] = '_blank';
			}

			if ( ! empty( $attr['rel'] ) ) {
				$attributes['rel'] = 'nofollow';
			}

			if ( ! empty( $attr['link-css-attr'] ) ) {
				$attributes['data-css'] = $attr['link-css-attr'];
			}

			$name = TCB_Utils::wrap_content( $name, 'a', '', array(), $attributes );
		} else {
			$name = TVD_Global_Shortcodes::maybe_link_wrap( $name, $attr );
		}

		return $name;
	}

	/**
	 * @param array  $attr
	 * @param string $content
	 *
	 * @return string
	 */
	public function course_author_name( $attr = array(), $content = '' ) {
		$name = $this->get_active_course()->author->get_user()->display_name;

		return TVD_Global_Shortcodes::maybe_link_wrap( $name, $attr );
	}

	/**
	 * Course List Item Description shortcode callback
	 *
	 * @param array  $attr
	 * @param string $content
	 *
	 * @return string
	 */
	public function course_description( $attr = array(), $content = '' ) {
		$description = nl2br( tcb_course_list_shortcode()->get_description( $this->get_active_course() ) );

		return TVD_Global_Shortcodes::maybe_link_wrap( $description, $attr );
	}

	/**
	 * Course List Item Type shortcode callback
	 *
	 * @param array  $attr
	 * @param string $content
	 *
	 * @return string
	 */
	public function course_type( $attr = array(), $content = '' ) {
		$type = $this->get_active_course()->type_label;

		return TVD_Global_Shortcodes::maybe_link_wrap( $type, $attr );
	}

	/**
	 *Course List Item Type icon shortcode callback
	 *
	 * @param array  $attr
	 * @param string $content
	 *
	 * @return string
	 */
	public function course_type_icon( $attr = array(), $content = '' ) {
		return tcb_course_list_shortcode()->get_type_icon( $this->get_active_course() );
	}

	/**
	 * Course List Item Topic shortcode callback
	 *
	 * @param array  $attr
	 * @param string $content
	 *
	 * @return string
	 */
	public function course_topic_title( $attr = array(), $content = '' ) {
		$topic = $this->get_active_course()->get_topic();

		return TVD_Global_Shortcodes::maybe_link_wrap( $topic->title, $attr );
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
		$topic = $this->get_active_course()->get_topic();
		$icon  = tcb_course_list_shortcode()->get_topic_icon( $topic );

		if ( strpos( $icon, '<svg' ) !== false ) {
			$color = $topic->layout_icon_color;
			$html  = str_replace( '<svg', '<svg style="fill:' . $color . ';color:' . $color . ';"', $icon );
		} elseif ( wp_http_validate_url( $icon ) ) {
			$html = '<div class="tva-course-list-item-topic-icon-bg" style="background-image: url(' . $icon . ');"></div>';
		} else {
			$html = $icon;
		}

		return $html;
	}

	/**
	 * Course List Item Lesson Number shortcode callback
	 *
	 * @param array  $attr
	 * @param string $content
	 *
	 * @return string
	 */
	public function course_lessons_number( $attr = array(), $content = '' ) {
		$args = array();
		if ( ! Main::$is_editor_page ) {
			$args['post_status'] = array( 'publish' );
		}

		$number = $this->get_active_course()->count_lessons( $args );

		return TVD_Global_Shortcodes::maybe_link_wrap( $number, $attr );
	}

	/**
	 * Course List Item Difficulty shortcode callback
	 *
	 * @param array  $attr
	 * @param string $content
	 *
	 * @return string
	 */
	public function course_difficulty( $attr = array(), $content = '' ) {
		$difficulty = $this->get_active_course()->get_difficulty()->name;

		return TVD_Global_Shortcodes::maybe_link_wrap( $difficulty, $attr );
	}

	/**
	 * Course List Item Label Title Shortcode callback
	 *
	 * @param array  $attr
	 * @param string $content
	 *
	 * @return string
	 */
	public function course_label_title( $attr = array(), $content = '' ) {
		$label_data = $this->get_active_course()->get_label_data();

		if ( ( ! empty( $label_data['default_label'] ) || $label_data['ID'] < 0 ) && ! Main::$is_editor_page ) {
			return '';
		}

		return TVD_Global_Shortcodes::maybe_link_wrap( $label_data['title'], $attr );
	}

	/**
	 * Course List Item Progress shortcode callback
	 *
	 * @param array  $attr
	 * @param string $content
	 *
	 * @return string
	 */
	public function course_progress( $attr = array(), $content = '' ) {
		$progress = tcb_course_list_shortcode()->get_progress_label( $this->get_active_course() );

		return TVD_Global_Shortcodes::maybe_link_wrap( $progress, $attr );
	}

	/**
	 * Course List Item Progress Percentage
	 *
	 * @param array  $attr
	 * @param string $content
	 *
	 * @return string
	 */
	public function course_progress_percentage( $attr = array(), $content = '' ) {
		$progress = tcb_course_list_shortcode()->get_progress_percentage( $this->get_active_course() );

		return TVD_Global_Shortcodes::maybe_link_wrap( $progress, $attr );
	}

	/**
	 * Course Action Label shortcode callback
	 *
	 * @param array  $attr
	 * @param string $content
	 *
	 * @return string
	 */
	public function course_action_label( $attr = array(), $content = '' ) {
		$label = TVA_Dynamic_Labels::get_course_cta( $this->get_active_course() );

		return TVD_Global_Shortcodes::maybe_link_wrap( $label, $attr );
	}

	/**
	 * @param array  $attr
	 * @param string $content
	 *
	 * @return string
	 */
	public function module_number_with_label( $attr = array(), $content = '' ) {
		$label = tcb_tva_dynamic_actions()->get_children_count_with_label( $this->get_active_course()->get_published_modules() );

		return TVD_Global_Shortcodes::maybe_link_wrap( $label, $attr );
	}

	/**
	 * @param array  $attr
	 * @param string $content
	 *
	 * @return string
	 */
	public function chapter_number_with_label( $attr = array(), $content = '' ) {
		$label = tcb_tva_dynamic_actions()->get_children_count_with_label( $this->get_active_course()->get_published_chapters() );

		return TVD_Global_Shortcodes::maybe_link_wrap( $label, $attr );
	}

	/**
	 * @param array  $attr
	 * @param string $content
	 *
	 * @return string
	 */
	public function lesson_number_with_label( $attr = array(), $content = '' ) {
		$label = tcb_tva_dynamic_actions()->get_children_count_with_label( $this->get_active_course()->get_published_items() );

		return TVD_Global_Shortcodes::maybe_link_wrap( $label, $attr );
	}

	/**
	 * Course List Item Permalink shortcode callback
	 *
	 * @param array  $attr
	 * @param string $content
	 * @param string $tag
	 *
	 * @return string
	 */
	public function course_permalink( $attr = array(), $content = '', $tag = '' ) {
		if ( empty( $this->get_active_course() ) ) {
			return '[' . $tag . ']';
		}

		return Main::$is_editor_page ? '#' : tva_get_course_url( $this->get_active_course() );
	}

	/**
	 * @param array  $attr
	 * @param string $content
	 * @param string $tag
	 *
	 * @return string
	 */
	public function course_dynamic_image( $attr = array(), $content = '', $tag = '' ) {
		if ( empty( $this->get_active_course() ) ) {
			return '[' . $tag . ']';
		}

		$attr = array_merge(
			array(
				'css'   => '',
				'class' => '',
				'type'  => 'featured',
			),
			$attr
		);

		$name = $this->get_active_course()->name;

		$image_url = $attr['type'] === 'featured' ? tcb_course_list_shortcode()->get_cover_image( $this->get_active_course() ) : tcb_course_list_shortcode()->get_author_image( $this->get_active_course() );

		return '<img loading="lazy" class="' . $attr['class'] . '" alt="' . $name . '" data-id="' . 0 . '" data-d-f="' . $attr['type'] . '" width="500" height="500" title="' . $name . '" src="' . $image_url . '" data-css="' . $attr['css'] . '">';
	}

	/**
	 * Return the shortcode keys
	 *
	 * @return array
	 */
	public function get_shortcodes() {
		return array_keys( $this->shortcodes );
	}

	/**
	 * Prepares the course list content before doing the shortcode rendering
	 *
	 * @param string $content
	 *
	 * @return string
	 */
	private function prepare_content( $content = '' ) {
		return $this->construct_inline_shortcodes( $content );
	}

	/**
	 * Construct the inline shortcodes after the brackets have been replace with entities
	 *
	 * Used for constructing back the dynamic links shortcodes
	 *
	 * @param string $content
	 *
	 * @return string
	 */
	private function construct_inline_shortcodes( $content = '' ) {
		$trans = array(
			'&#091;' => '[',
			'&#093;' => ']',
		);

		return strtr( $content, $trans );
	}

	/**
	 * Returns the active course from the course iteration
	 *
	 * @return TVA_Course_V2
	 */
	public function get_active_course() {

		/**
		 * @var TVA_Course_V2
		 */
		global $tva_active_course;

		return $tva_active_course;
	}
}
