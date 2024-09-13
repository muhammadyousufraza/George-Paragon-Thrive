<?php

namespace TVA\Architect\Assessment;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

if ( ! class_exists( 'TCB_Login_Form_Item_Element' ) ) {
	require_once TVE_TCB_ROOT_PATH . 'inc/classes/elements/class-tcb-login-form-item-element.php';
}


class TCB_Assessment_Form_Item_Element extends \TCB_Login_Form_Item_Element {

	/**
	 * Element tag
	 *
	 * @var string
	 */
	protected $_tag = 'assessment_form_item';

	public function name() {
		return __( 'A Form Item', 'thrive-apprentice' );
	}

	public function identifier() {
		return '.tva-assessment-form-item';
	}

	public function inherit_components_from() {
		return 'login_form_item';
	}
}
