<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

namespace TVA\Architect\Course;

use TCB_Utils;
use TVA\Architect\Course\Shortcodes\Shortcodes;
use TVA_Const;
use TVA_Course_Overview_Post;
use TVA_Course_V2;
use TVA_Manager;
use TVA_Post;
use TVA_Product;
use TVA_Term;
use WP_Post;
use function TVA\Architect\Visual_Builder\tcb_tva_visual_builder;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Main
 *
 * @package  TVA\Architect\Course
 * @project  : thrive-apprentice
 */
class Main {
	const IDENTIFIER = '.tva-course';

	const STATES = [ 0, 1, 2, 3, 4 ];

	/**
	 * @var Main
	 */
	private static $instance;

	/**
	 * @var TVA_Course_V2
	 */
	private $course;

	/**
	 * @var TVA_Post
	 */
	public static $parent_item;

	/**
	 * @var int Course ID
	 */
	public static $course_id;

	public static $course_attr;

	/**
	 * @var bool
	 */
	public static $is_editor_page = false;

	/**
	 * @var WP_Post
	 */
	public static $display_level;

	/**
	 * @var bool
	 */
	public static $show_assessments;

	/**
	 * Course subitem shortcodes
	 *
	 * @var
	 */
	public $module_shortcodes;
	public $chapter_shortcodes;
	public $lesson_shortcodes;

	/**
	 * @var string|null
	 */
	private $course_label;

	const COURSE_TEXT_WARNING_CLASS = 'tva-course-warning-text';

	/**
	 * @var string[]
	 */
	private $shortcodes = array(
		'tva_course'             => 'render',
		'tva_course_title'       => 'course_title',
		'tva_course_url'         => 'url',
		'tva_course_topic'       => 'course_topic',
		'tva_course_difficulty'  => 'course_difficulty',
		'tva_course_type'        => 'course_type',
		'tva_course_description' => 'course_description',
		'tva_course_begin'       => 'course_begin',
		'tva_course_end'         => 'course_end',
	);

	/**
	 * Array of shortcode dependency
	 *
	 * @var array
	 */
	private $shortcode_configuration = array(
		'module_shortcodes'  => 'TVA\Architect\Course\Shortcodes\Module_Shortcodes',
		'chapter_shortcodes' => 'TVA\Architect\Course\Shortcodes\Chapter_Shortcodes',
		'lesson_shortcodes'  => 'TVA\Architect\Course\Shortcodes\Lesson_Shortcodes',
	);

