<?php

namespace TVA\Architect\Visual_Builder\Elements;

use TVA\Architect\Visual_Builder\Abstract_Visual_Builder_Element;

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Visual_Builder_Button_Download_Certificate
 *
 * @package  TVA\Architect\Visual_Builder\Elements
 * @project  : thrive-apprentice
 */
class Visual_Builder_Button_Download_Certificate extends Abstract_Visual_Builder_Element {
	/**
	 * @var string
	 */
	protected $_tag = 'visual_builder_button_download_certificate';

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Download Certificate', 'thrive-apprentice' );
	}

	/**
	 * Return the icon class needed for display in menu
	 *
	 * @return string
	 */
	public function icon() {
		return 'download-certificate';
	}
}

return new Visual_Builder_Button_Download_Certificate();
