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
 * Class Password_Button
 *
 * @package TVD\Login_Editor
 */
class Password_Button extends \TCB_Element_Abstract {

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Password button', 'thrive-dash' );
	}

	/**
	 * WordPress element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '#login .wp-pwd .wp-hide-pw';
	}

	public function own_components() {
		$components = $this->general_components();

		$components = array_merge( $components, array(
			'typography'       => array( 'hidden' => true ),
			'animation'        => array( 'hidden' => true ),
			'responsive'       => array( 'hidden' => true ),
			'styles-templates' => array( 'hidden' => true ),
		) );

		$components['layout']['disabled_controls']   = array( 'Display', 'Alignment' );
		$components['shadow']['config']['important'] = true;

		$components['tvd-login-password-button'] = array(
			'config' => array(
				'Color' => array(
					'config'  => array(
						'default' => '000',
						'label'   => __( 'Color', 'thrive-dash' ),
						'options' => array(
							'output' => 'object',
						),
					),
					'extends' => 'ColorPicker',
				),
			),
		);

		return $components;
	}

	public function hide() {
		return true;
	}
}

return new Password_Button( 'tvd-login-password-button' );
