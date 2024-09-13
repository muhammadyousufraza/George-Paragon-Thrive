<?php

namespace TVA\Access\Providers;

use TVA\Access\History_Table;
use TVA\Product;

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

abstract class Base {
	/**
	 * @var string
	 */
	const KEY = '';

	/**
	 * Status added value
	 * This value will be stored in tha database-status column when user will receive access to a product
	 */
	const STATUS_ACCESS_ADDED = 1;

	/**
	 * Status revoked value
	 * This value will be stored in tha database-status column when user will receive access to a product
	 */
	const STATUS_ACCESS_REVOKED = - 1;

	/**
	 * Base constructor
	 *
	 * adds dynamic hooks based on integration
	 */
	public function __construct() {
		add_action( 'tva_products_' . static::KEY . '_integration_add_access', [ $this, 'product_added_access' ], 10, 2 );

		add_action( 'tva_products_' . static::KEY . '_integration_removed_access', [ $this, 'product_removed_access' ], 10, 2 );
		add_action( 'tva_course_published', [ $this, 'course_published' ] );
	}

	/**
	 * @param Product $product
	 * @param array   $course_ids
	 * @param int     $status
	 *
	 * @return void
	 */
	public function product_course_content_modified( $product, $course_ids, $status ) {
		$access_levels = $product->get_ids_of_integration( static::KEY );
		$course_ids    = tva_filter_published_courses_ids( $course_ids );

		if ( ! empty( $access_levels ) ) {
			$user_ids = $this->get_users_with_access( $access_levels );

			$data = [];

			foreach ( $user_ids as $user_id ) {

				foreach ( $course_ids as $course_id ) {
					$data[] = [
						'user_id'    => (int) $user_id,
						'product_id' => (int) $product->get_id(),
						'course_id'  => (int) $course_id,
						'status'     => (int) $status,
						'source'     => static::KEY,
					];
				}
			}

			$this->commit_data( $data );
		}
	}

	/**
	 * Used to revoke access to
	 *
	 * @param Product  $product
	 * @param int|null $reason
	 *
	 * @return void
	 */
	public function product_revoke_access( $product, $reason = null ) {
		$access_levels = $product->get_ids_of_integration( static::KEY );
		if ( ! empty( $access_levels ) ) {

			$course_ids = $product->get_published_courses( true );

			$user_ids = $this->get_users_with_access( $access_levels );

			$data = [];

			foreach ( $user_ids as $user_id ) {

				foreach ( $course_ids as $course_id ) {
					$data[] = [
						'user_id'    => (int) $user_id,
						'product_id' => (int) $product->get_id(),
						'course_id'  => (int) $course_id,
						'status'     => - 1,
						'source'     => static::KEY,
						'reason'     => $reason,
					];
				}
			}

			$this->commit_data( $data );

		}
	}

	/**
	 * Should be extended in child classes
	 *
	 * @param Product $product
	 * @param array   $added_access_levels
	 *
	 * @return void
	 */
	public function product_added_access( $product, $added_access_levels ) {
		$this->commit_data( $this->build_user_data( $product, $added_access_levels, static::STATUS_ACCESS_ADDED ) );
	}

	/**
	 * Should be extended in child classes
	 *
	 * @param Product $product
	 * @param array   $removed_access_levels
	 *
	 * @return void
	 */
	public function product_removed_access( $product, $removed_access_levels ) {
		$this->commit_data( $this->build_user_data( $product, $removed_access_levels, static::STATUS_ACCESS_REVOKED ) );
	}

	/**
	 * Should be extended in child classes
	 *
	 * @param array $levels
	 *
	 * @return array
	 */
	public function get_users_with_access( $levels = [] ) {
		return [];
	}

	/**
	 * When a draft course is published we need to adjust the access history
	 *
	 * @return void
	 */
	public function course_published( $course ) {
		$products = $course->get_product( true );
		$data     = [];

		if ( empty( $products ) ) {
			return;
		}

		foreach ( $products as $product ) {
			$access_levels = $product->get_ids_of_integration( static::KEY );

			if ( empty( $access_levels ) ) {
				continue;
			}

			$user_ids = $this->get_users_with_access( $access_levels );

			$this->course_data( $data, $course->get_id(), $product, $user_ids, static::KEY );
		}

		$this->commit_data( $data );
	}

