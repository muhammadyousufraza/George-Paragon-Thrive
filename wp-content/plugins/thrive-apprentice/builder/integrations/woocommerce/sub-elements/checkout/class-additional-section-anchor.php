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
 * Class Additional_Section_Anchor
 * @package Thrive\Theme\Integrations\WooCommerce\Elements
 */
class Additional_Section_Anchor extends WooCommerce\Elements\Abstract_Sub_Element {
	/**
	 * @return string
	 */
	public function name() {
		return __( 'Link', 'thrive-theme' );
	}

	/**
	 * @return string
	 */
	public function identifier() {
		return '.woocommerce-info a';
	}

	/**
	 * @return array
	 */
	public function own_components() {
		$components = parent::own_components();

		return $components;
	}
}

return new Additional_Section_Anchor( 'wc-additional-section-anchor' );
