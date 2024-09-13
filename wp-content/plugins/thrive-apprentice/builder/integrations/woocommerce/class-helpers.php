<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

namespace Thrive\Theme\Integrations\WooCommerce;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Helpers
 *
 * @package Thrive\Theme\Integrations\WooCommerce
 */
class Helpers {

	/**
	 * WooCommerce label from the elements sidebar
	 *
	 * @return string|void
	 */
	public static function get_products_category_label() {
		return __( 'WooCommerce', 'thrive-theme' );
	}

	/**
	 * Return WooCommerce template content
	 *
	 * @param string $template
	 *
	 * @return string
	 */
	public static function get_template_content( $template = '' ) {
		$content = '';
		$file    = WC()->plugin_path() . '/templates/' . $template;

		if ( is_file( $file ) ) {
			ob_start();
			load_template( $file, false );
			$content = ob_get_clean();
		}

		return $content;
	}

	/**
	 * Check if we're on a WooCommerce template
	 *
	 * @param string $type
	 *
	 * @return bool
	 */
	public static function is_woo_template( $type = '' ) {
		return static::is_woo_product_template( $type ) || static::is_woo_page_template( $type );
	}

	/**
	 * Check if we're on a WooCommerce product template
	 *
	 * @param $type
	 *
	 * @return bool
	 */
	public static function is_woo_product_template( $type = '' ) {
		return ( empty( $type ) ? thrive_template()->get_secondary() : $type ) === Main::POST_TYPE;
	}

	/**
	 * Check if we're on a WooCommerce archive template
	 *
	 * @return bool
	 */
	public static function is_woo_archive_template() {
		$thrive_template = thrive_template();

		return $thrive_template->is_archive() && in_array( $thrive_template->get_secondary(), [ Main::TAG_TAXONOMY, Main::CATEGORY_TAXONOMY ] );
	}

	/**
	 * Check if we're on a WooCommerce page template ( cart, checkout, my account )
	 *
	 * @param $type
	 *
	 * @return bool
	 */
	public static function is_woo_page_template( $type = '' ) {
		if ( empty( $type ) ) {
			$type = thrive_template()->get_secondary();
		}

		return in_array( $type, Main::PAGE_TEMPLATES, true );
	}

	/**
	 * @param string $type
	 *
	 * @return bool
	 */
	public static function is_account_template( $type = '' ) {
		if ( empty( $type ) ) {
			$type = thrive_template()->get_secondary();
		}

		return $type === Main::ACCOUNT_TEMPLATE;
	}

	/**
	 * @param string $type
	 *
	 * @return bool
	 */
	public static function is_cart_template( $type = '' ) {
		if ( empty( $type ) ) {
			$type = thrive_template()->get_secondary();
		}

		return $type === Main::CART_TEMPLATE;
	}

	/**
	 * @param string $type
	 *
	 * @return bool
	 */
	public static function is_checkout_template( $type = '' ) {
		if ( empty( $type ) ) {
			$type = thrive_template()->get_secondary();
		}

		return $type === Main::CHECKOUT_TEMPLATE;
	}

	/**
	 * @param string $type
	 *
	 * @return bool
	 */
	public static function is_shop_template( $type = '' ) {
		return static::is_woo_product_template( $type ) && thrive_template()->get_primary() === THRIVE_ARCHIVE_TEMPLATE;
	}

	/**
	 * Get only the WooCommerce templates
	 *
	 * @return array
	 */
	public static function get_templates() {
		return array_filter( thrive_skin()->get_templates( 'object' ), static function ( $template ) {
			/* filter out the templates to get only the woo templates */
			$primary   = $template->primary_template;
			$secondary = $template->secondary_template;

			return
				( $primary === THRIVE_SINGULAR_TEMPLATE && in_array( $secondary, Main::ALL_TEMPLATES, true ) ) ||
				( $primary === THRIVE_ARCHIVE_TEMPLATE && $secondary === MAIN::POST_TYPE );
		} );
	}

	/**
	 * @param $page_type
	 *
	 * @return bool|int|mixed|void
	 */
	public static function get_page_id_for_type( $page_type ) {
		switch ( $page_type ) {
			case Main::ACCOUNT_TEMPLATE:
				$page_id = get_option( 'woocommerce_myaccount_page_id' );
				break;
			case Main::CART_TEMPLATE:
			case Main::CHECKOUT_TEMPLATE:
			case Main::SHOP_TEMPLATE:
				$page_id = get_option( "woocommerce_{$page_type}_page_id" );
				break;
			default:
				break;
		}

		return empty( $page_id ) ? 0 : $page_id;
	}

	/**
	 * Return if we have woo templates on the site
	 *
	 * @return bool
	 */
	public static function has_woo_templates() {
		return ! empty( thrive_skin()->get_meta( Main::GENERATED_TEMPLATES_OPTION ) );
	}

	/**
	 * Check if the addons plugin is active
	 * @return bool
	 */
	public static function is_addons_plugin_active() {
		return is_plugin_active( 'woocommerce-product-addons/woocommerce-product-addons.php' );
	}
}
