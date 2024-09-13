<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

namespace TCB\Integrations\WooCommerce\Shortcodes\Inline;

use TCB\Integrations\WooCommerce\Main as Woo_Main;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Main
 *
 * @package TCB\Integrations\WooCommerce\Shortcodes\Inline
 */
class Main {
	const META_SHORTCODE = 'thrive_woo_meta_shortcode';
	const LINK_SHORTCODE = 'thrive_woo_link_shortcode';

	/* price shortcode attributes */
	const PRICE_ON_SALE_EFFECT          = 'on_sale_effect';
	const PRICE_INCLUDE_CURRENCY_SYMBOL = 'include_currency_symbol';
	const PRICE_SHOW_DECIMALS           = 'show_decimals';

	const PATH = 'classes/shortcodes/inline/';

	/**
	 *
	 */
	public static function init() {
		$shortcode_path = Woo_Main::get_integration_path( static::PATH );

		require_once $shortcode_path . 'class-helpers.php';
		require_once $shortcode_path . 'class-hooks.php';

		Hooks::add();

		add_shortcode( static::META_SHORTCODE, [ __CLASS__, 'render_meta_shortcode' ] );
		add_shortcode( static::LINK_SHORTCODE, [ __CLASS__, 'render_link_shortcode' ] );
	}

	/**
	 * Render inline shortcodes - stored into the meta of the current product
	 *
	 * @param $attr
	 *
	 * @return mixed|string
	 */
	public static function render_meta_shortcode( $attr ) {

		if ( empty( $attr['id'] ) ) {
			$content = '';
		} else {
			$content = static::do_shortcode( $attr['id'], $attr );
		}

		return $content;
	}

	/**
	 * Render our WooCommerce shortcodes
	 *
	 * @param string $shortcode_id
	 * @param array  $attr
	 *
	 * @return mixed|string
	 */
	public static function do_shortcode( $shortcode_id, $attr = [] ) {
		$content = '';

		if ( array_key_exists( $shortcode_id, Helpers::available_shortcodes() ) ) {
			/* always fetch the product based on the ID, otherwise the post list custom loop can mess this up */
			$product_id = get_the_ID();
			$product    = wc_get_product( $product_id );

			switch ( $shortcode_id ) {
				case '_sale_price':
					$content = Helpers::render_price( 'sale', $product, $attr );
					break;
				case '_regular_price':
					$content = Helpers::render_price( 'regular', $product, $attr );
					break;
				case '_wc_average_rating':
					$content = $product->get_average_rating();
					break;
				case 'product_description':
					$content = $product->get_short_description();
					break;
				case 'product_category':
					$content = get_the_term_list( $product_id, 'product_cat', null, ', ', null );
					break;
				case 'product_tags':
					$content = get_the_term_list( $product_id, 'product_tag', null, ', ', null );
					break;

				default:
					$content = '';
			}
		}

		return $content;
	}

	/**
	 * Render shortcodes for dynamic links
	 *
	 * @param $attr
	 *
	 * @return mixed|string
	 */
	public static function render_link_shortcode( $attr ) {
		$attr = shortcode_atts( [
			'id'                   => '',
			'product-id'           => '',
			'product-variation-id' => '',
			'redirect-destination' => '',
		], $attr );

		switch ( $attr['id'] ) {
			case 'cart_url':
				$link = wc_get_cart_url();
				break;
			case 'shop_url':
				$link = Woo_Main::get_shop_url();
				break;
			case 'checkout_url':
				$link = Woo_Main::get_checkout_url();
				break;
			case 'my_account_url':
				$link = wc_get_page_permalink( 'myaccount' );
				break;
			case 'add_to_cart':
				$link = wc_get_cart_url() . '?add-to-cart=' . get_the_ID();
				break;
			case 'dynamic_product_link':
				$product_id = $attr['product-id'];
				$link       = $attr['redirect-destination'] === 'cart' ? wc_get_cart_url() : Woo_Main::get_checkout_url();

				if ( $product_id ) {
					$variation_id = empty( $attr['product-variation-id'] ) ? 0 : $attr['product-variation-id'];
					$product      = wc_get_product( $product_id );
					$link         = add_query_arg( [
						'add-to-cart' => $variation_id ? $variation_id : $product_id,
					], $link );

					if ( $product && $product->get_type() === 'grouped' ) {
						$children = $product->get_children();
						foreach ( $children as $child ) {
							//the default quantity for each part of a grouped product is 1
							$link .= "&quantity[$child]=1";
						}
					}
				}
				break;
			default:
				$link = '#';
		}

		return $link;
	}

	/**
	 * @return array
	 */
	public static function get_localized_data() {
		return array(
			'currency_symbol'   => Helpers::get_currency_symbol(),
			'currency_position' => get_option( 'woocommerce_currency_pos' ),
			'meta_shortcode'    => static::META_SHORTCODE,
		);
	}
}
