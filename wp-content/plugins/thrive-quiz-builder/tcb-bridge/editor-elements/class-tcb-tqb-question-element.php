<?php

class TCB_Question_Element extends TCB_Element_Abstract {

	private $_post;

	/**
	 * TCB_Question_Element constructor.
	 *
	 * @param string $tag
	 */
	public function __construct( $tag = '' ) {

		global $post;

		$this->_post = $post;

		parent::__construct( $tag );
	}

	public function name() {
		return __( 'Quiz Builder Questions', 'thrive-quiz-builder' );
	}

	public function identifier() {
		return '.tqb-question-wrapper';
	}

	public function hide() {
		return true;
	}

	/**
	 * Element HTML
	 *
	 * @return string
	 */
	public function html() {

		$quiz_id           = $this->_post->ID;
		$quiz_style        = TQB_Post_meta::get_quiz_style_meta( $quiz_id );
		$data              = TQB_Quiz_Manager::get_shortcode_content( $quiz_id );
		$question_manager  = new TGE_Question_Manager( $quiz_id );
		$content           = tcb_post( $quiz_id )->tcb_content;
		$progress_settings = tqb_progress_settings_instance( (int) $quiz_id )->get();
		$palettes          = new TQB_Quiz_Palettes( $quiz_style );
		$colors            = $palettes->get_palettes_as_string();
		$qna               = get_post_meta( $quiz_id, 'tve_qna_templates', true );
		$hasQnaTemplate    = ! empty( $qna[ $quiz_style ] );
		$quiz_type         = TQB_Post_meta::get_quiz_type_meta( $quiz_id, true );
		$is_write_wrong    = 'right_wrong' === $quiz_type;
		$feedback_settings  = TQB_Post_meta::get_feedback_settings_meta( $quiz_id );
		$navigation_enabled = isset ( $feedback_settings['nav_buttons'] ) ? $feedback_settings['nav_buttons'] : false;

		ob_start();
		include tqb()->plugin_path( 'tcb-bridge/editor-layouts/elements/question.php' );
		$content = ob_get_contents();
		ob_end_clean();

		return $content;
	}

	/**
	 * Component and control config
	 *
	 * @return array
	 */
	public function own_components() {
		$components = array(
			'tqb_question'     => array(
				'config' => array(
					'Palettes'    => array(
						'config'    => array(),
						'important' => true,
					),
					'ProgressBar' => array(
						'config'     => array(
							'name'    => '',
							'label'   => __( 'Progress Bar', 'thrive-quiz-builder' ),
							'default' => true,
						),
						'css_suffix' => '',
						'css_prefix' => '',
						'extends'    => 'Switch',
					),
				),
			),
			'typography'       => array( 'hidden' => true ),
			'animation'        => array( 'hidden' => true ),
			'responsive'       => array( 'hidden' => true ),
			'styles-templates' => array( 'hidden' => true ),
		);

		return array_merge( $components, $this->group_component() );
	}

	/**
	 * Group Edit Properties
	 *
	 * @return array|bool
	 */
	public function has_group_editing() {

		return array(
			'exit_label'    => __( 'Exit Group Styling', 'thrive-cb' ),
			'select_values' => array(
				array(
					'value'    => 'all_answers',
					'selector' => '.tqb-answer-inner-wrapper',
					'name'     => __( 'Grouped Answer Items', 'thrive-quiz-builder' ),
					'singular' => __( '-- Answer Item %s', 'thrive-quiz-builder' ),
				),
				array(
					'value'    => 'all_answers_text',
					'selector' => '.tqb-answer-inner-wrapper .tqb-answer-text ',
					'name'     => __( 'Grouped Answer Text', 'thrive-quiz-builder' ),
					'singular' => __( '-- Answer Text %s', 'thrive-quiz-builder' ),
				),
			),
		);
	}
}
