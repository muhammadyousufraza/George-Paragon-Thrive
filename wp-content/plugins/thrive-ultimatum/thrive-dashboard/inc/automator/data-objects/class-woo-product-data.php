<?php

namespace TVE\Dashboard\Automator;

use Exception;
use Thrive\Automator\Items\Data_Object;
use function wc_get_product;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Woo_Product_Data
 */
class Woo_Product_Data extends Data_Object {
	/**
	 * Get the data-object identifier
	 *
	 * @return string
	 */
	public static function get_id() {
		return 'woo_product_data';
	}

	public static function get_nice_name() {
		return 'WooCommerce product';
	}

	/**
	 * Array of field object keys that are contained by this data-object
	 *
	 * @return array
	 */
	public static function get_fields() {
		return array(
			'woo_product_id',
			'product_type',
			'woo_product_name',
			'product_slug',
			'product_created_date',
			'product_modified_date',
			'product_status',
			'product_featured',
			'product_catalog_visibility',
			'product_description',
			'product_short_description',
			'product_sku',
			'product_active_price',
			'product_regular_price',
			'product_sale_price',
			'product_date_on_sale_from',
			'product_date_on_sale_to',
			'product_total_number_sales',
			'product_tax_status',
			'product_tax_class',
			'product_manage_stock',
			'product_sale_quantity',
			'product_stock_status',
			'product_backorders',
			'product_low_stock_amount',
			'product_sold_individually',
			'product_weight',
			'product_length',
			'product_width',
			'product_height',
			'product_upsell_ids',
			'product_cross_sell_ids',
			'product_parent_id',
			'product_reviews_allowed',
			'product_purchase_note',
			'product_attributes',
			'product_default_attributes',
			'product_category_ids',
			'product_tag_ids',
			'product_get_virtual',
			'product_gallery_ids',
			'product_shipping_class_id',
			'product_rating_count',
			'product_average_rating',
			'product_review_count',
		);
	}

	public static function create_object( $param ) {
		if ( empty( $param ) ) {
			throw new Exception( 'No parameter provided for Woo_Product_Data object' );
		}

		$product = null;
		if ( is_a( $param, 'WC_Order_Item_Product' ) ) {
			$product = $param->get_product();
		} elseif ( is_a( $param, 'WC_Product' ) ) {
			$product = $param;
		} elseif ( is_numeric( $param ) ) {
			$product = wc_get_product( $param );
		}

		if ( $product ) {
			return array(
				'woo_product_id'             => $product->get_id(),
				'product_type'               => $product->get_type(),
				'woo_product_name'           => $product->get_name(),
				'product_slug'               => $product->get_slug(),
				'product_created_date'       => $product->get_date_created(),
				'product_modified_date'      => $product->get_date_modified(),
				'product_status'             => $product->get_status(),
				'product_featured'           => $product->get_featured(),
				'product_catalog_visibility' => $product->get_catalog_visibility(),
				'product_description'        => $product->get_description(),
				'product_short_description'  => $product->get_short_description(),
				'product_sku'                => $product->get_sku(),
				'product_active_price'       => $product->get_price(),
				'product_regular_price'      => $product->get_regular_price(),
				'product_sale_price'         => $product->get_sale_price(),
				'product_date_on_sale_from'  => $product->get_date_on_sale_from(),
				'product_date_on_sale_to'    => $product->get_date_on_sale_to(),
				'product_total_number_sales' => $product->get_total_sales(),
				'product_tax_status'         => $product->get_tax_status(),
				'product_tax_class'          => $product->get_tax_class(),
				'product_manage_stock'       => $product->get_manage_stock(),
				'product_sale_quantity'      => $product->get_stock_quantity(),
				'product_stock_status'       => $product->get_stock_status(),
				'product_backorders'         => $product->get_backorders(),
				'product_low_stock_amount'   => $product->get_low_stock_amount(),
				'product_sold_individually'  => $product->get_sold_individually(),
				'product_weight'             => $product->get_weight(),
				'product_length'             => $product->get_length(),
				'product_width'              => $product->get_width(),
				'product_height'             => $product->get_height(),
				'product_upsell_ids'         => $product->get_upsell_ids(),
				'product_cross_sell_ids'     => $product->get_cross_sell_ids(),
				'product_parent_id'          => $product->get_parent_id(),
				'product_reviews_allowed'    => $product->get_reviews_allowed(),
				'product_purchase_note'      => $product->get_purchase_note(),
				'product_attributes'         => $product->get_attributes(),
				'product_default_attributes' => $product->get_default_attributes(),
				'product_category_ids'       => $product->get_category_ids(),
				'product_tag_ids'            => $product->get_tag_ids(),
				'product_get_virtual'        => $product->get_virtual(),
				'product_gallery_ids'        => $product->get_gallery_image_ids(),
				'product_shipping_class_id'  => $product->get_shipping_class_id(),
				'product_rating_count'       => $product->get_rating_counts(),
				'product_average_rating'     => $product->get_average_rating(),
				'product_review_count'       => $product->get_review_count(),
			);
		}

		return $product;
	}

	public static function get_data_object_options() {

		$options = [];

		foreach ( Woo::get_products() as $product ) {
			$name           = $product->get_name();
			$id             = $product->get_id();
			$options[ $id ] = array(
				'id'    => $id,
				'label' => $name,
			);
		}

		return $options;
	}
}
