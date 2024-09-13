<?php

namespace TVA\Architect\Visual_Builder\Elements;

use TVA\Architect\Visual_Builder\Abstract_Visual_Builder_Element;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}


class Visual_Builder_Certificate_Verification extends Abstract_Visual_Builder_Element {

	protected $_tag = 'certificate_verification';

	public function name() {
		return __( 'Certificate Verification', 'thrive-apprentice' );
	}

	public function category() {
		return $this->get_thrive_integrations_label();
	}

	public function icon() {
		return 'certificate-validation';
	}

	public function identifier() {
		return '.tva-certificate-verification-element';
	}

	public function is_placeholder() {
		return false;
	}

	public function html() {
		ob_start();
		include \TVA_Const::plugin_path( 'tcb-bridge/editor-layouts/elements/certificate/default.php' );
		$content = ob_get_contents();
		ob_end_clean();

		return $content;
	}

	public function has_group_editing() {
		return \TVA\Architect\Certificate\Main::get_group_editing_options();
	}

	public function own_components() {
		$components = array(
			'certificate_verification' => array(
				'config' => array(
					'LabelText' => array(
						'config'  => array(
							'label' => __( 'No certification message', 'thrive-apprentice' ),
						),
						'extends' => 'LabelInput',
					),
				),
			),
		);

		return array_merge( $components, $this->group_component() );
	}
}

return new Visual_Builder_Certificate_Verification();
