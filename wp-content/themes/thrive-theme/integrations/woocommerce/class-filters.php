<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

namespace Thrive\Theme\Integrations\WooCommerce;

use TCB\Lightspeed\Woocommerce;
use Thrive\Theme\Integrations\WooCommerce\Shortcodes\Checkout_Template;
use Thrive\Theme\Integrations\WooCommerce\Shortcodes\Shop_Template;
use WP_Widget;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Filters
 *
 * @package Thrive\Theme\Integrations\WooCommerce
 */
class Filters {

	/**
	 * Filters called when woocommerce is inactive
	 */
	public static function inactive() {

		add_filter( 'thrive_theme_cloud_templates', [ __CLASS__, 'filter_woo_templates' ] );
		add_filter( 'thrive_theme_templates_localize', [ __CLASS__, 'filter_woo_templates' ] );

		add_filter( 'tcb_lazy_load_data', [ __CLASS__, 'filter_lazy_load_data' ], 100, 2 );

		/** Do not show product and shop sections when WooCommerce is inactive */
		add_filter( 'thrive_theme_cloud_sections', static function ( $sections ) {
			return array_filter( $sections, static function ( $section ) {
				$is_woo_section = ! empty( $section['is_woo'] ) && $section['is_woo'] === '1';

				return ! ( $is_woo_section && ( ! empty( $section['template_type'] ) || $section['type'] === 'sidebar' ) );
			} );
		} );

		/* Do not show woo h&f in wizard when WooCommerce is inactive */
		add_filter( 'thrive_theme_wizard_templates', static function ( $templates, $request ) {
			$type = $request->get_param( 'type' );

			if ( $type === Main::HEADER || $type === Main::FOOTER ) {
				$templates = array_filter( $templates, static function ( $template ) {
					return isset( $template['is_woo'] ) && ! $template['is_woo'];
				} );
			}

			return $templates;
		}, 10, 2 );


		/* Do not show woo h&f in template when WooCommerce is inactive */
		add_filter( 'thrive_theme_cloud_hf_templates', static function ( $sections ) {
			return array_filter( $sections, static function ( $section ) {
				return ! $section['is_woo'];
			} );
		} );
	}

