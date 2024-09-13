<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Thrive_Price_Filter_Title_Element
 */
class Thrive_Price_Filter_Title_Element extends Thrive_Theme_Element_Abstract {
	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Title', 'thrive-theme' );
	}

	/**
	 * WordPress element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.thrv_woocommerce_price_filter .widget-title';
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

		$components['animation'] = [ 'hidden' => true ];
		$components['shadow']    = [ 'hidden' => true ];

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
}

return new Thrive_Price_Filter_Title_Element( 'wc-price-filter-title' );
