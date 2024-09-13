<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

namespace TVA\Architect\Course_List;

use TCB_Course_List_Rest_Controller;
use TCB_Utils;
use TVA\Architect\Utils as Architect_Utils;
use TVA_Const;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Hooks
 *
 * @package  TVA\Architect\Course_List
 * @project  : thrive-apprentice
 */
class Hooks {

	/**
	 * Hooks constructor.
	 */
	public function __construct() {
		add_filter( 'tcb_content_allowed_shortcodes', [ $this, 'content_allowed_shortcodes_filter' ] );

		add_filter( 'tcb_menu_path_course_list', [ $this, 'include_course_list_menu' ], 10, 1 );
		add_filter( 'tcb_menu_path_course_list_dropdown', [ $this, 'include_course_list_dropdown_menu' ], 10, 1 );
		add_filter( 'tcb_menu_path_course_list_item_topic_icon', [ $this, 'include_course_list_item_topic_icon_menu' ], 10, 1 );

		add_filter( 'tcb_element_instances', [ $this, 'tcb_element_instances' ] );

		add_action( 'rest_api_init', [ $this, 'integration_rest_api_init' ] );

		add_action( 'wp_print_footer_scripts', [ $this, 'wp_print_footer_scripts' ], 9 );

		add_filter( 'tcb_modal_templates', [ $this, 'include_modals' ] );

		add_filter( 'tve_frontend_options_data', [ $this, 'tve_frontend_data' ] );

		add_filter( 'tcb_inline_shortcodes', [ $this, 'inline_shortcodes' ] );

		add_filter( 'tcb_dynamiclink_data', [ $this, 'dynamic_links' ] );

		add_filter( 'tcb_waf_fields_restore', [ $this, 'waf_fields_restore' ] );
	}

	/**
	 * Allow the course shortcode to be rendered in the editor
	 *
	 * @param array $shortcodes
	 *
	 * @return array
	 */
	public function content_allowed_shortcodes_filter( $shortcodes = [] ) {

		if ( is_editor_page_raw( true ) ) {
			$shortcodes = array_merge(
				$shortcodes,
				tcb_course_list_shortcode()->get_shortcodes(),
				tcb_course_list_dropdown_shortcode()->get_shortcodes()
			);
		}

		return $shortcodes;
	}

	/**
	 * Includes the course list menu file
	 *
	 * @param $file
	 *
	 * @return mixed|string
	 */
	public function include_course_list_menu( $file ) {

		return TVA_Const::plugin_path( 'tcb-bridge/editor-layouts/menus/course_list.php' );
	}

	/**
	 * Includes the course list dropdown menu file
	 *
	 * @param $file
	 *
	 * @return mixed|string
	 */
	public function include_course_list_dropdown_menu( $file ) {
		return TVA_Const::plugin_path( 'tcb-bridge/editor-layouts/menus/course_list_dropdown.php' );
	}

	/**
	 * Includes the course list item topic icon menu file
	 *
	 * @param $file
	 *
	 * @return mixed|string
	 */
	public function include_course_list_item_topic_icon_menu( $file ) {
		return TVA_Const::plugin_path( 'tcb-bridge/editor-layouts/menus/course-list-item-topic-icon.php' );
	}

	/**
	 * Include the main element and the sub-elements
	 *
	 * @param array $instances
	 *
	 * @return array mixed
	 */
	public function tcb_element_instances( $instances ) {

		$root_path = TVA_Const::plugin_path( 'tcb-bridge/editor-elements/course-list/' );

		/* add the main element */
		$instance = require_once $root_path . '/class-tcb-course-list-element.php';

		$instances[ $instance->tag() ] = $instance;

		$dropdown_instance                      = class_exists( 'TCB_Course_List_Dropdown_Element', false ) ? tcb_elements()->element_factory( 'course-list-dropdown' ) : require_once $root_path . '/class-tcb-course-list-dropdown-element.php';
		$instances[ $dropdown_instance->tag() ] = $dropdown_instance;

		/* include this before we include the dependencies */
		require_once $root_path . '/class-abstract-course-list-sub-element.php';

		$sub_element_path = $root_path . '/sub-elements';

		return array_merge( $instances, Architect_Utils::get_tcb_elements( $root_path, $sub_element_path ) );
	}

	/**
	 * Includes the REST Class
	 */
	public function integration_rest_api_init() {
		new TCB_Course_List_Rest_Controller();
	}

	/**
	 * Localize the course list data on the frontend so we can use it for pagination
	 */
	public function wp_print_footer_scripts() {
		if ( ! TCB_Editor()->is_inner_frame() && ! Main::$is_editor_page && ! empty( $GLOBALS['tva_course_list_localize'] ) ) {
			foreach ( $GLOBALS['tva_course_list_localize'] as $course_list ) {

				echo TCB_Utils::wrap_content(
					str_replace( [ '[', ']' ], [ '{({', '})}' ], $course_list['content'] ),
					'script',
					'',
					'tcb-course-list-template',
					[
						'type'            => 'text/template',
						'data-identifier' => $course_list['template'],
					]
				);
			}

			/* remove the course content before localizing */
			$courses_localize = array_map(
				function ( $item ) {
					unset( $item['content'] );

					return $item;
				}, $GLOBALS['tva_course_list_localize'] );

			$script_contents = "var tva_course_lists=JSON.parse('" . addslashes( json_encode( $courses_localize ) ) . "');";

			echo TCB_Utils::wrap_content( $script_contents, 'script', '', '', [ 'type' => 'text/javascript' ] );
		}
	}

