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
 * Class Product_Select_Label
 * @package Thrive\Theme\Integrations\WooCommerce\Elements
 */
class Product_Select_Label extends WooCommerce\Elements\Abstract_Sub_Element {
	/**
	 * @return string
	 */
	public function name() {
		return __( 'Product Select Label', 'thrive-theme' );
	}

	/**
	 * @return string
	 */
	public function identifier() {
		return '.variations label';
	}
}

return new Product_Select_Label( 'wc-product-select-label' );
