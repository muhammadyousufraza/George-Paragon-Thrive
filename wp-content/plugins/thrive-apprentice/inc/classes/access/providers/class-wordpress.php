<?php

namespace TVA\Access\Providers;

use TVA\Product;

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Wordpress extends Base {

	/**
	 * @var string
	 */
	const KEY = 'wordpress';

	/**
	 * Constructor function
	 */
	public function __construct() {
		/**
		 * Activate general hooks
		 */
		parent::__construct();

		/**
		 * WordPress role actions
		 */
		add_action( 'add_user_role', [ $this, 'add_user_role' ], 10, 2 );
		add_action( 'remove_user_role', [ $this, 'remove_user_role' ], 10, 2 );
	}

	/**
	 * Called from "product_added_access" and "product_removed_access" functions from Base
	 * Returns a list of users IDs with access of the corresponding levels
	 *
	 * @param array $levels
	 *
	 * @return array
	 */
	public function get_users_with_access( $levels = [] ) {
		return get_users( [ 'role__in' => $levels, 'fields' => 'ID' ] );
	}

	/**
	 * Callback for when a role is changed from wordpress for a user
	 *
	 * @param int    $user_id
	 * @param string $new_role
	 *
	 * @return void
	 */
	public function add_user_role( $user_id, $new_role ) {
		Product::flush_global_cache( [ 'get_protected_products_by_integration', static::KEY ] );

		if ( ! is_super_admin( $user_id ) ) {
			$this->check_product_and_log_changes( $new_role, $user_id, static::STATUS_ACCESS_ADDED );
		}
	}

	/**
	 *  Callback for when a role is changed from wordpress for a user
	 *
	 * @param int    $user_id
	 * @param string $old_role
	 *
	 * @return void
	 */
	public function remove_user_role( $user_id, $old_role ) {
		Product::flush_global_cache( [ 'get_protected_products_by_integration', static::KEY ] );

		if ( ! is_super_admin( $user_id ) ) {
			$this->check_product_and_log_changes( $old_role, $user_id, static::STATUS_ACCESS_REVOKED );
		}
	}

	/**
	 * Returns the level change date
	 * USED IN THE MIGRATION PROCESS
	 *
	 * @param int            $user_id
	 * @param int[]|string[] $access_levels
	 *
	 * @return string
	 */
	public function get_level_change_date( $user_id, $access_levels ) {
		global $wpdb;

		return $wpdb->prepare( "SELECT user_registered from $wpdb->users WHERE ID = %d", [ $user_id ] );
	}


	/**
	 * @return bool
	 */
	public static function is_active() {
		return true;
	}
}
