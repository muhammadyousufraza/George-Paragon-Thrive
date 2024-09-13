<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

namespace TCB\Lightspeed;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

/**
 * Class Woocommerce
 *
 * @package TCB\Lightspeed
 */
class Woocommerce {

	const  DISABLE_WOOCOMMERCE = '_tve_disable_woocommerce';

	const  DISABLE_WOOCOMMERCE_LP = '_tve_disable_woocommerce_lp';

	const WOO_MODULE_META_NAME = '_tve_js_modules_woo';

	public static function get_woocommerce_assets( $module = '', $key = '' ) {
		$data = [
			'woo'      => [
				'identifier' => '.woocommerce, .products, .product, .tcb-woo-mini-cart, .thrive-shop, [data-block-name^="woocommerce"]',
			],
			'woo-cart' => [
				'identifier' => '.add_to_cart_button, .woocommerce-cart, .tcb-woo-mini-cart',
			],
		];
		if ( ! empty( $key ) ) {
			$data = array_map( static function ( $item ) use ( $key ) {
				return empty( $item[ $key ] ) ? [] : $item[ $key ];
			}, $data );
		}

		return empty( $module ) ? $data : $data[ $module ];
	}

	public static function is_woocommerce_disabled( $is_lp = false ) {
		return ! empty( get_option( $is_lp ? static::DISABLE_WOOCOMMERCE_LP : static::DISABLE_WOOCOMMERCE, 0 ) );
	}

	public static function get_woo_js_modules() {
		return [
			'tve_woo'        => tve_editor_js( '/woo' . \TCB_Utils::get_js_suffix() ),
			'selectWoo'      => static::get_modules_urls( 'assets/js/selectWoo/selectWoo.full.js' ),
			'woocommerce'    => static::get_modules_urls( 'assets/js/frontend/woocommerce.js' ),
			'cart-fragments' => static::get_modules_urls( 'assets/js/frontend/cart-fragments.js' ),
			'add-to-cart'    => static::get_modules_urls( 'assets/js/frontend/add-to-cart.js' ),
		];
	}

	public static function get_woo_styles() {
		return apply_filters( 'tcb_lightspeed_woo_scripts', [
			'woocommerce-layout'      => static::get_modules_urls( 'assets/css/woocommerce-layout.css?ver=5.7.1' ),
			'woocommerce-smallscreen' => static::get_modules_urls( 'assets/css/woocommerce-smallscreen.css?ver=5.7.1' ),
			'woocommerce-general'     => static::get_modules_urls( 'assets/css/woocommerce.css?ver=5.7.1' ),
		] );
	}

	public static function get_modules_urls( $path ) {
		return plugins_url( $path, WC_PLUGIN_FILE );
	}

	public static function get_modules( $post_id, $key = '' ) {
		return get_post_meta( $post_id, static::WOO_MODULE_META_NAME . $key, true );
	}

}
