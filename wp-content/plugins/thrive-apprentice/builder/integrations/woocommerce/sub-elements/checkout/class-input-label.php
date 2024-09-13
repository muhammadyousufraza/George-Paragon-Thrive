<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

namespace Thrive\Theme\Integrations\WooCommerce\Elements;

use Thrive\Theme\Integrations\WooCommerce;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Input_Label
 * @package Thrive\Theme\Integrations\WooCommerce\Elements
 */
class Input_Label extends WooCommerce\Elements\Abstract_Sub_Element {
	/**
	 * @return string
	 */
	public function name() {
		return __( 'Checkout label', 'thrive-theme' );
	}

	/**
	 * @return string
	 */
	public function identifier() {
		return '.form-row label, .lost_password, .woocommerce-form-login > p:first-child';
	}

	/**
	 * @return array
	 */
	public function own_components() {
		$components = parent::own_components();
		foreach ( $components['typography']['config'] as $control => $config ) {
			if ( is_array( $config ) ) {
				$components['typography']['config'][ $control ]['css_suffix'] = [ ' abbr', '' ];
			}
		}
		$components['typography']['config']['FontColor']['important'] = true;

		return $components;
	}
}

return new Input_Label( 'wc-input-label' );
