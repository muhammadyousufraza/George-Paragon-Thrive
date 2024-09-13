<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

namespace TVA\Architect\Course\Shortcodes;

use TCB_Utils;
use TVA\Architect\Course as Course;
use TVA\Architect\Utils;
use TVA\Course\Structure\TVA_Course_Structure;
use TVA_Chapter;
use TVA_Const;
use TVA_Course_V2;
use TVA_Lesson;
use TVA_Module;
use TVA_Post;
use TVD_Global_Shortcodes;
use function TVA\Architect\Course\tcb_course_shortcode;
use function TVA\Architect\Dynamic_Actions\tcb_tva_dynamic_actions;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Shortcodes
 *
 * @package  TVA\Architect\Course
 * @project  : thrive-apprentice
 */
class Shortcodes {
	const TVA_NO_DRAG_CLASS  = 'tva-course-no-drag';
	const TVE_NO_DRAG_CLASS  = 'tve_no_drag'; /* used to be compatible with TAR dragging logic */
	const TVA_ALLOW_COLLAPSE = 'tva-course-allow-collapse'; /*used as a flag for shortcode to test if an item can be collapsed*/
	/**
	 * Only Editor Class - hides the element from the breadcrumbs
	 */
	const TVE_NO_BREADCRUMBS = 'breadcrumbs-hidden';

	const THRV_WRAPPER_CLASS = 'thrv_wrapper';

	/**
	 * @var array Stores the number of lessons in each module/chapter for the currently rendered lesson list
	 */
	public static $child_count_per_element = array();
	/**
	 * Contains the List of Shortcodes
	 *
	 * @var array
	 */
	protected $shortcodes = array();

	/**
	 * @var TVA_Post|TVA_Module|TVA_Chapter|TVA_Lesson
	 */
	protected $active_item;

	/**
	 * States identifiers
	 *
	 * @var int[]
	 */
	private $states = array(
		'not_completed' => 0,
		'no_access'     => 1,
		'completed'     => 2,
		'progress'      => 3,
		'locked'        => 4,
	);

	public $type;

	/**
	 * Shortcodes constructor.
	 *
	 * @param string $type
	 */
	public function __construct( $type = '' ) {
		$this->type = $type;
		$this->init_list_shortcodes( $type );
		$this->init_items_shortcodes( $type );
	}

	/**
	 * Shortcodes related to list
	 *
	 * @param $type
	 *
	 * @return void
	 */
	public function init_list_shortcodes( $type ) {
		$list_shortcodes = array(
			'tva_course_' . $type . '_children_count'            => 'children_count',
			'tva_course_' . $type . '_children_count_with_label' => 'children_count_with_label',
			'tva_course_' . $type . '_children_completed'        => 'children_completed',
			'tva_course_' . $type . '_restriction_label'         => 'restriction_label',
			'tva_course_' . $type . '_list'                      => 'items',
		);

		$this->shortcodes = array_merge( $this->shortcodes, $list_shortcodes );

		foreach ( $list_shortcodes as $shortcode => $function ) {
			add_shortcode( $shortcode, array( $this, $function ) );
		}
	}

	/**
	 * Shortcodes related to items
	 *
	 * @param $type
	 *
	 * @return void
	 */
	public function init_items_shortcodes( $type ) {
		$items_shortcodes = [
			'tva_course_' . $type . '_begin'       => 'item_begin',
			'tva_course_' . $type . '_state'       => 'item_state',
			'tva_course_' . $type . '_end'         => 'item_end',
			'tva_course_' . $type . '_title'       => 'title',
			'tva_course_' . $type . '_description' => 'description',
			'tva_course_' . $type . '_url'         => 'url',
			'tva_course_' . $type . '_type'        => 'type_label',
			'tva_course_' . $type . '_index'       => 'index',
			'tva_course_' . $type . '_type_icon'   => 'type_icon',
			'tva_course_' . $type . '_status'      => 'status', //Completed | Not Completed
			'tva_course_' . $type . '_template'    => 'item_template',
		];

		$this->shortcodes = array_merge( $this->shortcodes, $items_shortcodes );

		foreach ( $items_shortcodes as $shortcode => $function ) {
			add_shortcode( $shortcode, array( $this, $function ) );
		}
	}