	/**
	 * Singleton implementation for TCB_Custom_Fields_Shortcode
	 *
	 * @return Main
	 */
	public static function get_instance() {
		if ( self::$instance === null ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Main constructor.
	 */
	private function __construct() {
		foreach ( $this->shortcodes as $shortcode => $function ) {
			add_shortcode( $shortcode, array( $this, $function ) );
		}

		new Hooks();

		$this->includes();
	}

	private function includes() {
		foreach ( $this->shortcode_configuration as $key => $file_name ) {
			$type       = str_replace( '_shortcodes', '', $key );
			$this->$key = new $file_name( $type );
		}
	}

	/**
	 * @param array $attr
	 * @param array $content
	 *
	 * @return string
	 */
	public function render( $attr = array(), $content = '' ) {
		$data = array();

		if ( ! empty( $attr ) ) {
			foreach ( $attr as $key => $value ) {
				if ( $key !== 'class' ) { /* we don't want data-class to persist, we process it inside get_classes() */
					$data[ 'data-' . $key ] = esc_attr( $value );
				}
			}
		}

		self::$is_editor_page   = is_editor_page_raw( true );
		self::$show_assessments = self::$is_editor_page || ( ! empty( $attr['show-assessments'] ) && (int) $attr['show-assessments'] === 1 );

		$classes = $this->get_classes( $attr );

		if ( empty( $attr['id'] ) ) {
			return $this->warning_content( 'Invalid Course ID', $classes, $data );
		}

		self::$course_id = (int) $attr['id'];

		if ( self::$course_id === - 1 ) {
			$aux = 0;

			if ( wp_doing_ajax() && ! empty( $_REQUEST['post_id'] ) ) {
				$aux = (int) $_REQUEST['post_id'];
			}

			self::$course_id = TVA_Course_V2::get_active_course_id( $aux );
		}

		$this->course = new TVA_Course_V2( self::$course_id );

		//allow only assessments
		$course_lesson_number     = $this->course->count_lessons();
		$course_assessment_number = $this->course->count_assessments();
		if ( $course_lesson_number === 0 && $course_assessment_number === 0 ) {

			/**
			 * If the lesson number is 0 we do not render the course.
			 */
			return $this->warning_content( 'No lessons or assessments are available for the selected course, please complete the setup of your course.', $classes, $data );
		}

		$this->course->load_structure();

		self::$course_attr   = $attr;
		self::$parent_item   = $this->course;
		self::$display_level = null;

		if ( ! empty( $attr['display-level'] ) && is_numeric( $attr['display-level'] ) ) {
			if ( (int) $attr['display-level'] === - 1 ) {
				$attr['display-level'] = ( wp_doing_ajax() && ! empty( $_REQUEST['post_id'] ) ) ? (int) $_REQUEST['post_id'] : get_the_ID();
			}
			$display_level_post = get_post( $attr['display-level'] );

			if ( ! in_array( $display_level_post->post_type, array( TVA_Course_Overview_Post::POST_TYPE ) ) ) {

				self::$display_level = $display_level_post;

				$children = TVA_Manager::get_children( self::$display_level );

				if (
					count( $children ) === 0 || /* if there are no children */
					( ! self::$is_editor_page && ! Utils::has_published_children( $children ) ) /* if we're not in the editor and this has no published children */
				) {
					return $this->warning_content( 'No lessons are available for the defined display level.', $classes, $data );
				}
			}
		}

		if ( ! self::$is_editor_page && ! $this->course->is_published() && false === TVA_Product::has_access() && ! tva_access_manager()->check_rules() ) {
			//For unpublished courses we return nothing if it is not in the editor page or preview the module or the user doesn't have access (e.g to work with hidde/archive courses)
			return '';
		}

		$html = $this->render_course( $attr, $this->normalize_content( $content ) );

		/**
		 * If all the content is hidden from the course hide the lesson list wrapper as well
		 */
		if ( empty( Shortcodes::$child_count_per_element ) ) {
			$data['data-hide-course'] = 1;
		}

		return TCB_Utils::wrap_content( $html, 'div', '', $classes, $data );
	}

	/**
	 * Normalize the lesson list shortcode to include other logic later developed
	 * Used to inject assessment shortcodes in the main lesson list shortcode
	 *
	 * @param string $content
	 *
	 * @return string
	 */
	public function normalize_content( $content ) {

		if ( strpos( $content, '[tva_course_assessment_state' ) === false ) {
			$content = str_replace( '[/tva_course_lesson_list]', Shortcodes::get_default_template( 'assessment-normalized' ) . '[/tva_course_lesson_list]', $content );
		}

		return $content;
	}

	/**
	 * @param array $attr
	 *
	 * @return array
	 */
	public function get_classes( $attr ) {
		$classes = array( str_replace( '.', '', static::IDENTIFIER ), 'thrv_wrapper' );

		/* hide the 'Save as Symbol' icon */
		if ( self::$is_editor_page ) {
			$classes[] = 'tcb-selector-no_save';
		}

		/* set custom classes, if they are present */
		if ( ! empty( $attr['class'] ) ) {
			$classes[] = $attr['class'];
		}

		return $classes;
	}

	/**
	 * Renders the course markup
	 *
	 * @param array  $attr
	 * @param string $content
	 *
	 * @return string
	 */
	public function render_course( $attr = array(), $content = '' ) {

		if ( empty( $content ) ) { //Here should be first time
			$content = $this->get_default_content();
		}

		return do_shortcode( $content );
	}

	/**
	 * Renders the Course Title
	 *
	 * @param array  $attr
	 * @param string $content
	 *
	 * @return string
	 */
	public function course_title( $attr = array(), $content = '' ) {

		$title = $this->course->name;

		if ( ! empty( $attr['link'] ) ) {
			$attributes = array(
				'href' => get_term_link( $this->course->get_id() ),
			);

			if ( ! empty( $attr['target'] ) ) {
				$attributes['target'] = '_blank';
			}

			if ( ! empty( $attr['rel'] ) ) {
				$attributes['rel'] = 'nofollow';
			}

			$title = TCB_Utils::wrap_content( $title, 'a', '', array(), $attributes );
		}

		return $title;
	}

	/**
	 * Renders the URL shortcode
	 *
	 * @param array  $attr
	 * @param string $content
	 * @param string $tag
	 *
	 * @return string
	 */
	public function url( $attr = array(), $content = '', $tag = '' ) {
		if ( empty( $this->course ) ) {
			return '[' . $tag . ']';
		}

		return get_term_link( $this->course->get_id() );
	}

	/**
	 * Renders the Course Topic Shortcode
	 *
	 * @param array  $attr
	 * @param string $content
	 *
	 * @return string
	 */
	public function course_topic( $attr = array(), $content = '' ) {
		return $this->course->get_topic()->title;
	}

	/**
	 * Renders the Course Difficulty Level Shortcode
	 *
	 * @param array  $attr
	 * @param string $content
	 *
	 * @return mixed|null
	 */
	public function course_difficulty( $attr = array(), $content = '' ) {
		return $this->course->get_difficulty()->name;
	}

	/**
	 * Renders the Course Type Shortcode
	 *
	 * @param array  $attr
	 * @param string $content
	 *
	 * @return mixed
	 */
	public function course_type( $attr = array(), $content = '' ) {
		$tva_term = new TVA_Term( $this->course->get_wp_term() );

		return $tva_term->get_term()->course_type;
	}

	/**
	 * Renders the Course Description
	 *
	 * @param array  $attr
	 * @param string $content
	 *
	 * @return string
	 */
	public function course_description( $attr = array(), $content = '' ) {
		return strip_tags( $this->course->get_description() );
	}

	/**
	 * Renders the course Begin Element
	 *
	 * @param array  $attr
	 * @param string $content
	 *
	 * @return string
	 */
	public function course_begin( $attr = array(), $content = '' ) {
		$classes    = array( 'tva-course-item-dropzone', 'tva-course-dropzone', 'thrv_wrapper', Shortcodes::TVA_NO_DRAG_CLASS, Shortcodes::TVE_NO_DRAG_CLASS );
		$attributes = array();

		$trans = array(
			'&#091;' => '[',
			'&#093;' => ']',
		);

		if ( empty( $content ) ) {
			$content = Shortcodes::get_default_template( 'course-item' );
		}

		return TCB_Utils::wrap_content( do_shortcode( strtr( $content, $trans ) ), 'div', '', $classes, $attributes );
	}

	/**
	 * Renders the course end element
	 *
	 * @param array  $attr
	 * @param string $content
	 *
	 * @return string
	 */
	public function course_end( $attr = array(), $content = '' ) {
		return '';
	}

	/**
	 * Includes the default content
	 *
	 * @return string
	 */
	public function get_default_content() {
		ob_start();

		include_once __DIR__ . '/default-content.php';

		$content = ob_get_contents();

		ob_end_clean();

		return $content;
	}

	/**
	 * Return the shortcode list needed to render the Course
	 *
	 * @return array
	 */
	public function get_shortcodes() {
		$shortcodes = array_keys( $this->shortcodes );

		foreach ( $this->shortcode_configuration as $key => $filename ) {
			$shortcodes = array_merge( $shortcodes, $this->$key->get_shortcodes() );
		}

		return $shortcodes;
	}

	/**
	 * Returns the TVA_Post type icon
	 *
	 * @param TVA_Post $post
	 *
	 * @return string
	 */
	public function get_type_icon( $post ) {
		$type = $post->get_type();

		if ( empty( $type ) ) {
			return '';
		}

		$file      = 'tcb-bridge/assets/fonts/course-type-icons/' . str_replace( '_', '-', $type . '.svg' );
		$file_path = TVA_Const::plugin_path( $file );

		if ( ! is_file( $file_path ) ) {
			return '';
		}

		$icon = tva_get_file_contents( $file, [ 'title' => ucfirst( $type . ' lesson' ) ] );

		return str_replace( '<svg', '<svg class="tcb-icon"', trim( $icon ) );
	}

	/**
	 * Checks if the smart autocollapse is permitted
	 *
	 * @return bool
	 */
	public function allow_smart_autocollapse() {
		return ! empty( self::$course_attr['autocollapse'] ) && (int) self::$course_attr['autocollapse'] === 1 && ! empty( tcb_tva_visual_builder()->get_active_object() );
	}

	/**
	 * Checks if the the active_item should be expanded
	 *
	 * @param {int} $post_id
	 *
	 * @return bool
	 */
	public function should_be_expanded( $post_id ) {

		if ( $post_id === tcb_tva_visual_builder()->get_active_object()->ID ) {
			return true;
		}

		$ancestors = tcb_tva_visual_builder()->get_active_object_ancestors();

		return in_array( $post_id, $ancestors );
	}

	/**
	 * Returns the course label
	 *
	 * Used only when a course item doesn't have access to display the label on that item
	 *
	 * @param TVA_Course_V2|null $course
	 *
	 * @return string
	 */
	public function get_course_label( $course = null ) {

		if ( empty( $course ) ) {
			$course = $this->course;
		}

		if ( empty( $this->course_label ) ) {
			$label = tva_get_labels( array( 'ID' => $course->get_label_id() ) );

			$this->course_label = empty( $label['title'] ) ? __( 'Members Only', 'thrive-apprentice' ) : $label['title'];
		}

		return $this->course_label;
	}

	/**
	 * Returns the warning content.
	 * On frontend if there is a warning returns nothing
	 *
	 * @param string $message
	 * @param array  $classes
	 * @param array  $data
	 *
	 * @return string
	 */
	private function warning_content( $content = '', $classes = array(), $data = array() ) {
		if ( ! self::$is_editor_page ) {
			$content = '';
		}

		$classes[] = static::COURSE_TEXT_WARNING_CLASS;

		return TCB_Utils::wrap_content( $content, 'div', '', $classes, $data );
	}

}

/**
 * Returns the instance of the Course Shortcode
 *
 * @return Main
 */
function tcb_course_shortcode() {
	return Main::get_instance();
}
