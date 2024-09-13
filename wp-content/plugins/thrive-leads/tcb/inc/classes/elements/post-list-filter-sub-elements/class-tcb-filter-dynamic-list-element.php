<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class TCB_Filter_Dynamic_List_Element
 */
class TCB_Filter_Dynamic_List_Element extends TCB_Element_Abstract {
	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Dynamic Styled List', 'thrive-cb' );
	}

	public function hide() {
		return true;
	}

	/**
	 * WordPress element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.tcb-filter-list-group';
	}

	/**
	 * Default components that most theme elements use
	 *
	 * @return array
	 */
	public function own_components() {
		return [
			'filter_dynamic_list' => [
				'config' => [
					'EnableIcons' => [
						'config'  => [
							'name'    => '',
							'label'   => __( 'Enable Icons for List Items', 'thrive-cb' ),
							'default' => true,
						],
						'extends' => 'Switch',
					],
					'ModalPicker' => [
						'config' => [
							'label' => __( 'Change all icons', 'thrive-cb' ),
						],
					],
				],
			],
			'typography'          => [
				'disabled_controls' => [
					'[data-value="tcb-typography-line-height"] ',
					'.tve-advanced-controls',
					'p_spacing',
					'h1_spacing',
					'h2_spacing',
					'h3_spacing',
					'[data-view="LineHeight"]',
				],
				'config'            => [
					'TextAlign' => [
						'css_suffix'   => ' .tcb-filter-list',
						'property'     => 'justify-content',
						'property_val' => [
							'left'    => 'flex-start',
							'center'  => 'center',
							'right'   => 'flex-end',
							'justify' => 'space-evenly',
						],
					],
				],
			],
			'layout'              => [
				'disabled_controls' => [
					'Display',
					'.tve-advanced-controls',
				],
			],
			'background'          => [
				'disabled_controls' => [
					'.video-bg',
				],
			],
			'animation'           => [
				'disabled_controls' => [
					'.btn-inline:not(.anim-animation):not(.anim-popup)',
				],
			],
			'responsive'          => [ 'hidden' => true ],
			'shadow'              => [ 'hidden' => true ],
			'styles-templates'    => [ 'hidden' => true ],
		];
	}
}
