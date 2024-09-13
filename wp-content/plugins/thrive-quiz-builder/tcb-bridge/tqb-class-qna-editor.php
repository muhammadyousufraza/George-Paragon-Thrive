<?php

class TQB_QNA_Editor {

	public static function init() {

		self::maybe_redirect();

		add_filter( 'tcb_custom_post_layouts', array( __CLASS__, 'qna_editor_layout' ), 10, 3 );
		add_filter( 'tcb_main_frame_enqueue', array( __CLASS__, 'enqueue' ) );
		add_filter( 'tcb_element_instances', array( __CLASS__, 'get_elements' ) );
		add_filter( 'tve_frontend_options_data', array( __CLASS__, 'filter_tve_frontend_options' ) );
		add_action( 'tcb_ajax_save_post', array( __CLASS__, 'save' ), 10, 2 );
		add_action( 'preview_post_link', array( __CLASS__, 'preview_post_link' ), 10, 2 );
		add_filter( 'tcb_skip_license_check', array( __CLASS__, 'skip_tcb_licence_check' ) );

		self::register_components();
	}

	/**
	 * Check if the current post is editable
	 *
	 * @param bool $skip
	 *
	 * @return bool
	 */
	public static function skip_tcb_licence_check( $skip ) {

		if ( $skip ) {
			return true;
		}

		return self::is_editable( get_post_type() );
	}

	/**
	 * Redirect to dash if users tries to edit an unsupported quiz style
	 */
	public static function maybe_redirect() {

		$post = get_post( isset( $_REQUEST['post'] ) ? absint( $_REQUEST['post'] ) : 0 );

		if ( true !== $post instanceof WP_Post ) {
			return;
		}

		$quiz_style = TQB_Post_meta::get_quiz_style_meta( $post->ID );

		if ( self::is_editable( $post->post_type ) && ! in_array( (int) $quiz_style, self::get_editable_styles() ) ) {
			wp_redirect( admin_url( 'admin.php?page=tqb_admin_dashboard' ) );
			exit();
		}
	}

	/**
	 * Get editable quiz styles
	 *
	 * @return array
	 */
	public static function get_editable_styles() {

		return array( 4 );
	}

	public static function save( $quiz_id, $data ) {

		if ( empty( $data['qna_templates'] ) ) {
			return;
		}

		$post                = get_post( $quiz_id );
		$style               = TQB_Post_meta::get_quiz_style_meta( $quiz_id );
		$templates           = $post->tve_qna_templates;
		$templates           = empty( $templates ) ? array() : $templates;
		$templates[ $style ] = $data['qna_templates'][ $style ];
		update_post_meta( $quiz_id, 'tve_qna_templates', $templates );
	}

	/**
	 * Register elements components
	 */
	protected static function register_components() {

		$components = array(
			'tqb_question' => tqb()->plugin_path( 'tcb-bridge/editor-layouts/menus/qna/question.php' ),
			'answer_item'  => tqb()->plugin_path( 'tcb-bridge/editor-layouts/menus/qna/answer-item.php' ),
			'tqb_icon'     => tqb()->plugin_path( 'tcb-bridge/editor-layouts/menus/qna/icon.php' ),
			'answer_icon'  => tqb()->plugin_path( 'tcb-bridge/editor-layouts/menus/qna/answer-icon.php' ),
		);

		$base_filter = 'tcb_menu_path_';

		foreach ( $components as $key => $component ) {
			add_filter( $base_filter . $key, function () use ( $component ) {
				return $component;
			} );
		}
	}

