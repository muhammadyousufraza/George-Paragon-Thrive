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
 * Class Product_Review_Author_Image
 * @package Thrive\Theme\Integrations\WooCommerce\Elements
 */
class Product_Review_Author_Image extends WooCommerce\Elements\Abstract_Sub_Element {
	/**
	 * @return string
	 */
	public function name() {
		return __( 'Review Author Image', 'thrive-theme' );
	}

	/**
	 * @return string
	 */
	public function identifier() {
		/* supercharged so it's more specific than Woo CSS */
		return '#reviews #comments .thrive-comment-author-picture img';
	}

	/**
	 * @return array
	 */
	public function own_components() {
		$components = parent::own_components();

		$components['typography'] = [ 'hidden' => true ];
		$components['background'] = [ 'hidden' => true ];

		return $components;
	}
}

return new Product_Review_Author_Image( 'wc-product-review-author-image' );
