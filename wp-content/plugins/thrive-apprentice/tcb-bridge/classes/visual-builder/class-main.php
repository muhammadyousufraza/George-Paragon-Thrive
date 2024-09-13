<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

namespace TVA\Architect\Visual_Builder;

use TCB_Post_List_Author_Image;
use TCB_Post_List_Featured_Image;
use TVA_Access_Restriction;
use TVA_Const;
use TVA_Course_V2;
use TVA_Dynamic_Labels;
use TVA_Lesson;
use TVA_Module;
use TVA_Post;
use TVA_Topic;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}


/**
 * Class Main
 *
 * @package  TVA\Architect\Visual_Builder
 * @project  : thrive-apprentice
 */
class Main {

	/**
	 * @var Main
	 */
	private static $instance;

	/**
	 * @var Shortcodes
	 */
	private $class_shortcodes;

	/**
	 * @var TVA_Lesson|TVA_Module|null
	 */
	private $active_object;

	/**
	 * Active object ancestors
	 *
	 * @var int[]
	 */
	private $active_object_ancestors;

	/**
	 * @var TVA_Course_V2|null
	 */
	private $active_course;

	/**
	 * @var TVA_Access_Restriction
	 */
	private $access_restriction;

	/**
	 * @var int
	 */
	private $active_course_author_id;

	/**
	 * @var TVA_Topic|null
	 */
	private $active_course_topic;

	/**
	 * @var bool
	 */
	public static $is_editor_page = false;

	/**
	 * Singleton implementation for Main
	 *
	 * @return Main
	 */
	public static function get_instance() {
		if ( self::$instance === null ) {
			self::$instance = new self();
		}

		static::$is_editor_page = is_editor_page_raw( true );

		return self::$instance;
	}

	/**
	 * Main constructor.
	 */
	private function __construct() {
		$this->class_shortcodes = new Shortcodes();

		new Hooks();
	}

	/**
	 * @param TVA_Course_V2 $active_course
	 */
	public function set_active_course( $active_course ) {
		$this->active_course = $active_course;

		$this->access_restriction = tva_access_restriction_settings( $active_course->get_product_term() );

		return $this;
	}

	public function set_active_object( $active_object ) {
		$this->active_object = $active_object;

		return $this;
	}

	/**
	 * @return TVA_Lesson|TVA_Module|null
	 */
	public function get_active_object() {
		return $this->active_object;
	}

	/**
	 * Returns the active object ancestors
	 * Adds cache on class level
	 *
	 * @return int[]
	 */
	public function get_active_object_ancestors() {

		if ( ! isset( $this->active_object_ancestors ) ) {
			$this->active_object_ancestors = get_post_ancestors( $this->active_object->get_the_post() );
		}

		return $this->active_object_ancestors;
	}

	/**
	 * Returns the cover image depending on the context type
	 *
	 * if the context cover image doesn't exist it returns the cover image from parent or a placeholder
	 *
	 * @return string
	 */
	public function get_cover_image() {

		if ( empty( $this->active_course ) ) {
			return '';
		}

		$cover_image = $this->active_object instanceof TVA_Post ? $this->active_object->inherit_cover_image() : $this->active_course->cover_image;

		return empty( $cover_image ) ? TCB_Post_List_Featured_Image::get_default_url() : $cover_image;
	}

	/**
	 * @return string
	 */
	public function get_author_image() {
		$author_image = $this->active_course->get_author()->get_avatar();

		return empty( $author_image ) ? TCB_Post_List_Author_Image::get_default_url() : $author_image;
	}

	/**
	 * Returns the Apprentice Content Title
	 *
	 * @return string
	 */
	public function get_title() {
		if ( isset( $this->active_object ) ) {
			$title = $this->active_object->post_title;

			if ( ! static::$is_editor_page && ! tva_access_manager()->has_access() ) {
				if ( tva_access_manager()->is_object_locked( $this->active_object->get_the_post() ) && tva_access_manager()->has_access_to_object( $this->active_object->get_the_post() ) ) {
					$title = $this->access_restriction->the_title( '', '', false, 'locked' );
				} else {
					$title = $this->access_restriction->the_title( '', '', false );
				}
			}
		} else {
			$title = $this->active_course->name;
		}

		/**
		 * Allow others to modify the visual builder title
		 * Used in completed post - to apply the access restriction settings
		 */
		return apply_filters( 'tva_visual_builder_get_title', strip_tags( $title ), $this->active_course, $this->active_object );
	}