	public static function add() {
		add_filter( 'tve_frontend_options_data', [ __CLASS__, 'tve_frontend_data' ] );

		add_filter( 'pre_get_posts', [ __CLASS__, 'pre_get_posts' ] );

		add_filter( 'woocommerce_default_catalog_orderby', [ __CLASS__, 'woocommerce_default_catalog_orderby' ] );

		add_filter( 'thrive_theme_template_url', [ __CLASS__, 'thrive_theme_template_url' ], 10, 5 );

		add_filter( 'thrive_theme_breadcrumbs_labels', [ __CLASS__, 'breadcrumbs_labels' ] );

		add_filter( 'thrive_theme_section_default_content', [ __CLASS__, 'thrive_theme_section_default_content' ], 10, 3 );

		add_filter( 'tcb_element_instances', [ __CLASS__, 'tcb_element_instances' ] );

		add_filter( 'tcb_categories_order', [ __CLASS__, 'tcb_categories_order' ] );

		add_filter( 'woocommerce_product_tabs', [ __CLASS__, 'woocommerce_product_tabs' ] );
		add_filter( 'woocommerce_product_thumbnails_columns', [ __CLASS__, 'woocommerce_product_thumbnails_columns' ] );

		add_filter( 'woocommerce_output_related_products_args', static function ( $args ) {
			return static::filter_products_section_args( $args, 'related' );
		} );
		add_filter( 'woocommerce_upsell_display_args', static function ( $args ) {
			return static::filter_products_section_args( $args, 'upsells' );
		} );

		add_filter( 'woocommerce_cross_sells_columns', static function () {
			//TODO: add component on cart element to control number of columns
			return 4;
		} );

		add_filter( 'thrive_theme_breadcrumbs_root_items', [ __CLASS__, 'breadcrumbs_root_items' ], 10, 2 );

		add_filter( 'thrive_theme_allowed_taxonomies_in_breadcrumbs', [ __CLASS__, 'allowed_taxonomies_in_breadcrumbs' ], 10, 2 );

		add_filter( 'thrive_theme_get_posts_args', [ __CLASS__, 'thrive_theme_get_posts_args' ] );

		add_filter( 'thrive_theme_content_types', [ __CLASS__, 'add_woo_content_types_for_localize' ], 10, 2 );

		add_filter( 'thrive_theme_template_meta', [ __CLASS__, 'modify_template_meta' ] );

		add_filter( 'thrive_template_default_values', [ __CLASS__, 'modify_template_default_values' ] );

		add_filter( 'thrive_theme_default_templates', [ __CLASS__, 'default_templates' ], 10, 3 );

		remove_filter( 'tcb_editor_widgets', [ 'TCB\Integrations\WooCommerce\Widgets', 'tcb_editor_widgets' ] );

		add_filter( 'tcb_editor_widgets', [ __CLASS__, 'tcb_editor_widgets' ] );

		add_filter( 'thrive_theme_cloud_sections', [ __CLASS__, 'cloud_sections' ] );

		add_filter( 'tcb_editor_javascript_params', [ __CLASS__, 'tcb_editor_javascript_params' ] );

		add_filter( 'tcm_active', [ __CLASS__, 'tc_active' ] );
		add_filter( 'tcm_allow_comments_editor', [ __CLASS__, 'tc_active' ] );

		add_filter( 'woocommerce_product_description_heading', '__return_empty_string' );

		add_filter( 'woocommerce_product_additional_information_heading', '__return_empty_string' );

		add_filter( 'woocommerce_checkout_redirect_empty_cart', [ __CLASS__, 'allow_checkout_redirect' ] );

		add_filter( 'tcb_woo_shop_identifier', [ __CLASS__, 'shop_element_identifier' ], 10, 2 );

		add_filter( 'tcb_woo_shop_hide_element', [ __CLASS__, 'hide_architect_shop_element' ] );

		add_filter( 'woocommerce_get_script_data', [ __CLASS__, 'change_woo_script_data' ], 10, 2 );

		add_filter( 'thrive_theme_query_vars', [ __CLASS__, 'query_vars' ] );

		add_filter( 'tcb_backbone_templates', [ __CLASS__, 'tcb_backbone_templates' ] );

		add_filter( 'thrive_theme_cloud_hf_templates', [ __CLASS__, 'sort_header_footer_templates' ] );

		add_filter( 'thrive_theme_show_reading_progress', [ __CLASS__, 'show_reading_progress' ] );

		add_filter( 'woocommerce_checkout_fields', [ __CLASS__, 'alter_billing_fields' ] );

		add_filter( 'woocommerce_get_country_locale', [ __CLASS__, 'alter_wc_country_locale' ] );

		add_filter( 'woocommerce_default_address_fields', [ __CLASS__, 'change_default_address_fields_priorities' ] );

		//make sure this has a big priority so it is done as late as possible
		add_filter( 'woocommerce_enable_order_notes_field', [ __CLASS__, 'should_display_order_notes' ], 100 );

		add_filter( 'thrive_theme_should_filter_blog_posts', [ __CLASS__, 'should_filter_blog_posts' ] );

		add_filter( 'tcb_lightspeed_optimize_woo', [ __CLASS__, 'needs_woocommerce_enqueued' ] );

		add_filter( 'tcb_lightspeed_woo_scripts', [ __CLASS__, 'get_woo_ttb_scripts' ] );
	}

	/**
	 * Change number of posts for the shop
	 *
	 * @param \WP_Query $query
	 */
	public static function pre_get_posts( $query ) {
		if ( ! ( \Thrive_Utils::during_ajax() || is_admin() ) && $query->is_main_query() && ( is_post_type_archive( 'product' ) || is_product_taxonomy() ) ) {
			$posts_per_page = thrive_template()->get_meta_from_sections( 'posts_per_page' );

			if ( empty( $posts_per_page ) ) {
				/* by default, we always display 8 products */
				$posts_per_page = Shop_Template::DEFAULT_PRODUCTS_TO_DISPLAY;
			}

			$query->set( 'posts_per_page', $posts_per_page );
		}
	}

	/**
	 * Overwrite default order of products in the shop
	 *
	 * @param $order_by_value
	 *
	 * @return mixed
	 */
	public static function woocommerce_default_catalog_orderby( $order_by_value ) {

		if ( ! \Thrive_Utils::during_ajax() ) {
			$ordering = thrive_template()->get_meta_from_sections( 'ordering' );
			if ( ! empty( $ordering ) ) {
				$order_by_value = $ordering;
			}
		}

		return $order_by_value;
	}

