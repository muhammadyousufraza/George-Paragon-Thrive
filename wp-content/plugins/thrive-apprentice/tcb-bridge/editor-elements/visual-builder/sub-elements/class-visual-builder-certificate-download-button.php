<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

namespace TVA\Architect\Visual_Builder\Elements;

use TVA\Architect\Visual_Builder\Abstract_Visual_Builder_Element;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Visual_Builder_Certificate_Download_Button extends Abstract_Visual_Builder_Element {

	protected $_tag = 'tva_certificate_download_button';

	public function name() {
		return esc_html__( 'Download Certificate', 'thrive-apprentice' );
	}

	public function category() {
		return $this->get_thrive_integrations_label();
	}

	public function icon() {
		return 'certificate-download';
	}

	public function identifier() {
		return '.tva-certificate-download-button';
	}

	public function html() {
		$content = '';

		ob_start();
		include \TVA_Const::plugin_path( 'tcb-bridge/editor-layouts/elements/certificate/download-button.php' );
		$content = ob_get_contents();
		ob_end_clean();

		return $content;
	}
}

return new Visual_Builder_Certificate_Download_Button();
