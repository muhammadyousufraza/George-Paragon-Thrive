<?php

namespace TVA\Architect\Dynamic_Actions;

use TVA\Product;
use TVA\TTB\Apprentice_Wizard;
use TVA_Assessment;
use TVA_Chapter;
use TVA_Const;
use TVA_Course_V2;
use TVA_Dynamic_Labels;
use TVA_Lesson;
use TVA_Manager;
use TVA_Module;
use TVA_Post;
use TVA_Product;
use WP_Post;

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Main
 *
 * @package TVA\Architect\Dynamic_Actions
 * @project : thrive-apprentice
 *
 * @property int course_count_items
 * @property int course_count_items_completed
 * @property int course_count_lessons
 * @property int course_count_lessons_completed
 * @property int module_count_items
 * @property int module_count_items_completed
 * @property int chapter_count_items
 * @property int chapter_count_items_completed
 * @property int module_count_lessons
 * @property int module_count_lessons_completed
 * @property int chapter_count_lessons
 * @property int chapter_count_lessons_completed
 */
class Main {

	/**
	 * @var Main
	 */
	private static $instance;

	/**
	 * @var TVA_Lesson|TVA_Assessment|TVA_Module|null
	 */
	private $active_object;

	/**
	 * @var TVA_Lesson|TVA_Assessment|null
	 */
	private $active_item;

	/**
	 * @var TVA_Course_V2|null
	 */
	private $active_course;

	/**
	 * @var bool
	 */
	public static $is_editor_page = false;

	/**
	 * @var array
	 */
	private $_data = [];


	/**
	 * @var mixed
	 */
	private $course_nav_labels;

	/**
	 * @var Product|null
	 */
	private $course_product;

	private $should_display_buy_link = false;

	/**
	 * Main constructor.
	 */
	private function __construct() {
		new Hooks();
		new Shortcodes();
	}

	public function __get( $key ) {
		$value = null;

		if ( isset( $this->_data[ $key ] ) ) {
			$value = $this->_data[ $key ];
		} elseif ( method_exists( $this, 'get_' . $key ) ) {
			$method_name = 'get_' . $key;

			$value = $this->$method_name();
		}

		return $value;
	}

