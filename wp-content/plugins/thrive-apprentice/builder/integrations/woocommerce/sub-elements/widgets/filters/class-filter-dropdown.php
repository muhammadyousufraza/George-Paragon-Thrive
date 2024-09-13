<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Thrive_Filter_Dropdown_Element
 */
class Thrive_Filter_Dropdown_Element extends Thrive_Theme_Element_Abstract {
	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Text', 'thrive-theme' );
	}

	/**
	 * WordPress element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.widget_layered_nav .select2-container--default';
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

		foreach ( $components['typography']['config'] as $control => $config ) {
			if ( is_array( $config ) ) {
				$components['typography']['config'][ $control ]['css_suffix'] = [ '', '.select2-selection' ];
			}
		}

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

return new Thrive_Filter_Dropdown_Element( 'wc-attribute-filter-dropdown' );
