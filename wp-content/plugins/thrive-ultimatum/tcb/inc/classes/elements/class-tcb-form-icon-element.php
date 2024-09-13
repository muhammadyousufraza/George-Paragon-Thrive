<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package TCB2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

require_once __DIR__ . '/class-tcb-icon-element.php';

class TCB_Form_Icon_Element extends TCB_Icon_Element {

	protected $_tag = 'form-icon';

	/**
	 * Get element alternate
	 *
	 * @return string
	 */
	public function alternate() {
		return 'icon,media';
	}


	/**
	 * @return string
	 */
	public function identifier() {
		return '.tve_lg_input_container:not(.tve_lg_file) .thrv_icon, .tve-login-form-input .thrv_icon';
	}

	/**
	 * @return bool
	 */
	public function hide() {
		return true;
	}

	/**
	 * @return array
	 */
	public function own_components() {
		$prefix_config = tcb_selection_root();

		return array(
			'icon'       => array(
				'config' => array(
					'ColorPicker' => array(
						'css_prefix' => $prefix_config . ' ',
						'css_suffix' => ' > :first-child',
						'config'     => array(
							'label' => __( 'Icon color', 'thrive-cb' ),
						),
					),
					'Slider'      => array(
						'css_prefix' => $prefix_config . ' ',
						'config'     => array(
							'default' => '30',
							'min'     => '10',
							'max'     => '200',
							'label'   => __( 'Icon size', 'thrive-cb' ),
							'um'      => [ 'px' ],
							'css'     => 'fontSize',
						),
					),
				),
			),
			'typography' => [
				'hidden' => true,
			],
			'layout'     => [
				'config'            => [
					'MarginAndPadding' => [
						'css_prefix' => $prefix_config . ' ',
					],
				],
				'disabled_controls' => [
					'Width',
					'Height',
					'Alignment',
					'.tve-advanced-controls',
				],
			],
			'borders'    => [
				'config' => [
					'Borders' => [
						'css_prefix' => $prefix_config . ' ',
					],
					'Corners' => [
						'css_prefix' => $prefix_config . ' ',
					],
				],
			],
			'shadow'     => [
				'config' => [
					'disabled_controls' => [ 'text' ],
				],
			],
			'animation'  => [
				'hidden' => true,
			],
		);
	}
}