	/**
	 * Checks if a product exists in the system and it is protected by the level specified in the parameter
	 * Logs the changes if product is found
	 *
	 * @param int|string $level_id
	 * @param int        $user_id
	 * @param int        $status
	 *
	 * @return void
	 */
	protected function check_product_and_log_changes( $level_id, $user_id, $status ) {
		$this->commit_data( $this->build_product_data( $level_id, $user_id, $status ) );
	}

	/**
	 * @param Product $product
	 * @param int     $user_id
	 * @param int     $status
	 * @param string  $created
	 *
	 * @return array
	 */
	public function build_course_data( $product, $user_id, $status, $course_ids, &$data, $created = '' ) {
		if ( empty( $course_ids ) ) {
			$data[] = [
				'user_id'    => (int) $user_id,
				'product_id' => (int) $product->get_id(),
				'course_id'  => 'NULL',
				'status'     => (int) $status,
				'source'     => static::KEY,
				'created'    => $created,
			];
		} else {
			foreach ( $course_ids as $id_course ) {
				$data[] = [
					'user_id'    => (int) $user_id,
					'product_id' => (int) $product->get_id(),
					'course_id'  => (int) $id_course,
					'status'     => (int) $status,
					'source'     => static::KEY,
					'created'    => $created,
				];
			}
		}
	}

	/**
	 * @param int|string $level_id
	 * @param int        $user_id
	 * @param int        $status
	 *
	 * @return array
	 */
	public function build_product_data( $level_id, $user_id, $status ) {
		$products = $this->get_products_with_integration_level( $level_id );

		$data = [];
		foreach ( $products as $product ) {
			$course_ids = $product->get_published_courses( true );

			$this->build_course_data( $product, $user_id, $status, $course_ids, $data );
		}

		return $data;
	}

	/**
	 * @param Product        $product
	 * @param int[]|string[] $levels
	 * @param int            $status
	 *
	 * @return array
	 */
	public function build_user_data( $product, $levels, $status ) {
		$user_ids   = $this->get_users_with_access( $levels );
		$course_ids = $product->get_published_courses( true );

		$data = [];
		foreach ( $user_ids as $ID ) {
			$this->build_course_data( $product, $ID, $status, $course_ids, $data );
		}

		return $data;
	}

	/**
	 * @param array $data
	 *
	 * @return int|boolean
	 */
	public function commit_data( $data ) {
		return History_Table::get_instance()->insert_multiple( $data );
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
		return '0000-00-00 00:00';
	}

	/**
	 * @return false
	 */
	public static function is_active() {
		return false;
	}

	/**
	 * Returns the activation hook file
	 *
	 * @return string
	 */
	public static function get_activation_hook_file() {
		return '';
	}

	/**
	 * @param $level
	 *
	 * @return Product[]
	 */
	private function get_products_with_integration_level( $level = '' ) {
		$products = [];

		/**
		 * @var Product $product
		 */
		foreach ( \TVA\Product::get_protected_products_by_integration( static::KEY ) as $product ) {
			if ( $product->is_protected_by( static::KEY, $level ) ) {
				$products[] = $product;
			}
		}

		return $products;
	}

	/**
	 * Returns data array
	 * We also check if there already are logs for a user and if he already has access to this course, we skip him
	 *
	 * @return array
	 */
	protected function course_data( &$data, $course_id, $product, $user_ids, $key ) {
		if ( ! empty( $user_ids ) && is_array( $user_ids ) ) {
			$course_status = History_Table::get_instance()->get_number_of_entries( [
				'course_id'  => $course_id,
				'user_id'    => $user_ids,
				'product_id' => [ $product->get_id() ],
			] );

			foreach ( $user_ids as $user_id ) {
				if (
					! array_key_exists( (int) $user_id, $course_status ) ||
					( array_key_exists( (int) $user_id, $course_status ) && $course_status[ (int) $user_id ] <= 0 )
				) {
					$data[] = [
						'user_id'    => (int) $user_id,
						'product_id' => (int) $product->get_id(),
						'course_id'  => (int) $course_id,
						'status'     => 1,
						'source'     => $key,
					];
				}
			}
		}
	}
}