	/**
	 * Returns the Apprentice Content Summary
	 *
	 * @return string
	 */
	public function get_summary() {
		if ( isset( $this->active_object ) ) {
			$summary = $this->active_object->post_excerpt;
		} else {
			$summary = $this->active_course->excerpt;
		}

		return strip_tags( $summary );
	}

	/**
	 * * Returns the Apprentice Content Difficulty
	 *
	 * @return string
	 */
	public function get_difficulty_name() {
		return $this->active_course->get_difficulty()->name;
	}

	/**
	 * Course Type label
	 *
	 * @return string
	 */
	public function get_course_type() {
		return $this->active_course->type_label;
	}

	/**
	 * Returns the course type icon string
	 *
	 * TODO: unify the course list function with this one
	 *
	 * @return string
	 */
	public function get_course_type_icon() {
		$file      = 'tcb-bridge/assets/fonts/course-type-icons/' . str_replace( '_', '-', $this->active_course->type . '.svg' );
		$file_path = TVA_Const::plugin_path( $file );

		if ( ! is_file( $file_path ) ) {
			return '';
		}

		$title  = '';
		$labels = TVA_Dynamic_Labels::get( 'course_labels' );
		if ( ! empty( $labels[ $this->active_course->type ]['title'] ) ) {
			$title = $labels[ $this->active_course->type ]['title'];
		}

		$icon = tva_get_file_contents( $file, [ 'title' => $title ] );

		return str_replace( '<svg', '<svg class="tcb-icon"', trim( $icon ) );
	}


	/**
	 * Returns the course topic icon
	 *
	 * @return string
	 */
	public function get_course_topic_icon() {
		$icon_type  = $this->get_active_course_topic()->icon_type;
		$topic_icon = $this->get_active_course_topic()->$icon_type;
		if ( empty( $topic_icon ) ) {
			$defaults   = TVA_Topic::get_defaults();
			$topic_icon = $defaults[0]->svg_icon;
		}

		return trim( $topic_icon );
	}

	/**
	 * Returns the course label data
	 *
	 * TODO: make a cache here
	 *
	 * @return array
	 */
	public function get_course_label() {
		return $this->active_course->get_label_data();
	}

	/**
	 * Returns the course progress label
	 *
	 * @return string
	 */
	public function get_course_progress() {
		return tva_customer()->get_course_progress_label( $this->active_course );
	}

	/**
	 * Returns the course topic title
	 *
	 * @return string
	 */
	public function get_course_topic_title() {
		/**
		 * @var TVA_Topic
		 */
		$topic = $this->active_course->get_topic();

		return trim( $topic->title );
	}

	/**
	 * Returns the active course of an apprentice content or null
	 *
	 * @return TVA_Course_V2|null
	 */
	public function get_active_course() {
		return $this->active_course;
	}

	/**
	 * Returns the Active Course Author ID
	 *
	 * @return int
	 */
	public function get_active_course_author_id() {
		if ( empty( $this->active_course_author_id ) ) {
			$this->active_course_author_id = $this->active_course->get_author()->get_user()->ID;
		}

		return $this->active_course_author_id;
	}

	/**
	 * Returns the active course topic
	 *
	 * @return TVA_Topic|null
	 */
	public function get_active_course_topic() {
		if ( empty( $this->active_course_topic ) ) {
			$this->active_course_topic = $this->active_course->get_topic();
		}

		return $this->active_course_topic;
	}

	/**
	 * Name of the Visual Editor Elements Category
	 *
	 * @return string
	 */
	public function get_elements_category() {
		return 'Thrive Apprentice';
	}

	/**
	 * Return the shortcode list needed to render the Course
	 *
	 * @return array
	 */
	public function get_shortcodes() {
		return $this->class_shortcodes->get_shortcodes();
	}
}

/**
 * Returns the instance of the visual builder class
 *
 * @return Main
 */
function tcb_tva_visual_builder() {
	return Main::get_instance();
}