	/**
	 * Add some data to the frontend localized object
	 *
	 * @param $data
	 *
	 * @return mixed
	 */
	public function tve_frontend_data( $data ) {

		if ( ! empty( $data['routes'] ) ) {
			$data['routes']['courses'] = get_rest_url( get_current_blog_id(), TVA_Const::REST_NAMESPACE . '/course_list_element' );
		}

		return $data;
	}

	/**
	 * Adds the course list element inline shortcodes
	 *
	 * @param array $shortcodes
	 *
	 * @return array
	 */
	public function inline_shortcodes( $shortcodes = [] ) {

		$shortcodes = array_merge_recursive( [
			'Apprentice course data' => [
				[
					'option' => __( 'Course title', 'thrive-apprentice' ),
					'value'  => 'tva_course_list_item_name',
					'input'  => $this->get_link_configuration(),
				],
				[
					'option' => __( 'Course summary', 'thrive-apprentice' ),
					'value'  => 'tva_course_list_item_description',
				],
				[
					'option' => __( 'Course author name', 'thrive-apprentice' ),
					'value'  => 'tva_course_list_item_author_name',
				],
				[
					'option' => __( 'Course type', 'thrive-apprentice' ),
					'value'  => 'tva_course_list_item_type',
				],
				[
					'option' => __( 'Course label', 'thrive-apprentice' ),
					'value'  => 'tva_course_list_item_label_title',
				],
				[
					'option' => __( 'Course call to action label', 'thrive-apprentice' ),
					'value'  => 'tva_course_list_item_action_label',
				],
				[
					'option' => __( 'Course topic', 'thrive-apprentice' ),
					'value'  => 'tva_course_list_item_topic_title',
				],
				[
					'option' => __( 'Number of lessons in course', 'thrive-apprentice' ),
					'value'  => 'tva_course_list_item_lessons_number',
				],
				[
					'option' => __( 'Course progress status', 'thrive-apprentice' ),
					'value'  => 'tva_course_list_item_progress',
				],
				[
					'option' => __( 'Course progress', 'thrive-apprentice' ),
					'value'  => 'tva_course_list_item_progress_percentage',
				],
				[
					'option' => __( 'Course difficulty level', 'thrive-apprentice' ),
					'value'  => 'tva_course_list_item_difficulty_name',
				],
				[
					'option' => __( 'Module count with label', 'thrive-apprentice' ),
					'value'  => 'tva_course_list_item_module_number_with_label',
				],
				[
					'option' => __( 'Chapter count with label', 'thrive-apprentice' ),
					'value'  => 'tva_course_list_item_chapter_number_with_label',
				],
				[
					'option' => __( 'Lesson count with label', 'thrive-apprentice' ),
					'value'  => 'tva_course_list_item_lesson_number_with_label',
				],
			],
		], $shortcodes );

		return $shortcodes;
	}

	/**
	 * Add the Course List Links to the list of dynamic links
	 *
	 * @param array $data
	 *
	 * @return array|mixed
	 */
	public function dynamic_links( $data = [] ) {

		$data['Apprentice Course List'] = [
			'links'     => [
				[
					[
						'name'  => __( 'Course URL', 'thrive-apprentice' ),
						'label' => __( 'Course URL', 'thrive-apprentice' ),
						'url'   => '',
						'show'  => true,
						'id'    => 'tva_course_list_item_permalink', //This ID will be replace in the frontend with the actual content ID
					],
				],
			],
			'shortcode' => 'tva_course_list_item_permalink',
		];

		return $data;
	}

	/**
	 * Pushed the course list content key for restore_post_waf_content to search in that key also
	 *
	 * @param array $field_list
	 *
	 * @return array
	 * @see TCB_Utils::restore_post_waf_content
	 */
	public function waf_fields_restore( $field_list = [] ) {
		$field_list[] = 'content';

		return $field_list;
	}


	/**
	 * Include modal files
	 *
	 * @param array $files
	 *
	 * @return array
	 */
	public function include_modals( $files = [] ) {

		$files[] = TVA_Const::plugin_path( 'tcb-bridge/editor-layouts/modals/course-list-query.php' );

		return $files;
	}

	/**
	 * Course List inline shortcode link configuration
	 *
	 * @return array[]
	 */
	private function get_link_configuration() {
		return [
			'link'   => [
				'type'  => 'checkbox',
				'label' => __( 'Link to content', 'thrive-apprentice' ),
				'value' => true,
			],
			'target' => [
				'type'       => 'checkbox',
				'label'      => __( 'Open in new tab', 'thrive-apprentice' ),
				'value'      => false,
				'disable_br' => true,
			],
			'rel'    => [
				'type'  => 'checkbox',
				'label' => __( 'No follow', 'thrive-apprentice' ),
				'value' => false,
			],
		];
	}
}
