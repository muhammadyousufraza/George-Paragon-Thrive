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

/**
 * Problema Bundle - Many TO Many
 */
class Membermouse_Bundle extends Base {
	/**
	 * @var string
	 */
	const KEY = 'membermouse_bundle';

	/**
	 * Class constructor
	 */
	public function __construct() {
		/**
		 * Activate general hooks
		 */
		parent::__construct();

		add_action( 'mm_bundles_add', [ $this, 'bundles_status_change' ], 10, 1 );
		add_action( 'mm_bundles_status_change', [ $this, 'bundles_status_change' ], 10, 1 );
	}

	/**
	 * @param array $data
	 *
	 * @return void
	 */
	public function bundles_status_change( $data ) {

		if ( isset( $data['bundle_status'], $data['bundle_id'], $data['member_id'] ) && class_exists( '\MM_Status', false ) ) {
			$status = null;

			if ( (int) $data['bundle_status'] === (int) \MM_Status::$CANCELED ) {
				$status = static::STATUS_ACCESS_REVOKED;
			} elseif ( (int) $data['bundle_status'] === (int) \MM_Status::$ACTIVE ) {
				$status = static::STATUS_ACCESS_ADDED;
			}

			if ( is_numeric( $status ) ) {
				$this->check_product_and_log_changes( $data['bundle_id'], $data['member_id'], $status );
			}
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
		$mm_applied_bundles = MM_TABLE_APPLIED_BUNDLES;
		$active_status      = (int) \MM_Status::$ACTIVE;
		$params             = [];
		foreach ( $levels as $id ) {
			$params[] = '%d';
		}

		$query = $wpdb->prepare( "SELECT DISTINCT (u.ID) FROM $wpdb->users AS u INNER JOIN $mm_applied_bundles AS m ON m.access_type_id=u.ID AND access_type='user'
            WHERE m.status = $active_status AND m.bundle_id IN (" . implode( ',', $params ) . ")", $levels );

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
		if ( defined( 'MM_TABLE_APPLIED_BUNDLES' ) ) {

			global $wpdb;

			$params = [ $user_id ];
			$levels = [];
			foreach ( $access_levels as $id ) {
				$levels[] = '%d';
				$params[] = $id;
			}

			return $wpdb->prepare( "SELECT m.apply_date FROM " . MM_TABLE_APPLIED_BUNDLES . " AS m WHERE m.access_type='user' AND m.access_type_id=%d AND m.bundle_id IN (" . implode( ',', $levels ) . ") ORDER BY m.apply_date DESC LIMIT 1", $params );
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
}