	/**
	 * Display only WooCommerce widgets and if we're editing a shop page, show also filters
	 * Don't display the Cart widget on Cart and Checkout templates ( because of woocommerce constraints )
	 *
	 * @param $widgets
	 *
	 * @return mixed
	 */
	public static function tcb_editor_widgets( $widgets ) {

		$is_shop = thrive_template()->get_primary() === THRIVE_ARCHIVE_TEMPLATE;

		$widgets = array_filter( $widgets, static function ( $widget ) use ( $is_shop ) {
			$include = false;

			/* @var WP_Widget $widget */
			$is_woo_widget = strpos( $widget->id_base, 'woocommerce' ) !== false;

			if ( $is_woo_widget ) {
				$is_filter_widget = strpos( $widget->id_base, 'woocommerce_layered_nav' ) !== false || strpos( $widget->id_base, 'filter' ) !== false;
				$is_cart_widget   = strpos( $widget->id_base, 'woocommerce_widget_cart' ) !== false;

				if ( $is_shop ) {
					$include = true;
				} elseif ( Helpers::is_cart_template() || Helpers::is_checkout_template() ) {
					$include = ! $is_cart_widget && ! $is_filter_widget;
				} else {
					$include = ! $is_filter_widget;
				}
			}

			return $include;
		} );

		return $widgets;
	}

	/**
	 * Return the shop url when editing the shop template
	 *
	 * @param String           $url
	 * @param \Thrive_Template $template
	 * @param String           $primary
	 * @param String           $secondary
	 * @param String           $variable
	 *
	 * @return String
	 */
	public static function thrive_theme_template_url( $url, $template, $primary, $secondary, $variable ) {

		if ( $primary === THRIVE_ARCHIVE_TEMPLATE && $secondary === Main::POST_TYPE && Main::active() ) {
			/**
			 * When default permalinks are enabled, change the URL of the shop page to the post type archive url ( woocommerce does this by default, so we do it too )
			 *
			 * @see Actions::template_redirect - this is where we stop the default template_redirect ( more explanations there too )
			 */
			if ( get_option( 'permalink_structure' ) === '' ) {
				$url = get_post_type_archive_link( 'product' );
			} else {
				$url = Main::get_shop_url();
			}
		}

		return $url;
	}

	/**
	 * Return default content for WooCommerce pages
	 *
	 * @param String          $content
	 * @param \Thrive_Section $section
	 * @param String          $type
	 *
	 * @return mixed
	 */
	public static function thrive_theme_section_default_content( $content, $section, $type ) {
		if ( $type === 'content' ) {
			$secondary = $section->template->get_secondary();
			switch ( $secondary ) {
				case Main::POST_TYPE:
					$template = $section->template->is_singular() ? 'product' : 'shop';
					$content  = \Thrive_Utils::return_part( '/integrations/woocommerce/views/default/' . $template . '.php' );
					break;
				case Main::ACCOUNT_TEMPLATE:
				case Main::CART_TEMPLATE:
				case Main::CHECKOUT_TEMPLATE:
					$content = \Thrive_Utils::return_part( '/integrations/woocommerce/views/default/' . $secondary . '.php' );
					break;
				default:
					break;
			}
		}

		return $content;
	}

	/**
	 * Add WooCommerce products to the editor
	 *
	 * @param $instances
	 *
	 * @return array
	 */
	public static function tcb_element_instances( $instances ) {
		if ( \Thrive_Utils::during_ajax() || Helpers::is_woo_template() || Helpers::is_woo_archive_template() ) {
			$instances = array_merge( $instances, Main::$elements );
		}

		return $instances;
	}

	/**
	 * Set the order of this category group
	 *
	 * @param $groups
	 *
	 * @return mixed
	 */
	public static function tcb_categories_order( $groups ) {
		if ( Helpers::is_woo_template() ) {
			/* 5 represents the order of the category in the right sidebar */
			$groups[5] = Helpers::get_products_category_label();
		}

		return $groups;
	}

	/**
	 * Add the 'Shop' label to breadcrumbs
	 *
	 * @param $labels
	 *
	 * @return mixed
	 */
	public static function breadcrumbs_labels( $labels ) {

		if ( Main::active() ) {
			$labels['shop'] = __( 'Shop', 'thrive-theme' );
		}

		return $labels;
	}

	/**
	 * Hide tabs from WooCommerce product template
	 *
	 * @param $tabs
	 *
	 * @return mixed
	 */
	public static function woocommerce_product_tabs( $tabs ) {

		if ( ! empty( $GLOBALS[ Shortcodes\Product_Template::SHORTCODE ]['hide-review'] ) ) {
			unset( $tabs['reviews'] );
		}

		return $tabs;
	}

