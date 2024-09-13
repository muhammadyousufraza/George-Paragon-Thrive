<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

namespace TCB\Integrations\WooCommerce;

use TCB\Lightspeed\Woocommerce;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Hooks
 *
 * @package TCB\Integrations\WooCommerce
 */
class Hooks {
	public static function add() {
		static::add_actions();
		static::add_filters();
	}

	public static function add_actions() {
		add_action( 'tcb_editor_iframe_after', [ __CLASS__, 'tcb_editor_iframe_after' ] );

		add_action( 'wp_enqueue_scripts', [ __CLASS__, 'enqueue_scripts' ], PHP_INT_MAX );

		add_action( 'wp_print_footer_scripts', [ __CLASS__, 'wp_print_footer_scripts' ], 9 );

		add_action( 'tcb_output_components', [ __CLASS__, 'tcb_output_components' ] );

		add_action( 'rest_api_init', [ __CLASS__, 'rest_api_init' ] );

		add_action( 'tcb_editor_enqueue_scripts', [ 'TCB\Integrations\WooCommerce\Main', 'enqueue_scripts' ] );

		add_action( 'tve_frontend_extra_scripts', [ 'TCB\Integrations\WooCommerce\Main', 'enqueue_scripts' ] );

		add_action( 'tve_lightspeed_enqueue_module_scripts', [ __CLASS__, 'check_woo_modules_to_enqueue' ], 10, 2 );
	}

	public static function add_filters() {
		add_filter( 'wp_list_categories', [ __CLASS__, 'wp_list_categories' ], 10, 2 );

		add_filter( 'tve_frontend_options_data', [ __CLASS__, 'tve_frontend_data' ] );

		add_filter( 'tcb_alter_cloud_template_meta', [ __CLASS__, 'tcb_alter_cloud_template_meta' ], 10, 3 );

		add_filter( 'woocommerce_enqueue_styles', [ __CLASS__, 'woocommerce_enqueue_styles' ] );

		add_filter( 'tcb_filter_rest_products', [ __CLASS__, 'tcb_filter_products' ], 10, 2 );

		add_filter( 'tcb_main_frame_localize', [ __CLASS__, 'localize_woo_modules' ], 10, 1 );

		add_filter( 'pre_get_posts', [ __CLASS__, 'pre_get_posts' ] );
	}

	/**
	 * Include WooCommerce icons for sidebar elements
	 */
	public static function tcb_editor_iframe_after() {
		include TVE_TCB_ROOT_PATH . 'inc/woocommerce/assets/icons.svg';
	}

	/**
	 * Enqueue scripts needed by WooCommerce
	 */
	public static function enqueue_scripts() {
		if ( \TCB\Lightspeed\Woocommerce::is_woocommerce_disabled() || \TCB\Lightspeed\Woocommerce::is_woocommerce_disabled( true ) ) /* Dequeue all woo scripts */ {
			/* Enqueue only when it's needed */
			if ( Main::needs_woo_enqueued() ) {
				wp_enqueue_script( 'selectWoo' );
				wp_enqueue_script( 'woocommerce' );
				wp_enqueue_script( 'wc-cart-fragments' );
				if ( Main::needs_woo_cart_enqueued() ) {
					if ( 'yes' === get_option( 'woocommerce_enable_ajax_add_to_cart' ) ) {
						wp_enqueue_script( 'wc-add-to-cart' );
					}
					wp_enqueue_script( 'wc-cart-fragments' );
				}
			} else {
				wp_dequeue_script( 'selectWoo' );
				wp_dequeue_script( 'woocommerce' );
				wp_dequeue_script( 'wc-cart-fragments' );
				wp_dequeue_script( 'wc-add-to-cart' );
			}

			if ( ! is_admin() ) {
				wp_enqueue_style( 'select2' );
			}
		} else {
			if ( ! is_admin() ) {
				wp_enqueue_script( 'selectWoo' );
				wp_enqueue_style( 'select2' );
			}
		}
	}

	/**
	 * Add some backbone templates for the editor.
	 */
	public static function wp_print_footer_scripts() {
		if ( TCB_Editor()->is_inner_frame() ) {
			$templates = tve_dash_get_backbone_templates( TVE_TCB_ROOT_PATH . 'inc/woocommerce/views/backbone' );

			tve_dash_output_backbone_templates( $templates, 'tve-woocommerce-' );
		}
	}

	/**
	 * Include WooCommerce editor components
	 */
	public static function tcb_output_components() {
		$path  = TVE_TCB_ROOT_PATH . 'inc/woocommerce/views/components/';
		$files = array_diff( scandir( $path ), [ '.', '..' ] );

		foreach ( $files as $file ) {
			include $path . $file;
		}
	}

	/**
	 * Initialize the rest api class
	 */
	public static function rest_api_init() {
		require_once TVE_TCB_ROOT_PATH . 'inc/woocommerce/classes/class-rest-api.php';

		Rest_Api::register_routes();
	}

	/**
	 * Remove parenthesis from category count
	 *
	 * @param String $output
	 * @param array  $args
	 *
	 * @return string|string[]
	 */
	public static function wp_list_categories( $output, $args ) {

		if ( ! empty( $args['walker'] ) && $args['walker'] instanceof \WC_Product_Cat_List_Walker ) {
			$output = preg_replace( '/(class=\"count\"[^>]*>)\D*(\d*)[^<]*/', '$1$2', $output );
		}

		return $output;
	}