	/**
	 * Renders the title shortcode
	 *
	 * @param array $attr
	 *
	 * @return string
	 */
	public function title( $attr = array() ) {
		if ( empty( $this->active_item ) ) {
			return '';
		}

		$title = $this->active_item->post_title;

		if ( ! empty( $attr['link'] ) ) {
			$attributes = array(
				'href' => get_permalink( $this->active_item->ID ),
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

			$title = TCB_Utils::wrap_content( $title, 'a', '', array(), $attributes );
		} else {
			$title = TVD_Global_Shortcodes::maybe_link_wrap( $title, $attr );
		}

		return $title;
	}

	/**
	 * Renders the description shortcode
	 *
	 * @param array $attr
	 *
	 * @return string
	 */
	public function description( $attr = array() ) {
		if ( empty( $this->active_item ) ) {
			return '';
		}

		$post_excerpt = strip_tags( $this->active_item->post_excerpt );

		return TVD_Global_Shortcodes::maybe_link_wrap( $post_excerpt, $attr );
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

		if ( empty( $this->active_item ) ) {
			return '[' . $tag . ']';
		}

		return get_permalink( $this->active_item->ID );
	}

	/**
	 * Index shortcode callback
	 *
	 * @return mixed|string|null
	 */
	public function index() {
		if ( empty( $this->active_item ) ) {
			return '';
		}

		return $this->active_item->index;
	}

	/**
	 * Type label shortcode callback
	 *
	 * @param array  $attr
	 * @param string $content
	 * @param string $tag
	 *
	 * @return string
	 */
	public function type_label( $attr = array() ) {
		if ( empty( $this->active_item ) ) {
			return '';
		}

		$type      = trim( $this->active_item->get_type() );
		$label_map = [
			'youtube_link'  => 'assessments_youtube',
			'upload'        => 'assessments_upload',
			'tqb'           => 'assessments_quiz',
			'external_link' => 'assessments_external',
		];

		//Computes the types for UI:
		if ( ! empty( $label_map[ $type ] ) ) {
			$type = trim( tcb_tva_dynamic_actions()->get_course_structure_label( $label_map[ $type ], 'singular' ) );
		}

		return TVD_Global_Shortcodes::maybe_link_wrap( $type, $attr );
	}

	/**
	 * Type icon shortcode callback
	 *
	 * @param array $attr
	 *
	 * @return string
	 */
	public function type_icon( $attr = array() ) {
		if ( empty( $this->active_item ) ) {
			return '';
		}

		return TCB_Utils::wrap_content( tcb_course_shortcode()->get_type_icon( $this->active_item ), 'div', '', array(
			static::THRV_WRAPPER_CLASS,
			'tva-course-type-icon',
			'tve-auxiliary-icon-element',
		), array(
			'data-css' => ! empty( $attr['css'] ) ? $attr['css'] : '',
		) );
	}

	/**
	 * Render the status shortcode
	 *
	 * @return string
	 */
	public function status() {
		if ( empty( $this->active_item ) ) {
			return '';
		}

		$label = $this->active_item->is_completed() ? 'item_completed' : 'item_not_completed';

		if ( $this->active_item instanceof \TVA_Assessment ) {
			$latest_user_assessment = current( \TVA\Assessments\TVA_User_Assessment::get_user_submission( [ 'post_parent' => $this->active_item->ID, 'posts_per_page' => 1 ] ) );

			if ( ! empty( $latest_user_assessment ) ) {
				$label = 'assessment_not_graded';
				$status = $latest_user_assessment->status;

				switch ( $status ) {
					case TVA_Const::ASSESSMENT_STATUS_COMPLETED_FAILED:
						$label = 'assessments_fail';
						break;
					case TVA_Const::ASSESSMENT_STATUS_COMPLETED_PASSED:
						$label = 'assessments_pass';
						break;
					default:
						break;
				}
			}
		}

		return tcb_tva_dynamic_actions()->get_course_structure_label( $label, 'singular' );
	}

	/**
	 * Added the Children Count ShortCode
	 *
	 * @param array  $attr
	 * @param string $content
	 * @param string $tag
	 *
	 * @return string
	 */
	public function children_count() {
		if ( empty( $this->active_item ) ) {
			return '';
		}

		return count( $this->active_item->get_visible_lessons() );
	}

	/**
	 * Children count with label shortcode
	 *
	 * @param array  $attr
	 * @param string $content
	 * @param string $tag
	 *
	 * @return string
	 */
	public function children_count_with_label() {
		if ( empty( $this->active_item ) ) {
			return '';
		}

		return tcb_tva_dynamic_actions()->get_children_count_with_label( $this->active_item );
	}

	/**
	 * Renders the children completed shortcode
	 *
	 * @return int
	 */
	public function children_completed() {
		if ( empty( $this->active_item ) ) {
			return '';
		}

		return tva_count_completed_items( $this->active_item->get_lessons() );
	}

	/**
	 * Renders the children completed shortcode
	 *
	 * @return string
	 */
	public function restriction_label() {
		if ( empty( $this->active_item ) ) {
			return '';
		}

		return tcb_course_shortcode()->get_course_label();
	}

	/**
	 * @param array  $attr
	 * @param string $content
	 * @param string $shortcode
	 *
	 * @return string
	 */
	public function item_template( $attr = array(), $content = '', $shortcode = '' ) {
		if ( Course\Main::$is_editor_page ) {
			return TCB_Utils::wrap_content( $content, 'div', '', [ str_replace( '_', '-', $shortcode ) ], [ 'style' => 'display:none' ] );
		}

		return '';
	}

	/**
	 * Checks if element in lesson list is visible or should be hidden
	 *
	 * @return bool
	 */
	private function get_post_visibility() {
		$has_access_to_post = tva_access_manager()->has_access_to_object( $this->active_item->get_the_post() );
		$not_a_chapter      = $this->active_item->post_type !== TVA_Const::CHAPTER_POST_TYPE;
		$is_visible         = $this->active_item->is_content_visible();
		$is_locked          = tva_access_manager()->is_object_locked( $this->active_item->get_the_post() );

		if ( $has_access_to_post && $not_a_chapter && ! $is_visible && $is_locked ) {
			return false;
		}

		return true;
	}

	/**
	 * Renders the list shortcode
	 *
	 * @param array  $attr
	 * @param string $content
	 *
	 * @return string
	 */
	public function items( $attr = array(), $content = '' ) {
		$return = '';

		$structure = $this->get_structure();
		$counter   = 0;

		if ( Course\Main::$parent_item instanceof TVA_Course_V2 ) {
			static::$child_count_per_element = array();
		}

		/**
		 * @var $post TVA_Post
		 */
		foreach ( $structure as $post ) {

			$this->set_active_item( null );

			if ( in_array( $post->post_type, $this->get_post_type() ) ) {
				$this->set_active_item( $post );

				if ( ! Course\Main::$is_editor_page && ( ! $post->is_published() || ! $this->get_post_visibility() ) ) {
					continue;
				}

				$this->active_item->index = ++ $counter;

				if ( ! empty( Course\Main::$display_level ) ) {

					if ( $this->active_item->ID === Course\Main::$display_level->post_parent ) {
						/**
						 * If the display_level is a chapter, and the active item is a module,
						 * we must render the active item content to skipp the module
						 */
						$this->set_active_item( null );
						break;
					}

					$ancestors = get_post_ancestors( $this->active_item->get_the_post() );
					if ( $this->active_item->ID !== Course\Main::$display_level->ID && ! in_array( Course\Main::$display_level->ID, $ancestors ) ) {
						continue;
					}
				}

				$attributes = array(
					'data-index'        => $this->active_item->index,
					'data-id'           => $post->ID,
					'data-type'         => str_replace( 'tva_', '', $post->post_type ),
					'data-course-state' => $this->get_active_state(),
				);

				$item_class = $this->get_item_class( $this->active_item->post_type );
				$classes    = array( $item_class, static::THRV_WRAPPER_CLASS, static::TVA_NO_DRAG_CLASS, static::TVE_NO_DRAG_CLASS, static::TVA_ALLOW_COLLAPSE );


				if ( empty( $content ) ) {
					$content = $this->get_default_content( $content );
				}

				/**
				 * We make a clone of the content not to affect the original content
				 */
				$prepared_content = $content;

				$structure = $this->active_item->load_structure();
				if ( is_null( $structure ) || count( $structure ) === 0 ) {
					/**
					 * If there are no children, we render the item content.
					 *
					 * Handles the use case for dynamic links inside the item content
					 */
					$prepared_content = $this->prepare_content( $prepared_content );

					$post_parent = $this->active_item->post_parent;
					//increases the count of parents lessons
					static::$child_count_per_element[ $post_parent ] = isset( static::$child_count_per_element[ $post_parent ] ) ? static::$child_count_per_element[ $post_parent ] : 0;
					static::$child_count_per_element[ $post_parent ] ++;

					if ( get_post_type( $post_parent ) === TVA_Const::CHAPTER_POST_TYPE ) {
						$module_id = wp_get_post_parent_id( $post_parent );

						if ( $module_id ) {
							//this lesson is inside a chapter and a module, so we increase the module's lesson count
							static::$child_count_per_element[ $module_id ] = isset( static::$child_count_per_element[ $module_id ] ) ? static::$child_count_per_element[ $module_id ] : 0;
							static::$child_count_per_element[ $module_id ] ++;
						}
					}
				}

				/**
				 * we need to call do_shortcode in order to execute the logic for the current item's structure as well
				 * to have the static $child_count_per_element have the needed value
				 **/
				$rendered_children              = do_shortcode( $prepared_content );
				$attributes['data-child-count'] = isset( static::$child_count_per_element[ $this->active_item->ID ] ) ? static::$child_count_per_element[ $this->active_item->ID ] : 0;
				$return                         .= TCB_Utils::wrap_content( $rendered_children, 'div', '', $classes, $attributes );
			}
		}

		if ( empty( $this->active_item ) && $this->get_post_type()[0] !== TVA_Const::LESSON_POST_TYPE ) {

			if ( Course\Main::$is_editor_page ) {
				$item_of_type = TCB_Utils::wrap_content( do_shortcode( $content ), 'div', '', array(
					"tva-course-$this->type",
					static::THRV_WRAPPER_CLASS,
					static::TVA_NO_DRAG_CLASS,
					static::TVE_NO_DRAG_CLASS,
					static::TVE_NO_BREADCRUMBS,
				), array() );

				$return .= TCB_Utils::wrap_content( $item_of_type, 'div', '', array(
					"tva-course-$this->type-list",
					static::THRV_WRAPPER_CLASS,
					static::TVA_NO_DRAG_CLASS,
					static::TVE_NO_DRAG_CLASS,
					static::TVE_NO_BREADCRUMBS,
				), array() );
			} else {
				/**
				 * If the system reaches this point it means that there is no (module|chapter|lesson) for the defined structure
				 * We must render the content to display the List
				 */
				$return .= do_shortcode( $content );
			}
		} else {
			/**
			 * We have the active item, we need to wrap the content inside the active item wrapper
			 */
			$classes    = array( "tva-course-$this->type-list", static::THRV_WRAPPER_CLASS, static::TVA_NO_DRAG_CLASS, static::TVE_NO_DRAG_CLASS );
			$attributes = array();
			$return     = TCB_Utils::wrap_content( $return, 'div', '', $classes, $attributes );
		}

		/**
		 * On the same request this renderer might be executed multiple times
		 * and we need to invalidate the cached active item in order to avoid
		 * calculation of dynamic data for the same item
		 * e.g.: dynamic links for a lesson start buttons inside a conditional display
		 */
		$this->set_active_item( null );

		return $return;
	}

	/**
	 * Can be extended by the child classes
	 *
	 * @param string $content
	 *
	 * @return mixed
	 */
	public function get_default_content( $content ) {
		return $content;
	}

	/**
	 * Renders the dropzone before the element
	 *
	 * @param array  $attr
	 * @param string $content
	 *
	 * @return string
	 */
	public function item_begin( $attr = array(), $content = '' ) {

		if ( empty( $this->active_item ) && ! Course\Main::$is_editor_page ) {
			return '';
		}

		$classes = array( "tva-course-$this->type-dropzone", 'tva-course-dropzone', static::THRV_WRAPPER_CLASS, static::TVA_NO_DRAG_CLASS, static::TVE_NO_DRAG_CLASS );

		if ( empty( Course\Main::$course_attr["deny-collapse-$this->type"] ) ) {

			if ( ! empty( Course\Main::$course_attr["default-state"] ) ) {
				$classes[] = 'tve-state-expanded';
			} else if ( tcb_course_shortcode()->allow_smart_autocollapse() && ! empty( $this->active_item ) && ! tcb_course_shortcode()->should_be_expanded( $this->active_item->ID ) ) {
				$classes[] = 'tve-state-expanded tva-smart-autocollapse';
			}
		}

		$attributes = array();

		if ( empty( $content ) ) {
			if ( empty( $this->active_item->post_type ) ) {
				$item_type = $this->get_post_type()[0];
			} else {
				$item_type = $this->active_item->post_type;
			}

			$item_type = str_replace( 'tva_', '', $item_type );
			$content   = static::get_default_template( $item_type );
		}

		$content = $this->prepare_content( $content );

		if ( empty( $this->active_item ) ) {
			$classes[] = 'tcb-permanently-hidden';
		}

		return TCB_Utils::wrap_content( do_shortcode( $content ), 'div', '', $classes, $attributes );
	}

	/**
	 * Logic for displaying the state content
	 * On the user side, we display content with respect to the user state
	 *
	 * @param array  $attr
	 * @param string $content
	 *
	 * @return string
	 */
	public function item_state( $attr = array(), $content = '' ) {
		if ( ! isset( $attr['course-state'] ) ) {
			/**
			 * This should be deleted for next release
			 */
			return do_shortcode( $content );
		}

		$classes    = 'tva-course-state';
		$attributes = array();

		if ( ! empty( $attr ) ) {
			foreach ( $attr as $key => $value ) {
				if ( $key !== 'class' ) { /* we don't want data-class to persist, we process it inside get_classes() */
					$attributes[ 'data-' . $key ] = esc_attr( $value );
				}
			}
		}

		$course_state = (int) $attributes['data-course-state'];

		if ( Course\Main::$is_editor_page ) {
			if ( $course_state !== 0 ) {
				$attributes['style'] = 'display:none;';
			}

			return TCB_Utils::wrap_content( do_shortcode( $content ), 'div', '', $classes, $attributes );
		}

		if ( empty( $this->active_item ) ) {
			/**
			 * Security check
			 */
			return TCB_Utils::wrap_content( do_shortcode( $content ), 'div', '', $classes, $attributes );
		}

		if ( $course_state !== $this->get_active_state() ) {
			return '';
		}

		return TCB_Utils::wrap_content( do_shortcode( $content ), 'div', '', $classes, $attributes );
	}

	/**
	 * @param string $item_type
	 *
	 * @return string
	 */
	public static function get_default_template( $item_type ) {
		ob_start();

		require( Utils::get_integration_path( "classes/course/default-content/$item_type.php" ) );

		$content = ob_get_contents();

		ob_end_clean();

		return $content;
	}

	/**
	 * Renders the dropzone after the element
	 *
	 * @param array  $attr
	 * @param string $content
	 *
	 * @return string
	 */
	public function item_end( $attr = array(), $content = '' ) {
		/**
		 * For now we do not render this shortcode
		 *
		 * In the future, extra functionality will be added here
		 */
		return '';
	}

	/**
	 * Returns the class shortcodes names as a list
	 *
	 * @return array
	 */
	public function get_shortcodes() {
		return array_keys( $this->shortcodes );
	}

	/**
	 * Sets the Class Active Item
	 *
	 * Should be extended by child classes
	 *
	 * @param $data null|TVA_Post
	 */
	protected function set_active_item( $data ) {

		$this->active_item = $data;

		if ( $data ) {
			Course\Main::$parent_item = $data;
		}
	}

	/**
	 * Returns the structure
	 *
	 * Extended in child classes
	 *
	 * @return TVA_Course_Structure
	 */
	protected function get_structure() {
		return Course\Main::$parent_item->structure;
	}

	/**
	 * Returns the shortcode post type
	 *
	 * Should be extended by child classes
	 *
	 * @return array
	 */
	protected function get_post_type() {
		return [];
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
	 * Builds the state content
	 *
	 * 1. If is_editor_page -> output all the states with default state displayed
	 * 2. If is on frontend -> output only the active user state
	 *
	 * @param string $content
	 *
	 * @return string
	 */
	private function parse_states_content( $content = '' ) {

		if ( Course\Main::$is_editor_page ) {
			$search  = [];
			$replace = [];

			foreach ( Course\Main::STATES as $state ) {
				if ( $state ) {
					$search[]  = 'data-course-state="' . $state . '"';
					$replace[] = 'data-course-state="' . $state . '" style="display:none;"';
				}
			}

			$content = str_replace( $search, $replace, $content );
		} else {
			$active_state = $this->get_active_state();

			foreach ( array_values( $this->states ) as $state ) {
				if ( $active_state === $state ) {
					continue;
				}

				$search  = array( 'data-course-state="' . $state . '"' );
				$replace = array( 'data-course-state="' . $state . '" style="display:none;" data-tva-remove-state="1"' );

				$content = str_replace( $search, $replace, $content );
			}
		}

		return $content;
	}

	/**
	 * Returns the user active state
	 *
	 * @return int
	 */
	private function get_active_state() {

		if ( Course\Main::$is_editor_page ) {
			/**
			 * If is_editor_page, we return the not completed state
			 */
			return $this->states['not_completed'];
		}

		if ( is_numeric( $this->active_item->active_state ) ) {
			return $this->active_item->active_state;
		}

		$state = $this->states['not_completed'];

		if ( $this->active_item->is_completed() ) {
			$state = $this->states['completed'];
		} elseif ( ! empty( Course\Main::$course_attr["progress-state-enabled"] ) && $this->active_item->is_in_progress() ) {
			$state = $this->states['progress'];
		}

		if ( ! tva_access_manager()->has_freemium_access( $this->active_item->get_the_post() ) && ( ! tva_access_manager()->has_access_to_object( $this->active_item->get_the_post() ) || ! tva_access_manager()->has_access_to_object( $this->active_item->get_course_v2()->get_wp_term() ) ) ) {
			$state = $this->states['no_access'];
		} else if ( $this->active_item->is_locked() ) {
			/**
			 * If a lesson is locked and the lesson list has the locked state enabled, we display the locked state
			 * Otherwise we display the no access state
			 */
			$state = ! empty( Course\Main::$course_attr['locked-state-enabled'] ) ? $this->states['locked'] : $this->states['no_access'];
		}

		/**
		 * Cache the active state to avoid the same logic called multiple times
		 */
		$this->active_item->active_state = $state;

		return $state;
	}

	/**
	 * Prepares the content for display
	 *
	 * @param string $content
	 *
	 * @return string
	 */
	private function prepare_content( $content = '' ) {

		$content = $this->construct_inline_shortcodes( $content );

		return $this->parse_states_content( $content );
	}

	protected function get_item_class( $post_type = '' ) {
		return 'tva-course-' . $this->type;
	}
}
