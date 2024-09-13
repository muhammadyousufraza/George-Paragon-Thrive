<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

namespace TCB\Integrations\WooCommerce\Shortcodes\Inline;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Helpers
 *
 * @package TCB\Integrations\WooCommerce\Shortcodes\Inline
 */
class Helpers {

	/**
	 * Fetch the sale/regular price for this product, format it and prepare it to be rendered
	 *
	 * @param string      $price_type - sale / regular
	 * @param \WC_Product $product
	 * @param array       $attr
	 *
	 * @return string
	 */
	public static function render_price( $price_type, $product, $attr ) {
		$add_currency  = ! empty( $attr[ Main::PRICE_INCLUDE_CURRENCY_SYMBOL ] );
		$show_decimals = ! empty( $attr[ Main::PRICE_SHOW_DECIMALS ] );

		if ( $product instanceof \WC_Product_Variable ) {
			$prices = $product->get_variation_prices();

			if ( empty( $prices["{$price_type}_price"] ) ) {
				$price = '';
			} else {
				$min_price = min( $prices["{$price_type}_price"] );
				$max_price = max( $prices["{$price_type}_price"] );

				if ( $min_price === $max_price ) {
					$price = static::prepare_price( $min_price, $add_currency, $show_decimals );
				} else {
					$price = static::prepare_price( $min_price, $add_currency, $show_decimals ) . ' – ' .
					         static::prepare_price( $max_price, $add_currency, $show_decimals );
				}
			}
		} else {
			$price = call_user_func( [ $product, "get_{$price_type}_price" ] );

			$price = static::prepare_price( $price, $add_currency, $show_decimals );
		}

		return $price;
	}

	/**
	 * Prepare price for display - add currency and maybe show decimals
	 *
	 * @param $price
	 * @param $add_currency
	 * @param $show_decimals
	 *
	 * @return string
	 */
	private static function prepare_price( $price, $add_currency = true, $show_decimals = true ) {
		$price = (float) $price;

		$price = static::get_formatted_price( $price, $show_decimals );

		if ( $add_currency ) {
			$price = static::add_currency_symbol( $price );
		}

		return $price;
	}

	/**
	 * Format the current price, respecting WooCommerce settings: add thousand separators, keep or remove decimals, etc
	 * The idea is to display the shortcodes just like WooCommerce displays the regular prices
	 *
	 * @param float $price
	 * @param bool  $show_decimals
	 *
	 * @return string
	 */
	public static function get_formatted_price( $price, $show_decimals = true ) {
		$decimals = $show_decimals ? wc_get_price_decimals() : 0;

		return number_format( $price, $decimals, wc_get_price_decimal_separator(), wc_get_price_thousand_separator() );
	}

	/**
	 * Add the currency symbol to the price while respecting the position from the WooCommerce settings.
	 *
	 * @param $price
	 *
	 * @return string
	 */
	private static function add_currency_symbol( $price ) {
		$prefix = '';
		$suffix = '';
		$symbol = static::get_currency_symbol();

		switch ( get_option( 'woocommerce_currency_pos' ) ) {
			case 'left_space':
				$prefix = $symbol . ' ';
				break;
			case 'left':
				$prefix = $symbol;
				break;
			case 'right_space':
				$suffix = ' ' . $symbol;
				break;
			case 'right':
				$suffix = $symbol;
				break;
			default:
				break;
		}

		return $prefix . $price . $suffix;
	}

	/**
	 * Wrapper for the WooCommerce currency symbol getter.
	 *
	 * @return string
	 */
	public static function get_currency_symbol() {
		return get_woocommerce_currency_symbol();
	}

	/**
	 * Currently available inline shortcodes
	 *
	 * @return array
	 */
	public static function available_shortcodes() {
		return array(
			'product_description' => array(
				'name'     => __( 'Product short description', 'thrive-cb' ),
				'controls' => [],
				'type'     => '',
			),
			'product_category'    => array(
				'name'     => __( 'Product category', 'thrive-cb' ),
				'controls' => [],
				'type'     => '',
			),
			'product_tags'        => array(
				'name'     => __( 'Product tags', 'thrive-cb' ),
				'controls' => [],
				'type'     => '',
			),
			'_sale_price'         => array(
				'name'     => __( 'Product sale price', 'thrive-cb' ),
				'controls' => static::get_control_config( [
					Main::PRICE_INCLUDE_CURRENCY_SYMBOL,
					Main::PRICE_SHOW_DECIMALS,
				] ),
				'type'     => 'price',
			),
			'_regular_price'      => array(
				'name'     => __( 'Product regular price', 'thrive-cb' ),
				'controls' => static::get_control_config( [
					Main::PRICE_ON_SALE_EFFECT,
					Main::PRICE_INCLUDE_CURRENCY_SYMBOL,
					Main::PRICE_SHOW_DECIMALS,
				] ),
				'type'     => 'price',
			),
			'_wc_average_rating'  => array(
				'name'     => __( 'Product average rating', 'thrive-cb' ),
				'controls' => [],
				'type'     => '',
			),
		);
	}

