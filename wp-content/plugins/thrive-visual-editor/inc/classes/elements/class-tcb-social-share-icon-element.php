<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class TCB_Social_Follow_Item_Element
 */
class TCB_Social_Share_Icon_Element extends TCB_Social_Follow_Item_Element {
	/**
	 * Element name
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Social Share Icon', 'thrive-cb' );
	}

	/**
	 * Element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.tve_share_item .tve_s_icon';
	}

	/**
	 * Component and control config
	 *
	 * @return array
	 */
	public function own_components() {
		$components = parent::own_components();

		$components['animation']['disabled_controls'] = [ '.btn-inline.anim-link', '.btn-inline.anim-popup' ];

		$components['social_follow_item']['config']['Slider']['css_suffix'] = '.tve_s_item .tve_s_icon ';

		$components['social_follow_item']['disabled_controls'] = [ 'NetworkColor' ];

		$components['background']['config'] = array(
			'ColorPicker' => array( 'css_prefix' => tcb_selection_root() . ' a ' ),
			'PreviewList' => array( 'css_prefix' => tcb_selection_root() . ' a ' ),
		);

		$components['borders'] = [
			'config' => [
				'Borders' => [ 'css_prefix' => '', 'important' => 'true' ],
				'Corners' => [ 'css_prefix' => '', 'important' => 'true' ],
			],
		];

		$components['shadow'] = [
			'config' => [
				'css_prefix'        => '',
				'css_suffix'        => '',
				'important'         => 'true',
				'disabled_controls' => [ 'text' ],
			],
		];

		return $components;
	}

}
