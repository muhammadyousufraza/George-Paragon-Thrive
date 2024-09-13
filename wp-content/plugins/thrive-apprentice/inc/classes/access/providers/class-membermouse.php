<?php

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

namespace TVA\Access\Providers;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Membermouse extends Base {
	/**
	 * @var string
	 */
	const KEY = 'membermouse';

	/**
	 * Class constructor
	 */
	public function __construct() {
		/**
		 * Activate general hooks
		 */
		parent::__construct();

		add_action( 'mm_member_status_change', [ $this, 'member_status_change' ], 10, 1 );
		add_action( 'mm_member_membership_change', [ $this, 'member_membership_change' ], 10, 1 );
		add_action( 'mm_member_add', [ $this, 'member_add' ], 10, 1 );
		add_action( 'mm_member_delete', [ $this, 'member_delete' ], 10, 1 );
	}

	/**
	 * @param array $data
	 *
	 * @return void
	 */
	public function member_status_change( $data ) {
		if ( isset( $data['status'], $data['membership_level'], $data['member_id'] ) && class_exists( '\MM_Status', false ) ) {
			$status = null;
			if ( (int) $data['status'] === (int) \MM_Status::$CANCELED ) {
				$status = static::STATUS_ACCESS_REVOKED;
			} elseif ( (int) $data['status'] === (int) \MM_Status::$ACTIVE ) {
				$status = static::STATUS_ACCESS_ADDED;
			}

			if ( is_numeric( $status ) ) {
				$this->check_product_and_log_changes( $data['membership_level'], $data['member_id'], $status );
			}
		}
	}

	/**
	 * @param array $data
	 *
	 * @return void
	 */
	public function member_add( $data ) {
		if ( isset( $data['membership_level'], $data['member_id'] ) ) {
			$this->check_product_and_log_changes( $data['membership_level'], $data['member_id'], static::STATUS_ACCESS_ADDED );
		}

	}

	/**
	 * @param array $data
	 *
	 * @return void
	 */
	public function member_delete( $data ) {
		if ( isset( $data['membership_level'], $data['member_id'] ) ) {
			$this->check_product_and_log_changes( $data['membership_level'], $data['member_id'], static::STATUS_ACCESS_REVOKED );
		}
	}

	/**
	 * @param array $data
	 *
	 * @return void
	 */
	public function member_membership_change( $data ) {
		if ( isset( $data['membership_level'], $data['last_membership_id'], $data['member_id'] ) ) {
			//Maybe status check if we decide status is relevant
			$this->check_product_and_log_changes( $data['last_membership_id'], $data['member_id'], static::STATUS_ACCESS_REVOKED );

			$this->check_product_and_log_changes( $data['membership_level'], $data['member_id'], static::STATUS_ACCESS_ADDED );
		}
	}

	/**
	 * @param array $levels
	 *
	 * @return array
	 */
	public function get_users_with_access( $levels = [] ) {
		/**
		 * We need to have autoload on TRUE for the ajax requests made from ThriveApprentice
		 */
		if ( ! class_exists( 'MM_Status', true ) || ! defined( 'MM_TABLE_USER_DATA' ) ) {
			return [];
		}

		global $wpdb;
		$mm_user_data  = MM_TABLE_USER_DATA;
		$active_status = (int) \MM_Status::$ACTIVE;
		$params        = [];
		foreach ( $levels as $id ) {
			$params[] = '%d';
		}

		$query = $wpdb->prepare( "SELECT u.ID FROM $wpdb->users AS u INNER JOIN $mm_user_data AS m ON m.wp_user_id=u.ID
            WHERE m.status = $active_status AND m.membership_level_id IN (" . implode( ',', $params ) . ")", $levels );

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
		/**
		 * We need to have autoload on TRUE for the ajax requests made from ThriveApprentice
		 */
		if ( class_exists( 'MM_Status', true ) && defined( 'MM_TABLE_USER_DATA' ) ) {
			global $wpdb;

			return $wpdb->prepare( "SELECT m.became_active FROM " . MM_TABLE_USER_DATA . " AS m WHERE m.wp_user_id = %d", [ $user_id ] );
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

		return is_plugin_active( 'membermouse/index.php' );
	}

	/**
	 * Returns the activation hook file
	 *
	 * @return string
	 */
	public static function get_activation_hook_file() {
		return 'membermouse/index.php';
	}
}
