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
 * Class Form_Label
 * @package TVD\Login_Editor
 */
class Form_Label extends \TCB_Element_Abstract {

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Form label', 'thrive-dash' );
	}

	/**
	 * WordPress element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '#login form div label, #login form p:not(.forgetmenot) label';
	}

	public function own_components() {
		$components = $this->general_components();

		$components = array_merge( $components, array(
			'animation'        => array( 'hidden' => true ),
			'responsive'       => array( 'hidden' => true ),
			'styles-templates' => array( 'hidden' => true ),
		) );

		$components['layout']['disabled_controls'] = array( 'Display', 'Alignment', '.tve-advanced-controls' );

		foreach ( $components['typography']['config'] as $control => $config ) {
			if ( in_array( $control, array( 'css_suffix', 'css_prefix' ) ) ) {
				continue;
			}

			$components['typography']['config'][ $control ]['css_suffix'] = array( '' );
		}

		return $components;
	}

	public function hide() {
		return true;
	}
}

return new Form_Label( 'tvd-login-form-label' );