	/**
	 * Modify the number of columns for the gallery
	 *
	 * @param $columns
	 *
	 * @return int
	 */
	public static function woocommerce_product_thumbnails_columns( $columns ) {

		if ( ! empty( $GLOBALS[ Shortcodes\Product_Template::SHORTCODE ]['gallery-columns'] ) ) {
			$gallery_columns = (int) $GLOBALS[ Shortcodes\Product_Template::SHORTCODE ]['gallery-columns'];

			if ( $gallery_columns >= Shortcodes\Product_Template::GALLERY_MIN_COLUMNS && $gallery_columns <= Shortcodes\Product_Template::GALLERY_MAX_COLUMNS ) {
				$columns = $gallery_columns;
			}
		}

		return $columns;
	}

	/**
	 * Modify args for the section of a products list
	 *
	 * @param array  $args
	 * @param String $section
	 *
	 * @return array
	 */
	private static function filter_products_section_args( $args, $section ) {

		$product_args = [ 'columns', 'posts_per_page', 'orderby', 'order' ];

		foreach ( $product_args as $arg ) {
			$key = "$section-$arg";
			if ( isset( $GLOBALS[ Shortcodes\Product_Template::SHORTCODE ][ $key ] ) ) {
				$args[ $arg ] = $GLOBALS[ Shortcodes\Product_Template::SHORTCODE ][ $key ];
			}
		}

		return $args;
	}

	/**
	 * Add WooCommerce shop item to the breadcrumbs
	 *
	 * @param array $items
	 * @param int   $index
	 *
	 * @return array
	 */
	public static function breadcrumbs_root_items( $items, $index ) {
		$is_product = is_product();

		if ( $is_product || is_product_taxonomy() || is_product_category() || is_product_tag() || is_cart() || is_checkout() || is_account_page() ) {

			$shop_title = \Thrive_Breadcrumbs::$labels['shop'];
			if ( ! empty( $shop_title ) ) {

				if ( $is_product ) {
					$index --;
				}
				$items[ $index - 1 ] = \Thrive_Breadcrumbs::create_item( $index, $shop_title, Main::get_shop_url(), [ \Thrive_Utils::is_inner_frame() ? 'shop-label' : '' ] );
			}
		}

		return $items;
	}

	/**
	 * For WooCommerce archives, the default template is the shop one, in case that exists
	 *
	 * @param $templates
	 * @param $args
	 * @param $template_meta
	 *
	 * @return int[]|\WP_Post[]
	 */
	public static function default_templates( $templates, $args, $template_meta ) {

		if ( empty( $templates ) ) {
			if ( is_product_taxonomy() || is_product_category() || is_product_tag() ) {
				$args['meta_query'][2]['value'] = 'product';

				$templates = get_posts( $args );
			} elseif ( is_cart() || is_checkout() || is_account_page() ) {
				/* if there are no cart/checkout/account templates, use a page template */
				$args['meta_query'][2]['value'] = THRIVE_PAGE_TEMPLATE;
				$templates                      = get_posts( $args );
			}
		}

		return $templates;
	}

	/**
	 * If we're on a cart(/checkout/account) template, change 'cart' to 'page' so we can find it with the post query.
	 *
	 * @param $args
	 *
	 * @return mixed
	 */
	public static function thrive_theme_get_posts_args( $args ) {
		$post_type = $args['post_type'];

		switch ( $post_type ) {
			case Main::ACCOUNT_TEMPLATE:
			case Main::CART_TEMPLATE:
			case Main::CHECKOUT_TEMPLATE:
				$page_id = Helpers::get_page_id_for_type( $post_type );
				break;
			case THRIVE_PAGE_TEMPLATE:
				if ( empty( $args['exclude'] ) ) {
					$args['exclude'] = [];
				}
				/* exclude checkout, cart, my account, shop from the pages that are fetched here */
				$args['exclude'] = array_merge( $args['exclude'], [
					Helpers::get_page_id_for_type( Main::ACCOUNT_TEMPLATE ),
					Helpers::get_page_id_for_type( Main::CART_TEMPLATE ),
					Helpers::get_page_id_for_type( Main::CHECKOUT_TEMPLATE ),
					Helpers::get_page_id_for_type( Main::SHOP_TEMPLATE ),
				] );
				break;
			default:
				break;
		}

		if ( ! empty( $page_id ) ) {
			$args['post_type'] = THRIVE_PAGE_TEMPLATE;
			$args['include']   = [ $page_id ];
		}

		return $args;
	}

