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
 * Class Logo
 * @package TVD\Login_Editor
 */
class Logo extends \TCB_Element_Abstract {

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Logo', 'thrive-dash' );
	}

	/**
	 * WordPress element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '#login > h1 > a';
	}

	public function own_components() {
		$components = array(
			'typography'       => array( 'hidden' => true ),
			'animation'        => array( 'hidden' => true ),
			'responsive'       => array( 'hidden' => true ),
			'background'       => array( 'hidden' => true ),
			'styles-templates' => array( 'hidden' => true ),
			'tvd-login-logo'   => array(
				'config' => array(
					'ImagePicker' => array(
						'config' => array(
							'label' => __( 'Chose Logo Image', 'thrive-dash' ),
						),
					),
					'Size'        => array(
						'config'  => array(
							'min'   => 24,
							'max'   => 320,
							'label' => __( 'Logo Size', 'thrive-dash' ),
							'um'    => array( 'px' ),
						),
						'extends' => 'Slider',
					),
				),
			),
		);

		$components['layout']['disabled_controls'] = array( 'Display', 'Alignment', '.tve-advanced-controls', 'Width', 'Height' );

		return $components;
	}

	public function hide() {
		return true;
	}
}

return new Logo( 'tvd-login-logo' );
