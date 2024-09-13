<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

namespace TVA\Architect\Course_List;

use TCB_Post_List;
use TCB_Post_List_Author_Image;
use TCB_Post_List_Featured_Image;
use TCB_Utils;
use TVA_Bundle;
use TVA_Const;
use TVA_Course_V2;
use TVA_Dynamic_Labels;
use TVA_Topic;
use function TVA\Architect\Dynamic_Actions\tcb_tva_dynamic_actions;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Main
 *
 * @package  TVA\Architect\Course_List
 * @project  : thrive-apprentice
 */
class Main {

	/**
	 * Course List Identifier
	 */
	const IDENTIFIER = '.tva-course-list';

	/**
	 * @var Shortcodes
	 */
	private $class_shortcodes;

	/**
	 * @var Main
	 */
	private static $instance;

	/**
	 * @var bool
	 */
	public static $is_editor_page = false;

	/**
	 * @var bool
	 */
	public static $disabled_links = false;

	/**
	 * @var string[]
	 */
	private $shortcodes = array(
		'tva_course_list' => 'render',
	);

	/**
	 * Main constructor.
	 */
	private function __construct() {
		foreach ( $this->shortcodes as $shortcode => $function ) {
			add_shortcode( $shortcode, array( $this, $function ) );
		}
		$this->class_shortcodes = new Shortcodes();

		new Hooks();
	}

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
	 * @return Shortcodes
	 */
	public function get_shortcodes_class() {
		return $this->class_shortcodes;
	}

	/**
	 * Renders the Course List Shortcode
	 *
	 * @param array  $attr
	 * @param string $content
	 *
	 * @return string
	 */
	public function render( $attr = array(), $content = '' ) {
		if ( ! TCB_Post_List::is_outside_post_list_render() ) {
			/**
			 * If the request is inside a post list, we return the empty string
			 */
			return '';
		}

		$attr = $this->parse_attr( $attr );
		$attr = array_merge( $this->default_args(), $attr );

		$query = $this->init_query( $attr['query'] );

		static::$disabled_links = isset( $attr['disabled-links'] ) && (int) $attr['disabled-links'] === 1;

		/**
		 * @var TVA_Course_V2
		 */
		global $tva_active_course;

		$courses  = $this->get_courses( $query );
		$c_number = count( $courses['courses'] );

		/**
		 * @var array contains the classes of the course list element
		 */
		$classes = $this->get_classes( $attr, $c_number );

		/**
		 * Used for jump links
		 */
		$id = empty( $attr['id'] ) ? '' : $attr['id'];

		/* generate a data-css if we don't have one yet */
		$attr['css'] = empty( $attr['css'] ) ? substr( uniqid( 'tve-u-', true ), 0, 17 ) : $attr['css'];

		/* pagination compatibility */
		$attr['total_post_count'] = $this->is_code_pagination( $query ) ? $courses['total_count'] : Main::get_total_course_count( $query );
		$attr['paged']            = 1;

		$data = array();

		/* add the data of each rendered course list to $GLOBALS so we can localize it in a footer script later in the execution */
		$GLOBALS['tva_course_list_localize'][] = array(
			'identifier' => '[data-css="' . $attr['css'] . '"]',
			'template'   => $attr['css'],
			'attr'       => $attr,
			'content'    => $content,
		);

		/**
		 * If the number of courses satify the query is 0 we need 1 course to be shown in the editor page
		 * because the user needs to be able to modify the query and the system needs to read the template from somewhere
		 */
		if ( static::$is_editor_page && $c_number === 0 ) {
			$courses = $this->get_courses( array( 'posts_per_page' => 1 ) );
		}

		if ( empty( $content ) ) {
			$content = $this->get_default_content();
		}

		$html = '';

		foreach ( $courses['courses'] as $tva_active_course ) {
			$html .= do_shortcode( $content );
		}

		/**
		 * Deactivate the $tva_active_course global variable
		 */
		$tva_active_course = null;

		$html = TCB_Post_List::prepare_carousel( $html, $attr );

		foreach ( $attr as $key => $value ) {
			switch ( $key ) {
				case 'class':
					/* we don't want data-class to persist, we process it inside get_classes() */
					break;
				case 'style':
					/* we don't want data-key on the style attr */
					$data[ $key ] = esc_attr( $value );
					break;
				default:
					$data[ 'data-' . $key ] = esc_attr( $value );

			}
		}

		return TCB_Utils::wrap_content( $html, 'div', $id, $classes, $data );
	}

