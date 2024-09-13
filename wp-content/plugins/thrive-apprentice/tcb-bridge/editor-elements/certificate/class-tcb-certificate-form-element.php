<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package ${NAMESPACE}
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class TCB_Certificate_Form_Element extends TCB_Element_Abstract {

	protected $_tag = 'tva_certificate_form';

	public function name() {
		return esc_html__( 'Certificate Form', 'thrive-apprentice' );
	}

	public function hide() {
		return true;
	}

	public function identifier() {
		return '.tva-certificate-form';
	}
}
