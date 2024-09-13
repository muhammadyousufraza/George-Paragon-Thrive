<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package ${NAMESPACE}
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class TCB_Certificate_State_Element extends TCB_Element_Abstract {

	protected $_tag = 'tva_certificate_state';

	public function name() {
		return esc_html__( 'Certificate State', 'thrive-apprentice' );
	}

	public function hide() {
		return true;
	}

	public function identifier() {
		return '.tva-certificate-state';
	}
}
