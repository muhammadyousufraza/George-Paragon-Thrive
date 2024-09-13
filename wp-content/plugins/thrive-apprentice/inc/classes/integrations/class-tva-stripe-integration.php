<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

use TVA\Product;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}


class TVA_Stripe_Integration extends TVA_Integration {


	public static $CACHE = [];

	public function allow() {
		return true;
	}

	/**
	 * Get all products for identifier
	 *
	 * @param $identifier
	 * @param $as_objects
	 *
	 * @return array|Product[]
	 */
	public static function get_all_products_for_identifier( $identifier, $as_objects = true ) {
		if ( ! isset( static::$CACHE[ $identifier ] ) ) {
			$args        = [
				'taxonomy'   => Product::TAXONOMY_NAME,
				'hide_empty' => false,
				'meta_query' => [
					'relation'         => 'AND',
					'tva_order_clause' => [
						'key' => 'tva_order',
					],
				],
				'fields'     => 'ids',
				'orderby'    => 'meta_value_num',
				'order'      => 'DESC', // backwards compat ordering
			];
			$product_ids = get_terms( $args );
			$products    = [];

			foreach ( $product_ids as $product_id ) {
				$rules       = get_term_meta( $product_id, 'tva_rules', true );
				$integration = array_values( array_filter( $rules, static function ( $rule ) {
					return $rule['integration'] === 'stripe';
				} ) );

				if ( count( $integration ) && isset( $integration[0]['items'] ) ) {
					$items = $integration[0]['items'];
					if ( count( $items ) && isset( $items[0]['id'] ) && $identifier === $items[0]['id'] ) {
						$products[] = $product_id;
					}
				}
			}
			if ( $as_objects ) {
				$products = array_map( static function ( $product_id ) {
					return new Product( $product_id );
				}, $products );
			}
			static::$CACHE[ $identifier ] = $products;
		}

		return static::$CACHE[ $identifier ];
	}

	public function is_rule_applied( $rule ) {
		$allowed  = false;
		$tva_user = tva_access_manager()->get_tva_user();

		if ( true === $tva_user instanceof TVA_User ) {
			foreach ( $rule['items'] as $item ) {
				$price_id = $item['id'];
				$order    = $tva_user->has_bought( $price_id );
				if ( $order instanceof TVA_Order ) {
					$this->set_order( $order );
					$this->set_order_item( $order->get_order_item_by_product_id( $price_id ) );
					$allowed = true;
					break;
				}
			}
		}

		return $allowed;
	}

	protected function init_items() {
		// TODO: Implement init_items() method.
	}

	protected function _get_item_from_membership( $key, $value ) {
		// TODO: Implement _get_item_from_membership() method.
	}

	public function get_customer_access_items( $customer ) {
		// TODO: Implement get_customer_access_items() method.
	}
}