	/**
	 * Add some data to the frontend localized object
	 *
	 * @param $data
	 *
	 * @return mixed
	 */
	public static function tve_frontend_data( $data ) {
		$data['woo_rest_routes'] = array(
			'shop'               => get_rest_url( get_current_blog_id(), 'tcb/v1/woo/render_shop' ),
			'product_categories' => get_rest_url( get_current_blog_id(), 'tcb/v1/woo/render_product_categories' ),
			'product_variations' => get_rest_url( get_current_blog_id(), 'tcb/v1/woo/variations' ),
		);

		return $data;
	}

	/**
	 * Modifies the template content for headers/footers
	 *
	 * @param array   $template_data
	 * @param array   $meta
	 * @param boolean $do_shortcode
	 *
	 * @return array
	 */
	public static function tcb_alter_cloud_template_meta( $template_data, $meta, $do_shortcode ) {
		if ( ! is_array( $template_data ) ) {
			$template_data = [];
		}

		if ( $do_shortcode && in_array( $template_data['type'], [ 'header', 'footer' ] ) && ! empty( $template_data['content'] ) ) {
			/* the main reason for calling this is to render woo widgets such as Product Search which rely on __CONFIG__s */
			$template_data['content'] = tve_thrive_shortcodes( $template_data['content'], is_editor_page_raw( true ) || \TCB_Utils::is_rest() );
		}

		return $template_data;
	}

	/**
	 * Don't load woocommerce if there are no elements used
	 *
	 * @param $styles
	 *
	 * @return mixed
	 */
	public static function woocommerce_enqueue_styles( $styles ) {
		/* Dequeue all woo scripts */
		if ( \TCB\Lightspeed\Woocommerce::is_woocommerce_disabled() || \TCB\Lightspeed\Woocommerce::is_woocommerce_disabled( true ) ) {
			foreach ( $styles as $style_key => $style_data ) {
				if ( ! Main::needs_woo_enqueued() ) {
					unset( $styles[ $style_key ] );
				}
			}
		}

		/* Deregister the woo blocks scripts */
		if ( ! Main::needs_woo_enqueued() ) {
			wp_deregister_style( 'wc-blocks-style' );
			wp_dequeue_style( 'wc-blocks-style' );

			wp_deregister_style( 'wc-blocks-vendors-style' );
			wp_dequeue_style( 'wc-blocks-vendors-style' );
		}

		return $styles;
	}

	/**
	 * Filter out undesired products based on the $extra argument
	 *
	 * @param $products
	 * @param $request
	 *
	 * @return array|mixed
	 */
	public static function tcb_filter_products( $products, $request ) {
		$extra = $request->get_param( 'extra' );

		if ( $extra === 'dynamic_add_to_cart' ) {
			$products = array_filter( $products, function ( $product ) {
				$full_product = wc_get_product( $product->ID );

				return ! $full_product->is_type( 'external' );
			} );
		}

		return $products;
	}

	/**
	 * Localize the woo modules if Woocommerce is active
	 *
	 * @param $data
	 *
	 * @return mixed
	 */
	public static function localize_woo_modules( $data ) {
		$data['lightspeed']['woo_modules'] = \TCB\Lightspeed\Woocommerce::get_woocommerce_assets( null, 'identifier' );

		return $data;
	}

	/**
	 * Check if posts has woo modules to include
	 *
	 * @param $post_id
	 * @param $modules
	 *
	 * @return void
	 */
	public static function check_woo_modules_to_enqueue( $post_id, $modules ) {
		$woo_modules = get_post_meta( $post_id, Woocommerce::WOO_MODULE_META_NAME, true );

		if ( ! empty( $woo_modules ) ) {
			add_filter( 'tcb_lightspeed_optimize_woo', '__return_true' );

			static::enqueue_scripts();

			Main::enqueue_scripts();

			foreach ( Woocommerce::get_woo_styles() as $handle => $src ) {
				$media = strpos( $handle, 'smallscreen' ) === false ? 'all' : 'only screen and (max-width: 768px)';

				wp_enqueue_style( $handle, $src, [], false, $media );
			}
		}
	}

	/**
	 * Do not display the hidden products when searching
	 *
	 * @param \WP_Query $query
	 */
	public static function pre_get_posts( $query ) {
		if ( $query->is_main_query() && $query->is_search() && isset( $_GET['tcb_sf_post_type'] ) && is_array( $_GET['tcb_sf_post_type'] ) && in_array( 'product', $_GET['tcb_sf_post_type'] ) ) {
			$tax_query = $query->get( 'tax_query', [] );

			$tax_query[] = [
				'relation' => 'OR',
				[
					'taxonomy' => 'product_visibility',
					'field'    => 'name',
					'terms'    => 'exclude-from-catalog',
					'operator' => 'NOT IN',
				],
				[
					'taxonomy' => 'product_visibility',
					'field'    => 'name',
					'terms'    => 'exclude-from-catalog',
					'operator' => '!=',
				],
			];

			$query->set( 'tax_query', $tax_query );
		}
	}
}
