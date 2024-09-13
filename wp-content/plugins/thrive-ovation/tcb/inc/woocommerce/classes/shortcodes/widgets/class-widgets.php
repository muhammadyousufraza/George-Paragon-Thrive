<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

namespace TCB\Integrations\WooCommerce;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Widgets
 *
 * @package TCB\Integrations\WooCommerce
 */
class Widgets {

	public static function init() {
		static::add_actions();
		static::add_filters();
	}

	public static function add_actions() {
		add_action( 'tcb_ajax_before_widget_render', [ __CLASS__, 'tcb_ajax_before_widget_render' ] );

		add_action( 'tcb_before_widget_render', [ __CLASS__, 'tcb_before_widget_render' ] );
	}

	public static function add_filters() {
		add_filter( 'tve_include_widgets_in_editor', '__return_true' );

		add_filter( 'tcb_editor_widgets', [ __CLASS__, 'tcb_editor_widgets' ], 10 );

		add_filter( 'tcb_widget_element_category', [ __CLASS__, 'tcb_widget_element_category' ], 10, 2 );

		add_filter( 'tcb_widget_element_icon', [ __CLASS__, 'tcb_widget_element_icon' ], 10, 2 );

		add_filter( 'tcb_widget_data_widget_woocommerce_widget_cart', [ __CLASS__, 'cart_widget_data' ] );

		add_filter( 'sidebars_widgets', [ __CLASS__, 'sidebars_widgets' ] );

		add_filter( 'tcb_element_instances', [ __CLASS__, 'tcb_element_instances' ] );
	}

	/**
	 * Filter allowed widgets in the editor
	 *
	 * @param $widgets
	 *
	 * @return mixed
	 */
	public static function tcb_editor_widgets( $widgets ) {

		$widgets = array_filter( $widgets, static function ( $widget ) {
			/* @var \WP_Widget $widget */
			return strpos( $widget->id_base, 'woocommerce' ) !== false && strpos( $widget->id_base, 'filter' ) === false;
		} );

		return $widgets;
	}

	/**
	 * Group WooCommerce widgets in one category
	 *
	 * @param string     $category
	 * @param \WP_Widget $widget
	 *
	 * @return string
	 */
	public static function tcb_widget_element_category( $category, $widget ) {

		if ( strpos( $widget->id_base, 'woocommerce' ) !== false ) {
			$category = 'WooCommerce';
		}

		return $category;
	}

	/**
	 * Custom icons for WooCommerce icons
	 *
	 * @param string     $icon
	 * @param \WP_Widget $widget
	 *
	 * @return string
	 */
	public static function tcb_widget_element_icon( $icon, $widget ) {

		if ( strpos( $widget->id_base, 'woocommerce' ) !== false ) {
			$icon = (string) $widget->id_base;
		}

		return $icon;
	}

	/**
	 * If the cart is empty, temporarily add a few products to the cart so we can show some content in the editor
	 *
	 * @param array $data
	 *
	 * @return array
	 */
	public static function cart_widget_data( $data ) {
		$woo_cart = wc()->cart->get_cart();

		if ( empty( $woo_cart ) ) {
			$dummy_products = get_posts( [
				'posts_per_page' => 3,
				'post_type'      => 'product',
				'orderby'        => 'rand',
			] );

			foreach ( $dummy_products as $product ) {
				try {
					WC()->cart->add_to_cart( $product->ID );
				} catch ( \Exception $e ) {
					if ( $e->getMessage() ) {
						wc_add_notice( $e->getMessage(), 'error' );
					}
				}
			}

			ob_start();
			woocommerce_mini_cart();
			$mini_cart = ob_get_clean();

			/* replace the empty wrapper with the populated equivalent */
			$data['content'] = str_replace(
				'<div class="widget_shopping_cart_content"></div>',
				'<div class="widget_shopping_cart_content">' . $mini_cart . '</div>',
				$data['content'] );

			$data['cart_is_empty'] = empty( $woo_cart );

			/* empty the cart to remove the products that we just added */
			WC()->cart->empty_cart();
		}

		return $data;
	}

	/**
	 * Set the query as being the shop when trying to render widgets
	 * This action has a dynamic name - @see \TCB_Editor_Ajax::handle()
	 */
	public static function tcb_ajax_before_widget_render() {
		if ( isset( $_GET['widget'] ) && strpos( sanitize_text_field( $_GET['widget'] ), 'woocommerce', true ) !== false && ! empty( $_GET['query'] ) ) {

			/** @var \WP_Query */
			global $wp_query;

			$query_args = map_deep( $_GET['query'], 'sanitize_text_field' );

			if ( ! empty( $query_args['p'] ) ) {
				/* manually set the post type inside the query, otherwise the 'post' post-type gets queried */
				$query_args['post_type'] = get_post_type( $query_args['p'] );
			}

			/* set the global query as the same one that we're editing so we can convert shortcodes better */
			$wp_query->query( $query_args );

			if ( $wp_query->is_singular() ) {
				/* product page */
				$wp_query->the_post();
				wc_setup_product_data( get_the_ID() );
			} else {
				/* shop page */
				\WooCommerce::instance()->query->product_query( $wp_query );
			}
		}
	}

	/**
	 * Filter for sidebar widgets. On product pages we always want to track recent viewed because widgets can be used outside of the sidebar.
	 *
	 * @param array $sidebars_widgets
	 *
	 * @return array
	 */
	public static function sidebars_widgets( $sidebars_widgets ) {

		if ( ! is_admin() && is_product() ) {
			$sidebars_widgets['dummy-widgets'] = [ 'woocommerce_recently_viewed_products' ];
		}

		return $sidebars_widgets;
	}

	/**
	 * Flush the widget cache before rendering certain widgets
	 * For instance, when two different Product Widgets are added, if we don't flush the cache after rendering the first widget, then the second widget copies the content of the first one
	 *
	 * @param \WC_Widget $widget
	 *
	 * @see \WC_Widget::cache_widget()
	 */
	public static function tcb_before_widget_render( $widget ) {
		if ( in_array( $widget->option_name, static::get_cached_widget_keys() ) ) {
			$widget->flush_widget_cache();
		}
	}

	/**
	 * The content of these widgets is being cached, and we want to prevent this because it can duplicate different widgets
	 *
	 * @return string[]
	 */
	public static function get_cached_widget_keys() {
		return [
			'widget_woocommerce_products',
			'widget_woocommerce_top_rated_products',
			'widget_woocommerce_recent_reviews',
		];
	}

	/**
	 * @param $instances
	 *
	 * @return mixed
	 */
	public static function tcb_element_instances( $instances ) {
		require_once __DIR__ . '/class-abstract-sub-element.php';

		$widgets = array_diff( scandir( __DIR__ . '/sub-elements' ), [ '.', '..' ] );

		foreach ( $widgets as $widget ) {
			$files = array_diff( scandir( __DIR__ . '/sub-elements/' . $widget ), [ '.', '..' ] );
			foreach ( $files as $file ) {
				$instance                      = require_once __DIR__ . '/sub-elements/' . $widget . '/' . $file;
				$instances[ $instance->tag() ] = $instance;
			}
		}

		return $instances;
	}
}
