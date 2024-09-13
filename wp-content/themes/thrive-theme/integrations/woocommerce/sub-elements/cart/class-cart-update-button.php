<?php

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

namespace Thrive\Theme\Integrations\WooCommerce\Elements;

use TCB\Integrations\WooCommerce\Shortcodes\Shop\Main;
use Thrive\Theme\Integrations\WooCommerce;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Cart_Update_Button
 * @package Thrive\Theme\Integrations\WooCommerce\Elements
 */
class Cart_Update_Button extends Cart_Apply_Coupon {
	/**
	 * @return string
	 */
	public function identifier() {
		return '.actions > button';
	}
}

return new Cart_Update_Button( 'wc-cart-update-button' );
