<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

namespace TCB\Integrations\WooCommerce\Shortcodes\Shop;

use TCB\Integrations\WooCommerce\Main as Woo_Main;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Main
 *
 * @package TCB\Integrations\WooCommerce\Shortcodes\Shop
 */
class Main {

	const DEFAULT_PRODUCTS_TO_DISPLAY = 8;
	const SHORTCODE = 'tcb_woo_shop';
	const PATH = 'classes/shortcodes/shop/';
	const IDENTIFIER = '.tcb-woo-shop';

	/**
	 * Hooks used by WooCommerce for displaying various components on the shop page
	 */
	public static $shop_content_hooks = [
		'sale-flash'       => [
			'tag'      => 'woocommerce_before_shop_loop_item_title',
			'callback' => 'woocommerce_show_product_loop_sale_flash',
			'priority' => 10,
		],
		'result-count'     => [
			'tag'      => 'woocommerce_before_shop_loop',
			'callback' => 'woocommerce_result_count',
			'priority' => 20,
		],
		'catalog-ordering' => [
			'tag'      => 'woocommerce_before_shop_loop',
			'callback' => 'woocommerce_catalog_ordering',
			'priority' => 30,
		],
		'title'            => [
			'tag'      => 'woocommerce_shop_loop_item_title',
			'callback' => 'woocommerce_template_loop_product_title',
			'priority' => 10,
		],
		'rating'           => [
			'tag'      => 'woocommerce_after_shop_loop_item_title',
			'callback' => 'woocommerce_template_loop_rating',
			'priority' => 5,
		],
		'price'            => [
			'tag'      => 'woocommerce_after_shop_loop_item_title',
			'callback' => 'woocommerce_template_loop_price',
			'priority' => 10,
		],
		'cart'             => [
			'tag'      => 'woocommerce_after_shop_loop_item',
			'callback' => 'woocommerce_template_loop_add_to_cart',
			'priority' => 10,
		],
		'pagination'       => [
			'tag'      => 'woocommerce_after_shop_loop',
			'callback' => 'woocommerce_pagination',
			'priority' => 10,
		],
	];

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

		$classes = [ 'tcb-woo-shop', THRIVE_WRAPPER_CLASS ];

		static::before_render( $attr );

		$in_editor = is_editor_page_raw( true );

		if ( ! $in_editor ) {
			foreach ( static::$shop_content_hooks as $key => $hook ) {
				if ( ! empty( $attr[ 'hide-' . $key ] ) ) {
					/* by removing this action we actually hide the element */
					remove_action( $hook['tag'], $hook['callback'], $hook['priority'] );
				}
			}
		}

		/* Woo has some logic based on using $GLOBALS['post'], which we don't have during REST, so we set it to null as a workaround for Woo */
		if ( \TCB_Utils::is_rest() && ! isset( $GLOBALS['post'] ) ) {
			$GLOBALS['post'] = null;
		}

		$products = new \WC_Shortcode_Products( $attr );
		$content  = $products->get_content();

		if ( ! $in_editor ) {
			foreach ( static::$shop_content_hooks as $key => $hook ) {
				if ( ! empty( $attr[ 'hide-' . $key ] ) ) {
					/* add the actions back */
					add_action( $hook['tag'], $hook['callback'], $hook['priority'] );
				}
			}
		}

		static::after_render( $attr );

		$id = empty( $attr['id'] ) ? '' : $attr['id'];
		unset( $attr['id'] );

		if ( $in_editor ) {
			$classes[] = 'tcb-selector-no_save tcb-child-selector-no_icons';
		} else {
			/* only keep a few attributes on the frontend */
			$attr = array_intersect_key( $attr, [
				'align-items' => '',
				'css'         => '',
			] );
		}

		$data = [];

		foreach ( $attr as $key => $value ) {
			$data[ 'data-' . $key ] = esc_attr( $value );
		}


