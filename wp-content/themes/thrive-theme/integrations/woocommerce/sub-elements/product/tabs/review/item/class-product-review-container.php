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
 * Class Product_Review_Container
 * @package Thrive\Theme\Integrations\WooCommerce\Elements
 */
class Product_Review_Container extends WooCommerce\Elements\Abstract_Sub_Element {
	/**
	 * @return string
	 */
	public function name() {
		return __( 'Review Container', 'thrive-theme' );
	}

	/**
	 * @return string
	 */
	public function identifier() {
		return '#reviews .comment_container .comment-text';
	}

	/**
	 * @return array
	 */
	public function own_components() {
		$components = parent::own_components();

		$components['typography'] = [ 'hidden' => true ];

		return $components;
	}
}

return new Product_Review_Container( 'wc-product-review-container' );
