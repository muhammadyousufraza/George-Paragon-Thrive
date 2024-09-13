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
 * Class Product_Description_Content
 * @package Thrive\Theme\Integrations\WooCommerce\Elements
 */
class Product_Description_Content extends WooCommerce\Elements\Abstract_Sub_Element {
	/**
	 * @return string
	 */
	public function name() {
		return __( 'Description Content', 'thrive-theme' );
	}

	/**
	 * @return string
	 */
	public function identifier() {
		return '#tab-description';
	}

	/**
	 * @return array
	 */
	public function own_components() {
		$components = parent::own_components();

		foreach ( $components['typography']['config'] as $control => $config ) {
			if ( is_array( $config ) ) {
				$components['typography']['config'][ $control ]['css_suffix'] = [ '', ' p' ];
			}
		}

		return $components;
	}
}

return new Product_Description_Content( 'wc-product-description-content' );