	/**
	 * @param array|string $query
	 *
	 * @return array
	 */
	private function init_query( $query = '' ) {
		if ( is_string( $query ) ) {
			$query = str_replace( "'", '"', html_entity_decode( $query, ENT_QUOTES ) );
			$query = json_decode( $query, true );
		}

		if ( ! is_array( $query ) ) {
			$query = array();
		}

		return $query;
	}

	/**
	 * @param array $attr
	 * @param array $query
	 *
	 * @return array
	 */
	public function prepare_pagination_query( $attr, $query = array() ) {
		$query = $this->init_query( $query );

		$posts_per_page = $attr['posts_per_page'];

		$query['posts_per_page'] = $posts_per_page;
		$query['offset']         = $posts_per_page * ( $attr['paged'] - 1 );

		return $query;
	}

	/**
	 * Returns the Course List classes
	 *
	 * @param array $attr
	 * @param int   $course_count
	 *
	 * @return array
	 */
	public function get_classes( $attr, $course_count ) {
		/* tve-content-list identifies lists with dynamic content */
		$classes = array( str_replace( '.', '', static::IDENTIFIER ), 'thrv_wrapper', 'tve-content-list' );

		/* hide the 'Save as Symbol' icon */
		if ( static::$is_editor_page ) {
			$classes[] = 'tcb-selector-no_save';
			$classes[] = 'tve_no_duplicate';
		}

		/* set custom classes, if they are present */
		if ( ! empty( $attr['class'] ) ) {
			$classes[] = $attr['class'];
		}

		if ( isset( $attr['type'] ) && $attr['type'] === 'masonry' ) {
			/* this adds general masonry animations to the course items */
			$classes[] = 'tve_post_grid_masonry';
		}

		if ( $course_count === 0 ) {
			$classes[] = 'tva-empty-list';
		}

		return $classes;
	}

	/**
	 * Return the shortcode list needed to render the Course
	 *
	 * @return array
	 */
	public function get_shortcodes() {
		return array_merge( $this->class_shortcodes->get_shortcodes(), array_keys( $this->shortcodes ) );
	}

