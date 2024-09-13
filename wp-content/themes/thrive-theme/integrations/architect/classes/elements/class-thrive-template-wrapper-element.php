<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Thrive_Template_Wrapper_Element
 */
class Thrive_Template_Wrapper_Element extends Thrive_Theme_Element_Abstract {
	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return thrive_template()->title() . ' ' . __( 'Settings', 'thrive-theme' );
	}

	/**
	 * WordPress element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '#wrapper';
	}

	/**
	 * Component and control config
	 *
	 * @return array
	 */
	public function own_components() {
		$default = parent::own_components();

		$default['animation']  = [ 'hidden' => true ];
		$default['responsive'] = [ 'hidden' => true ];
		$default['typography'] = [ 'hidden' => true ];

		$default['layout']['disabled_controls'] = [
			'Width',
			'Height',
			'Display',
			'Alignment',
			'.tve-advanced-controls',
			'hr',
		];

		$default['background'] = [
			'config' => [ 'to' => 'main::#wrapper' ],
		];

		$progress_bar_selector = '.thrive-progress-bar';

		$controls = [
			'template-wrapper' => [
				'config' => [
					'ContentWidth'      => [
						'config'  => [
							'default' => '1080',
							'min'     => '420',
							'max'     => '1980',
							'label'   => __( 'Content width', 'thrive-theme' ),
							'um'      => [ 'px', '%' ],
							'css'     => 'max-width',
						],
						'extends' => 'Slider',
					],
					'LayoutWidth'       => [
						'config'  => [
							'default' => '1080',
							'min'     => '420',
							'max'     => '1980',
							'label'   => __( 'Layout width', 'thrive-theme' ),
							'um'      => [ 'px', '%' ],
							'css'     => 'max-width',
						],
						'extends' => 'Slider',
					],
					'ProgressIndicator' => [
						'config'  => [
							'name'    => '',
							'label'   => __( 'Reading progress indicator', 'thrive-theme' ),
							'default' => false,
						],
						'extends' => 'Switch',
						'to'      => $progress_bar_selector,
					],
					'ProgressPosition'  => [
						'config'  => [
							'default' => 'top',
							'name'    => __( 'Progress Position', 'thrive-theme' ),
							'options' => [
								[
									'name'  => __( 'Top of viewport', 'thrive-theme' ),
									'value' => 'top',
								],
								[
									'name'  => __( 'Underneath the header', 'thrive-theme' ),
									'value' => 'under',
								],
							],
						],
						'extends' => 'Select',
						'to'      => $progress_bar_selector,
					],
					'ProgressBarColor'  => [
						'config'  => [
							'label'   => __( 'Color', 'thrive-theme' ),
							'options' => [
								'output' => 'object',
							],
						],
						'to'      => $progress_bar_selector,
						'extends' => 'ColorPicker',
					],
					'ProgressBarHeight' => [
						'config'  => [
							'default' => '6',
							'min'     => '2',
							'max'     => '10',
							'label'   => __( 'Height', 'thrive-theme' ),
							'um'      => [ 'px' ],
							'css'     => 'height',
						],
						'to'      => $progress_bar_selector,
						'extends' => 'Slider',
					],
				],
			],
		];

		return array_merge( $default, $controls );
	}

	/**
	 * Hide this.
	 */
	public function hide() {
		return true;
	}

	/**
	 * This element has a selector
	 *
	 * @return bool
	 */
	public function has_selector() {
		return true;
	}

	/**
	 * No icons for the wrapper
	 *
	 * @return bool
	 */
	public function has_icons() {
		return false;
	}
}

return new Thrive_Template_Wrapper_Element( 'template-wrapper' );
