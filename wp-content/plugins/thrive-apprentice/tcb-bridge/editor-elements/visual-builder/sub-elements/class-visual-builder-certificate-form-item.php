<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package ${NAMESPACE}
 */


namespace TVA\Architect\Visual_Builder\Elements;

use TVA\Architect\Visual_Builder\Abstract_Visual_Builder_Element;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Visual_Builder_Certificate_Form_Item extends Abstract_Visual_Builder_Element {

	protected $_tag = 'tva_certificate_form_item';

	public function name() {
		return esc_html__( 'Certificate Form Item', 'thrive-apprentice' );
	}

	public function hide() {
		return true;
	}

	public function identifier() {
		return '.tva-certificate-form-item';
	}
}

return new Visual_Builder_Certificate_Form_Item();
