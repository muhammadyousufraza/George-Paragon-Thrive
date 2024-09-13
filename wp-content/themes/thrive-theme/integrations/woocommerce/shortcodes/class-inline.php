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
 * Class Inline
 * @package Thrive\Theme\Integrations\WooCommerce\Shortcodes
 */
class Inline {

	const INLINE_SHORTCODES = [
		'thrive_woocommerce_shop_title',
		'thrive_woocommerce_shop_url',
		'thrive_woocommerce_shop_id',
	];

	public static function init() {
		foreach ( static::INLINE_SHORTCODES as $shortcode ) {
			add_shortcode( $shortcode, [ __CLASS__, 'render_' . $shortcode ] );
		}
	}

	/**
	 * Render shop title
	 * @return string
	 */
	public static function render_thrive_woocommerce_shop_title() {
		return get_the_title( wc_get_page_id( 'shop' ) );
	}

	/**
	 * Render shop title
	 * @return string
	 */
	public static function render_thrive_woocommerce_shop_url() {
		return get_permalink( wc_get_page_id( 'shop' ) );
	}

	/**
	 * Render shop title
	 * @return string
	 */
	public static function render_thrive_woocommerce_shop_id() {
		return wc_get_page_id( 'shop' );
	}
}

return Inline::class;