	/**
	 * Pushes QnA elements if post is editable
	 *
	 * @param TCB_Element_Abstract[] $elements
	 *
	 * @return TCB_Element_Abstract[]
	 */
	public static function get_elements( $elements ) {

		if ( ! self::is_editable( get_post_type() ) ) {
			return $elements;
		}

		require_once tqb()->plugin_path( 'tcb-bridge/editor-elements/class-tcb-tqb-qna-text-element.php' );
		require_once tqb()->plugin_path( 'tcb-bridge/editor-elements/class-tcb-tqb-question-text-element.php' );
		require_once tqb()->plugin_path( 'tcb-bridge/editor-elements/class-tcb-tqb-question-description-element.php' );
		require_once tqb()->plugin_path( 'tcb-bridge/editor-elements/class-tcb-tqb-answers-container-element.php' );
		require_once tqb()->plugin_path( 'tcb-bridge/editor-elements/class-tcb-tqb-answer-item-element.php' );
		require_once tqb()->plugin_path( 'tcb-bridge/editor-elements/class-tcb-tqb-answer-item-right-element.php' );
		require_once tqb()->plugin_path( 'tcb-bridge/editor-elements/class-tcb-tqb-answer-item-wrong-element.php' );
		require_once tqb()->plugin_path( 'tcb-bridge/editor-elements/class-tcb-tqb-answer-text.php' );
		require_once tqb()->plugin_path( 'tcb-bridge/editor-elements/class-tcb-tqb-question-element.php' );
		require_once tqb()->plugin_path( 'tcb-bridge/editor-elements/class-tcb-tqb-answer-feedback-element.php' );
		require_once tqb()->plugin_path( 'tcb-bridge/editor-elements/class-tcb-tqb-feedback-text-element.php' );
		require_once tqb()->plugin_path( 'tcb-bridge/editor-elements/class-tcb-tqb-answer-icon-element.php' );
		require_once tqb()->plugin_path( 'tcb-bridge/editor-elements/class-tcb-tqb-question-container-element.php' );

		$elements['tqb_qna_text']             = new TCB_TQB_QNA_Text( 'tqb_qna_text' );
		$elements['tqb_question_text']        = new TCB_TQB_Question_Text( 'tqb_question_text' );
		$elements['tqb_question_description'] = new TCB_TQB_Question_Description( 'tqb_question_description' );
		$elements['tqb_answers_container']    = new TCB_TQB_Answers_Container_Element( 'tqb_answers_container' );
		$elements['tqb_answer_item']          = new TCB_TQB_Answer_Item( 'tqb_answer_item' );
		$elements['tqb_answer_right_item']    = new TCB_TQB_Answer_Right_Item( 'tqb_answer_right_item' );
		$elements['tqb_answer_wrong_item']    = new TCB_TQB_Answer_Wrong_Item( 'tqb_answer_wrong_item' );
		$elements['tqb_answer_text']          = new TCB_TQB_Answer_Text( 'tqb_answer_text' );
		$elements['tqb_answer_feedback']      = new TCB_TQB_Answer_Feedback( 'tqb_answer_feedback' );
		$elements['tqb_question']             = new TCB_Question_Element( 'tqb_question' );
		$elements['tqb_answer_feedback_text'] = new TCB_TQB_Answer_Feedback_Text( 'tqb_answer_feedback_text' );
		$elements['tqb_question_container']   = new TCB_TQB_Question_Container_Element( 'tqb_question_container' );

		return $elements;
	}

	/**
	 * Loads scrips on main frame on admin action=architect
	 */
	public static function enqueue() {

		if ( ! self::is_editable( get_post_type() ) ) {
			return;
		}

		tqb_enqueue_script(
			'tqb-internal-qna',
			tqb()->plugin_url( 'tcb-bridge/assets/js/tqb-tcb-qna.min.js' ),
			array(
				'tve-main',
			),
			false,
			true
		);
	}

	/**
	 * Pushes a new template for TAr Layouts - QnA Editor
	 *
	 * @param array  $layouts
	 * @param int    $post_id
	 * @param string $post_type
	 *
	 * @return array
	 */
	public static function qna_editor_layout( $layouts, $post_id, $post_type ) {

		if ( ! self::is_editable( $post_type ) ) {
			return $layouts;
		}

		tqb_enqueue_default_scripts();

		tqb_enqueue_style(
			'tqb-internal-qna',
			tqb()->plugin_url( 'tcb-bridge/assets/css/qna_editor.css' )
		);

		$layouts[] = tqb()->plugin_path( 'tcb-bridge/editor/page/qna.php' );

		return $layouts;
	}

	public static function is_editable( $post_or_type ) {

		$post_or_type = is_numeric( $post_or_type ) ? get_post_type( $post_or_type ) : $post_or_type;

		return TQB_Post_types::QUIZ_POST_TYPE === $post_or_type && ! empty( $_GET['tve'] );
	}

	public static function filter_tve_frontend_options( $data ) {

		if ( ! self::is_editable( get_post_type() ) ) {
			return $data;
		}

		$post = get_post();

		$data['qna_data'] = array(
			'quiz'        => array(
				'id'    => $post->ID,
				'style' => TQB_Post_meta::get_quiz_style_meta( $post->ID ),
			),
			'pg_settings' => tqb_progress_settings_instance( (int) $post->ID )->get(),
		);

		return $data;
	}

	/**
	 * @param string  $url
	 * @param WP_Post $post
	 *
	 * @return string
	 */
	public static function preview_post_link( $url, $post ) {

		if ( isset( $_REQUEST['tve'] ) && $post instanceof WP_Post && Thrive_Quiz_Builder::SHORTCODE_NAME === $post->post_type ) {
			$url = admin_url( 'admin.php?page=tqb_admin_dashboard#dashboard/quiz/' . $post->ID );
		}

		return $url;
	}
}

add_action( 'init', array( 'TQB_QNA_Editor', 'init' ) );
