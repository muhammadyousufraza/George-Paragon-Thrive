<?php

namespace TVA\Access;

use TVA\Access\Providers\Base;
use TVA\Product;

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Migration {

	const OPTION_NAME = 'tva_use_reporting_logic';

	const META_NAME = 'tva_rdy_for_report';

	/**
	 * @return bool
	 */
	public static function should_migrate() {
		//TODO: check for products -> user has products in the system

		$is_rest = defined( 'REST_REQUEST' ) && REST_REQUEST;

		return ! \TVA_Const::$tva_during_activation && empty( get_option( static::OPTION_NAME ) ) && ( $is_rest || wp_doing_ajax() ) && ! wp_doing_cron();
	}

	public static function is_done() {
		$products = Product::get_items();
		$ready    = true; //User has no products -> array is empty -> migration is done;

		foreach ( $products as $product ) {
			if ( ! static::product_ready_for_report( $product ) ) {
				$ready = false;
				break;
			}
		}

		return $ready;
	}

	public static function mark_migration_done() {
		update_option( static::OPTION_NAME, 1, 'no' );
	}

	/**
	 * @param Product $product
	 *
	 * @return bool
	 */
	public static function product_ready_for_report( $product ) {
		return ! empty( get_term_meta( $product->get_id(), static::META_NAME, true ) );
	}

	/**
	 * @param Product $product
	 * @param int     $ready
	 *
	 * @return void
	 */
	public static function mark_product( $product, $ready = 1 ) {
		update_term_meta( $product->get_id(), static::META_NAME, $ready );
	}

	/**
	 * @param Product $product
	 *
	 * @return void
	 */
	public static function migrate_access_for_product( $product ) {

		if ( static::product_ready_for_report( $product ) ) {
			return; //Ensure the product is not processed twice
		}

		static::mark_product( $product );

		//We take the course IDs first
		//NOTE: even if a product has no courses the access still should be migrated
		$course_ids        = $product->get_courses( true );
		$non_sendowl_order = empty ( $product->get_rules_by_integration( 'sendowl_product' ) ) && empty ( $product->get_rules_by_integration( 'sendowl_bundle' ) );

		foreach ( Main::get_providers() as $key => $provider ) {
			$data = [];

			if ( $key === 'order' && $non_sendowl_order || $key === 'sendowl_product' ) {
				$users = array_map( static function ( $users_data ) {
					return [
						'ID'      => $users_data['ID'],
						'created' => $users_data['item_created'],
					];
				}, $product->get_customers() );
				$data  = static::build_migration_data( array_column( $users, 'ID' ), $product, $course_ids, $provider, array_column( $users, 'created' ) );
			} else {
				$access_levels = $product->get_ids_of_integration( $key );

				if ( ! empty( $access_levels ) ) {
					$users = $provider->get_users_with_access( $access_levels );

					$data = static::build_migration_data( $users, $product, $course_ids, $provider, $access_levels );
				}
			}

			$provider->commit_data( $data );
		}
	}

	/**
	 * @param array|null   $user_ids
	 * @param Product      $product
	 * @param Base         $provider
	 * @param array|string $access_levels
	 *
	 * @return array
	 */
	public static function build_migration_data( $users_ids, $product, $course_ids, $provider, $access_levels ) {
		$data = [];

		$created_in_users = $provider instanceof \TVA\Access\Providers\Order || $provider instanceof \TVA\Access\Providers\Sendowl;

		if ( ! empty( $users_ids ) && is_array( $users_ids ) ) {
			foreach ( $users_ids as $key => $user_id ) {
				$created = $created_in_users ? $access_levels[ $key ] : $provider->get_level_change_date( $user_id, $access_levels );

				$provider->build_course_data( $product, $user_id, $provider::STATUS_ACCESS_ADDED, $course_ids, $data, $created );
			}
		}

		return $data;
	}

	/**
	 * Reverts the initial population for access history table
	 *
	 * @return void
	 */
	public static function revert_migration() {
		$products = Product::get_items();

		delete_option( static::OPTION_NAME );

		foreach ( $products as $product ) {
			static::mark_product( $product, 0 );
		}

		//CLear also the access table
		global $wpdb;
		$table = $wpdb->prefix . 'tva_' . \TVA\Access\History_Table::get_table_name();
		$query = "TRUNCATE $table";
		$wpdb->query( $query );
	}
}