	/**
	 * Singleton implementation for TCB_Custom_Fields_Shortcode
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

	public function get_next_item_text() {
		if ( empty( $this->active_object ) ) {
			return '';
		}

		$course_nav_labels = $this->get_course_nav_labels();
		$label             = __( 'Next lesson', 'thrive-apprentice' );
		$next_item         = $this->get_next_item();

		if ( $next_item && ! $next_item instanceof TVA_Lesson && ! empty( $course_nav_labels['next_item']['title'] ) ) {
			$label = $course_nav_labels['next_item']['title'];
		} else if ( ! empty( $course_nav_labels['next_lesson']['title'] ) ) {
			$label = $course_nav_labels['next_lesson']['title'];
		}

		if ( ( $this->active_object instanceof TVA_Lesson || $this->active_object instanceof TVA_Assessment ) && ! $this->has_next_item() ) {
			/**
			 * For assessments we set completed page as next item because there isn't a mark as completed button
			 */
			if ( $this->maybe_go_to_completed_page() || ( $this->active_course->has_completed_post() && $this->active_course->get_completed_post()->is_valid() && $this->active_object instanceof TVA_Assessment ) ) {
				$label = $course_nav_labels['to_completion_page']['title'];
			} else {
				$label = $course_nav_labels['to_course_page']['title'];
			}
		}

		return $label;
	}

	public function get_prev_lesson_text() {
		if ( empty( $this->active_object ) ) {
			return '';
		}

		$course_nav_labels = $this->get_course_nav_labels();
		$label             = __( 'Previous lesson', 'thrive-apprentice' );

		$prev_item = $this->get_previous_item();

		if ( $prev_item && ! $prev_item instanceof TVA_Lesson && ! empty( $course_nav_labels['prev_item']['title'] ) ) {
			$label = $course_nav_labels['prev_item']['title'];
		} else if ( ! empty( $course_nav_labels['prev_lesson']['title'] ) ) {
			$label = $course_nav_labels['prev_lesson']['title'];
		}

		if ( $this->is_first_visible_item() ) {

			$label = __( 'To course page', 'thrive-apprentice' );

			if ( ! empty( $course_nav_labels['to_course_page']['title'] ) ) {
				$label = $course_nav_labels['to_course_page']['title'];
			}
		}

		return $label;
	}


	public function get_module_count_lessons() {
		if ( ! isset( $this->_data['module_count_lessons'] ) ) {
			$this->_data['module_count_lessons'] = $this->get_count_by_type( TVA_Const::MODULE_POST_TYPE, false );
		}

		return $this->_data['module_count_lessons'];
	}

	public function get_chapter_count_lessons() {
		if ( ! isset( $this->_data['chapter_count_lessons'] ) ) {
			$this->_data['chapter_count_lessons'] = $this->get_count_by_type( TVA_Const::CHAPTER_POST_TYPE, false );
		}

		return $this->_data['chapter_count_lessons'];
	}

	public function get_module_count_lessons_completed() {
		if ( ! isset( $this->_data['module_count_lessons_completed'] ) ) {
			$this->_data['module_count_lessons_completed'] = $this->get_count_by_type( TVA_Const::MODULE_POST_TYPE, true );
		}

		return $this->_data['module_count_lessons_completed'];
	}

	public function get_chapter_count_lessons_completed() {
		if ( ! isset( $this->_data['chapter_count_lessons_completed'] ) ) {
			$this->_data['chapter_count_lessons_completed'] = $this->get_count_by_type( TVA_Const::CHAPTER_POST_TYPE, true );
		}

		return $this->_data['chapter_count_lessons_completed'];
	}

	/**
	 * Check if this item is the first one from course
	 *
	 * @return bool
	 */
	public function is_first_visible_item() {
		$is_first_item = false;

		if ( $this->get_active_item() ) {
			$course = $this->get_active_course();

			$items = $course->get_ordered_visible_items();

			if ( ! empty( $items ) ) {
				$is_first_item = $items[0]->ID === $this->get_active_item()->ID;
			}
		}

		return $is_first_item;
	}

	/**
	 * Whether next item exists
	 *
	 * @return bool
	 */
	public function has_next_item() {
		return ! empty( $this->get_next_item() );
	}

	/**
	 *
	 *
	 * @return TVA_Lesson|TVA_Assessment|null
	 */
	public function get_next_item() {
		if ( $this->active_object instanceof TVA_Module ) {
			$next_item = $this->get_active_item();
		} else {
			$active_item = $this->get_active_item();
			$next_item   = $this->get_active_course()->get_next_visible_item( $active_item->ID, true );
		}

		return $next_item;
	}

	public function get_previous_item() {
		$active_item = $this->get_active_item();

		return $this->get_active_course()->get_previous_visible_item( $active_item->ID, true );
	}


	public function get_next_lesson_link() {

		$url = 'javascript:void(0);';
		if ( empty( $this->active_object ) ) {
			return $url;
		}

		$next_item = $this->get_next_item();

		if ( $next_item instanceof TVA_Lesson || $next_item instanceof TVA_Assessment ) {
			$url = $next_item->get_link();

			if ( ! $this->active_object instanceof TVA_Module ) {
				/**
				 * @var TVA_Module $next_item_module
				 */
				$next_item_module = $next_item->get_parent_by_type( TVA_Const::MODULE_POST_TYPE );

				if ( ! empty( $next_item_module ) ) {
					$item_posts = $next_item_module->get_visible_items();

					/**
					 * @var WP_Post $item_post
					 */
					$item_post = reset( $item_posts );

					if ( $item_post->ID === $next_item->ID ) {
						$url = $next_item_module->get_link();
					}
				}
			}
		} elseif ( empty( $next_item ) || ! $this->has_next_item() ) {
			/**
			 * For assessments we set completed page as next item because there isn't a mark as completed button
			 */
			if ( $this->maybe_go_to_completed_page() || ( $this->active_course->has_completed_post() && $this->active_course->get_completed_post()->is_valid() && $this->active_object instanceof TVA_Assessment ) ) {
				$url = $this->active_course->get_completed_post()->url;
			} else {
				$url = $this->active_course->get_link( false );
			}
		}

		return $url;
	}


	public function get_previous_lesson_link() {
		$url = 'javascript:void(0);';

		if ( empty( $this->active_object ) ) {
			return $url;
		}

		$prev_item = $this->get_previous_item();

		if ( $prev_item instanceof TVA_Lesson || $prev_item instanceof TVA_Assessment ) {
			$url = $prev_item->get_link();

			if ( ! $this->active_object instanceof TVA_Module ) {
				/**
				 * @var TVA_Module $prev_item_module
				 */
				$prev_item_module = $prev_item->get_parent_by_type( TVA_Const::MODULE_POST_TYPE );

				if ( ! empty( $prev_item_module ) ) {
					$item_posts = $prev_item_module->get_visible_items();

					/**
					 * @var WP_Post $item_post
					 */
					$item_post = end( $item_posts );

					if ( $item_post->ID === $prev_item->ID ) {
						$url = $prev_item_module->get_link();
					}
				}
			}
		} elseif ( $this->is_first_visible_item() || empty( $prev_item ) ) {
			$url = $this->active_course->get_link( false );
		}

		return $url;
	}

	public function get_mark_as_complete_text() {
		return $this->get_course_nav_label( 'mark_complete' );
	}

	public function get_mark_as_complete_link() {
		return '#';
	}

	/**
	 * Called dynamically from class Shortcodes -> dynamic_element function
	 *
	 * Returns the link for download certificate
	 *
	 * @return string
	 */
	public function get_download_certificate_link() {
		return '#';
	}

	/**
	 * Returns the completion page URL if the system allows to the user to navigate to completion page
	 *
	 * @return string
	 */
	public function get_completion_page_link() {
		$url = '#';
		if ( TVA_Product::has_access() || $this->maybe_go_to_completed_page() ) {
			$url = $this->active_course->get_completed_post()->url;
		}

		return $url;
	}

	/**
	 * @return string
	 */
	public function get_certificate_verification_url_link() {
		return tva_get_settings_manager()->factory( 'certificate_validation_page' )->get_link();
	}

	/**
	 * @return string
	 */
	public function get_mark_as_complete_next_text() {

		$text = $this->get_mark_as_complete_text();

		if ( $this->active_object instanceof TVA_Lesson && $this->active_object->is_completed() ) {
			$text = $this->get_next_item_text();
		}

		return $text;
	}

	public function get_mark_as_complete_next_link() {
		return '#';
	}

	public function should_display_buy_link() {
		if ( isset( $this->should_display_buy_link ) ) {
			$product                       = $this->get_course_product();
			$this->should_display_buy_link = $this->active_course &&
											 ! $this->active_course->has_access() &&
											 $product &&
											 $product->should_display_buy_now_link();
		}

		return $this->should_display_buy_link;
	}

	/**
	 * @return mixed|string|null
	 */
	public function get_call_to_action_text() {
		if ( $this->should_display_buy_link() ) {
			$label = TVA_Dynamic_Labels::get_cta_label( 'buy_now' );
		} else {
			$label = TVA_Dynamic_Labels::get_course_cta( $this->active_course, 'single' );
		}

		return $label;
	}

	/**
	 * @return string
	 */
	public function get_call_to_action_link() {
		$url = 'javascript:void(0);';

		if ( self::$is_editor_page ) {
			//We do this to avoid recursion for editor page
			return $url;
		}

		if ( $this->should_display_buy_link() ) {
			$url = $this->course_product->get_buy_link();
		} else {
			if ( empty( $this->active_object ) ) {

				/**
				 * If the course published lessons are all completed by the user the URL will be the first module overview
				 * If the user starts the course now the URL will be the first module overview as well
				 * Handles the case where users completed lessons are grater than published lessons
				 * (this can happen if the admin unpublished a lesson after user has completed it)
				 */
				$published_lessons_count        = $this->active_course->get_published_lessons_count();
				$course_completed_lessons_count = count( tva_customer()->get_course_learned_lessons( $this->active_course->ID ) );

				if ( $published_lessons_count > 0 && ( $course_completed_lessons_count === 0 || $published_lessons_count <= $course_completed_lessons_count ) ) {
					$modules = $this->active_course->get_published_modules();
					/**
					 * Check if there are any modules
					 */
					if ( ! empty( $modules ) ) {
						$next_item = $modules[0];
					} else {
						$published_lessons = $this->active_course->get_ordered_visible_lessons();
						$next_item         = $published_lessons[0];
					}
				} else {
					$next_item = TVA_Manager::get_next_user_uncompleted_visible_item( $this->active_course );
				}
			} else {
				$next_item = TVA_Manager::get_next_user_uncompleted_visible_item( $this->active_course, $this->active_object );
			}
			if ( $next_item instanceof TVA_Lesson || $next_item instanceof TVA_Assessment || $next_item instanceof TVA_Module ) {
				$url = $next_item->get_link();
			} else {
				$url = $this->active_course->get_link( false );
			}
		}

		return $url;
	}


	/**
	 * Returns the index page link
	 *
	 * @return string
	 */
	public function get_index_page_link() {
		$url = tva_get_settings_manager()->factory( 'index_page' )->get_link();

		if ( ! empty( $url ) && ! empty( $_REQUEST['tva_skin_id'] ) && is_numeric( $_REQUEST['tva_skin_id'] ) ) {
			/**
			 * If tva_skin_id is present, it passes it to the URL so it will return the index page content corresponding to the active skin
			 */

			$url = add_query_arg( [
				'tva_skin_id' => $_REQUEST['tva_skin_id'],
			], $url );
		}

		if ( empty( $url ) ) {
			$url = '#';
		}

		return $url;
	}

	/**
	 * Callback for course overview link shortcode
	 *
	 * @return string
	 */
	public function get_course_overview_link() {

		if ( static::$is_editor_page || empty( $this->active_course ) ) {
			//We do this to avoid recursion for editor page
			return 'javascript:void(0);';
		}

		return $this->active_course->get_link();
	}

	/**
	 * Cache the course navigation labels
	 *
	 * @return array|null
	 */
	public function get_course_nav_labels() {
		if ( ! isset( $this->course_nav_labels ) ) {
			$this->course_nav_labels = TVA_Dynamic_Labels::get( 'course_navigation' );
		}

		return $this->course_nav_labels;
	}

	/**
	 * Returns a specific label for course navigation
	 *
	 * @param string $key
	 *
	 * @return string
	 */
	public function get_course_nav_label( $key ) {
		$labels = $this->get_course_nav_labels();

		return $labels[ $key ]['title'];
	}

	/**
	 * Set data to the class
	 * Used in ajax requests to get next lesson URLs
	 *
	 * @param TVA_Lesson|TVA_Module $object
	 *
	 * @return $this
	 */
	public function set_data( $object ) {
		$this->set_active_object( $object );
		$this->set_active_course( $object->get_course_v2() );

		return $this;
	}

	/**
	 * @param TVA_Lesson|TVA_Module $object
	 */
	public function set_active_object( $object ) {
		$this->active_object = $object;
	}

	/**
	 * @return TVA_Lesson|TVA_Module|null
	 */
	public function get_active_object() {
		return $this->active_object;
	}

	/**
	 * @param TVA_Course_V2 $course
	 */
	public function set_active_course( $course ) {
		$this->active_course = $course;
	}

	/**
	 * @return TVA_Course_V2|null
	 */
	public function get_active_course() {
		return $this->active_course;
	}

	public function get_course_product() {
		if ( ! $this->course_product ) {
			$this->course_product = $this->active_course->get_product();
		}

		return $this->course_product;
	}

	/**
	 * @return TVA_Lesson|null
	 */
	public function get_active_item() {
		if ( empty( $this->active_item ) ) {

			if ( $this->active_object instanceof TVA_Module ) {
				$items = $this->active_object->get_visible_items();

				if ( ! empty( $items ) ) {
					$this->active_item = $items[0];
				}
			} elseif ( $this->active_object instanceof TVA_Lesson || $this->active_object instanceof TVA_Assessment ) {
				$this->active_item = $this->active_object;
			} else {
				$first_published_item = $this->active_course->get_first_published_item();

				if ( ! empty( $first_published_item ) ) {
					$this->active_item = $first_published_item;
				}
			}
		}

		if ( empty( $this->active_item ) ) {
			$this->active_item = new TVA_Lesson( Apprentice_Wizard::get_object_or_demo_content( TVA_Const::LESSON_POST_TYPE, 0, true ) );
		}

		return $this->active_item;
	}

	/**
	 * Returns dynamically a number type items with their labels (singular|plural form)
	 *
	 * Used in shortcodes for:
	 * - Apprentice Lesson List
	 * - Course List
	 * - Course Overview page
	 *
	 * @param TVA_Post|array $parent_post_or_array
	 */
	public function get_children_count_with_label( $parent_post_or_array ) {

		if ( is_array( $parent_post_or_array ) ) {
			$posts = $parent_post_or_array;
		} else {
			$posts = $parent_post_or_array->get_direct_children();
		}

		/**
		 * On front-end we need to count only the published posts
		 */
		if ( ! Main::$is_editor_page ) {
			/**
			 * @var TVA_Post $item
			 */
			$posts = array_values( array_filter( $posts, static function ( $item ) {
				return $item->is_published();
			} ) );
		}

		$return = Main::$is_editor_page ? '0 Items' : '';
		$labels = TVA_Dynamic_Labels::get( 'course_structure' );

		if ( ! empty( $posts ) ) {
			$count  = count( $posts );
			$assessments_count = 0;
			$lessons_count = 0;
			$return = $count . ' ';

			if ( $posts[0] instanceof TVA_Module ) {
				$suffix = 'module';
			} elseif ( $posts[0] instanceof TVA_Chapter ) {
				$suffix = 'chapter';
			} else {
				foreach ( $posts as $post ) {
					if ( $post instanceof TVA_Assessment ) {
						$assessments_count += 1;
					}

					if ( $post instanceof TVA_Lesson ) {
						$lessons_count += 1;
					}
				}

				if ( $assessments_count !== 0 && $lessons_count !== 0 ) {
					$assessments_label = $assessments_count . ' ' . $labels[ 'course_assessment' ][ $assessments_count === 1 ? 'singular' : 'plural' ];
					$lessons_label = $lessons_count . ' ' . $labels[ 'course_lesson' ][ $lessons_count === 1 ? 'singular' : 'plural' ];
					return $assessments_label . ', ' . $lessons_label;
				} else if ( $assessments_count !== 0 ) {
					$suffix = 'assessment';
				} else {
					$suffix = 'lesson';
				}
			}

			$return .= $labels[ 'course_' . $suffix ][ $count === 1 ? 'singular' : 'plural' ];
		}

		return $return;
	}

	/**
	 * Returns the progress by type
	 *
	 * @param string $type
	 *
	 * @return string
	 */
	public function get_progress_by_type( $type = 'course', $completed = - 1, $total = - 1 ) {

		if ( $completed === - 1 ) {
			$completed = $this->{$type . '_count_items_completed'};
		}

		if ( $total === - 1 ) {
			$total = $this->{$type . '_count_items'};
		}

		if ( is_int( $completed ) && is_int( $total ) && $total > 0 ) {
			$progress = ( $completed * 100 ) / $total;
			if ( (int) $progress > 100 ) {
				$progress = 100;
			}
		} else {
			$progress = '0';
		}

		return ( (int) $progress ) . '%';
	}

	/**
	 * Returns the completed lessons count for the active course
	 * Used for localization and inside the shortcode
	 *
	 * @return int
	 */
	public function get_course_count_lessons_completed() {
		if ( ! isset( $this->_data['course_count_lessons_completed'] ) ) {
			if ( Main::$is_editor_page ) {
				$this->_data['course_count_lessons_completed'] = tva_count_completed_items( $this->active_course->get_all_lessons() );
			} else {
				$this->_data['course_count_lessons_completed'] = tva_count_completed_items( $this->active_course->get_published_lessons() );
			}
		}

		return $this->_data['course_count_lessons_completed'];
	}

	/**
	 * Used inside the shortcode & localization
	 *
	 * @return int
	 */
	public function get_course_count_lessons() {
		if ( ! isset( $this->_data['course_count_lessons'] ) ) {
			$args = [];

			if ( ! Main::$is_editor_page ) {
				$args['post_status'] = [ 'publish' ];
			}

			$this->_data['course_count_lessons'] = $this->active_course->count_lessons( $args );
		}

		return $this->_data['course_count_lessons'];
	}

	/**
	 * Returns the total number of lessons inside the course
	 * Used inside the shortcode & localization
	 *
	 * @return int
	 */
	public function get_course_count_items() {
		if ( ! isset( $this->_data['course_count_items'] ) ) {
			$args = [];

			if ( ! Main::$is_editor_page ) {
				$args['post_status'] = [ 'publish' ];
			}

			$this->_data['course_count_items'] = $this->active_course->count_course_items( $args );
		}

		return $this->_data['course_count_items'];
	}


	public function get_module_count_items() {
		if ( ! isset( $this->_data['module_count_items'] ) ) {
			$this->_data['module_count_items'] = $this->get_count_by_type( TVA_Const::MODULE_POST_TYPE, false );
		}

		return $this->_data['module_count_items'];
	}

	public function get_chapter_count_items() {
		if ( ! isset( $this->_data['chapter_count_items'] ) ) {
			$this->_data['chapter_count_items'] = $this->get_count_by_type( TVA_Const::CHAPTER_POST_TYPE, false );
		}

		return $this->_data['chapter_count_items'];
	}


	/**
	 * Returns the completed lessons count for the active course
	 * Used for localization and inside the shortcode
	 *
	 * @return int
	 */
	public function get_course_count_items_completed() {
		if ( ! isset( $this->_data['course_count_items_completed'] ) ) {
			if ( Main::$is_editor_page ) {
				$this->_data['course_count_items_completed'] = tva_count_completed_items( $this->active_course->get_all_items() );
			} else {
				$this->_data['course_count_items_completed'] = tva_count_completed_items( $this->active_course->get_published_items() );
			}
		}

		return $this->_data['course_count_items_completed'];
	}

	public function get_module_count_items_completed() {
		if ( ! isset( $this->_data['module_count_items_completed'] ) ) {
			$this->_data['module_count_items_completed'] = $this->get_count_by_type( TVA_Const::MODULE_POST_TYPE, true );
		}

		return $this->_data['module_count_items_completed'];
	}

	public function get_chapter_count_items_completed() {
		if ( ! isset( $this->_data['chapter_count_items_completed'] ) ) {
			$this->_data['chapter_count_items_completed'] = $this->get_count_by_type( TVA_Const::CHAPTER_POST_TYPE, true );
		}

		return $this->_data['chapter_count_items_completed'];
	}

	public function get_course_structure_label( $name, $type ) {
		$course_structure_labels = TVA_Dynamic_Labels::get( 'course_structure' );

		return isset( $course_structure_labels[ $name ][ $type ] ) ? $course_structure_labels[ $name ][ $type ] : ucfirst( str_replace( '_', ' ', $name ) );
	}

	/**
	 * Counts the completed/uncompleted lessons for modules and chapters
	 *
	 * @param string $post_type
	 * @param false  $count_completed
	 *
	 * @return int|string
	 */
	private function get_count_by_type( $post_type = TVA_Const::MODULE_POST_TYPE, $count_completed = false ) {
		$return = 0;

		if ( ! isset( $this->active_object ) ) {
			return $return;
		}

		/**
		 * @var TVA_Module|TVA_Chapter|null $post
		 */
		$post = $this->active_object->get_the_post()->post_type === $post_type ? $this->active_object : $this->active_object->get_parent_by_type( $post_type );

		if ( ! empty( $post ) ) {
			$items  = $post->get_items();
			$return = $count_completed ? tva_count_completed_items( $items ) : count( $items );
		}

		return $return;
	}

	/**
	 * Maybe go to completed page
	 * Also used in mark_lesson complete ajax
	 *
	 * @return bool
	 */
	public function maybe_go_to_completed_page() {
		if ( empty( $this->get_active_course() ) ) {
			return false;
		}

		if ( ! tva_customer()->has_completed_course( $this->get_active_course() ) ) {
			return false;
		}

		return $this->get_active_course()->has_completed_post() && $this->get_active_course()->get_completed_post()->is_valid();
	}

}

/**
 * Returns an instance of dynamic actions class
 *
 * @return Main
 */
function tcb_tva_dynamic_actions() {
	return Main::get_instance();
}
