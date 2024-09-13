<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package TCB2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

class TCB_Lead_Generation_Textarea_Element extends TCB_Element_Abstract {

	public function name() {
		return __( 'Form Textarea', 'thrive-cb' );
	}

	public function identifier() {
		return '.tve_lg_textarea';
	}

	public function hide() {
		return true;
	}

	public function own_components() {
		$prefix_config = tcb_selection_root();

		$controls_default_config      = [
			'css_suffix' => [ ' input', ' textarea' ],
			'css_prefix' => $prefix_config . ' ',
		];
		$controls_default_config_text = [
			'css_suffix' => [
				' input',
				' textarea',
				' input::placeholder',
				' textarea::placeholder',
			],
			'css_prefix' => $prefix_config . ' ',
		];

		return array(
			'lead_generation_textarea' => array(
				'config' => array(
					'placeholder' => array(
						'config' => array(
							'label' => __( 'Placeholder', 'thrive-cb' ),
						),
					),
					'ShowLabel'   => array(
						'config'  => array(
							'name'    => '',
							'label'   => __( 'Show Label', 'thrive-cb' ),
							'default' => true,
						),
						'extends' => 'Switch',
					),
					'ShowCounter' => array(
						'config'  => array(
							'name'    => '',
							'label'   => __( 'Show Character Count', 'thrive-cb' ),
							'default' => true,
						),
						'extends' => 'Switch',
					),
					'Resizing'    => array(
						'config'  => array(
							'name'    => '',
							'label'   => __( 'Disable User Resizing', 'thrive-cb' ),
							'default' => true,
							'info'    => true,
						),
						'extends' => 'Switch',
					),
					'Rows'        => array(
						'config'  => array(
							'name'      => __( 'Rows / Visible Lines', 'thrive-cb' ),
							'default'   => 3,
							'min'       => 1,
							'max'       => 100,
							'maxlength' => 2,
						),
						'extends' => 'Input',
					),
					'MinChar'     => array(
						'config'  => array(
							'label'   => __( 'Min. Characters', 'thrive-cb' ),
							'default' => 10,
							'min'     => 0,
							'max'     => 350,
							'info'    => true,
							'size'    => 'medium',
						),
						'extends' => 'Input',
					),
					'MaxChar'     => array(
						'config'  => array(
							'label'   => __( 'Max. Characters', 'thrive-cb' ),
							'default' => 350,
							'min'     => 0,
							'max'     => 10000,
							'info'    => true,
							'size'    => 'medium',
						),
						'extends' => 'Input',
					),
				),
			),
			'typography'               => [
				'config' => [
					'FontSize'      => $controls_default_config_text,
					'FontColor'     => $controls_default_config_text,
					'FontFace'      => $controls_default_config_text,
					'LetterSpacing' => $controls_default_config_text,
					'LineHeight'    => $controls_default_config_text,
					'TextAlign'     => $controls_default_config_text,
					'TextStyle'     => $controls_default_config_text,
					'TextTransform' => $controls_default_config_text,
				],
			],
			'layout'                   => [
				'disabled_controls' => [
					'Width',
					'Height',
					'Alignment',
					'.tve-advanced-controls',
				],
				'config'            => [
					'MarginAndPadding' => [
						'margin_suffix'  => '',
						'padding_suffix' => [ ' input', ' textarea' ],
						'css_prefix'     => $prefix_config . ' ',
					],
				],
			],
			'borders'                  => [
				'config' => [
					'Borders' => $controls_default_config,
					'Corners' => $controls_default_config,
				],
			],
			'animation'                => [
				'hidden' => true,
			],
			'background'               => [
				'config' => [
					'ColorPicker' => $controls_default_config,
					'PreviewList' => $controls_default_config,
				],
			],
			'shadow'                   => [
				'config' => $controls_default_config,
			],
			'styles-templates'         => [
				'config' => [],
			],
			'responsive'               => [
				'hidden' => true,
			],
		);
	}

	/**
	 * @return bool
	 */
	public function has_hover_state() {
		return true;
	}
}
