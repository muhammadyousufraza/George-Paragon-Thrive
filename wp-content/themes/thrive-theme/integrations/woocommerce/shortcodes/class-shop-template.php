<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

namespace Thrive\Theme\Integrations\WooCommerce\Shortcodes;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Shop_Template
 *
 * @package Thrive\Theme\Integrations\WooCommerce\Shortcodes
 */
class Shop_Template {

	const DEFAULT_PRODUCTS_TO_DISPLAY = 8;

	const SHORTCODE = 'thrive_shop_template';

	/**
	 * Add WooCommerce shortcodes
	 */
	public static function init() {
		add_shortcode( static::SHORTCODE, [ __CLASS__, 'render' ] );
	}

	/**
	 * Render shop product list
	 *
	 * @param array $attr
	 *
	 * @return string
	 */
	public static function render( $attr = [] ) {

		$classes = [ 'shop-template-wrapper', THRIVE_WRAPPER_CLASS, 'thrive-shop' ]; /* 'thrive-shop' is the general shop class */

		static::before_render( $attr );

		$in_editor = \Thrive_Utils::is_inner_frame() || \Thrive_Utils::during_ajax();

		if ( ! $in_editor && class_exists( '\TCB\Integrations\WooCommerce\Shortcodes\Shop\Main', false ) ) {
			foreach ( \TCB\Integrations\WooCommerce\Shortcodes\Shop\Main::$shop_content_hooks as $key => $hook ) {
				if ( ! empty( $attr[ 'hide-' . $key ] ) ) {
					/* by removing this action we actually hide the element */
					remove_action( $hook['tag'], $hook['callback'], $hook['priority'] );
				}
			}
		}

		ob_start();
		woocommerce_content();
		$content = ob_get_clean();

		if ( $in_editor ) {
			$classes[] = 'tcb-selector-no_clone tcb-child-selector-no_icons';
		} else {
			$attr = array_filter( $attr, static function ( $key ) {
				return in_array( $key, [ 'align-items', 'styled-scrollbar' ] );
			}, ARRAY_FILTER_USE_KEY );
		}

		return \TCB_Utils::wrap_content( $content, 'div', '', $classes, \Thrive_Utils::create_attributes( $attr ) );
	}

	/**
	 * Update default attributes and prepare some filters and variables
	 *
	 * @param $attr
	 */
	public static function before_render( &$attr ) {

		$attr = array_merge( [
			'posts_per_page'        => static::DEFAULT_PRODUCTS_TO_DISPLAY,
			'columns'               => 4,
			'orderby'               => 'date',
			'order'                 => 'desc',
			'align-items'           => 'left',
			'hide-result-count'     => 0,
			'hide-catalog-ordering' => 0,
			'hide-sale-flash'       => 0,
			'hide-title'            => 0,
			'hide-price'            => 0,
			'hide-rating'           => 0,
			'hide-cart'             => 0,
			'hide-pagination'       => 0,
			'ct'                    => 'shop-0',
			'ct-name'               => esc_html__( 'Original Shop', 'thrive-theme' ),
		], is_array( $attr ) ? $attr : [] );

		$GLOBALS['woocommerce_loop']['columns'] = $attr['columns'];

		/* the default WooCommerce template displays also the title, but we don't need/want that */
		add_filter( 'woocommerce_show_page_title', '__return_false' );

		if ( \Thrive_Utils::during_ajax() ) {
			/* use this filter when we render the shop with ajax in the editor */
			add_filter( 'woocommerce_default_catalog_orderby', static function () use ( $attr ) {
				return $attr['orderby'] . '-' . $attr['order'];
			} );
		}
	}
}

return Shop_Template::class;
