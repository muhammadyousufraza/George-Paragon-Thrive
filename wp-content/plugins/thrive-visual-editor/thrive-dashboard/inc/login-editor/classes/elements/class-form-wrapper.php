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
 * Class Form_Wrapper
 * @package TVD\Login_Editor
 */
class Form_Wrapper extends \TCB_Element_Abstract {

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Form wrapper', 'thrive-dash' );
	}

	/**
	 * WordPress element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '#login';
	}

	public function own_components() {
		$components = array(
			'animation'              => array( 'hidden' => true ),
			'typography'             => array( 'hidden' => true ),
			'responsive'             => array( 'hidden' => true ),
			'styles-templates'       => array( 'hidden' => true ),
			'tvd-login-form-wrapper' => array(
				'config' => array(
					'HorizontalPosition' => array(
						'config'  => array(
							'name'    => __( 'Horizontal position', 'thrive-dash' ),
							'buttons' => array(
								array(
									'icon'    => 'a_left',
									'text'    => '',
									'value'   => 'flex-start',
									'default' => true,
								),
								array(
									'icon'  => 'a_center',
									'text'  => '',
									'value' => 'center',
								),
								array(
									'icon'  => 'a_right',
									'text'  => '',
									'value' => 'flex-end',
								),
							),
						),
						'extends' => 'ButtonGroup',
					),
					'VerticalPosition'   => array(
						'config'  => array(
							'name'    => __( 'Vertical position', 'thrive-dash' ),
							'buttons' => array(
								array(
									'icon'    => 'top',
									'default' => true,
									'value'   => 'flex-start',
								),
								array(
									'icon'  => 'vertical',
									'value' => 'center',
								),
								array(
									'icon'  => 'bot',
									'value' => 'flex-end',
								),
							),
						),
						'extends' => 'ButtonGroup',
					),
					'FullHeight'         => array(
						'config'  => array(
							'name'    => '',
							'label'   => __( 'Stretch content to full height', 'thrive-dash' ),
							'default' => true,
						),
						'extends' => 'Switch',
					),
				),
			),
		);

		$components['layout']['disabled_controls'] = array( 'Display', 'Alignment' );

		return $components;
	}

	public function hide() {
		return true;
	}
}

return new Form_Wrapper( 'tvd-login-form-wrapper' );
