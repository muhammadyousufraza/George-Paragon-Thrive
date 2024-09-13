<?php

namespace TVA\TQB;

use TVA_Const;
use TVA_Course_Overview_Post;
use TVA_Lesson;
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
 * Hooks class
 */
class Hooks {
	/**
	 * Class constructor
	 */
	public function __construct() {
		$this->actions();
		$this->filters();
	}

	public function actions() {
		add_action( 'tcb_main_frame_enqueue', [ $this, 'add_script_to_main_frame' ] );

		add_action( 'tcb_tqb_quiz_component_menu_after_controls', [ $this, 'add_extra_quiz_component_html' ] );

		add_action( 'tcb_ajax_save_post', [ $this, 'tcb_ajax_save_post' ], 10, 2 );
	}

	public function filters() {
		add_filter( 'tva_admin_get_backbone_templates', [ $this, 'get_admin_backbone_templates' ] );

		add_filter( 'tcb_element_quiz_config', [ $this, 'add_quiz_extra_controls' ], 10, 2 );

		add_filter( 'tve_main_js_dependencies', [ $this, 'main_js_dependencies' ] );

		add_filter( 'tqb_quiz_shortcode_action_response', [ $this, 'alter_quiz_builder_shortcode_response' ], 10, 2 );

		add_filter( 'tqb_get_course_overview_details', [ $this, 'get_course_overview_details' ], 10, 2 );

		add_filter( 'tqb_filter_get_posts_args', [ $this, 'filter_get_post_args' ] );
	}

	/**
	 * Checks if the quiz integration is allowed for the request
	 * Returns true if the active post is a lesson
	 *
	 * @param string $post_type
	 *
	 * @return bool
	 */
	private function allow( $post_type = '' ) {

		if ( ! class_exists( 'TQB_Customer', false ) ) {
			/**
			 * This is to ensure that also TQB plugin gets updated before we enable the TQB-TVA functionality
			 *
			 * This should be removed after 2-3 releases
			 */
			return false;
		}

		if ( empty( $post_type ) ) {
			$post_type = get_post_type();
		}

		return $post_type === TVA_Const::LESSON_POST_TYPE;
	}

	/**
	 * Adds scripts to Editor Main frame
	 *
	 * @return void
	 */
	public function add_script_to_main_frame() {
		/**
		 * This script must be loaded after the quiz builder one
		 */
		if ( $this->allow() ) {
			tva_enqueue_script( 'tva-tqb-editor', TVA_Const::plugin_url( 'tqb-bridge/assets/js/tva-tqb-editor.min.js' ), array( 'tqb-external-editor' ) );
		}
	}

	/**
	 * Adds extra quiz markup to the quiz component
	 *
	 * @return void
	 */
	public function add_extra_quiz_component_html() {
		if ( $this->allow() ) {
			include TVA_Const::plugin_path( 'tqb-bridge/templates/quiz-component-extra.phtml' );
		}
	}

	/**
	 * @param array $config
	 *
	 * @return array
	 */
	public function add_quiz_extra_controls( $config ) {
		if ( $this->allow() ) {
			$config = array_merge_recursive( [
				'components' => [
					'quiz' => [
						'config' => [
							'ControlMarkComplete' => [
								'config'  => [
									'name'    => '',
									'label'   => esc_html__( 'Control the "mark as complete" behaviour based on the results on this quiz', 'thrive-apprentice' ),
									'default' => true,
								],
								'extends' => 'Switch',
							],
						],
					],
				],
			], $config );
		}

		return $config;
	}

	/**
	 * Inject the tva-tqb editor js file as a dependency over the tve-main file
	 *
	 * @param array $dependencies
	 *
	 * @return array
	 */
	public function main_js_dependencies( $dependencies = [] ) {
		if ( $this->allow() ) {
			$dependencies[] = 'tva-tqb-editor';
		}

		return $dependencies;
	}

	/**
	 * @param array $data
	 * @param int   $post_id
	 *
	 * @return array
	 */
	public function alter_quiz_builder_shortcode_response( $data, $post_id ) {
		if ( $this->allow( get_post_type( $post_id ) ) && is_array( $data ) && ! empty( $data['page_type'] ) && $data['page_type'] === 'results' ) {
			$lesson = new TVA_Lesson( (int) $post_id );

			$data['tva_allow_mark_as_complete'] = (int) $lesson->can_be_marked_as_completed();
		}

		return $data;
	}

	/**
	 * Adds extra templates for apprentice admin screen
	 *
	 * @param array $templates
	 *
	 * @return array
	 */
	public function get_admin_backbone_templates( $templates = [] ) {

		return array_merge( $templates, tve_dash_get_backbone_templates( TVA_Const::plugin_path( 'tqb-bridge/templates/admin' ), 'admin' ) );
	}

	/**
	 * @param int   $post_id
	 * @param array $post_request_data
	 *
	 * @return void
	 */
	public function tcb_ajax_save_post( $post_id, $post_request_data ) {
		if ( $this->allow( get_post_type( $post_id ) ) ) {

			if ( ! empty( $post_request_data['tva_tqb_mark_as_complete'] ) && is_array( $post_request_data['tva_tqb_mark_as_complete'] ) ) {
				/**
				 * In the future, if there are more integrations make a smart update here
				 */
				update_post_meta( $post_id, TVA_Const::TVA_META_NAME_CAN_MARK_COMPLETE, [ 'tqb' => $post_request_data['tva_tqb_mark_as_complete'] ] );
			} else {
				//For now we delete the entire meta
				//If more integrations will be for this functionality make a smart delete
				delete_post_meta( $post_id, TVA_Const::TVA_META_NAME_CAN_MARK_COMPLETE );
			}
		}
	}

	/**
	 * Exclude demo content when searching for posts
	 *
	 * @param $args
	 *
	 * @return array
	 */
	public function filter_get_post_args( $args = [] ) {
		$args['meta_query'][] = array(
			'demo_content' => array(
				'key'     => 'tva_is_demo',
				'compare' => 'NOT EXISTS',
			),
		);

		return $args;
	}

	/**
	 * @param array   $details
	 * @param WP_Post $post
	 *
	 * @return array
	 */
	public function get_course_overview_details( $details, $post ) {
		if ( $post->post_type === TVA_Course_Overview_Post::POST_TYPE ) {
			$course = wp_get_object_terms( $post->ID, TVA_Const::COURSE_TAXONOMY );

			if ( ! empty( $course ) ) {
				$details['post_title'] = $course[0]->name;
			}
		}

		return $details;
	}
}