	/**
	 * Add the woo pages to the localized data so we can make templates for them.
	 *
	 * @param $types
	 * @param $context
	 *
	 * @return mixed
	 */
	public static function add_woo_content_types_for_localize( $types, $context ) {

		/* we only modify this when localizing to the theme dashboard, where this is used only for labels / display logic */
		if ( $context === 'localize' ) {
			$types[ Main::KEY ] = [
				'key'  => Main::KEY,
				'name' => Main::get_label(),
			];

			foreach ( Main::PAGE_TEMPLATES as $page_template ) {
				$types[ $page_template ] = [
					'key'                   => $page_template,
					'name'                  => ucfirst( $page_template ),
					'exclude_from_dropdown' => true,
				];
			}

			$types[ Main::POST_TYPE ]['exclude_from_dropdown'] = true;
		}

		return $types;
	}

	/**
	 * Cart, Checkout and My Account have the sidebar section hidden by default.
	 *
	 * @param $data
	 *
	 * @return mixed
	 */
	public static function modify_template_default_values( $data ) {

		if ( Helpers::is_woo_page_template( $data['meta_input']['secondary_template'] ) ) {
			$data['meta_input']['layout_data'] = [
				'hide_sidebar' => 1,
			];
		}

		return $data;
	}

	/**
	 * Return template meta that is specific to woocommerce content.
	 *
	 * @param array $templates
	 *
	 * @return array
	 */
	public static function modify_template_meta( $templates ) {
		$primary_template   = '';
		$secondary_template = '';

		if ( is_shop() ) {
			$primary_template   = THRIVE_ARCHIVE_TEMPLATE;
			$secondary_template = 'product';
		} elseif ( is_cart() ) {
			$primary_template   = THRIVE_SINGULAR_TEMPLATE;
			$secondary_template = Main::CART_TEMPLATE;
		} elseif (
			( is_checkout() || ( ! empty( $_REQUEST['wc-ajax'] ) && \Thrive_Utils::during_ajax() ) ) &&
			! is_archive() /* fixes a compatibility issue with the One Page Checkout plugin, which thinks that the category archives are the checkout page ( @see post_is_opc() ) */
		) {
			$primary_template = THRIVE_SINGULAR_TEMPLATE;

			/* the 'NextMove Lite - Thank You Page for WooCommerce' plugin are setting their 'thank you' pages as 'checkout' for some reason */
			if ( class_exists( '\xlwcty', false ) ) {
				$instance = \xlwcty::get_instance();

				/*  In order to be compatible with their 'Thank you' pages, we change the secondary template from checkout to 'page' if we detect that this is one of those pages */
				if ( ! empty( $instance ) && method_exists( $instance, 'is_xlwcty_page' ) && $instance->is_xlwcty_page() ) {
					$secondary_template = THRIVE_PAGE_TEMPLATE;
				}
			}

			if ( empty( $secondary_template ) ) {
				$secondary_template = Main::CHECKOUT_TEMPLATE;
			}
		} elseif ( is_account_page() ) {
			$primary_template   = THRIVE_SINGULAR_TEMPLATE;
			$secondary_template = Main::ACCOUNT_TEMPLATE;
		}

		if ( ! empty( $primary_template ) ) {
			$templates[ THRIVE_PRIMARY_TEMPLATE ]   = $primary_template;
			$templates[ THRIVE_SECONDARY_TEMPLATE ] = $secondary_template;
			$templates[ THRIVE_VARIABLE_TEMPLATE ]  = '';
		}

		return $templates;
	}

	/**
	 * Allow categories also for products
	 *
	 * @param $allowed_taxonomies
	 *
	 * @return string[]
	 */
	public static function allowed_taxonomies_in_breadcrumbs( $allowed_taxonomies, $post_type ) {

		if ( $post_type === Main::POST_TYPE ) {
			$allowed_taxonomies = [ 'product_cat' ];
		}

		return $allowed_taxonomies;
	}

	/**
	 * We need to make some changes on the sections when we are on a woo template
	 *
	 * @param array $sections
	 *
	 * @return void[]
	 */
	public static function cloud_sections( $sections ) {
		$is_woo_template = Helpers::is_woo_template();
		$secondary       = thrive_template()->get_secondary();

		foreach ( $sections as $key => $section ) {
			$is_woo_section = ! empty( $section['is_woo'] ) && $section['is_woo'] === '1';

			if ( $is_woo_section ) {
				$sections[ $key ]['order'] = $is_woo_template ? - 1 : PHP_INT_MAX;
			} else {
				$sections[ $key ]['order'] = isset( $section['order'] ) ? $section['order'] : 0;
			}

			/* Allow sidebar woo sections only on woo templates */
			if ( $section['type'] === 'sidebar' && ! $is_woo_template && $is_woo_section ) {
				unset( $sections[ $key ] );
			}

			/* The singular and list sections are allowed only on product or shop templates */
			if ( ( $section['template_type'] === THRIVE_SINGULAR_TEMPLATE || $section['template_type'] === 'list' ) && $secondary !== Main::POST_TYPE && $is_woo_section ) {
				unset( $sections[ $key ] );
			}
		}

		return $sections;
	}