	/**
	 * Returns the courses needed for the course_list shortcode based on the query provided
	 *
	 * @param array   $query
	 * @param boolean $count
	 *
	 * @return array|int
	 */
	public function get_courses( $query = array(), $count = false ) {
		$query = $this->init_query( $query );

		if ( ! empty( $query['terms'] ) ) {
			$terms = array();
			foreach ( $query['terms'] as $term_id ) {
				if ( strpos( $term_id, 'bundle_' ) !== false ) {
					//get bundle courses IDS
					$bundle_id = str_replace( 'bundle_', '', $term_id );
					$bundle    = new TVA_Bundle( (int) $bundle_id );
					$terms     = array_merge( $terms, (array) $bundle->products );
				} else {
					$terms[] = $term_id;
				}
			}
			$query['include'] = $terms;
		}

		$args = array_merge( array(
			'include'  => array(),
			'topics'   => array(),
			'authors'  => array(),
			'labels'   => array(),
			'levels'   => array(),
			'progress' => array(),
			'limit'    => ! empty( $query['posts_per_page'] ) ? $query['posts_per_page'] : 6,
			'offset'   => ! empty( $query['offset'] ) ? $query['offset'] : 0,
			'status'   => 'publish',
		), $query );

		if ( static::$is_editor_page ) {
			$args['progress'] = array(); //The progress filter should only be applied on front-end
		}

		//We need to remove the hardcoded label for - No restriction if present
		if ( in_array( - 1000, $args['labels'] ) ) {
			$args['labels']       = array_diff( $args['labels'], array( - 1000 ) );
			$args['free_for_all'] = 1;
		}

		/**
		 * Allow other plugins to modify the course list arguments before showing the courses
		 *
		 * @param array $args
		 */
		$args = apply_filters( 'tva_course_list_get_courses_args', $args );

		$limit  = $args['limit'];
		$offset = $args['offset'];

		if ( ! $count && $this->is_code_pagination( $args ) ) {
			$args['limit']  = 0;
			$args['offset'] = 0;
		}

		add_filter( 'terms_clauses', [ $this, 'hidden_courses_logic' ], 10, 2 );

		$courses = TVA_Course_V2::get_items( $args, $count );

		remove_filter( 'terms_clauses', [ $this, 'hidden_courses_logic' ], 10 );

		if ( $count ) {
			return $courses;
		}

		$total_count = count( $courses );
		if ( $this->is_code_pagination( $args ) ) {

			$based_on_progress = array();
			foreach ( $courses as $course ) {
				$progress_status = tva_customer()->get_course_progress_status( $course->get_id() );

				if ( in_array( $progress_status, $args['progress'] ) ) {
					$based_on_progress[] = $course;
				}
			}

			$total_count = count( $based_on_progress );

			if ( $limit > 0 ) {
				$courses = array_slice( $based_on_progress, $offset, $limit );
			} else {
				$courses = $based_on_progress;
			}
		}

		return array( 'courses' => $courses, 'total_count' => $total_count );
	}

	public function hidden_courses_logic( $clauses, $taxonomy ) {
		if ( is_user_logged_in() && $taxonomy[0] === TVA_Const::COURSE_TAXONOMY ) {
			global $wpdb;

			/**
			 * We need to include here courses that the user made progress and became hidden / archived after the admin action
			 * This courses need to be available for the customers
			 */
			$learned_courses     = implode( ',', tva_customer()->get_learned_courses() );
			$learned_courses_sql = '';
			if ( ! empty( $learned_courses ) ) {
				$learned_courses_sql = "t.term_id IN ({$learned_courses}) OR ";
			}

			$clauses['where'] .= " 
			OR ( mt1.meta_key = 'tva_status' AND mt1.meta_value = 'hidden' AND ({$learned_courses_sql} t.term_id IN (
				SELECT course_id FROM {$wpdb->prefix}tva_access_history WHERE user_id = " . get_current_user_id() . " GROUP BY course_id HAVING SUM(status) > 0
			)) ) 
			OR ( mt1.meta_key = 'tva_status' AND mt1.meta_value = 'archived' AND ({$learned_courses_sql} t.term_id IN (
				SELECT course_id FROM {$wpdb->prefix}tva_access_history WHERE user_id = " . get_current_user_id() . " AND TIMESTAMP(created) < (
					SELECT TIMESTAMP(meta_value) FROM {$wpdb->prefix}termmeta WHERE term_id = t.term_id AND meta_key = 'tva_status_changed_on' 
				) GROUP BY course_id HAVING SUM(status) > 0
			)) )";
		}

		return $clauses;
	}

	/**
	 * @param array $query
	 *
	 * @return TVA_Course_V2[]
	 */
	public function get_total_course_count( $query ) {
		$query['posts_per_page'] = - 1;

		return $this->get_courses( $query, true );
	}

	/**
	 * Cache the topics on the class
	 *
	 * @return TVA_Topic[]
	 */
	public function get_topics() {
		if ( ! isset( $this->topics ) ) {
			$this->topics = TVA_Topic::get_items();
		}

		return $this->topics;
	}

