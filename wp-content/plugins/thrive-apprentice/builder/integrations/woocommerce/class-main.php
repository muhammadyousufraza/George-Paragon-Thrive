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
 * Class Main
 *
 * @package Thrive\Theme\Integrations\WooCommerce
 */
class Main extends \TCB\Integrations\WooCommerce\Main {

	const KEY              = 'woocommerce';
	const INTEGRATION_PATH = THEME_PATH . '/integrations/woocommerce/';

	const SHOP_TEMPLATE     = 'shop';
	const CART_TEMPLATE     = 'cart';
	const CHECKOUT_TEMPLATE = 'checkout';
	const ACCOUNT_TEMPLATE  = 'account';
	const HEADER            = 'woo_header';
	const FOOTER            = 'woo_footer';

	const PAGE_TEMPLATES = [ Main::CART_TEMPLATE, Main::CHECKOUT_TEMPLATE, Main::ACCOUNT_TEMPLATE ];
	const ALL_TEMPLATES  = [ Main::CART_TEMPLATE, Main::CHECKOUT_TEMPLATE, Main::ACCOUNT_TEMPLATE, Main::POST_TYPE, Main::SHOP_TEMPLATE ];

	const TAG_TAXONOMY      = 'product_tag';
	const CATEGORY_TAXONOMY = 'product_cat';

	const GENERATED_TEMPLATES_OPTION = 'thrive_woocommerce_generated_templates';
	/**
	 * File from WooCommerce that has the product template functionality
	 */
	const SINGLE_PRODUCT_CONTENT = 'content-single-product.php';

	public static $elements   = [];
	public static $shortcodes = [];

	public static function init() {
		static::include_core();

		if ( static::active() ) {
			static::include_elements();

			Actions::add();
			Filters::add();
			Wizard::init();
		} else {
			require_once __DIR__ . '/class-filters.php';

			Filters::inactive();
		}
	}

	/**
	 * Include WooCommerce files
	 */
	public static function include_core() {
		require_once __DIR__ . '/class-helpers.php';
		require_once __DIR__ . '/class-filters.php';
		require_once __DIR__ . '/class-actions.php';
		require_once __DIR__ . '/class-wizard.php';
	}

	/**
	 * Include WooCommerce files for elements
	 */
	public static function include_elements() {
		require_once __DIR__ . '/class-abstract-sub-element.php';

		//check if we are inside the editor
		if (
			! empty( $_GET[ TVE_EDITOR_FLAG ] ) &&
			! \TCB_Utils::is_rest() &&
			! wp_doing_ajax() &&
			! \Thrive_Utils::is_inner_frame() &&
			! empty( $_GET['post'] )
		) {
			$thrive_template    = thrive_template( $_GET['post'] );
			$primary_template   = $thrive_template->get_primary();
			$secondary_template = $thrive_template->get_secondary();
			$theme_elements     = \Thrive_Architect_Utils::get_architect_theme_elements( static::INTEGRATION_PATH . 'elements' );

			$template_element_key = "$secondary_template-template";

			switch ( $secondary_template ) {
				case self::CART_TEMPLATE:
				case self::CHECKOUT_TEMPLATE:
					static::$elements = array_merge(
						[ $template_element_key => $theme_elements[ $template_element_key ] ],
						\Thrive_Architect_Utils::get_architect_theme_elements( static::INTEGRATION_PATH . "sub-elements/$secondary_template" )
					);
					break;
				case static::POST_TYPE:
				case static::TAG_TAXONOMY:
				case static::CATEGORY_TAXONOMY:
					/* the shop template is available on product, product tag and product category archives */
					if ( $primary_template === THRIVE_ARCHIVE_TEMPLATE ) {
						static::$elements = array_merge( [ 'shop-template' => $theme_elements['shop-template'] ],
							\Thrive_Architect_Utils::get_architect_theme_elements( static::INTEGRATION_PATH . 'sub-elements/widgets/filters' ) );
					} elseif ( $secondary_template === static::POST_TYPE ) {
						/* if we're not on an archive and the post type is product, then this is the Product Template and we can load stuff for it */
						static::$elements = array_merge(
							[ $template_element_key => $theme_elements[ $template_element_key ] ],
							\Thrive_Architect_Utils::get_architect_theme_elements( static::INTEGRATION_PATH . "sub-elements/$secondary_template" )
						);

						if ( Helpers::is_addons_plugin_active() ) {
							static::$elements = array_merge(
								static::$elements,
								\Thrive_Architect_Utils::get_architect_theme_elements( static::INTEGRATION_PATH . 'sub-elements/product-add-ons' )
							);
						}
					}
					break;
				case self::ACCOUNT_TEMPLATE:
					static::$elements = [ $template_element_key => $theme_elements[ $template_element_key ] ];
					break;
				default:
					break;
			}
		}

		$shortcodes_path = static::INTEGRATION_PATH . 'shortcodes/';
		$files           = array_diff( scandir( $shortcodes_path ), [ '.', '..' ] );

		foreach ( $files as $file ) {
			static::$shortcodes[] = include $shortcodes_path . $file;
		}
	}

	/**
	 * Check if we're on the admin edit page of the shop
	 *
	 * @return bool
	 */
	public static function is_admin_shop_page() {
		return parent::active() && wc_get_page_id( static::SHOP_TEMPLATE ) === get_the_ID();
	}

	/**
	 * Get data to localize in the TTB dashboard
	 *
	 * @return array
	 */
	public static function admin_localize() {
		$is_active = static::active();

		$data = [
			'key'           => static::KEY,
			'is_active'     => $is_active ? 1 : 0,
			'has_templates' => Helpers::has_woo_templates(),
		];

		/* localize the rest of the data only if woo is active */
		if ( $is_active ) {
			$data = array_merge( $data, [
				'label'      => static::get_label(),
				'shop_label' => esc_html__( 'Shop', 'thrive-theme' ),
				'templates'  => array_merge( static::PAGE_TEMPLATES, [ static::POST_TYPE, static::TAG_TAXONOMY, static::CATEGORY_TAXONOMY ] ),
				'shop_url'   => static::get_shop_url(),
			] );
		}

		return $data;
	}

	/**
	 * @return string
	 */
	public static function get_label() {
		return esc_html__( 'WooCommerce', 'thrive-theme' );
	}
}
