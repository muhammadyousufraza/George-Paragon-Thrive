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
 * @package Thrive\Theme\Integrations\WooCommerce\Elements
 */
class Dropdown extends WooCommerce\Elements\Abstract_Sub_Element {
	/**
	 * @return string
	 */
	public function name() {
		return __( 'Checkout Dropdown', 'thrive-theme' );
	}

	/**
	 * @return string
	 */
	public function identifier() {
		return '.select2-container, #select2-billing_state-results, #select2-billing_country-results, .woocommerce-input-wrapper select';
	}

	/**
	 * @return array
	 */
	public function own_components() {
		$components               = parent::own_components();
		$components['background'] = [ 'hidden' => true ];

		return $components;
	}
}

return new Dropdown( 'wc-dropdown' );