	/**
	 * Localizes the dynamic fields needed for the course element
	 *
	 * @param array $courses
	 *
	 * @return array
	 */
	public function localize_dynamic_fields( $courses = array() ) {

		$localization = array();

		/**
		 * @var TVA_Course_V2 $course
		 */
		foreach ( $courses as $index => $course ) {

			$topic = $course->get_topic();
			$label = $course->get_label_data();

			$localization[] = array(
				'id'                        => $course->get_id(),
				'name'                      => $course->name,
				'description'               => $this->get_description( $course ),
				'author'                    => $course->author,
				'author_name'               => $course->author->get_user()->display_name,
				'type'                      => $course->type_label,
				'type_icon'                 => $this->get_type_icon( $course ),
				'label_title'               => esc_attr( $label['title'] ), //Output inside the color picker as dynamic color
				'label_color'               => $label['color'],
				'topic_color'               => $topic->color,
				'topic_title'               => esc_attr( $topic->title ),//Output inside the color picker as dynamic color
				'topic_icon'                => $this->get_topic_icon( $topic ),
				'topic_icon_color'          => $topic->layout_icon_color,
				'lessons_number'            => $course->count_lessons(),
				'difficulty_name'           => $course->get_difficulty()->name,
				'progress'                  => $this->get_progress_label( $course ),
				'progress_percentage'       => $this->get_progress_percentage( $course ),
				'action_label'              => TVA_Dynamic_Labels::get_course_cta( $course ),
				'buy_label'                 => TVA_Dynamic_Labels::get_cta_label( 'buy_now' ),
				'permalink'                 => $course->get_link(),
				'variables'                 => $this->get_dynamic_variables( $course ),
				'module_number_with_label'  => tcb_tva_dynamic_actions()->get_children_count_with_label( $course->get_published_modules() ),
				'chapter_number_with_label' => tcb_tva_dynamic_actions()->get_children_count_with_label( $course->get_published_chapters() ),
				'lesson_number_with_label'  => tcb_tva_dynamic_actions()->get_children_count_with_label( $course->get_published_lessons() ),
				'dynamic_images'            => array(
					'featured' => $this->get_cover_image( $course ),
					'author'   => $this->get_author_image( $course ),
				),
			);
		}

		return $localization;
	}

	/**
	 * Returns the course topic icon svg string
	 *
	 * @param TVA_Topic $topic
	 *
	 * @return string
	 */
	public function get_topic_icon( $topic ) {
		$icon_type  = $topic->icon_type;
		$topic_icon = $topic->$icon_type;
		if ( empty( $topic_icon ) ) {
			$defaults   = TVA_Topic::get_defaults();
			$topic_icon = $defaults[0]->svg_icon;
		}

		return trim( $topic_icon );
	}

	/**
	 * Computes the Course Progress Label
	 *
	 * @param TVA_Course_V2 $course
	 *
	 * @return string
	 */
	public function get_progress_label( $course ) {
		return tva_customer()->get_course_progress_label( $course );
	}

	/**
	 * Computes the Course Progress Percentage for a Course List Item
	 *
	 * @param TVA_Course_V2 $course
	 *
	 * @return string
	 */
	public function get_progress_percentage( $course ) {
		$args = array();

		if ( ! Main::$is_editor_page ) {
			$args['post_status'] = array( 'publish' );
		}

		$total     = $course->count_lessons( $args );
		$completed = tva_count_completed_items( Main::$is_editor_page ? $course->get_all_lessons() : $course->get_published_lessons() );

		return tcb_tva_dynamic_actions()->get_progress_by_type( 'course', $completed, $total );
	}

	/**
	 * Returns the course type icon
	 *
	 * @param TVA_Course_V2 $course
	 *
	 * @return string
	 */
	public function get_type_icon( $course ) {
		$file      = 'tcb-bridge/assets/fonts/course-type-icons/' . str_replace( '_', '-', $course->type . '.svg' );
		$file_path = TVA_Const::plugin_path( $file );

		if ( ! is_file( $file_path ) ) {
			return '';
		}

		$title = isset( TVA_Dynamic_Labels::get( 'course_labels' )[ $course->type ] ) ? TVA_Dynamic_Labels::get( 'course_labels' )[ $course->type ]['title'] : __( 'General', 'thrive-cb' );

		$icon = tva_get_file_contents( $file, [ 'title' => $title ] );

		return str_replace( '<svg', '<svg class="tcb-icon"', trim( $icon ) );
	}

