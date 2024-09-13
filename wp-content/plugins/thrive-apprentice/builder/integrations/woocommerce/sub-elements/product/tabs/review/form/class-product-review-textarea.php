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
 * Class Product_Review_Textarea
 * @package Thrive\Theme\Integrations\WooCommerce\Elements
 */
class Product_Review_Textarea extends WooCommerce\Elements\Abstract_Sub_Element {
	/**
	 * @return string
	 */
	public function name() {
		return __( 'Review Textarea', 'thrive-theme' );
	}

	/**
	 * @return string
	 */
	public function identifier() {
		return '#review_form #comment';
	}

	/**
	 * @return array
	 */
	public function own_components() {
		$components = parent::own_components();

		foreach ( $components['typography']['config'] as $control => $config ) {
			if ( is_array( $config ) ) {
				$components['typography']['config'][ $control ]['css_suffix'] = [ '', ' a' ];
			}
		}

		return $components;
	}
}

return new Product_Review_Textarea( 'wc-product-review-textarea' );
