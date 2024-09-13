<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Thrive_Widget_Title_Element
 */
class Thrive_Price_Filter_Button_Element extends Thrive_Theme_Element_Abstract {
	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Button', 'thrive-theme' );
	}

	/**
	 * WordPress element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.thrv_woocommerce_price_filter button';
	}

	/**
	 * Hide this.
	 */
	public function hide() {
		return true;
	}

	/**
	 * Default components that most theme elements use
	 *
	 * @return array
	 */
	public function own_components() {
		$components = parent::own_components();

		$components['animation']                       = [ 'hidden' => true ];
		$components['responsive']                      = [ 'hidden' => true ];
		$components['layout']                          = [ 'hidden' => true ];
		$components['shadow']                          = [ 'hidden' => true ];
		$components['borders']                         = [ 'hidden' => true ];
		$components['typography']['disabled_controls'] = [ 'TextAlign', '.tve-advanced-controls' ];

		return $components;
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

	/**
	 * Whether or not the this element can be edited while under :hover state
	 *
	 * @return bool
	 */
	public function has_hover_state() {
		return true;
	}

}

return new Thrive_Price_Filter_Button_Element( 'wc-price-filter-button' );
