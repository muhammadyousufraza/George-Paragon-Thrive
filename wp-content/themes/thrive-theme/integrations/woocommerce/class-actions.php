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
 * Class Actions
 *
 * @package Thrive\Theme\Integrations\WooCommerce
 */
class Actions {

	public static function add() {

		add_action( 'wp_loaded', [ __CLASS__, 'init_shortcodes' ], 11 );

		add_action( 'rest_api_init', [ __CLASS__, 'rest_api_init' ] );

		add_action( 'wp_enqueue_scripts', [ __CLASS__, 'wp_enqueue_scripts' ] );

		add_action( 'thrive_theme_section_before_download', [ __CLASS__, 'init_shortcodes' ] );

		add_action( 'tcb_output_components', [ __CLASS__, 'tcb_output_components' ] );

		add_action( 'thrive_theme_template_copied_data', [ __CLASS__, 'thrive_theme_template_copied_data' ], 10, 2 );

		add_action( 'wp_print_footer_scripts', [ __CLASS__, 'wp_print_footer_scripts' ], 9 );

		add_action( 'wp', [ __CLASS__, 'wp' ] );

		/* this is set to priority 9 because the Woo template_redirect is at 10, and we want this to execute earlier */
		add_action( 'template_redirect', [ __CLASS__, 'template_redirect' ], 9 );

		add_action( 'tcb_before_get_content_template', [ __CLASS__, 'before_content_template' ], 10, 2 );

		add_action( 'woocommerce_before_quantity_input_field', static function () {
			echo '<button class="tve-woo-quantity-button" type="button" data-op="minus">-</button>';
		}, PHP_INT_MAX );

		add_action( 'woocommerce_after_quantity_input_field', static function () {
			echo '<button class="tve-woo-quantity-button" type="button" data-op="plus">+</button>';
		}, 1 );

		add_action( 'theme_before_render_blog_list', static function () {
			/* when rendering the shop list, just in case, make sure that it is ordered DESC by date */
			add_filter( 'woocommerce_get_catalog_ordering_args', [ Filters::class, 'set_desc_order' ] );
		} );

		add_action( 'theme_after_render_blog_list', static function () {
			/* remove the filter just after blog render, so we won't affect other functionality */
			remove_filter( 'woocommerce_get_catalog_ordering_args', [ Filters::class, 'set_desc_order' ] );
		} );
	}

	/**
	 * When default permalinks are enabled, Woo does a template_redirect on shop page ( from page_id=X to the post type archive )
	 * Since this breaks our template editor ( and wizard template preview ), we prevent it and replace the shop URL manually
	 *
	 * @see Filters::thrive_theme_template_url -> this is where we replace the URL
	 * @see wc_template_redirect -> the woo functionality we are preventing
	 */
	public static function template_redirect() {
		if ( get_option( 'permalink_structure' ) === '' && ( \Thrive_Utils::is_inner_frame() || \Thrive_Wizard::is_frontend() ) && Helpers::is_shop_template() ) {
			remove_action( 'template_redirect', 'wc_template_redirect', 10 );
		}
	}

	/**
	 * Just after the wp element has been set
	 */
	public static function wp() {
		if ( is_cart() ) {
			remove_action( 'woocommerce_cart_collaterals', 'woocommerce_cart_totals', 10 );
			/* change the layout of the cart a little bit by moving the cart totals just after the cart */
			add_action( 'woocommerce_before_cart_collaterals', 'woocommerce_cart_totals' );
		}
	}

	/**
	 * Initialize the rest api class
	 */
	public static function rest_api_init() {
		require_once __DIR__ . '/class-rest-api.php';

		Rest_Api::register_routes();
	}

	/**
	 * Load woocommerce styles only when the plugin is active
	 */
	public static function wp_enqueue_scripts() {
		if ( Main::needs_woo_enqueued() ) {
			wp_enqueue_style( 'thrive-theme-woocommerce', THEME_ASSETS_URL . '/woocommerce.css', [], THEME_VERSION );

			/* don't load this before 'tve_woo' because there are dependencies between the theme woo file and the TCB woo file */
			tve_dash_enqueue_script( 'theme-woo', THEME_ASSETS_URL . '/woo.min.js', [ 'theme-frontend', 'jquery', 'jquery-ui-resizable', 'tve_woo' ], THEME_VERSION, true );
		}
	}

	/**
	 * Add all shortcodes
	 *
	 * @see Shop_Template::init() for an implementation example
	 */
	public static function init_shortcodes() {
		foreach ( Main::$shortcodes as $shortcode ) {
			if ( method_exists( $shortcode, 'init' ) ) {
				$shortcode::init();
			}
		}
	}

	/**
	 * Include WooCommerce editor components
	 */
	public static function tcb_output_components() {
		if ( Helpers::is_woo_product_template() ||
		     Helpers::is_woo_archive_template() ||
		     Helpers::is_checkout_template() ||
		     Helpers::is_cart_template() ) {
			$path  = Main::INTEGRATION_PATH . 'views/components/';
			$files = array_diff( scandir( $path ), [ '.', '..' ] );

			foreach ( $files as $file ) {
				include $path . $file;
			}
		}
	}

	/**
	 * Add some backbone templates for the editor.
	 */
	public static function wp_print_footer_scripts() {

		if ( \Thrive_Utils::is_inner_frame() ) {
			/* the template locations are mutually exclusive ( we can't have products on the cart template ) */
			if ( Helpers::is_woo_product_template() ) {
				$templates = tve_dash_get_backbone_templates( Main::INTEGRATION_PATH . 'views/editor/product', 'product' );
			} elseif ( Helpers::is_woo_page_template() ) {
				$templates = tve_dash_get_backbone_templates( Main::INTEGRATION_PATH . 'views/editor/page', 'page' );
			}

			if ( ! empty( $templates ) ) {
				tve_dash_output_backbone_templates( $templates, 'tve-theme-' );
			}
		}
	}

	/**
	 * When we're creating a woo page template by copying from a non-woo template, replace the content section with the default one.
	 * The reason for doing this is that non-woo sections have a Post Content which contains the default Woo shortcode.
	 * We generally want to avoid rendering Woo shortcodes directly because we are using our own Woo shortcodes, which allow customizing the elements.
	 *
	 * @param \Thrive_Template $destination_template
	 * @param \Thrive_Template $source_template
	 */
	public static function thrive_theme_template_copied_data( $destination_template, $source_template ) {
		$secondary = $destination_template->get_secondary();

		if ( Helpers::is_woo_page_template( $secondary ) && $secondary !== $source_template->get_secondary() ) {
			$sections = $source_template->meta( 'sections' );

			$sections['content'] = [
				'id'      => 0,
				/* default_content() calls the 'thrive_theme_section_default_content' filter, where the default content part is handled separately for Woo */
				'content' => ( new \Thrive_Section( 0, [ 'type' => 'content' ], $destination_template->ID ) )->default_content( 'content' ),
			];

			$destination_template->set_meta( 'sections', $sections );
		}
	}

	/**
	 * Set query variables before rendering the cloud template
	 *
	 * @param \WP_Post $post
	 * @param array    $meta
	 */
	public static function before_content_template( $post, $meta ) {

		if ( isset( $_REQUEST['query_vars'] ) ) {
			$type = empty( $meta['type'] ) ? '' : explode( '-', $meta['type'] )[0];

			/* Check if we are downloading a woo template */
			if ( in_array( $type, Main::ALL_TEMPLATES, true ) ) {
				\Thrive_Utils::set_query_vars( $_REQUEST['query_vars'] );
			}
		}
	}
}
