<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package TCB2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

class TCB_Lead_Generation_Select_Option_Element extends TCB_Element_Abstract {

	public function name() {
		return __( 'Dropdown Field Option', 'thrive-cb' );
	}

	public function identifier() {
		return '.tve-lg-dropdown-option';
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
	public function active_state_config() {
		return true;
	}

	public function own_components() {
		$prefix_config = tcb_selection_root() . ' ';

		return array(
			'lead_generation_select_option' => array(
				'config' => array(
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
