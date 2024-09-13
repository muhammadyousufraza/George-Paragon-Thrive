<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

namespace TCB\Integrations\WooCommerce\Shortcodes\Product_Categories;

use TCB\Integrations\WooCommerce\Main as Main_Woo;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Main
 *
 * @package TCB\Integrations\WooCommerce\Shortcodes\Product_Categories
 */
class Main {

	const SHORTCODE = 'tcb_woo_product_categories';

	const IDENTIFIER = '.tcb-woo-product-categories';

	public static function init() {
		add_shortcode( static::SHORTCODE, [ __CLASS__, 'render' ] );

		require_once __DIR__ . '/class-hooks.php';

		Hooks::add();
	}

	/**
	 * @param array $attr
	 *
	 * @return string
	 */
	public static function render( $attr = [] ) {
		/* the woocommerce hooks are not initialized during REST / ajax requests, so we do it manually */
		if ( \TCB_Utils::is_rest() || wp_doing_ajax() ) {
			Main_Woo::init_frontend_woo_functionality();
		}

		$in_editor = is_editor_page_raw( true );

		static::before_render( $attr, $in_editor );

		$content = \WC_Shortcodes::product_categories( $attr );

		static::after_render( $attr, $in_editor );

		$classes = array( str_replace( '.', '', static::IDENTIFIER ), THRIVE_WRAPPER_CLASS );

		if ( $in_editor ) {
			$classes[] = 'tcb-selector-no_save tcb-child-selector-no_icons';
		} else {
			/* only keep a few attributes on the frontend */
			$attr = array_intersect_key( $attr, [
				'align-items'   => '',
				'text-layout'   => '',
				'text-position' => '',
				'css'           => '',
			] );
		}

		$data = [];

		foreach ( $attr as $key => $value ) {
			$data[ 'data-' . $key ] = esc_attr( $value );
		}

		return \TCB_Utils::wrap_content( $content, 'div', '', $classes, $data );
	}

	/**
	 * Update default attributes and prepare some filters and variables
	 *
	 * @param $attr
	 * @param $in_editor
	 */
	public static function before_render( &$attr, $in_editor ) {
		$attr = array_merge( [
			'limit'         => 4,
			'columns'       => 4,
			'hide_empty'    => 1,
			'orderby'       => 'name',
			'order'         => 'asc',
			'ids'           => '',
			'parent'        => 0,
			'align-items'   => 'center',
			'text-layout'   => 'text_on_image',
			'text-position' => 'center',
		], is_array( $attr ) ? $attr : [] );

		/*
		 * Fix for an earlier mistake, or maybe something that WooCommerce changed since then:
		 * The default value for 'parent' should be 0, not '', otherwise
		 * all the product categories are displayed, not only the top-level ones.
		 */
		if ( $attr['parent'] === '' ) {
			$attr['parent'] = 0;
		}

		if ( ! $in_editor && ! empty( $attr['hide-title'] ) ) {
			/* by removing this action we actually hide the title; the action is added back in after_render() */
			remove_action( 'woocommerce_shop_loop_subcategory_title', 'woocommerce_template_loop_category_title' );
		}

		/* add our custom text wrapper through woo actions */
		add_action( 'woocommerce_before_subcategory_title', [ __CLASS__, 'open_thrive_text_wrapper' ] );
		add_action( 'woocommerce_after_subcategory_title', [ __CLASS__, 'close_thrive_text_wrapper' ], 12 );

		/* remove the default product category count, ( we are adding a custom implementation through the next filter ) */
		add_filter( 'woocommerce_subcategory_count_html', [ __CLASS__, 'remove_default_product_count' ] );

		if ( $in_editor || empty( $attr['hide-product-number'] ) ) {
			add_action( 'woocommerce_after_subcategory_title', [ __CLASS__, 'add_thrive_product_count' ], 11 );
		}
	}

	/**
	 * @param $attr
	 * @param $in_editor
	 */
	public static function after_render( $attr, $in_editor ) {
		if ( ! $in_editor && ! empty( $attr['hide-title'] ) ) {
			/* re-add the action that we removed so we don't affect other product categories  */
			add_action( 'woocommerce_shop_loop_subcategory_title', 'woocommerce_template_loop_category_title' );
		}

		/* remove our custom text wrapper */
		remove_action( 'woocommerce_before_subcategory_title', [ __CLASS__, 'open_thrive_text_wrapper' ] );
		remove_action( 'woocommerce_after_subcategory_title', [ __CLASS__, 'close_thrive_text_wrapper' ], 12 );

		/* remove our 'product count removal' filter */
		remove_filter( 'woocommerce_subcategory_count_html', [ __CLASS__, 'remove_default_product_count' ] );

		if ( $in_editor || empty( $attr['hide-product-number'] ) ) {
			/* remove our custom product count implementation */
			remove_action( 'woocommerce_after_subcategory_title', [ __CLASS__, 'add_thrive_product_count' ], 11 );
		}
	}

	public static function open_thrive_text_wrapper() {
		echo '<div class="thrive-product-category-text-wrapper">';
	}

	public static function close_thrive_text_wrapper() {
		echo '</div>';
	}

	/**
	 * Remove the default product category count, so we can add our custom implementation.
	 *
	 * @param $product_count_html
	 *
	 * @return string
	 * @see add_thrive_product_count
	 *
	 */
	public static function remove_default_product_count( $product_count_html ) {
		return '';
	}

	/**
	 * Custom 'number of product categories' element
	 *
	 * @param $category
	 */
	public static function add_thrive_product_count( $category ) {
		if ( $category->count > 0 ) {
			echo \TCB_Utils::wrap_content( $category->count . ' products', 'p', '', 'thrive-product-category-count' ); //phpcs:ignore
		}

	}

	/**
	 * Get all the product categories and format them to be select-friendly
	 *
	 * @return array
	 */
	public static function get_product_categories_for_select() {
		$product_categories = apply_filters(
			'woocommerce_product_categories',
			get_terms( 'product_cat' )
		);

		$select_options = array_map( static function ( $item ) {
			return [
				'name'  => $item->name,
				'value' => $item->term_id,
			];
		}, $product_categories );

		return array_values( $select_options );
	}
}