	/**
	 * Returns the course description
	 * First it fetches data from the course excerpt.
	 * If the excerpt is empty, it fetches from the course overview
	 *
	 * @param TVA_Course_V2 $course
	 *
	 * @return string
	 */
	public function get_description( $course ) {
		$description = strip_tags( $course->get_excerpt() );

		if ( empty( $description ) ) {
			$description = strip_tags( $course->get_description() );
		}

		return $description;
	}

	/**
	 * Returns the  dynamic variables needed for a course
	 *
	 * @param TVA_Course_V2 $course
	 *
	 * @return string
	 */
	public function get_dynamic_variables( $course ) {
		$topic_color = $course->get_topic()->color;
		$label_data  = $course->get_label_data();

		$variables = '';
		$variables .= TVE_DYNAMIC_COLOR_VAR_CSS_PREFIX . 'topic-color:' . $topic_color . ';';
		$variables .= TVE_DYNAMIC_COLOR_VAR_CSS_PREFIX . 'label-color:' . $label_data['color'] . ';';

		$cover_image_var  = TVE_DYNAMIC_BACKGROUND_URL_VAR_CSS_PREFIX . 'course-image';
		$author_image_var = TVE_DYNAMIC_BACKGROUND_URL_VAR_CSS_PREFIX . 'course-author';

		$variables .= $cover_image_var . ':url("' . $this->get_cover_image( $course ) . '");';
		$variables .= $author_image_var . ':url("' . $this->get_author_image( $course ) . '");';

		return '.tva-course-list-item[data-id="' . $course->get_id() . '"]{' . $variables . '}';
	}

	/**
	 * @param TVA_Course_V2 $course
	 *
	 * @return string
	 */
	public function get_cover_image( $course ) {
		$cover_image = $course->cover_image;

		return empty( $cover_image ) ? TCB_Post_List_Featured_Image::get_default_url() : $cover_image;
	}

	/**
	 * @param TVA_Course_V2 $course
	 *
	 * @return string
	 */
	public function get_author_image( $course ) {
		$author_image = $course->get_author()->get_avatar();

		return empty( $author_image ) ? TCB_Post_List_Author_Image::get_default_url() : $author_image;
	}

	/**
	 * Fetches the default content
	 *
	 * @return string
	 */
	private function get_default_content() {
		ob_start();

		include TVA_Const::plugin_path( 'tcb-bridge/editor-layouts/elements/course-list-default-content.php' );

		$content = ob_get_contents();

		ob_end_clean();

		return $content;
	}


	/**
	 * Default args
	 *
	 * @return array
	 */
	private function default_args() {
		return array(
			'query'               => '',
			'ct'                  => 'course_list-123',
			'ct-name'             => esc_attr__( 'Default Template', 'thrive-apprentice' ),
			'type'                => 'grid',
			'columns-d'           => 3,
			'columns-t'           => 2,
			'columns-m'           => 1,
			'vertical-space-d'    => 30,
			'horizontal-space-d'  => 30,
			'posts_per_page'      => 6,
			'no_posts_text'       => esc_attr__( 'There are no courses to display.', 'thrive-apprentice' ),
			'no_posts_text_color' => '#999999',
			'pagination-type'     => 'none',
			'pages_near_current'  => '2',
		);
	}

	private function parse_attr( $attr = array() ) {
		return array_map( function ( $v ) {
			$v = esc_attr( $v );

			return str_replace( array( '|{|', '|}|' ), array( '[', ']' ), $v );
		}, $attr );
	}

	/**
	 * Returns true if the pagination should be done in the code - php logic
	 *
	 * @param array $args
	 *
	 * @return bool
	 */
	private function is_code_pagination( $args ) {
		return ! empty( $args['progress'] ) && is_array( $args['progress'] );
	}
}

/**
 * Returns the instance of the Course_List Shortcode
 *
 * @return Main
 */
function tcb_course_list_shortcode() {
	return Main::get_instance();
}
