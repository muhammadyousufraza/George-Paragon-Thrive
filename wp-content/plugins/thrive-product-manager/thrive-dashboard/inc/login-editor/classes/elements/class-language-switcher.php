<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */

namespace TVD\Login_Editor;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Language_Switcher
 * @package TVD\Login_Editor
 */
class Language_Switcher extends \TCB_Element_Abstract {

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Language switcher', 'thrive-dash' );
	}

	/**
	 * WordPress element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '#language-switcher';
	}

	public function own_components() {
		$components = $this->general_components();

		$components = array_merge( $components, array(
			'animation'        => array( 'hidden' => true ),
			'typography'       => array( 'hidden' => true ),
			'responsive'       => array( 'hidden' => true ),
			'styles-templates' => array( 'hidden' => true ),
		) );

		$components['layout']['disabled_controls'] = array( 'Display', 'Alignment', '.tve-advanced-controls' );

		return $components;
	}

	public function hide() {
		return true;
	}
}

return new Language_Switcher( 'tvd-login-language-switcher' );
