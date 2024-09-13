<?php

namespace TVA\Access\Providers;

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Wishlist extends Base {

	/**
	 * @var string
	 */
	const KEY = 'wishlist';

	/**
	 * Class constructor
	 */
	public function __construct() {
		/**
		 * Activate general hooks
		 */
		parent::__construct();

		/**
		 * WishlistMember hooks
		 */
		add_action( 'wishlistmember_remove_user_levels', [ $this, 'remove_user_levels' ], 10, 3 );
		add_action( 'wishlistmember_add_user_levels', [ $this, 'add_user_levels' ], 10, 3 );
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
		$user_ids = [];

		global $WishListMemberInstance;
		if ( ! empty( $WishListMemberInstance ) && method_exists( $WishListMemberInstance, 'member_ids' ) ) {
			$user_ids = $WishListMemberInstance->member_ids( $levels );
		}

		return $user_ids;
	}

	/**
	 * @param int   $user_id
	 * @param array $new_levels
	 * @param array $removed_levels
	 *
	 * @return void
	 */
	public function add_user_levels( $user_id, $new_levels, $removed_levels ) {
		foreach ( (array) $new_levels as $level ) {
			$this->check_product_and_log_changes( $level, $user_id, static::STATUS_ACCESS_ADDED );
		}
	}

	/**
	 * TODO:comments
	 *
	 * @param int   $user_id
	 * @param array $removed_levels
	 * @param array $new_levels
	 *
	 * @return void
	 */
	public function remove_user_levels( $user_id, $removed_levels, $new_levels ) {

		foreach ( (array) $removed_levels as $level ) {
			$this->check_product_and_log_changes( $level, $user_id, static::STATUS_ACCESS_REVOKED );
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
		global $WishListMemberInstance;
		if ( ! empty( $WishListMemberInstance ) ) {
			global $wpdb;

			$params         = [ $user_id, 'level', 'added' ];
			$log_value_like = [];
			foreach ( $access_levels as $access_level ) {
				$log_value_like[] = " `log_value` LIKE '%%s%' ";
				$params[]         = $access_level;
			}

			if ( empty( $WishListMemberInstance->table_names->logs ) ) {
				return current_datetime()->format( 'Y-m-d H:i:s' );
			} else {
				return $wpdb->prepare(
					'SELECT date_added FROM `' . esc_sql( $WishListMemberInstance->table_names->logs ) . '` WHERE `user_id`=%d AND `log_group` LIKE %s AND `log_key` LIKE %s AND (' . implode( ' OR ', $log_value_like ) . ') ORDER BY date_added DESC LIMIT 1',
					$params
				);
			}
		}

		return parent::get_level_change_date( $user_id, $access_levels );
	}

	/**
	 * @return bool
	 */
	public static function is_active() {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		return is_plugin_active( 'wishlist-member/wpm.php' );
	}

	/**
	 * Returns the activation hook file
	 *
	 * @return string
	 */
	public static function get_activation_hook_file() {
		return 'wishlist-member/wpm.php';
	}
}
