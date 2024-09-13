<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package TCB2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

class TCB_Dynamic_Dropdown_Option_Element extends TCB_Element_Abstract {

	public function name() {
		return __( 'Dropdown Field Option', 'thrive-cb' );
	}

	public function identifier() {
		return '.tve-dynamic-dropdown-option';
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
		$suffix        = ' .tve-input-option-text';

		return [

			'typography'       => [
				'config' => [
					'FontColor'     => [
						'css_suffix' => $suffix,
						'important'  => true,
					],
					'TextAlign'     => [
						'css_suffix' => $suffix,
						'css_prefix' => $prefix_config,
						'important'  => true,
					],
					'FontSize'      => [
						'css_suffix' => $suffix,
						'important'  => true,
					],
					'TextStyle'     => [
						'css_suffix' => $suffix,
						'css_prefix' => $prefix_config,
						'important'  => true,
					],
					'LineHeight'    => [
						'css_suffix' => $suffix,
						'important'  => true,
					],
					'FontFace'      => [
						'css_suffix' => $suffix,
						'important'  => true,
					],
					'LetterSpacing' => [
						'css_suffix' => $suffix,
						'css_prefix' => $prefix_config,
						'important'  => true,
					],
					'TextTransform' => [
						'css_suffix' => $suffix,
						'css_prefix' => $prefix_config,
						'important'  => true,
					],
				],
			],
			'layout'           => [
				'disabled_controls' => [
					'margin',
					'.tve-advanced-controls',
					'Alignment',
					'Display',
				],
			],
			'animation'        => [
				'hidden' => true,
			],
			'styles-templates' => [ 'hidden' => true ],
			'responsive'       => [ 'hidden' => true ],
		];
	}
}
