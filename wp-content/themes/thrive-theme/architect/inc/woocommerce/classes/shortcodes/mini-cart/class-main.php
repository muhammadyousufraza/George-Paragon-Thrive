<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

namespace TCB\Integrations\WooCommerce\Shortcodes\MiniCart;

use TCB\Integrations\WooCommerce\Main as Main_Woo;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Main
 *
 * @package TCB\Integrations\WooCommerce\Shortcodes\MiniCart
 */
class Main {

	const SHORTCODE = 'tcb_woo_mini_cart';

	public static function init() {
		add_shortcode( static::SHORTCODE, [ __CLASS__, 'render' ] );

		require_once __DIR__ . '/class-hooks.php';

		Hooks::add();
	}

	/**
	 * Render mini cart
	 *
	 * @param array  $attr
	 * @param string $content
	 *
	 * @return string
	 */
	public static function render( $attr = [], $content = '' ) {

		/* the woocommerce hooks and the cart functionality are not initialized during REST / ajax requests, so we do it manually */
		if ( \TCB_Utils::is_rest() || wp_doing_ajax() ) {
			Main_Woo::init_frontend_woo_functionality();
		}

		$cart = wc()->cart;

		if ( empty( $cart ) ) {
			return '';
		}

		if ( ! is_array( $attr ) ) {
			$attr = [];
		}

		$attr = array_map( static function ( $v ) {
			return str_replace( [ '|{|', '|}|' ], [ '[', ']' ], esc_attr( $v ) );
		}, $attr );

		/* ensure default values */
		$attr = array_merge( [
			'data-type'          => 'icon',
			'data-align'         => 'left',
			'data-trigger'       => 'click',
			'data-direction'     => 'under',
			'data-text'          => 'Cart',
			'data-icon-position' => 'left',
		], $attr );

		$id = empty( $attr['data-id'] ) ? '' : $attr['data-id'];
		unset( $attr['data-id'] );

		$classes = [ 'tcb-woo-mini-cart', THRIVE_WRAPPER_CLASS ];

		if ( ! empty( $attr['data-class'] ) ) {
			$classes = array_merge( $classes, explode( ' ', $attr['data-class'] ) );
		}

		$in_editor = is_editor_page_raw( true );

		if ( $in_editor ) {
			$attr['data-shortcode'] = static::SHORTCODE;
		}

		$cart_content = $cart->get_cart();

		/* when in editor and we don't have any products */
		$generate_dummy_cart = $in_editor && empty( $cart_content );

		if ( $generate_dummy_cart ) {
			static::generate_dummy_cart();
		}

		if ( empty( $content ) ) {
			$content = \TCB_Utils::return_part( Main_Woo::get_integration_path( 'assets/mini-cart.svg' ) );
		}

		$content .= static::get_cart_items_count( $in_editor );

		$content = \TCB_Utils::wrap_content( $content, 'div', '', 'tcb-woo-mini-cart-icon' );

		$content .=
			static::get_cart_price_amount() .
			static::get_cart_text( $attr['data-text'] ) .
			static::get_cart_items( $in_editor );

		if ( $generate_dummy_cart ) {
			WC()->cart->empty_cart();
		}

		return \TCB_Utils::wrap_content( $content, 'div', $id, $classes, $attr );
	}

	/**
	 * Render items from the cart using WooCommerce function
	 *
	 * @param bool $in_editor
	 *
	 * @return string
	 */
	public static function get_cart_items( $in_editor = false ) {
		ob_start();
		woocommerce_mini_cart();
		$content = ob_get_clean();

		if ( $in_editor ) {
			$classes = [ 'tcb-woo-mini-cart-items' ];
		} else {
			/* so it won't be synchronized very fast */
			$classes = [ 'widget_shopping_cart_content' ];
		}

		return \TCB_Utils::wrap_content( $content, 'div', '', $classes );
	}

	/**
	 * Return the items that are in the cart at the moment
	 *
	 * @param boolean $in_editor
	 *
	 * @return string
	 */
	public static function get_cart_items_count( $in_editor ) {
		$count = 0;
		$class = [ 'tcb-woo-mini-cart-count' ];

		if ( $in_editor ) {
			$class[] = 'tcb-selector-not_editable';
			$class[] = 'tcb-selector-no_highlight';
		}

		if ( WC()->cart ) {
			$count = WC()->cart->get_cart_contents_count();
		}

		if ( empty( $count ) ) {
			/* don't show zero products ? */
			$count = '';
		}

		return \TCB_Utils::wrap_content( $count, 'div', '', $class );
	}

	/**
	 * Get the total price amount for the products in the cart
	 *
	 * @return string
	 */
	public static function get_cart_price_amount() {
		if ( WC()->cart ) {
			$amount = strip_tags( WC()->cart->get_cart_subtotal() );
		} else {
			$amount = wc_price( 0 );
		}

		return \TCB_Utils::wrap_content( $amount, 'div', '', 'tcb-woo-mini-cart-amount' );
	}

	/**
	 * Just wrap cart text
	 *
	 * @param $text
	 *
	 * @return string
	 */
	public static function get_cart_text( $text ) {
		return \TCB_Utils::wrap_content( $text, 'div', '', 'tcb-woo-mini-cart-text' );
	}

	/**
	 * In some situations we want to style the cart/checkout and see how it looks even if it's empty.
	 * Most of the time, we only want to do this in the editor.
	 * Make sure you empty the cart afterwards!
	 *
	 * @param $products_per_page
	 */
	public static function generate_dummy_cart( $products_per_page = 4 ) {
		$dummy_products = get_posts( [
			'posts_per_page' => $products_per_page,
			'post_type'      => 'product',
			'orderby'        => 'rand',
		] );

		add_filter( 'woocommerce_is_purchasable', '__return_true' );

		foreach ( $dummy_products as $product ) {
			try {
				WC()->cart->add_to_cart( $product->ID );
			} catch ( \Exception $e ) {
				if ( $e->getMessage() ) {
					wc_add_notice( $e->getMessage(), 'error' );
				}
				WC()->cart->empty_cart();
			}
		}

		remove_filter( 'woocommerce_is_purchasable', '__return_true' );
	}
}
