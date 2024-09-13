<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package TCB2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

class TCB_Lead_Generation_Radio_Option_Element extends TCB_Element_Abstract {

	public function name() {
		return __( 'Radio Option', 'thrive-cb' );
	}

	public function identifier() {
		return '.tve_lg_radio_wrapper';
	}

	public function hide() {
		return true;
	}

	/**
	 * @return bool
	 */
	public function has_hover_state() {
		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function expanded_state_config() {
		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function expanded_state_label() {
		return __( 'Selected', 'thrive-cb' );
	}

	public function own_components() {
		$prefix_config = tcb_selection_root() . ' ';

		return array(
			'lead_generation_radio_option' => array(
				'config' => array(
					'RadioPalettes'     => array(
						'config'    => [],
						'extends'   => 'Palettes',
						'important' => apply_filters( 'tcb_lg_color_inputs_important', true ),
					),
					'LabelAsValue'      => array(
						'config'  => array(
							'name'    => '',
							'label'   => __( 'Use label as value', 'thrive-cb' ),
							'default' => true,
							'info'    => true,
						),
						'extends' => 'Switch',
					),
					'InputValue'        => array(
						'config'  => array(
							'label' => __( 'Value', 'thrive-cb' ),
						),
						'extends' => 'LabelInput',
					),
					'SetAsDefault'      => array(
						'config'  => array(
							'name'    => '',
							'label'   => __( 'Set as default', 'thrive-cb' ),
							'default' => false,
						),
						'extends' => 'Switch',
					),
					'RadioSize'         => array(
						'css_suffix' => ' .tve-checkmark',
						'config'     => array(
							'default' => '20',
							'min'     => '0',
							'max'     => '30',
							'label'   => __( 'Radio Size', 'thrive-cb' ),
							'um'      => [ 'px' ],
						),
						'extends'    => 'Slider',
					),
					'StyleChange'       => array(
						'config' => array(
							'label'   => __( 'Radio Style', 'thrive-cb' ),
							'preview' => [
								'key'   => '',
								'label' => 'default',
							],
						),
					),
					'RadioStylePicker'  => array(
						'config' => array(
							'label'   => __( 'Choose radio style', 'thrive-cb' ),
							'items'   => array(
								'default'    => __( 'Default', 'thrive-cb' ),
								'style-1'    => __( 'Style 1', 'thrive-cb' ),
								'style-2'    => __( 'Style 2', 'thrive-cb' ),
								'style-3'    => __( 'Style 3', 'thrive-cb' ),
								'gradient-1' => __( 'Gradient 1', 'thrive-cb' ),
								'gradient-2' => __( 'Gradient 2', 'thrive-cb' ),
								'gradient-3' => __( 'Gradient 3', 'thrive-cb' ),
								'gradient-4' => __( 'Gradient 4', 'thrive-cb' ),
							),
							'default' => 'no_style',
						),
					),
					'CustomAnswerInput' => [
						'config'  => [
							'full-width' => true,
						],
						'extends' => 'LabelInput',
					],
				),
			),

			'typography' => [
				'config' => [
					'FontColor'     => [
						'css_suffix' => ' .tve-input-option-text',
						'important'  => true,
					],
					'TextAlign'     => [
						'css_suffix' => ' .tve-input-option-text',
						'css_prefix' => $prefix_config,
						'important'  => true,
					],
					'FontSize'      => [
						'css_suffix' => ' .tve-input-option-text',
						'important'  => true,
					],
					'TextStyle'     => [
						'css_suffix' => ' .tve-input-option-text',
						'css_prefix' => $prefix_config,
						'important'  => true,
					],
					'LineHeight'    => [
						'css_suffix' => ' .tve-input-option-text',
						'important'  => true,
					],
					'FontFace'      => [
						'css_suffix' => ' .tve-input-option-text',
						'important'  => true,
					],
					'LetterSpacing' => [
						'css_suffix' => ' .tve-input-option-text',
						'css_prefix' => $prefix_config,
						'important'  => true,
					],
					'TextTransform' => [
						'css_suffix' => ' .tve-input-option-text',
						'css_prefix' => $prefix_config,
						'important'  => true,
					],
				],
			],
			'layout'     => [
				'disabled_controls' => [
					'margin',
					'.tve-advanced-controls',
					'Alignment',
					'Display',
				],
			],
			'animation'  => [
				'hidden' => true,
			],
		);
	}
}
