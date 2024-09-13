<?php
/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 11/6/2017
 * Time: 5:27 PM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

if ( ! class_exists( 'TCB_Text_Element' ) ) {
	require_once TVE_TCB_ROOT_PATH . 'inc/classes/elements/class-tcb-text-element.php';
}

class TCB_Resources_Label_Element extends TCB_Element_Abstract {

	protected $_tag = 'resources_label';

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Text', 'thrive-apprentice' );
	}

	/**
	 * Section element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.tva-resource-text';
	}

	public function own_components() {
		$css_prefix = tcb_selection_root() . ' ';
		$css_suffix = [
			' h1',
			' h2',
			' h3',
			' h4',
			' h5',
			' h6',
			' p',
		];

		return [
			'resources_label'  => array(
				'config' => array(
					'ToggleControls' => array(
						'config'  => array(
							'buttons' => array(
								array(
									'value'   => 'tcb-typography-font-size',
									'text'    => __( 'Font Size', 'thrive-apprentice' ),
									'default' => true,
								),
								array(
									'value' => 'tcb-typography-line-height',
									'text'  => __( 'Line Height', 'thrive-apprentice' ),
								),
								array(
									'value' => 'tcb-typography-letter-spacing',
									'text'  => __( 'Letter Spacing', 'thrive-apprentice' ),
								),
							),
						),
						'extends' => 'ButtonGroup',
					),
					'FontSize'       => array(
						'css_suffix' => $css_suffix,
						'css_prefix' => $css_prefix,
						'important'  => true,
						'config'     => array(
							'default' => '16',
							'min'     => '1',
							'max'     => '100',
							'label'   => '',
							'um'      => array( 'px', 'em' ),
							'css'     => 'fontSize',
						),
						'extends'    => 'FontSize',
					),
					'LetterSpacing'  => array(
						'css_suffix' => $css_suffix,
						'css_prefix' => $css_prefix,
						'important'  => true,
						'config'     => array(
							'default' => 'auto',
							'min'     => '0',
							'max'     => '100',
							'label'   => '',
							'um'      => array( 'px' ),
							'css'     => 'letterSpacing',
						),
						'extends'    => 'Slider',
					),
					'FontColor'      => array(
						'css_suffix' => $css_suffix,
						'css_prefix' => $css_prefix,
						'important'  => true,
						'config'     => array(
							'default' => '000',
							'label'   => 'Color',
							'options' => array(
								'output' => 'object',
							),
						),
						'extends'    => 'ColorPicker',
					),
					'TextAlign'      => array(
						'css_suffix' => $css_suffix,
						'css_prefix' => $css_prefix,
						'important'  => true,
						'config'     => array(
							'name'    => __( 'Alignment', 'thrive-apprentice' ),
							'buttons' => array(
								array(
									'icon'    => 'format-align-left',
									'text'    => '',
									'value'   => 'left',
									'default' => true,
								),
								array(
									'icon'  => 'format-align-center',
									'text'  => '',
									'value' => 'center',
								),
								array(
									'icon'  => 'format-align-right',
									'text'  => '',
									'value' => 'right',
								),
								array(
									'icon'  => 'format-align-justify',
									'text'  => '',
									'value' => 'justify',
								),
							),
						),
						'extends'    => 'ButtonGroup',
					),
					'TextStyle'      => array(
						'css_suffix' => $css_suffix,
						'css_prefix' => $css_prefix,
						'important'  => true,
					),
					'TextTransform'  => array(
						'css_suffix' => $css_suffix,
						'css_prefix' => $css_prefix,
						'important'  => true,
						'config'     => array(
							'name'    => __( 'Transform', 'thrive-apprentice' ),
							'buttons' => array(
								array(
									'icon'    => 'none',
									'text'    => '',
									'value'   => 'none',
									'default' => true,
								),
								array(
									'icon'  => 'format-all-caps',
									'text'  => '',
									'value' => 'uppercase',
								),
								array(
									'icon'  => 'format-capital',
									'text'  => '',
									'value' => 'capitalize',
								),
								array(
									'icon'  => 'format-lowercase',
									'text'  => '',
									'value' => 'lowercase',
								),
							),
						),
						'extends'    => 'ButtonGroup',
					),
					'FontFace'       => array(
						'css_suffix' => $css_suffix,
						'css_prefix' => $css_prefix,
						'important'  => true,
						'config'     => array(
							'template' => 'controls/font-manager',
							'inline'   => false,
						),
					),
					'LineHeight'     => array(
						'css_suffix' => $css_suffix,
						'css_prefix' => $css_prefix,
						'important'  => true,
						'config'     => array(
							'default' => '16',
							'min'     => '1',
							'max'     => '100',
							'label'   => '',
							'um'      => array( 'px', 'em' ),
							'css'     => 'lineHeight',
						),
						'extends'    => 'LineHeight',
					),
				),
			),
			'typography'       => [
				'hidden' => true,
			],
			'styles-templates' => [
				'hidden' => true,
			],
			'scroll'           => [
				'hidden' => true,
			],
			'responsive'       => [
				'hidden' => true,
			],
			'animation'        => [
				'hidden' => true,
			],
			'layout'           => [
				'disabled_controls' => [ 'Alignment', 'Display', '.tve-advanced-controls' ],
			],
		];
	}

	public function hide() {
		return true;
	}
}

return new TCB_Resources_Label_Element();
