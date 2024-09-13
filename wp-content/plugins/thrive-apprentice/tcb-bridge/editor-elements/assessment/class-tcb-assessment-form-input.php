<?php

namespace TVA\Architect\Assessment;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

if ( ! class_exists( 'TCB_Login_Form_Input_Element' ) ) {
	require_once TVE_TCB_ROOT_PATH . 'inc/classes/elements/class-tcb-login-form-input-element.php';
}

class TCB_Assessment_Form_Input_Element extends \TCB_Login_Form_Input_Element {

	/**
	 * Element tag
	 *
	 * @var string
	 */
	protected $_tag = 'assessment_form_input';

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'A Form Input', 'thrive-apprentice' );
	}

	/**
	 * Element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.tva-assessment-form-input';
	}

	public function inherit_components_from() {
		return 'login_form_input';
	}
}
