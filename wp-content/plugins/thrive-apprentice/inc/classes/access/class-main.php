<?php

namespace TVA\Access;

use TVA\Access\Providers\Base;
use TVA\Access\Providers\Order;
use TVA\Product;

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Main {
	/**
	 * Holds a list of active providers instances
	 *
	 * @var \TVA\Access\Providers\Base[]
	 */
	private static $providers = [];

	/**
	 * Holds a list of keys for the inactive providers
	 *
	 * @var array
	 */
	private static $inactive_providers = [];

	/**
	 * Actions & filters should be placed here
	 *
	 * @return void
	 */
	public static function init() {

		static::init_providers();
		Expiry::init();

		add_action( 'delete_' . Product::TAXONOMY_NAME, [ static::class, 'clear_logs' ], 10, 3 );
		add_action( 'delete_' . \TVA_Const::COURSE_TAXONOMY, [ static::class, 'clear_logs' ], 10, 3 );
		add_action( 'delete_user', static function ( $user_id ) {
			History_Table::get_instance()->delete( [ 'user_id' => (int) $user_id ] );
		}, 10, 1 );
	}

	/**
	 * Returns an array of active providers after they have been initialized
	 * Used for initial populating the access history table after the reporting release
	 *
	 * @return Base[]
	 */
	public static function get_providers() {
		return static::$providers;
	}

	/**
	 * Returns the keys of the inactive providers
	 *
	 * @return array
	 */
	public static function get_inactive_providers() {
		return static::$inactive_providers;
	}

	/**
	 * Bulk updates courses access from a list of added and removed courses
	 *
	 * @param Product $product
	 * @param array   $added_courses
	 * @param array   $removed_courses
	 *
	 * @return void
	 */
	public static function bulk_update_courses_access( $product, $added_courses, $removed_courses ) {
		foreach ( static::$providers as $provider_instance ) {
			if ( ! empty( $added_courses ) ) {
				$provider_instance->product_course_content_modified( $product, $added_courses, Base::STATUS_ACCESS_ADDED );
			}

			if ( ! empty( $removed_courses ) ) {
				$provider_instance->product_course_content_modified( $product, $removed_courses, Base::STATUS_ACCESS_REVOKED );
			}
		}
	}

	/**
	 * Bulk remove all product access
	 *
	 * @param Product    $product
	 * @param int|string $reason
	 *
	 * @return void
	 */
	public static function bulk_remove_access( $product, $reason = '' ) {

		foreach ( static::$providers as $provider_instance ) {
			$provider_instance->product_revoke_access( $product, $reason );
		}
	}

	/**
	 * @param Product  $product
	 * @param int      $user_id
	 * @param int|null $reason
	 *
	 * @return void
	 */
	public static function remove_order_access( $product, $user_id, $reason = null ) {
		$order_provider = static::$providers[ Order::KEY ];

		if ( $order_provider instanceof Order ) {
			$course_ids = $product->get_published_courses( true );
			$data       = [];
			//TODO: maybe check if order is ok before adding the log entry

			$order_provider->build_course_data( $product, $user_id, Order::STATUS_ACCESS_REVOKED, $course_ids, $data, '', '', $reason );
			$order_provider->commit_data( $data );
		}
	}

	/**
	 * @param int      $term
	 * @param int      $term_id
	 * @param \WP_Term $deleted_term
	 *
	 * @return void
	 */
	public static function clear_logs( $term_id, $term_taxonomy_id, $deleted_term ) {
		$where = [];
		switch ( $deleted_term->taxonomy ) {
			case Product::TAXONOMY_NAME:
				$where = [
					'product_id' => (int) $term_id,
				];
				break;
			case \TVA_Const::COURSE_TAXONOMY:
				$where = [
					'course_id' => (int) $term_id,
				];
				break;
		}

		History_Table::get_instance()->delete( $where );
	}

	/**
	 * Member plugin activation / deactivation callback
	 * Removes user cache at product level
	 *
	 * @return void
	 */
	public static function member_plugin_activation_toggle() {
		Product::delete_count_users_with_access_cache( 0 );
	}

	/**
	 * Init providers
	 *
	 * @return void
	 */
	private static function init_providers() {
		$providers = 'providers';
		$path      = __DIR__ . '/' . $providers;

		foreach ( array_diff( scandir( $path ), [ '.', '..' ] ) as $item ) {
			if ( preg_match( '/class-(.*).php/m', $item, $m ) && ! empty( $m[1] ) ) {
				$class_name = \TCB_ELEMENTS::capitalize_class_name( $m[1] );

				/**
				 * @var \TVA\Access\Providers\Base $class
				 */
				$class = __NAMESPACE__ . '\\' . ucfirst( $providers ) . '\\' . $class_name;

				$hook_file = $class::get_activation_hook_file();
				if ( ! empty( $hook_file ) ) {
					add_action( 'activate_' . $hook_file, [ __CLASS__, 'member_plugin_activation_toggle' ] );
					add_action( 'deactivate_' . $hook_file, [ __CLASS__, 'member_plugin_activation_toggle' ] );
				}

				if ( $class::is_active() ) {
					/**
					 * Activates constructor hooks
					 */
					$instance = new $class();

					static::$providers[ $class::KEY ] = $instance;
				} elseif ( ! empty( $class::KEY ) ) {
					static::$inactive_providers[] = $class::KEY;
				}
			}
		}
	}
}