		return \TCB_Utils::wrap_content( $content, 'div', $id, $classes, $data );
	}

	/**
	 * Update default attributes and prepare some filters and variables
	 *
	 * @param $attr
	 */
	public static function before_render( &$attr ) {

		$attr = array_merge( array(
			'limit'                 => static::DEFAULT_PRODUCTS_TO_DISPLAY,
			'columns'               => 4,
			'orderby'               => 'date',
			'order'                 => 'desc',
			'paginate'              => true,
			'cache'                 => 'false',
			'hide-result-count'     => 0,
			'hide-catalog-ordering' => 1,
			'hide-sale-flash'       => 0,
			'hide-title'            => 0,
			'hide-price'            => 0,
			'hide-rating'           => 0,
			'hide-cart'             => 0,
			'hide-pagination'       => 0,
			'ids'                   => '',
			'category'              => '',
			'cat_operator'          => 'in', /* 'in', 'not in', 'and' */
			'taxonomy'              => '',
			'terms'                 => '',
			'terms_operator'        => 'in', /* 'in', 'not in', 'and' */
			'tag'                   => '',
			'tag_operator'          => 'in', /* 'in', 'not in', 'and' */
			'align-items'           => 'center',
			'ct'                    => 'shop-0',
			'ct-name'               => esc_html__( 'Original Shop', 'thrive-cb' ),
		), is_array( $attr ) ? $attr : [] );

		if ( ! empty( $attr['taxonomy'] ) ) {
			$attr['attribute'] = $attr['taxonomy'];
		}

		/* the 'tag' attribute accepts only tag slugs instead of IDs, so we temporarily change the format */
		if ( ! empty( $attr['tag'] ) ) {
			$attr['temp_tag'] = $attr['tag'];
			$tag_slugs        = [];

			foreach ( explode( ',', $attr['tag'] ) as $tag ) {
				$term = get_term( $tag );

				if ( ! empty( $term ) && ! is_wp_error( $term ) ) {
					$tag_slugs[] = $term->slug;
				}
			}

			$attr['tag'] = implode( ',', $tag_slugs );
		}

		/* the default WooCommerce template also displays the title, but we don't need/want that */
		add_filter( 'woocommerce_show_page_title', '__return_false' );

		/**
		 * fix for a woocommerce catalog ordering glitch
		 * 'Sort by price - low to high' has 'price' as an option value, but needs 'price-asc' in woocommerce backend
		 *
		 * @see parse_query_args() from class-wc-shortcode-products.php
		 */
		if ( ! empty( $_GET['orderby'] ) && $_GET['orderby'] === 'price' ) {
			/* the fix is to manually set the order attribute when 'price' is present in the query args */
			$attr['order'] = 'asc';
		}
	}

	/**
	 * Some attributes were changed temporarily in before_render, we revert to their original names here
	 *
	 * @param $attr
	 */
	public static function after_render( &$attr ) {
		if ( ! empty( $attr['temp_tag'] ) ) {
			$attr['tag'] = $attr['temp_tag'];

			unset ( $attr['temp_tag'] );
		}

		if ( ! empty( $attr['attribute'] ) ) {
			$attr['taxonomy'] = $attr['attribute'];

			unset ( $attr['attribute'] );
		}
	}

	/**
	 * @return array
	 */
	public static function get_product_taxonomies() {
		$all = get_object_taxonomies( Woo_Main::POST_TYPE, 'object' );

		$taxonomies = array_map( static function ( $item ) {
			return [
				'name'  => $item->label,
				'value' => $item->name,
			];
		}, $all );

		$taxonomies = array_filter( $taxonomies, function ( $taxonomy ) {
			/* we only return attribute-taxonomies ( they are prefixed with 'pa_' ) */
			$is_attribute_taxonomy = strpos( $taxonomy['value'], 'pa_' ) !== false;

			$terms = get_terms( [
				'taxonomy'   => $taxonomy['value'],
				'hide_empty' => false,
			] );

			return $is_attribute_taxonomy && ( count( $terms ) > 0 ); /* we only return taxonomies that have terms inside them */
		} );

		return array_values( $taxonomies );
	}

	/**
	 * @param $identifier
	 *
	 * @return mixed|void
	 */
	public static function get_shop_element_identifier( $identifier ) {
		return apply_filters( 'tcb_woo_shop_identifier', Main::IDENTIFIER ) . ' ' . $identifier;
	}

	/**
	 * @var string[]
	 */
	public static $sub_elements = array(
		'product-button'                  => '.product a.button',
		'product-image'                   => '.product .woocommerce-loop-product__link > img',
		'product-onsale'                  => '.product .onsale',
		'product-price'                   => '.product .price',
		'product-rating'                  => '.product .star-rating',
		'product-title'                   => '.product .woocommerce-loop-product__title',
		'product-wrapper'                 => '.type-product.product',
		'product-result-count'            => '.woocommerce-result-count',
		'product-catalog-ordering'        => '.woocommerce-ordering',
		'product-pagination'              => '.woocommerce-pagination',
		'product-pagination-item'         => 'li .page-numbers:not(.current):not(.prev):not(.next)',
		'product-pagination-current-item' => '.page-numbers.current',
		'product-pagination-prev'         => '.page-numbers.prev',
		'product-pagination-next'         => '.page-numbers.next',
	);

	/**
	 * @param string $tag
	 *
	 * @return string|string[]
	 */
	public static function get_sub_element_identifier( $tag = '' ) {
		return empty( $tag ) ? static::$sub_elements : static::$sub_elements[ $tag ];
	}

	/**
	 * @return array[]
	 */
	public static function get_general_typography_config() {
		return [
			'disabled_controls' => [
				'.tve-advanced-controls',
				'p_spacing',
				'h1_spacing',
				'h2_spacing',
				'h3_spacing',
			],
			'config'            => [
				'css_suffix'    => '',
				'css_prefix'    => '',
				'TextShadow'    => [
					'css_suffix' => '',
					'css_prefix' => '',
				],
				'FontColor'     => [
					'css_suffix' => '',
					'css_prefix' => '',
				],
				'FontSize'      => [
					'css_suffix' => '',
					'css_prefix' => '',
				],
				'TextStyle'     => [
					'css_suffix' => '',
					'css_prefix' => '',
				],
				'LineHeight'    => [
					'css_suffix' => '',
					'css_prefix' => '',
				],
				'FontFace'      => [
					'css_suffix' => '',
					'css_prefix' => '',
				],
				'LetterSpacing' => [
					'css_suffix' => '',
					'css_prefix' => '',
				],
				'TextAlign'     => [
					'css_suffix' => '',
					'css_prefix' => '',
				],
				'TextTransform' => [
					'css_suffix' => '',
					'css_prefix' => '',
				],
			],
		];
	}
}