	/**
	 * Return the full shortcode config for the given shortcode keys.
	 *
	 * @param array $shortcode_keys
	 *
	 * @return array
	 */
	public static function get_control_config( $shortcode_keys ) {
		$config = [];

		foreach ( $shortcode_keys as $shortcode_key ) {
			switch ( $shortcode_key ) {
				case Main::PRICE_ON_SALE_EFFECT:
					$config[ $shortcode_key ] = array(
						'type'  => 'select',
						'label' => __( 'If product is on sale', 'thrive-cb' ),
						'value' => array(
							/* the first option is the default one */
							'strikethrough' => __( 'Strikethrough (e.g.  ̶3̶0̶.̶0̶0̶)', 'thrive-cb' ),
							'fade_n_strike' => __( 'Fade and strikethrough', 'thrive-cb' ),
							'fade'          => __( 'Fade', 'thrive-cb' ),
							'normal'        => __( 'Display as normal', 'thrive-cb' ),
						),
					);
					break;
				case Main::PRICE_INCLUDE_CURRENCY_SYMBOL:
					$config[ $shortcode_key ] = array(
						'type'       => 'checkbox',
						'label'      => __( 'Include currency symbol', 'thrive-cb' ),
						'value'      => true, /* must be checked by default */
						'disable_br' => true,
					);
					break;
				case Main::PRICE_SHOW_DECIMALS:
					$config[ $shortcode_key ] = array(
						'type'       => 'checkbox',
						'label'      => __( 'Show minor units (cents, pence etc.)', 'thrive-cb' ),
						'value'      => true, /* must be checked by default */
						'disable_br' => true,
					);
					break;
				default:
					break;
			}
		}

		return $config;
	}

	/**
	 * Remove the decimals from the price and return it ( but keep the thousand separators )
	 *
	 * @param $price
	 *
	 * @return mixed
	 */
	public static function get_price_without_decimals( $price ) {
		/* remove the thousand separator  1,345,543.56 --> 1345543.56 */
		$price = str_replace( wc_get_price_thousand_separator(), '', $price );

		/* cast to (int) in order to remove the decimals 1345543.56 --> 1345543 */
		$base_price = (int) $price;

		/* add the thousand separator back 1345543 --> 1,345,543 */
		$price = static::get_formatted_price( $base_price, false );

		return $price;
	}

	/**
	 * Dynamic links available in the editor
	 *
	 * @return array
	 */
	public static function get_dynamic_links() {
		return array(
			'WooCommerce' => array(
				'links'     => array(
					array(
						array(
							'name'  => __( 'Add to cart product', 'thrive-cb' ),
							'label' => __( 'Add to cart product', 'thrive-cb' ),
							'url'   => '',
							'show'  => true,
							'id'    => 'dynamic_product_link',
						),
						array(
							'name'  => __( 'Cart page', 'thrive-cb' ),
							'label' => __( 'Cart page', 'thrive-cb' ),
							'url'   => '',
							'show'  => true,
							'id'    => 'cart_url',
						),
						array(
							'name'  => __( 'Shop page', 'thrive-cb' ),
							'label' => __( 'Shop page', 'thrive-cb' ),
							'url'   => '',
							'show'  => true,
							'id'    => 'shop_url',
						),
						array(
							'name'  => __( 'Checkout page', 'thrive-cb' ),
							'label' => __( 'Checkout page', 'thrive-cb' ),
							'url'   => '',
							'show'  => true,
							'id'    => 'checkout_url',
						),
						array(
							'name'  => __( 'My account', 'thrive-cb' ),
							'label' => __( 'My account', 'thrive-cb' ),
							'url'   => '',
							'show'  => true,
							'id'    => 'my_account_url',
						),
						array(
							'name'  => __( 'Add to cart', 'thrive-cb' ),
							'label' => __( 'Add to cart', 'thrive-cb' ),
							'url'   => '',
							'show'  => true,
							'id'    => 'add_to_cart',
						),
					),
				),
				'shortcode' => Main::LINK_SHORTCODE,
			),
		);
	}
}
