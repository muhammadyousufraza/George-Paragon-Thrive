<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

namespace Thrive\Theme\Integrations\WooCommerce\Elements;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

if ( ! class_exists( 'Thrive_Theme_Element_Abstract' ) ) {
	require_once ARCHITECT_INTEGRATION_PATH . '/classes/class-thrive-theme-element-abstract.php';
}

/**
 * Class Abstract_Sub_Element
 *
 * @package Thrive\Theme\Integrations\WooCommerce\Elements
 */
class Abstract_Sub_Element extends \Thrive_Theme_Element_Abstract {
	/**
	 * All sub elements are invisible
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

		$components['layout']['disabled_controls'] = [ 'Display', 'Alignment', '.tve-advanced-controls' ];

		$components['styles-templates'] = [ 'hidden' => true ];
		$components['responsive']       = [ 'hidden' => true ];
		$components['animation']        = [ 'hidden' => true ];
		$components['shadow']           = [ 'hidden' => true ];

		foreach ( $components['typography']['config'] as $control => $config ) {
			if ( is_array( $config ) ) {
				$components['typography']['config'][ $control ]['css_suffix'] = '';
			}
		}

		return $components;
	}
}
