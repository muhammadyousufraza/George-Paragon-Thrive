<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

namespace Thrive\Theme\Integrations\WooCommerce\Shortcodes;

use Thrive\Theme\Integrations\WooCommerce;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Product_Template
 *
 * @package Thrive\Theme\Integrations\WooCommerce\Shortcodes
 */
class Product_Template {

	const SHORTCODE = 'thrive_product_template';

	const GALLERY_MIN_COLUMNS     = 2;
	const GALLERY_DEFAULT_COLUMNS = 4;
	const GALLERY_MAX_COLUMNS     = 8;

	/**
	 * Hooks used by WooCommerce for displaying various components on the product page
	 */
	const CONTENT_HOOKS
		= [
			'title'       => [
				'tag'      => 'woocommerce_single_product_summary',
				'callback' => 'woocommerce_template_single_title',
				'priority' => 5,
			],
			'price'       => [
				'tag'      => 'woocommerce_single_product_summary',
				'callback' => 'woocommerce_template_single_price',
				'priority' => 10,
			],
			'description' => [
				'tag'      => 'woocommerce_single_product_summary',
				'callback' => 'woocommerce_template_single_excerpt',
				'priority' => 20,
			],
			'button'      => [
				'tag'      => 'woocommerce_single_product_summary',
				'callback' => 'woocommerce_template_single_add_to_cart',
				'priority' => 30,
			],
			'meta'        => [
				'tag'      => 'woocommerce_single_product_summary',
				'callback' => 'woocommerce_template_single_meta',
				'priority' => 40,
			],
			'upsells'     => [
				'tag'      => 'woocommerce_after_single_product_summary',
				'callback' => 'woocommerce_upsell_display',
				'priority' => 15,
			],
			'related'     => [
				'tag'      => 'woocommerce_after_single_product_summary',
				'callback' => 'woocommerce_output_related_products',
				'priority' => 20,
			],
		];

	public static function init() {
		add_shortcode( static::SHORTCODE, [ __CLASS__, 'render' ] );
	}

	/**
	 * Render single product content
	 *
	 * @param $attr
	 *
	 * @return string
	 */
	public static function render( $attr = [] ) {
		$classes = [ 'product-template-wrapper', THRIVE_WRAPPER_CLASS ];

		$attr = array_merge( [
			'upsells-align-items' => 'left',
			'related-align-items' => 'left',
			'hide-magnifier'      => 0,
			'gallery-columns'     => static::GALLERY_DEFAULT_COLUMNS,
		], is_array( $attr ) ? $attr : [] );

		/* for less than 4 columns, the default thumbnail size is too small ( 100x100 ) and images look blurry, so we increase it to 300x300 */
		$override_gallery_thumbnail_size = static function ( $sizes ) use ( $attr ) {
			if ( $attr['gallery-columns'] < static::GALLERY_DEFAULT_COLUMNS ) {
				$sizes = [ 300, 300 ];
			}

			return $sizes;
		};

		add_filter( 'woocommerce_gallery_thumbnail_size', $override_gallery_thumbnail_size );

		$in_editor = \Thrive_Utils::is_inner_frame() || \Thrive_Utils::during_ajax();

		if ( ! $in_editor ) {
			/* when we're in editor we don't apply filters because we want all the elements and we will show/hide them with js */
			$GLOBALS[ static::SHORTCODE ] = $attr;

			foreach ( static::CONTENT_HOOKS as $key => $hook ) {
				if ( ! empty( $attr[ 'hide-' . $key ] ) ) {
					/* by removing this action we actually hide the element */
					remove_action( $hook['tag'], $hook['callback'], $hook['priority'] );
				}
			}

			if ( ! empty( $attr['hide-review'] ) ) {
				remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_rating', 10 );
			}
		}

		$products = get_posts( [
			'post_type' => WooCommerce\Main::POST_TYPE,
		] );

		/* when there are no products added, display a notice instead */
		if ( empty( $products ) ) {
			$content = \Thrive_Utils::return_part( '/integrations/woocommerce/views/no-products-placeholder.php' );
		} else {
			global $product;

			$content = $product ? WooCommerce\Helpers::get_template_content( WooCommerce\Main::SINGLE_PRODUCT_CONTENT ) : '';

			if ( $in_editor ) {
				$classes[] = 'tcb-selector-no_clone';
			} else {
				unset( $GLOBALS[ static::SHORTCODE ] );
				$attr = array_filter( $attr, static function ( $key ) {
					/* allowed attr on front */
					return in_array( $key, [ 'upsells-align-items', 'related-align-items', 'hide-magnifier', 'styled-scrollbar' ], true );
				}, ARRAY_FILTER_USE_KEY );
			}
		}

		remove_filter( 'woocommerce_gallery_thumbnail_size', $override_gallery_thumbnail_size );

		return \TCB_Utils::wrap_content( $content, 'div', '', $classes, \Thrive_Utils::create_attributes( $attr ) );
	}
}

return Product_Template::class;
