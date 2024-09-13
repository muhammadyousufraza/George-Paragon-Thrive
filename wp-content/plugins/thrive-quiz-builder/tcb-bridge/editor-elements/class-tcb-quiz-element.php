<?php
/**
 * Created by PhpStorm.
 * User: ovidiu
 * Date: 6/23/2017
 * Time: 10:53 AM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class TCB_Quiz_Element
 *
 * Quiz Builder Product - Allows inserting quizzes into pages
 */
class TCB_Quiz_Element extends TCB_Element_Abstract {

	/**
	 * Get element alternate
	 *
	 * @return string
	 */
	public function alternate() {
		return 'thrive';
	}

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Quiz', 'thrive-quiz-builder' );
	}

	/**
	 * Return icon class needed for display in menu
	 *
	 * @return string
	 */
	public function icon() {
		return 'quiz';
	}

	/**
	 * Element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.thrive-quiz-builder-shortcode'; //For backwards compatibility
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
	 * Element HTML
	 *
	 * @return string
	 */
	public function html() {
		$content = '';
		ob_start();
		include tqb()->plugin_path( 'tcb-bridge/editor-layouts/elements/quiz.php' );
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
		return array(
			'quiz'             => array(
				'config' => array(
					'change_quiz'          => array(
						'config'  => array(
							'name'        => esc_html__( 'Change quiz', 'thrive-quiz-builder' ),
							'label_col_x' => 4,
							'options'     => array(),
						),
						'extends' => 'Select',
					),
					'quiz_scroll'          => array(
						'config'     => array(
							'name'    => '',
							'label'   => esc_html__( 'Enable quiz scroll', 'thrive-quiz-builder' ),
							'default' => true,
						),
						'css_suffix' => '',
						'css_prefix' => '',
						'extends'    => 'Switch',
					),
					'SaveUserQuizProgress' => [
						'config'  => [
							'name'        => '',
							'label'       => esc_html__( 'Save users quiz progress', 'thrive-quiz-builder' ),
							'default'     => true,
							'info'        => true,
							'icontooltip' => esc_html__( 'This will allow logged-in users to return to the same page and resume their progress or see their results.', 'thrive-quiz-builder' ),
							'iconside'    => 'bottom',
						],
						'extends' => 'Switch',
					],
				),
			),
			'typography'       => array( 'hidden' => true ),
			'layout'           => array( 'hidden' => true ),
			'borders'          => array( 'hidden' => true ),
			'animation'        => array( 'hidden' => true ),
			'background'       => array( 'hidden' => true ),
			'styles-templates' => array( 'hidden' => true ),
			'shadow'           => array( 'hidden' => true ),
		);
	}

	/**
	 * Element category that will be displayed in the sidebar
	 *
	 * @return string
	 */
	public function category() {
		return static::get_thrive_integrations_label();
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
				'url'  => 'quiz',
				'link' => 'https://help.thrivethemes.com/en/articles/4426055-how-to-add-a-finished-quiz-to-your-website',
			),
		);
	}
}
