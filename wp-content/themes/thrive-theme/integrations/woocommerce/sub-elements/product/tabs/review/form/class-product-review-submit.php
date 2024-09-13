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
 * Class Product_Review_Submit
 *
 * @package Thrive\Theme\Integrations\WooCommerce\Elements
 */
class Product_Review_Submit extends WooCommerce\Elements\Abstract_Sub_Element {
	/**
	 * @return string
	 */
	public function name() {
		return __( 'Submit', 'thrive-theme' );
	}

	/**
	 * @return string
	 */
	public function identifier() {
		return '#review_form .comment-form-submit';
	}

	/**
	 * Whether or not the this element can be edited while under :hover state
	 *
	 * @return bool
	 */
	public function has_hover_state() {
		return true;
	}

	/**
	 * @return array
	 */
	public function own_components() {
		$components      = parent::own_components();
		$button_selector = ' button';

		foreach ( $components['typography']['config'] as $control => $config ) {
			if ( is_array( $config ) ) {
				$components['typography']['config'][ $control ]['css_suffix'] = $button_selector;
			}
		}

		$components['typography']['config']['css_suffix'] = $button_selector;
		$components['background']['config']['css_suffix'] = $button_selector;
		$components['borders']['config']['css_suffix']    = $button_selector;

		return $components;
	}
}

return new Product_Review_Submit( 'wc-product-review-submit' );
