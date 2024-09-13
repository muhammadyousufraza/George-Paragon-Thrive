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
 * Class TCB_Carousel_Dots_Element
 */
class TCB_Carousel_Dots_Element extends TCB_Icon_Element {
	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Dots', 'thrive-cb' );
	}

	/**
	 * Element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.tcb-carousel-dots,.slick-dots';
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

		$components['icon']['disabled_controls']   = [ 'ToggleURL', 'link', 'RotateIcon' ];
		$components['layout']['disabled_controls'] = [
			'Width',
			'Height',
			'Display',
			'Overflow',
			'ScrollStyle',
			'Alignment',
			'Position',
			'Float',
		];
		$components['scroll']                      = [ 'hidden' => true ];
		$components['animation']                   = [ 'hidden' => true ];
		$components['styles-templates']            = [ 'hidden' => true ];

		$components['carousel_dots']                                       = $components['icon'];
		$components['carousel_dots']['config']['ColorPicker']['important'] = true;
		$components['carousel_dots']['config']['HorizontalSpace']          = array(
			'config'  => array(
				'min'   => '0',
				'max'   => '100',
				'label' => __( 'Horizontal space', 'thrive-cb' ),
				'um'    => [ 'px', '%' ],
			),
			'extends' => 'Slider',
		);

		$components['layout']['config']['MarginAndPadding']['important'] = true;
		unset( $components['icon'] );

		return $components;
	}
}
