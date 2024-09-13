<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class TCB_Social_Follow_Item_Element
 */
class TCB_Social_Share_Count_Label_Element extends TCB_Element_Abstract {
	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Social Share Count Label', 'thrive-cb' );
	}

	/**
	 * Default element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.tve_s_cnt_label';
	}

	/**
	 * Either to display or not the element in the sidebar menu
	 *
	 * @return bool
	 */
	public function hide() {
		return true;
	}

	public function own_components() {
		$components = parent::own_components();

		$typography_defaults = [
			'css_prefix' => '',
			'css_suffix' => '',
			'important'  => true,
		];

		$components['typography']                  = [
			'disabled_controls' => [ '.tve-advanced-controls', ],
			'config'            => [
				'FontSize'      => $typography_defaults,
				'FontColor'     => $typography_defaults,
				'LineHeight'    => $typography_defaults,
				'FontFace'      => $typography_defaults,
				'TextStyle'     => $typography_defaults,
				'LetterSpacing' => $typography_defaults,
				'TextTransform' => $typography_defaults,
			],
		];
		$components['layout']['disabled_controls'] = [
			'Alignment',
			'Display',
		];

		return $components;
	}
}