	/**
	 * Change the shop identifier depending if we're on a shop template or not
	 *
	 * @param $identifier
	 *
	 * @return string
	 */
	public static function shop_element_identifier( $identifier ) {
		if ( Helpers::is_shop_template() || Helpers::is_woo_archive_template() ) {
			$identifier = '.shop-template-wrapper';
		}

		return $identifier;
	}

	/**
	 * Hide the TCB shop element on TTB shop templates
	 *
	 * @param $hide
	 *
	 * @return bool
	 */
	public static function hide_architect_shop_element( $hide ) {
		if ( Helpers::is_shop_template() ) {
			$hide = true;
		}

		return $hide;
	}

	/**
	 * Localize WooCommerce specific data
	 *
	 * @param $params
	 *
	 * @return mixed
	 */
	public static function tcb_editor_javascript_params( $params ) {

		if ( is_shop() ) {
			if ( empty( $params['woo'] ) ) {
				$params['woo'] = [];
			}

			$params['woo']['shop_id']    = wc_get_page_id( 'shop' );
			$params['woo']['shop_url']   = get_permalink( $params['woo']['shop_id'] );
			$params['woo']['shop_title'] = get_the_title( $params['woo']['shop_id'] );
		}

		if ( is_checkout() ) {
			$checkout_fields = WC()->checkout->get_checkout_fields();

			$fields_data = array_merge(
				empty( $checkout_fields['billing'] ) ? [] : $checkout_fields['billing'],
				empty( $checkout_fields['order'] ) ? [] : $checkout_fields['order']
			);

			$fields_to_localize = thrive_template()->get_meta_from_sections( 'checkout_field_data' );

			/* if the fields have never been saved in the editor, add the default field values */
			if ( empty( $fields_to_localize ) ) {
				$fields_to_localize = Checkout_Template::initialize_fields( $fields_data );
			} else {
				$has_order_comments = array_search( 'order_comments', array_column( $fields_to_localize, 'id' ), true ) !== false;

				if ( ! $has_order_comments && ! empty( $checkout_fields['order'] ) ) {
					/* if the order_comments field was not added yet, add it now (it was not part of the initial fields) */
					$fields_to_localize[] = [
						'id'       => 'order_comments',
						'required' => false,
						'priority' => 200,
						'visible'  => true,
					];
				}

				usort( $fields_to_localize, function ( $a, $b ) {
					return $a['priority'] - $b['priority'];
				} );
			}

			foreach ( $fields_to_localize as $index => $field ) {
				$key        = $field['id'];
				$field_data = $fields_data[ $key ];

				/* add the data that we need in the editor */
				$fields_to_localize[ $index ] = array_merge( $field, [
					'sortable'   => Checkout_Template::is_sortable( $key ),
					'toggleable' => Checkout_Template::is_toggleable( $key ),
					'label'      => empty( $field_data['label'] ) ? '' : $field_data['label'],
				] );
			}

			$params['woo']['checkout_fields'] = $fields_to_localize;
		}

		return $params;
	}

	/**
	 * All the billing fields that are set to be hidden must also be set as not required so the user can place the order
	 *
	 * @param $original_fields
	 *
	 * @return mixed
	 */
	public static function alter_billing_fields( $original_fields ) {
		if ( is_checkout() && ! static::is_cartflows_page() ) {
			$saved_billing_field_data = Checkout_Template::get_billing_fields_info();
			$is_editor                = is_editor_page_raw( true );

			if ( ! empty( $saved_billing_field_data ) ) {
				$billing_fields = $original_fields['billing'];

				/**
				 * special case for order comments (notes), we saved it in the same place as all the other fields
				 * but it has to be treated differently */
				if ( ! empty( $saved_billing_field_data['order_comments'] ) ) {
					unset( $saved_billing_field_data['order_comments'] );
				}

				foreach ( $saved_billing_field_data as $key => $field ) {
					//change the order
					$billing_fields[ $key ]['priority'] = $field['priority'];

					//unset the hidden fields (only in FE)
					if ( ! $is_editor ) {
						if ( ! $field['visible'] ) {
							unset( $billing_fields[ $key ] );
						}
					}
				}

				$original_fields['billing'] = $billing_fields;
			}
		}

		return $original_fields;
	}

