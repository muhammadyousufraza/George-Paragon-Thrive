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
class Order_Details_Header extends WooCommerce\Elements\Abstract_Sub_Element {
	/**
	 * @return string
	 */
	public function name() {
		return __( 'Order Details', 'thrive-theme' );
	}

	/**
	 * @return string
	 */
	public function identifier() {
		return '#order_review thead th, #order_review  tfoot th';
	}

	/**
	 * @return array
	 */
	public function own_components() {
		$components = parent::own_components();

		return $components;
	}
}

return new Order_Details_Header( 'wc-order-details-header' );
