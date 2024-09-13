<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

namespace TCB\Integrations\WooCommerce\Shortcodes\MiniCart;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Abstract_Sub_Element
 *
 * @package TCB\Integrations\WooCommerce\Shortcodes\MiniCart
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

	public function has_important_border() {
		return false;
	}

	/**
	 * TODO find a better way to define custom settings
	 *
	 * @param bool $hide_typography
	 * @param bool $important_border
	 *
	 * @return array
	 */
	public function _components( $hide_typography = false, $important_border = false ) {
		$components = $this->general_components();

		$components['layout']['disabled_controls'] = [ 'Display', 'Alignment', '.tve-advanced-controls' ];

		$components['animation']        = [ 'hidden' => true ];
		$components['responsive']       = [ 'hidden' => true ];
		$components['styles-templates'] = [ 'hidden' => true ];

		if ( $hide_typography ) {
			$components['typography'] = [ 'hidden' => true ];
		} else {
			foreach ( $components['typography']['config'] as $control => $config ) {
				if ( in_array( $control, [ 'css_suffix', 'css_prefix' ] ) ) {
					continue;
				}
				/* typography should apply only on the current element */
				$components['typography']['config'][ $control ]['css_suffix'] = [ '' ];
			}
		}

		if ( $this->has_important_border() ) {
			$components['borders']['config']['Borders']['important'] = true;
		}

		$components['layout']['disabled_controls'] = [ 'Display', 'Alignment', '.tve-advanced-controls' ];

		return $components;
	}
}