	public static function is_cartflows_page() {
		return get_post_type() === 'cartflows_step';
	}

	/**
	 * Woocommerce does some weird js gymnastics where they don't respect the priority assigned for the checkout address fields,
	 * changing the order of those fields using js according to some default values that are directly localized
	 *
	 * @param $default_fields
	 *
	 * @return mixed
	 */
	public static function change_default_address_fields_priorities( $default_fields ) {
		if ( is_checkout() ) {
			$billing_fields = Checkout_Template::get_billing_fields_info();

			if ( ! empty( $billing_fields ) ) {
				foreach ( $default_fields as $key => $data ) {
					if ( isset( $billing_fields[ 'billing_' . $key ]['priority'] ) ) {
						$default_fields[ $key ]['priority'] = $billing_fields[ 'billing_' . $key ]['priority'];
					}
				}
			}
		}

		return $default_fields;
	}

	/**
	 * As an extra layer of randomness, in wc the postcode field has a special priority that is SOMETIMES used for SOME countries
	 * instead of the one that was set for said field
	 *
	 * @param $countries
	 *
	 * @return mixed
	 */
	public static function alter_wc_country_locale( $countries ) {

		if ( is_checkout() ) {
			$billing_fields = Checkout_Template::get_billing_fields_info();

			if ( ! empty( $billing_fields ) ) {
				$postcode_priority = $billing_fields['billing_postcode']['priority'];

				foreach ( $countries as $country => $info ) {
					if ( ! empty( $info['postcode'] ) && ! empty( $info['postcode']['priority'] ) ) {
						$countries[ $country ]['postcode']['priority'] = $postcode_priority;
					}
				}
			}
		}

		return $countries;
	}

	/**
	 * @param $enabled
	 * Used to hide the order notes input if needed
	 *
	 * @return boolean
	 */
	public static function should_display_order_notes( $enabled ) {
		$billing_fields = Checkout_Template::get_billing_fields_info();

		if ( ! is_editor_page_raw( true ) && ! empty( $billing_fields['order_comments'] ) ) {
			$enabled = $billing_fields['order_comments']['visible'];
		}

		return $enabled;
	}

	/**
	 * @param $should_filter
	 *
	 * @return bool
	 */
	public static function should_filter_blog_posts( $should_filter ) {
		if ( ! empty( $_REQUEST['wc-ajax'] ) && \Thrive_Utils::during_ajax() ) {
			$should_filter = false;
		}

		return $should_filter;
	}

	/**
	 * Stop WooCommerce from redirecting to Cart if the Checkout is empty on the following use cases:
	 *
	 * in the editor and on a checkout template,
	 * in the wizard frame
	 *
	 * @param bool $redirect
	 *
	 * @return bool
	 */
	public static function allow_checkout_redirect( $redirect ) {
		if ( \Thrive_Utils::is_iframe() || ( Helpers::is_checkout_template() && ( \Thrive_Utils::is_inner_frame() || \Thrive_Utils::is_preview() ) ) ) {
			$redirect = false;
		}

		return $redirect;
	}

	/**
	 * Disable TC on woo templates
	 *
	 * @param bool $active
	 *
	 * @return bool
	 */
	public static function tc_active( $active ) {
		if ( Helpers::is_woo_template() ) {
			$active = false;
		}

		return $active;
	}


	/**
	 * Add WooCommerce specific query vars
	 *
	 * @param array $query_vars
	 *
	 * @return mixed
	 */
	public static function query_vars( $query_vars ) {
		if ( is_shop() ) {
			$query_vars['wc_query'] = 'product_query';
		}

		return $query_vars;
	}

	/**
	 * When we are in the iframe we need to disable some checkout scripts from woo
	 * In order to that we trick him and setting the is_checkout flag to 0
	 *
	 * @param array  $params All the data from woo that ends up in javascript
	 * @param string $handle Script handle the data will be attached to.
	 *
	 * @return mixed
	 */
	public static function change_woo_script_data( $params, $handle ) {
		if ( \Thrive_Utils::is_iframe() && $handle === 'wc-checkout' ) {
			$params['is_checkout'] = 0;
		}

		return $params;
	}

	/**
	 * Include woo architect backbone templates
	 *
	 * @param $templates
	 *
	 * @return array
	 */
	public static function tcb_backbone_templates( $templates ) {
		$woo_templates = tve_dash_get_backbone_templates( Main::INTEGRATION_PATH . '/views/backbone', 'backbone' );

		return array_merge( $woo_templates, $templates );
	}

