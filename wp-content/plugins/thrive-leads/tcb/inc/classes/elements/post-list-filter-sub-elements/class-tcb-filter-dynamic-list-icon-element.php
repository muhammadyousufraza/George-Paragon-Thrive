<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * TCB_Filter_Dynamic_List_Icon_Element
 *
 * This is a default element used for displaying only icon for a component
 * It is not displayed in the sidebar elements
 */
class TCB_Filter_Dynamic_List_Icon_Element extends TCB_Icon_Element {

	/**
	 * @return string
	 */
	public function name() {
		return __( 'Filter List Icon', 'thrive-cb' );
	}

	/**
	 * @return string
	 */
	public function identifier() {
		return '.tcb-styled-list-icon .thrv_icon';
	}

	/**
	 * Either to display or not the element in the sidebar menu
	 *
	 * @return bool
	 */
	public function hide() {
		return true;
	}

	/**
	 * @return array
	 */
	public function own_components() {
		return [
			'icon'       => [
				'config' => [
					'ModalPicker' => [
						'config' => [
							'label' => __( 'Choose Icon', 'thrive-cb' ),
						],
					],
					'ColorPicker' => [
						'css_prefix' => tcb_selection_root() . ' ',
						'css_suffix' => ' > :first-child',
						'config'     => [
							'label' => __( 'Icon color', 'thrive-cb' ),
						],
					],
					'Slider'      => [
						'config' => [
							'default' => '30',
							'min'     => '8',
							'max'     => '200',
							'label'   => __( 'Icon size', 'thrive-cb' ),
							'um'      => [ 'px' ],
							'css'     => 'fontSize',
						],
					],
				],
			],
			'typography' => [ 'hidden' => true ],
			'shadow'     => [
				'config' => [
					'disabled_controls' => [ 'text' ],
				],
			],
			'animation'  => [ 'hidden' => true ],
			'responsive' => [ 'hidden' => true ],
		];
	}
}
