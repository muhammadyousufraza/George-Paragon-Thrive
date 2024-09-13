<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

if ( ! class_exists( '\TCB_Icon_Element', false ) ) {
	require_once TVE_TCB_ROOT_PATH . 'inc/classes/elements/class-tcb-icon-element.php';
}

/**
 * Class TCB_Carousel_Arrows_Element
 */
class TCB_Carousel_Arrows_Element extends TCB_Icon_Element {
	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Arrows', 'thrive-cb' );
	}

	/**
	 * Element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.thrv_icon.tcb-carousel-arrow';
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
	 * @return array
	 */
	public function own_components() {
		$components = parent::own_components();

		$components['icon']['disabled_controls']              = [ 'ToggleURL', 'link', 'RotateIcon' ];
		$components['layout']['disabled_controls']            = [
			'margin-bottom',
			'Width',
			'Height',
			'Display',
			'Overflow',
			'ScrollStyle',
			'Alignment',
			'Position',
			'Float',
		];
		$components['icon']['config']['Slider']['css_prefix'] = tcb_selection_root() . ' .tcb-carousel-arrow';

		$components['scroll']    = [ 'hidden' => true ];
		$components['animation'] = [ 'hidden' => false ];

		$components['carousel_arrows'] = $components['icon'];
		unset( $components['icon'] );

		return $components;
	}
}