	/**
	 * When we are on a woo template we need to show firstly the headers and footers for WooCommerce
	 *
	 * @param array $sections
	 *
	 * @return mixed
	 */
	public static function sort_header_footer_templates( $sections ) {
		if ( Helpers::is_woo_template() ) {
			foreach ( $sections as $key => $section ) {
				$sections[ $key ]['order'] = ( isset( $section['is_woo'] ) && $section['is_woo'] === '1' ) ? - 1 : PHP_INT_MAX;
			}
		}

		return $sections;
	}

	/**
	 * When woocommerce is not active, hide sections with woo in the name
	 * TODO: maybe search all templates and check if the section is attached to any of them ?
	 *
	 * @param $data
	 * @param $post_id
	 *
	 * @return array
	 */
	public static function filter_lazy_load_data( $data, $post_id ) {
		if ( \Thrive_Utils::is_theme_template() ) {
			$data['headers_and_footers'] = array_filter( $data['headers_and_footers'], static function ( $section ) {
				return stripos( $section['name'], 'woocommerce' ) === false;
			} );

			$data['theme_sections'] = array_filter( $data['theme_sections'], static function ( $section ) {
				return stripos( $section['name'], 'woocommerce' ) === false;
			} );
		}

		return $data;
	}

	/**
	 * Do not show reading progress indicator on woo templates
	 *
	 * @param bool $show
	 *
	 * @return false
	 */
	public static function show_reading_progress( $show ) {

		if ( Helpers::is_woo_template() ) {
			$show = false;
		}

		return $show;
	}

	/**
	 * hide WooCommerce templates when the plugin is not active
	 *
	 * @param $templates
	 *
	 * @return array
	 */
	public static function filter_woo_templates( $templates ) {
		return array_filter( $templates, static function ( $template ) {
			$primary   = isset( $template['meta_input'][ THRIVE_PRIMARY_TEMPLATE ] ) ? $template['meta_input'][ THRIVE_PRIMARY_TEMPLATE ] : $template['primary'];
			$secondary = isset( $template['meta_input'][ THRIVE_SECONDARY_TEMPLATE ] ) ? $template['meta_input'][ THRIVE_SECONDARY_TEMPLATE ] : $template['secondary'];

			return ! (
				( $primary === THRIVE_ARCHIVE_TEMPLATE && in_array( $secondary, [ Main::POST_TYPE, Main::CATEGORY_TAXONOMY, Main::TAG_TAXONOMY ], true ) ) ||
				( $primary === THRIVE_SINGULAR_TEMPLATE && in_array( $secondary, Main::ALL_TEMPLATES, true ) )
			);
		} );
	}

	/**
	 * Add information about what woocommerce related plugins are active
	 *
	 * @param $data
	 *
	 * @return mixed
	 */
	public static function tve_frontend_data( $data ) {

		$data['woocommerce_related_plugins'] = [
			'product_addons' => Helpers::is_addons_plugin_active(),
		];

		return $data;
	}

	/**
	 * Set query params for order to DESC
	 *
	 * @param $args
	 *
	 * @return mixed
	 */
	public static function set_desc_order( $args ) {
		if ( $args['orderby'] === 'date id' && $args['order'] === 'ASC' ) {
			$args['order'] = 'DESC';
		}

		return $args;
	}

	/**
	 * Checks if we need to load woo scripts on a ttb template
	 *
	 * @param $needs_woo
	 *
	 * @return mixed
	 */
	public static function needs_woocommerce_enqueued( $needs_woo ) {
		if ( ! tve_post_is_landing_page( get_the_ID() ) ) {
			$woocommerce_disabled = \TCB\Lightspeed\Woocommerce::is_woocommerce_disabled( false );
			$woo_option           = get_post_meta( thrive_template()->ID, \TCB\Lightspeed\Woocommerce::WOO_MODULE_META_NAME, true );
			$needs_woo            = ! metadata_exists( 'post', thrive_template()->ID, \TCB\Lightspeed\Woocommerce::WOO_MODULE_META_NAME ) ||
			                        ! empty( $woo_option ) ||
			                        ! $woocommerce_disabled ||
			                        ! empty( $_GET['force-all-js'] ) ||
			                        is_editor_page_raw();
		}

		return $needs_woo;
	}

	/**
	 * Get the ttb specific woo scripts
	 *
	 * @param $scripts
	 *
	 * @return array
	 */
	public static function get_woo_ttb_scripts( $scripts ) {
		return array_merge( $scripts, [
			'thrive-theme-woocommerce' => THEME_ASSETS_URL . '/woocommerce.css',
		] );
	}
}
