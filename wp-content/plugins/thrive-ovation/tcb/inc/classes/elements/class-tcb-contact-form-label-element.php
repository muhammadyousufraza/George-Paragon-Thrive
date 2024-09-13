<?php
/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 5/21/2018
 * Time: 4:55 PM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

class TCB_Contact_Form_Label_Element extends TCB_Element_Abstract {

	/**
	 * Name of the Element
	 *
	 * @return string
	 */
	public function name() {

		return __( 'Contact Form Label', 'thrive-cb' );
	}

	/**
	 * Section element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.tve-cf-item label';
	}

	/**
	 * There is no need for HTML for this element since we need it only for control filter
	 *
	 * @return string
	 */
	protected function html() {
		return '';
	}

	/**
	 * Hide Element From Sidebar Menu
	 *
	 * @return bool
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
		return [
			'typography'       => [
				'disabled_controls' => [ 'TextAlign', '.tve-advanced-controls' ],
				'config'            => [
					'css_suffix'    => '',
					'FontSize'      => [
						'css_suffix' => '',
						'important'  => true,
					],
					'FontColor'     => [
						'css_suffix' => '',
						'important'  => true,
					],
					'LineHeight'    => [
						'css_suffix' => '',
						'important'  => true,
					],
					'LetterSpacing' => [
						'css_suffix' => '',
						'important'  => true,
					],
					'FontFace'      => [
						'css_suffix' => '',
						'important'  => true,
					],
					'TextStyle'     => [
						'css_suffix' => '',
						'important'  => true,
					],
					'TextTransform' => [
						'css_suffix' => '',
						'important'  => true,
					],
				],
			],
			'animation'        => [
				'hidden' => true,
			],
			'responsive'       => [
				'hidden' => true,
			],
			'styles-templates' => [
				'hidden' => true,
			],
		];
	}
}
