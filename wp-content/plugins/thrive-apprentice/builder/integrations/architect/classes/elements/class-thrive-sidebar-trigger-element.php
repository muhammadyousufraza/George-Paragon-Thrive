<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Thrive_Sidebar_Trigger_Element
 */
class Thrive_Sidebar_Trigger_Element extends Thrive_Theme_Element_Abstract {
	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Icon', 'thrive-theme' );
	}

	/**
	 * WordPress element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.tve-off-screen-sidebar-trigger';
	}

	/**
	 * Component and control config
	 *
	 * @return array
	 */
	public function own_components() {
		$components = [
			'sidebar-trigger'  => [
				'config' => [
					'ExpandedIcon'  => [
						'config'  => [
							'label' => __( 'Expanded Icon', 'thrive-theme' ),
						],
						'extends' => 'ModalPicker',
					],
					'CollapsedIcon' => [
						'config'  => [
							'label' => __( 'Collapsed Icon', 'thrive-theme' ),
						],
						'extends' => 'ModalPicker',
					],
					'IconColor'     => [
						'config'  => [
							'label' => __( 'Color', 'thrive-theme' ),
						],
						'extends' => 'ColorPicker',
					],
					'Size'          => [
						'config'  => [
							'min'   => '5',
							'max'   => '50',
							'label' => __( 'Size', 'thrive-theme' ),
							'um'    => [ 'px' ],
						],
						'extends' => 'Slider',
					],
				],
			],
			'typography'       => [ 'hidden' => true ],
			'animation'        => [ 'hidden' => true ],
			'responsive'       => [ 'hidden' => true ],
			'styles-templates' => [ 'hidden' => true ],
		];

		$components['shadow']['config']['important'] = true;
		$components['borders']['config']['Borders']  = [ 'important' => true ];

		$components['layout']['disabled_controls'] = [ 'Display', 'Alignment', '.tve-advanced-controls' ];

		return $components;
	}

	public function has_hover_state() {
		return true;
	}

	public function hide() {
		return true;
	}
}

return new Thrive_Sidebar_Trigger_Element( 'sidebar-trigger' );
