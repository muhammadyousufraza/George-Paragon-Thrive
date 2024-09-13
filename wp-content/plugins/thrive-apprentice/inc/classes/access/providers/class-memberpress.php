<?php

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

namespace TVA\Access\Providers;

use TVA\Product;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Memberpress extends Base {

	/**
	 * @var string
	 */
	const KEY = 'memberpress';

	/**
	 * Class constructor
	 */
	public function __construct() {
		/**
		 * Activate general hooks
		 */
		parent::__construct();

		/**
		 * MemberPress hooks
		 * TODO: see delete
		 */
		add_action( 'mepr-txn-store', [ $this, 'transaction_status_changed' ], 10, 2 );
	}

	/**
	 * @param \MeprTransaction $mepr_new_transaction /plugins/memberpress/app/models/MeprTransaction.php
	 * @param \MeprTransaction $mepr_old_transaction /plugins/memberpress/app/models/MeprTransaction.php
	 *
	 * TODO: handle the case where product id is changed from transation
	 *
	 * @return void
	 */
	public function transaction_status_changed( $mepr_new_transaction, $mepr_old_transaction ) {

		if ( ! property_exists( $mepr_new_transaction, 'complete_str' ) || empty( $mepr_new_transaction->product_id ) || empty( $mepr_new_transaction->user_id ) ) {
			/**
			 * Check if the class property exists
			 */
			return;
		}

		/**
		 * Bypass status validation check
		 * We need to add exceptions because the MemberPress transaction logic is not so well written
		 *
		 * Special case when a transaction is created during MemberPress Login form and the trasaction is completed after creation
		 */
		$bypass_status_validation = class_exists( 'MeprUtils', false ) && \MeprUtils::is_post_request() && isset( $_POST['mepr_process_signup_form'] ) && $mepr_new_transaction->status === $mepr_new_transaction::$complete_str;

		if ( ! $bypass_status_validation && $mepr_new_transaction->status === $mepr_old_transaction->status ) {
			/**
			 * The update button was pressed in the UI with no status changed
			 */
			return;
		}

		$status = null;

		if ( $mepr_new_transaction->status === $mepr_new_transaction::$complete_str ) {
			$status = static::STATUS_ACCESS_ADDED;
		} else if ( $mepr_old_transaction->status === $mepr_new_transaction::$complete_str && $mepr_new_transaction->status !== $mepr_new_transaction::$complete_str ) {
			$status = static::STATUS_ACCESS_REVOKED;
		}

		if ( is_numeric( $status ) ) {
			$this->check_product_and_log_changes( $mepr_new_transaction->product_id, $mepr_new_transaction->user_id, $status );
		}
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
		if ( ! class_exists( 'MeprDb', false ) ) {
			return [];
		}

		global $wpdb;
		$mepr_db = \MeprDb::fetch();

		$params = [];
		foreach ( $levels as $level_id ) {
			$params[] = '%d';
		}

		$query = $wpdb->prepare( "SELECT u.ID FROM $wpdb->users AS u INNER JOIN {$mepr_db->members} AS m ON m.user_id=u.ID
					WHERE (m.active_txn_count > 0 OR m.trial_txn_count > 0) AND m.memberships IN (" . implode( ',', $params ) . ")", $levels );

		return array_column( $wpdb->get_results( $query, ARRAY_A ), 'ID' );
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

		if ( class_exists( 'MeprDb', false ) ) {
			global $wpdb;
			$mepr_db = \MeprDb::fetch();

			return $wpdb->prepare( "SELECT t.created_at FROM $mepr_db->transactions AS t INNER JOIN {$mepr_db->members} AS m ON m.latest_txn_id=t.id
					WHERE m.user_id = %d AND t.user_id = %d", [ $user_id, $user_id ] );
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

		return is_plugin_active( 'memberpress/memberpress.php' );
	}

	/**
	 * Returns the activation hook file
	 *
	 * @return string
	 */
	public static function get_activation_hook_file() {
		return 'memberpress/memberpress.php';
	}
}
