<?php

namespace TVA\Architect\Assessment;

use TCB_Utils;
use TVA\Architect\Utils;

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Hooks {

	/**
	 * Actions & Filters
	 *
	 * @return void
	 */
	public static function init() {
		static::filters();
	}

	public static function filters() {
		add_action( 'wp_print_footer_scripts', array( __CLASS__, 'print_footer_scripts' ), 9 );

		add_filter( 'tcb_element_instances', [ __CLASS__, 'tcb_element_instances' ] );

		add_filter( 'tcb_menu_path_assessment', [ __CLASS__, 'include_assessment_menu' ] );
		add_filter( 'tcb_menu_path_assessment_result_list', [ __CLASS__, 'include_assessment_result_list_menu' ] );
		add_filter( 'tcb_menu_path_assessment_type_completed', [ __CLASS__, 'include_assessment_type_completed_menu' ] );

		add_action( 'rest_api_init', [ __CLASS__, 'integration_rest_api_init' ] );

		add_filter( 'tcb_content_allowed_shortcodes', [ __CLASS__, 'content_allowed_shortcodes_filter' ] );

		add_filter( 'tcb_inline_shortcodes', [ __CLASS__, 'inline_shortcodes' ] );
		add_filter( 'tcb_dynamiclink_data', [ __CLASS__, 'dynamic_links' ] );

		add_filter( 'tve_frontend_options_data', [ __CLASS__, 'frontend_data' ] );
	}

	public static function print_footer_scripts() {
		if ( ! TCB_Editor()->is_inner_frame() && ! Main::$is_editor_page && ! empty( $GLOBALS['tva_assessment_results_localize'] ) ) {
			foreach ( $GLOBALS['tva_assessment_results_localize'] as $result ) {

				echo TCB_Utils::wrap_content(
					str_replace( [ '[', ']' ], [ '{({', '})}' ], $result['content'] ),
					'script',
					'',
					'tcb-assessment-result-template',
					[
						'type'            => 'text/template',
						'data-identifier' => $result['template'],
					]
				);
			}
		}
	}

	/**
	 * Include the elements
	 *
	 * @param array $instances
	 *
	 * @return array
	 */
	public static function tcb_element_instances( $instances ) {

		$element        = new TCB_Assessment_Element();
		$element_type   = new TCB_Assessment_Type_Element();
		$element_c_type = new TCB_Assessment_Type_Completed_Element();
		$form_item      = new TCB_Assessment_Form_Item_Element();
		$form_input     = new TCB_Assessment_Form_Input_Element();
		$quiz           = new TCB_Assessment_Quiz_Wrapper_Element();
		$video          = new TCB_Assessment_Video_Preview_Element();
		$result_list    = new TCB_Assessment_Result_List_Element();
		$result_item    = new TCB_Assessment_Result_Item_Element();
		$result_header  = new TCB_Assessment_Result_Header_Element();
		$result_content = new TCB_Assessment_Result_Content_Element();

		$instances[ $element->tag() ]        = $element;
		$instances[ $element_type->tag() ]   = $element_type;
		$instances[ $element_c_type->tag() ] = $element_c_type;
		$instances[ $form_item->tag() ]      = $form_item;
		$instances[ $form_input->tag() ]     = $form_input;
		$instances[ $quiz->tag() ]           = $quiz;
		$instances[ $video->tag() ]          = $video;
		$instances[ $result_list->tag() ]    = $result_list;
		$instances[ $result_item->tag() ]    = $result_item;
		$instances[ $result_header->tag() ]  = $result_header;
		$instances[ $result_content->tag() ] = $result_content;

		return $instances;
	}

	/**
	 * Outputs the assessment submit menu into the editor
	 *
	 * @return string
	 */
	public static function include_assessment_menu() {
		return Utils::get_integration_path( 'editor-layouts/menus/assessment.php' );
	}

	/**
	 * Outputs the assessment type completed menu into the editor
	 *
	 * @return string
	 */
	public static function include_assessment_type_completed_menu() {
		return Utils::get_integration_path( 'editor-layouts/menus/assessment-type-completed.php' );
	}

	/**
	 * Outputs the assessment result menu into the editor
	 *
	 * @return string
	 */
	public static function include_assessment_result_list_menu() {
		return Utils::get_integration_path( 'editor-layouts/menus/assessment-result-list.php' );
	}

	/**
	 * Registers the user controller
	 * Used in TAR - Front-end when end-users interract with assessments
	 *
	 * @return void
	 */
	public static function integration_rest_api_init() {
		new TCB_Assessment_Rest_Controller();
	}

	/**
	 * Allow the assessments shortcode to be rendered in the editor
	 *
	 * @param array $shortcodes
	 *
	 * @return array
	 */
	public static function content_allowed_shortcodes_filter( $shortcodes = [] ) {

		if ( Main::$is_editor_page ) {
			$shortcodes = array_merge( $shortcodes, Shortcodes::get() );
		}

		return $shortcodes;
	}

	/**
	 * Inline shortcodes configuration
	 *
	 * @param array $shortcodes
	 *
	 * @return array
	 */
	public static function inline_shortcodes( $shortcodes = [] ) {
		return array_merge_recursive( [
			'Assessment Result'     => [
				[
					'option' => __( 'Title', 'thrive-apprentice' ),
					'value'  => 'tva_assessment_result_assessment_title',
					'input'  => static::get_link_configuration(),
				],
				[
					'option' => __( 'Grade', 'thrive-apprentice' ),
					'value'  => 'tva_assessment_result_assessment_grade',
				],
			],
			'Assessment submission' => [
				[
					'option'   => __( 'Assessment title', 'thrive-apprentice' ),
					'value'    => 'tva_assessment_title',
					'input'    => static::get_link_configuration(),
					'only_for' => [ 'assessment' ],
				],
				[
					'option'   => __( 'Assessment summary', 'thrive-apprentice' ),
					'value'    => 'tva_assessment_summary',
					'only_for' => [ 'assessment' ],
				],
				[
					'option'   => __( 'Latest assessment submission (14 Aug 2022)', 'thrive-apprentice' ),
					'value'    => 'tva_submission_latest_date1',
					'only_for' => [ 'assessment' ],
				],
				[
					'option'   => __( 'Latest assessment submission (14/8/2022)', 'thrive-apprentice' ),
					'value'    => 'tva_submission_latest_date2',
					'only_for' => [ 'assessment' ],
				],
				[
					'option'   => __( 'Latest assessment submission (8/14/2022)', 'thrive-apprentice' ),
					'value'    => 'tva_submission_latest_date3',
					'only_for' => [ 'assessment' ],
				],
				[
					'option'   => __( 'Assessment submission (14 Aug 2022)', 'thrive-apprentice' ),
					'value'    => 'tva_assessment_submission_date1',
					'only_for' => [ 'assessment_result_item' ],
				],
				[
					'option'   => __( 'Assessment submission (14/8/2022)', 'thrive-apprentice' ),
					'value'    => 'tva_assessment_submission_date2',
					'only_for' => [ 'assessment_result_item' ],
				],
				[
					'option'   => __( 'Assessment submission (8/14/2022)', 'thrive-apprentice' ),
					'value'    => 'tva_assessment_submission_date3',
					'only_for' => [ 'assessment_result_item' ],
				],
				[
					'option'   => __( 'Latest assessment grade', 'thrive-apprentice' ),
					'value'    => 'tva_assessment_grade1',
					'only_for' => [ 'assessment' ],
				],
				[
					'option'   => __( 'Assessment grade', 'thrive-apprentice' ),
					'value'    => 'tva_assessment_grade2',
					'only_for' => [ 'assessment_result_item' ],
				],
				[
					'option'   => __( 'Latest assessment notes', 'thrive-apprentice' ),
					'value'    => 'tva_assessment_notes1',
					'only_for' => [ 'assessment' ],
				],
				[
					'option'   => __( 'Assessment notes', 'thrive-apprentice' ),
					'value'    => 'tva_assessment_notes2',
					'only_for' => [ 'assessment_result_item' ],
				],
				[
					'option'   => __( 'Latest assessment passed/failed', 'thrive-apprentice' ),
					'value'    => 'tva_assessment_pass_fail1',
					'only_for' => [ 'assessment' ],
				],
				[
					'option'   => __( 'Assessment passed/failed', 'thrive-apprentice' ),
					'value'    => 'tva_assessment_pass_fail2',
					'only_for' => [ 'assessment_result_item' ],
				],
			],
		], $shortcodes );
	}

	/**
	 * @param array $data
	 *
	 * @return array
	 */
	public static function dynamic_links( $data = [] ) {
		$data['Assessment'] = [
			'links'     => [
				[
					'bk_to_submit'  => [
						'name'  => esc_html__( 'Back to submit state', 'thrive-apprentice' ),
						'label' => esc_html__( 'Back to submit state', 'thrive-apprentice' ),
						'url'   => '',
						'show'  => true,
						'id'    => 'back_to_submit',
					],
					'go_to_results' => [
						'name'  => esc_html__( 'Go to results', 'thrive-apprentice' ),
						'label' => esc_html__( 'Go to results', 'thrive-apprentice' ),
						'url'   => '',
						'show'  => true,
						'id'    => 'results',
					],
				],
			],
			'shortcode' => 'tva_assessment_dynamic_link',
		];

		return $data;
	}

	/**
	 * Assessment frontend data
	 *
	 * @param array $data
	 *
	 * @return array
	 */
	public static function frontend_data( $data = [] ) {
		$data['routes']['assessments'] = tva_get_route_url( 'user/assessment' );

		return $data;
	}

	private static function get_link_configuration() {
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
