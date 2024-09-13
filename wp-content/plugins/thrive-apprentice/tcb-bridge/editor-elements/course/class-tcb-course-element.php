<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

use function TVA\Architect\Visual_Builder\tcb_tva_visual_builder;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class TCB_Course_Element
 *
 * @project  : thrive-apprentice
 */
class TCB_Course_Element extends TCB_Cloud_Template_Element_Abstract {

	/**
	 * TCB_Course_Element constructor.
	 *
	 * @param string $tag
	 */
	public function __construct( $tag = '' ) {
		parent::__construct( $tag );

		add_filter( 'tcb_categories_order', array( $this, 'add_category_to_order' ) );

		add_action( 'tcb_before_get_content_template', array( $this, 'before_content_template' ), 10, 2 );
	}

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Lesson List', 'thrive-apprentice' );
	}

	/**
	 * Get element alternate
	 *
	 * @return string
	 */
	public function alternate() {
		return 'course, list';
	}

	/**
	 * Decide when to hide the TCB_Course_Element
	 *
	 * @return bool
	 */
	public function hide() {

		if ( Thrive_Utils::is_theme_template() ) {
			return ! tva_is_apprentice_template();
		}

		return parent::hide();
	}

	/**
	 * Element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return TVA\Architect\Course\Main::IDENTIFIER;
	}

	/**
	 * Return icon class needed for display in menu
	 *
	 * @return string
	 */
	public function icon() {
		return 'course-structure';
	}

	/**
	 * Element category that will be displayed in the sidebar
	 *
	 * @return string
	 */
	public function category() {
		return tva_is_apprentice_template() ? tcb_tva_visual_builder()->get_elements_category() : $this->get_thrive_integrations_label();
	}

	/**
	 * Element HTML
	 *
	 * @return string
	 */
	public function html() {
		$content = '';

		ob_start();
		include TVA_Const::plugin_path( 'tcb-bridge/editor-layouts/elements/course.php' );
		$content = ob_get_contents();
		ob_end_clean();

		return $content;
	}

	/**
	 * This is only a placeholder element
	 *
	 * @return bool
	 */
	public function is_placeholder() {
		return false;
	}

	/**
	 * Called from tcb_categories_order filter
	 *
	 * Adds elements_group_label category to order array
	 *
	 * @param array $order
	 */
	public function add_category_to_order( $order = array() ) {
		$order[3] = $this->elements_group_label();

		return $order;
	}

	/**
	 * Before the course content template gets applied modify the post_content to include the active course ID
	 *
	 * @param WP_Post $post
	 * @param array   $meta
	 */
	public function before_content_template( $post, $meta ) {

		if ( is_array( $meta ) && $meta['type'] === 'course' && ! empty( $_REQUEST['course_id'] ) && is_numeric( $_REQUEST['course_id'] ) ) {
			$course_id     = (int) $_REQUEST['course_id'];
			$display_level = ! empty( $_REQUEST['display_level'] ) ? (int) $_REQUEST['display_level'] : '';

			$replace_string = "[tva_course id='$course_id' ";
			if ( ! empty( $display_level ) ) {
				$replace_string .= "display-level='$display_level' ";
			}

			$post->post_content = str_replace( '[tva_course ', $replace_string, $post->post_content );
		}
	}

	/**
	 * Returns Elements Group Label
	 *
	 * @return string
	 */
	public function elements_group_label() {
		return __( 'Lesson List Elements', 'thrive-apprentice' );
	}

	/**
	 * Components that apply only to this
	 *
	 * @return array
	 */
	public function own_components() {
		return array(
			'course'           => array(
				'config' => array(
					'ShowAssessments' => [
						'config'  => [
							'label' => __( 'Show assessments', 'thrive-apprentice' ),
						],
						'extends' => 'Switch',
					],
					'changeCourse'    => array(
						'config'  => array(
							'name'       => '',
							'full-width' => true,
							'options'    => array(), // The option list is build in the frontend from the localized variables
						),
						'extends' => 'Select',
					),
					'displayLevel'    => array(
						'config'  => array(
							'name'       => '',
							'full-width' => true,
							'options'    => array(),
						),
						'extends' => 'Select',
					),
					'ToggleModule'    => array(
						'config'  => array(
							'name'  => '',
							'label' => __( 'Hide module header', 'thrive-apprentice' ),
						),
						'extends' => 'Switch',
					),
					'Palettes'            => array(
						'config'  => array(),
						'extends' => 'PalettesV2',
					),
					'AllowCollapsed'      => array(
						'config'  => array(
							'name'       => '',
							'full-width' => true,
							'checkbox'   => true,
							'buttons'    => array(
								array(
									'text'  => __( 'Modules', 'thrive-apprentice' ),
									'value' => 'module',
								),
								array(
									'text'  => __( 'Chapters', 'thrive-apprentice' ),
									'value' => 'chapter',
								),
							),
						),
						'extends' => 'ButtonGroup',
					),
					'DefaultState'        => array(
						'config'  => array(
							'name'       => '',
							'full-width' => true,
							'buttons'    => array(
								array(
									'text'    => __( 'Expanded', 'thrive-apprentice' ),
									'value'   => 'expanded',
									'default' => true,
								),
								array(
									'text'  => __( 'Collapsed', 'thrive-apprentice' ),
									'value' => 'collapsed',
								),
							),
						),
						'extends' => 'ButtonGroup',
					),
					'AutoCollapse'        => array(
						'config'  => array(
							'name'        => '',
							'label'       => __( 'Auto collapse', 'thrive-apprentice' ),
							'info'        => true,
							'icontooltip' => __( 'Auto collapse will automatically collapse modules & chapters that are not associated with the active lesson', 'thrive-apprentice' ),
							'iconside'    => 'top',
						),
						'extends' => 'Switch',
					),
					'AutoCourseStructure' => array(
						'config'  => array(
							'name'  => '',
							'label' => __( 'Display entire course structure', 'thrive-apprentice' ),
						),
						'extends' => 'Switch',
					),
				),
			),
			'typography'       => array(
				'disabled_controls' => array( '.typography-button-toggle-controls' ),
			),
			'animation'        => array( 'hidden' => true ),
			'layout'           => array( 'disabled_controls' => array( 'Display' ) ),
			'styles-templates' => [ 'hidden' => true ],
		);
	}

	/**
	 * Element info
	 *
	 * @return string|string[][]
	 */
	public function info() {
		return array(
			'instructions' => array(
				'type' => 'help',
				'url'  => 'apprentice_lesson_list',
				'link' => 'https://help.thrivethemes.com/en/articles/4794728-how-to-use-the-apprentice-lesson-list-element',
			),
		);
	}
}

return new TCB_Course_Element( 'course' );
