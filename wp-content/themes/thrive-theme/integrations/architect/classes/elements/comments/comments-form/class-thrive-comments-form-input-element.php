<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Thrive_Comments_Form_Input_Element
 */
class Thrive_Comments_Form_Input_Element extends Thrive_Theme_Element_Abstract {

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Comment Form Input', 'thrive-theme' );
	}

	/**
	 * WordPress element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.comment-form-input';
	}

	/**
	 * Hide this.
	 */
	public function hide() {
		return true;
	}

	/**
	 * Component and control config
	 *
	 * @return array
	 */
	public function own_components() {
		$controls_default_config = [
			'css_suffix' => '',
			'css_prefix' => '',
		];

		return [
			'comment-form-input' => [
				'config' => [
					'PlaceholderInput' => [
						'config'  => [
							'label'       => 'Placeholder',
							'extra_attrs' => '',
							'placeholder' => 'Placeholder',
						],
						'extends' => 'LabelInput',
					],
					'TextareaHeight'   => [
						'config'  => [
							'default' => '200',
							'min'     => '20',
							'max'     => '500',
							'label'   => __( 'Textarea Minimum Height', 'thrive-theme' ),
							'um'      => [ 'px', 'vh' ],
							'css'     => 'min-height',
						],
						'extends' => 'Slider',
					],
				],
			],
			'typography'         => [
				'config' => [
					'FontSize'      => $controls_default_config,
					'FontColor'     => $controls_default_config,
					'FontFace'      => $controls_default_config,
					'LetterSpacing' => $controls_default_config,
					'LineHeight'    => $controls_default_config,
					'TextAlign'     => $controls_default_config,
					'TextStyle'     => $controls_default_config,
					'TextTransform' => $controls_default_config,
				],
			],
			'layout'             => [
				'disabled_controls' => [
					'Alignment',
					'.tve-advanced-controls',
					'hr',
					'MaxWidth',
				],
			],
			'borders'            => [
				'config' => [
					'Borders' => array_merge( $controls_default_config, [ 'important' => true ] ),
					'Corners' => array_merge( $controls_default_config, [ 'important' => true ] ),
				],
			],
			'animation'          => [
				'hidden' => true,
			],
			'background'         => [
				'config' => [
					'ColorPicker' => $controls_default_config,
					'PreviewList' => $controls_default_config,
				],
			],
			'shadow'             => [
				'config' => array_merge( $controls_default_config, [ 'default_shadow' => 'none' ] ),
			],
			'styles-templates'   => [
				'hidden' => true,
			],
			'responsive'         => [
				'hidden' => true,
			],
		];
	}

	/**
	 * This element has no icons
	 * @return bool
	 */
	public function has_icons() {
		return false;
	}

	/**
	 * This element has a selector
	 * @return bool
	 */
	public function has_selector() {
		return true;
	}
}

return new Thrive_Comments_Form_Input_Element( 'comment-form-input' );

