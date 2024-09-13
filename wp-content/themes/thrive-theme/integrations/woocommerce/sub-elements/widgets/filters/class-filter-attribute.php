<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Thrive_Filter_Attribute_Element
 */
class Thrive_Filter_Attribute_Element extends Thrive_Theme_Element_Abstract {
	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Attribute', 'thrive-theme' );
	}

	/**
	 * WordPress element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.woocommerce-widget-layered-nav-list li';
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

		$components['animation']  = [ 'hidden' => true ];
		$components['responsive'] = [ 'hidden' => true ];
		$components['background'] = [ 'hidden' => true ];
		$components['layout']     = [ 'hidden' => true ];
		$components['shadow']     = [ 'hidden' => true ];
		$components['borders']    = [ 'hidden' => true ];

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

return new Thrive_Filter_Attribute_Element( 'wc-attribute-filter-attribute' );
