<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

namespace TCB\Integrations\WooCommerce\Shortcodes\Widgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Abstract_Sub_Element
 *
 * @package TCB\Integrations\WooCommerce\Shortcodes\Widgets
 */
class Abstract_Sub_Element extends \TCB_Element_Abstract {
	/**
	 * All sub elements are not visible
	 *
	 * @return bool
	 */
	public function hide() {
		return true;
	}

	/**
	 * Get the components that are specific to the widgets' editable elements
	 *
	 * @return array
	 */
	public function _components() {
		$components = $this->general_components();

		$components['animation']        = [ 'hidden' => true ];
		$components['responsive']       = [ 'hidden' => true ];
		$components['styles-templates'] = [ 'hidden' => true ];
		$components['background']       = [ 'hidden' => true ];
		$components['layout']           = [ 'hidden' => true ];
		$components['shadow']           = [ 'hidden' => true ];
		$components['borders']          = [ 'hidden' => true ];

		foreach ( $components['typography']['config'] as $control => $config ) {
			if ( is_array( $config ) ) {
				$components['typography']['config'][ $control ]['css_suffix'] = '';
			}
		}

		return $components;
	}

	/**
	 * @return array
	 */
	public function own_components() {
		return $this->_components();
	}
}
