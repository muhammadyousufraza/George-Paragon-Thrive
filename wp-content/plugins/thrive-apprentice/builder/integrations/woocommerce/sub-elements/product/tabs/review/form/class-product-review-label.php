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
 * Class Product_Review_Label
 * @package Thrive\Theme\Integrations\WooCommerce\Elements
 */
class Product_Review_Label extends WooCommerce\Elements\Abstract_Sub_Element {
	/**
	 * @return string
	 */
	public function name() {
		return __( 'Review Label', 'thrive-theme' );
	}

	/**
	 * @return string
	 */
	public function identifier() {
		return '#review_form label';
	}
}

return new Product_Review_Label( 'wc-product-review-label' );
