<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

namespace TVA\Architect\Course;

use TCB_Course_Rest_Controller;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Hooks
 *
 * @package TVA\Architect\Course
 */
class Hooks {

	/**
	 * Hooks constructor.
	 */
	public function __construct() {
		add_filter( 'tcb_content_allowed_shortcodes', array( $this, 'content_allowed_shortcodes_filter' ) );

		add_filter( 'tcb_element_instances', array( $this, 'tcb_element_instances' ) );

		add_filter( 'tcb_menu_path_course', array( $this, 'tva_include_course_menu' ), 10, 1 );
		add_filter( 'tcb_menu_path_course-structure-item', array( $this, 'tva_include_course_structure_item_menu' ), 10, 1 );

		add_action( 'rest_api_init', array( $this, 'tva_integration_rest_api_init' ) );

		add_filter( 'tcb_inline_shortcodes', array( $this, 'inline_shortcodes' ) );

		add_filter( 'tcb_dynamiclink_data', array( $this, 'dynamic_links' ) );
	}

	/**
	 * Allow the course shortcode to be rendered in the editor
	 *
	 * @param $shortcodes
	 *
	 * @return array
	 */
	public function content_allowed_shortcodes_filter( $shortcodes ) {
		if ( is_editor_page_raw( true ) ) {
			$shortcodes = array_merge( $shortcodes, tcb_course_shortcode()->get_shortcodes() );
		}

		return $shortcodes;
	}

	/**
	 * Include the main element and the sub-elements
	 *
	 * @param $instances
	 *
	 * @return mixed
	 */
	public function tcb_element_instances( $instances ) {

		$root_path = \TVA\Architect\Utils::get_integration_path( 'editor-elements/course' );

		/* add the main element */
		$instance = require_once $root_path . '/class-tcb-course-element.php';

		$instances[ $instance->tag() ] = $instance;

		/* include this before we include the dependencies */
		require_once $root_path . '/class-abstract-course-structure-sub-element.php';

		$sub_element_path = $root_path . '/sub-elements';

		return array_merge( $instances, \TVA\Architect\Utils::get_tcb_elements( $root_path, $sub_element_path ) );
	}

	public function tva_integration_rest_api_init() {
		new TCB_Course_Rest_Controller();
	}

	/**
	 * @param $file
	 *
	 * @return string
	 */
	public function tva_include_course_menu( $file ) {
		return \TVA\Architect\Utils::get_integration_path( 'editor-layouts/menus/course.php' );
	}

	/**
	 * Course Structure Item Menu
	 *
	 * @param $file
	 *
	 * @return string
	 */
	public function tva_include_course_structure_item_menu( $file ) {
		return \TVA\Architect\Utils::get_integration_path( 'editor-layouts/menus/course-structure-item.php' );
	}

	/**
	 * Adds the course element inline shortcodes
	 *
	 * @param array $shortcodes
	 *
	 * @return array
	 */
	public function inline_shortcodes( $shortcodes = array() ) {
		$shortcodes = array_merge_recursive( array(
			'Apprentice fields' => array(
				array(
					'option' => __( 'Title', 'thrive-apprentice' ),
					'value'  => 'tva_course_title',
					'input'  => $this->get_link_configuration(),
				),
				array(
					'option'   => __( 'Description', 'thrive-apprentice' ),
					'value'    => 'tva_course_description',
					'only_for' => array( 'course', 'course-module', 'course-lesson', 'course-assessment' ),
				),
				array(
					'option'   => __( 'Index', 'thrive-apprentice' ),
					'value'    => 'tva_course_index',
					'only_for' => array( 'course-module', 'course-chapter', 'course-lesson', 'course-assessment' ),
				),
				array(
					'option'   => __( 'Status', 'thrive-apprentice' ),
					'value'    => 'tva_course_status',
					'only_for' => array( 'course-lesson', 'course-assessment' ),
				),
				array(
					'option'   => __( 'Total lessons', 'thrive-apprentice' ),
					'value'    => 'tva_course_children_count',
					'only_for' => array( 'course-module', 'course-chapter' ),
				),
				array(
					'option'   => __( 'Completed lessons', 'thrive-apprentice' ),
					'value'    => 'tva_course_children_completed',
					'only_for' => array( 'course-module', 'course-chapter' ),
				),
				array(
					'option'   => __( 'Lesson count with label', 'thrive-apprentice' ),
					'value'    => 'tva_course_children_count_with_label',
					'only_for' => array( 'course-module', 'course-chapter' ),
				),
				array(
					'option'   => __( 'Course type', 'thrive-apprentice' ),
					'value'    => 'tva_course_type',
					'only_for' => array( 'course', 'course-lesson', 'course-assessment' ),
				),
				array(
					'option'   => __( 'Course difficulty level', 'thrive-apprentice' ),
					'value'    => 'tva_course_difficulty',
					'only_for' => array( 'course' ),
				),
				array(
					'option'   => __( 'Topic title', 'thrive-apprentice' ),
					'value'    => 'tva_course_topic',
					'only_for' => array( 'course' ),
				),
				array(
					'option'   => __( 'Access restriction label', 'thrive-apprentice' ),
					'value'    => 'tva_course_restriction_label',
					'only_for' => array( 'course-module', 'course-chapter', 'course-lesson', 'course-assessment' ),
				),
			),
		), $shortcodes );

		return $shortcodes;
	}

	/**
	 * Add the Course Links to the list of dynamic links
	 *
	 * @param array $data
	 *
	 * @return array
	 */
	public function dynamic_links( $data = array() ) {

		$data['Apprentice course links'] = array(
			'links'     => array(
				array(
					array(
						'name'  => __( 'Content URL', 'thrive-apprentice' ),
						'label' => __( 'Content URL', 'thrive-apprentice' ),
						'url'   => '',
						'show'  => true,
						'id'    => 'tva_course_content_url', //This ID will be replace in the frontend with the actual content ID
					),
				),
			),
			'shortcode' => 'tva_course_content_url',
		);

		return $data;
	}

	private function get_link_configuration() {
		return array(
			'link'   => array(
				'type'  => 'checkbox',
				'label' => __( 'Link to content', 'thrive-apprentice' ),
				'value' => true,
			),
			'target' => array(
				'type'       => 'checkbox',
				'label'      => __( 'Open in new tab', 'thrive-apprentice' ),
				'value'      => false,
				'disable_br' => true,
			),
			'rel'    => array(
				'type'  => 'checkbox',
				'label' => __( 'No follow', 'thrive-apprentice' ),
				'value' => false,
			),
		);
	}
}
