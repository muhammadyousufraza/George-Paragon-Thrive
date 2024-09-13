<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class TCB_Label_Element
 */
class TCB_Label_Element extends TCB_Element_Abstract {

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Inline text', 'thrive-cb' );
	}

	/**
	 * Return icon class needed for display in menu
	 *
	 * @return string
	 */
	public function icon() {
		return '';
	}

	/**
	 * Section element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.thrv-inline-text, .thrv_toggle_shortcode .tve_faqB h4';
	}

	/**
	 * Hidden element
	 *
	 * @return string
	 */
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
	 * Component and control config
	 *
	 * @return array
	 */
	public function own_components() {
		return array(
			'text'             => array(
				'config' => array(
					'FontSize'       => array(
						'config'  => array(
							'default' => '16',
							'min'     => '1',
							'max'     => '100',
							'label'   => __( 'Font Size', 'thrive-cb' ),
							'um'      => [ 'px', 'em' ],
							'css'     => 'fontSize',
						),
						'extends' => 'Slider',
					),
					'LineHeight'     => array(
						'config'  => array(
							'default' => '1',
							'min'     => '1',
							'max'     => '100',
							'label'   => __( 'Line Height', 'thrive-cb' ),
							'um'      => [ 'em', 'px' ],
							'css'     => 'lineHeight',
						),
						'extends' => 'Slider',
					),
					'LetterSpacing'  => array(
						'config'  => array(
							'default' => 'auto',
							'min'     => '1',
							'max'     => '100',
							'label'   => __( 'Letter Spacing', 'thrive-cb' ),
							'um'      => [ 'px' ],
							'css'     => 'letterSpacing',
						),
						'extends' => 'Slider',
					),
					'FontColor'      => array(
						'config'  => array(
							'default' => '000',
							'label'   => __( 'Font Color', 'thrive-cb' ),
							'options' => [
								'output' => 'object',
							],
						),
						'extends' => 'ColorPicker',
					),
					'FontBackground' => array(
						'config'  => array(
							'default' => '000',
							'label'   => __( 'Font Highlight', 'thrive-cb' ),
							'options' => [
								'output' => 'object',
							],
						),
						'extends' => 'ColorPicker',
					),
					'FontFace'       => [
						'config'  => [
							'template' => 'controls/font-manager',
							'inline'   => true,
						],
						'extends' => 'FontManager',
					],
				),
			),
			'typography'       => [ 'hidden' => true ],
			'layout'           => [ 'hidden' => true ],
			'borders'          => [ 'hidden' => true ],
			'animation'        => [ 'hidden' => true ],
			'background'       => [ 'hidden' => true ],
			'responsive'       => [ 'hidden' => true ],
			'styles-templates' => [ 'hidden' => true ],
			'shadow'           => [
				'config' => [
					'disabled_controls' => [ 'inner', 'drop' ],
					'with_froala'       => true,
				],
			],
		);
	}
}
