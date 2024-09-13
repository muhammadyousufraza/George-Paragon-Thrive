<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

namespace TVA;

use TVD\Content_Sets\Rule;
use TVD\Content_Sets\Set;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Product_Migration
 *
 * @package TVA
 * @project : thrive-apprentice
 */
class Product_Migration {

	const OPTION_NAME = 'tva_use_products_logic';

	/**
	 * @var array A cache level to store courses => products relation
	 */
	public static $course_product_cache = array();

	/**
	 * @var array A cache level to store bundles => products relation
	 */
	public static $course_bundle_cache = array();

	/**
	 * @var int Used to update the order
	 */
	public static $contor = 0;

	/**
	 * Returns true if the system should do a product migration
	 *
	 * @return bool
	 */
	public static function should_migrate() {
		$is_rest = defined( 'REST_REQUEST' ) && REST_REQUEST;

		return ! \TVA_Const::$tva_during_activation && empty( get_option( static::OPTION_NAME ) ) && ! wp_doing_ajax() && ! wp_doing_cron() && ! $is_rest;
	}

	/**
	 * Begins the migration logic
	 *
	 * Migrates the logic from the courses access restriction to products
	 */
	public static function migrate() {
		if ( static::should_migrate() ) {
			update_option( static::OPTION_NAME, '1' );

			foreach ( \TVA_Bundle::get_list() as $bundle ) {
				static::migrate_bundle( $bundle );
			}

			foreach ( \TVA_Course_V2::get_items() as $course ) {
				static::migrate_course( $course );
				static::migrate_freemium( $course );
			}

			static::migrate_woo_products();

			foreach ( static::$course_product_cache as $course_id => $product_id ) {
				static::migrate_existing_orders( $product_id, $course_id );
			}

			foreach ( static::$course_bundle_cache as $data ) {
				static::migrate_existing_orders( $data['product_id'], $data['bundle_number'] );
			}
		}
	}

	/**
	 * Migrate freemium
	 *
	 * @param \TVA_Course_V2 $course
	 *
	 * @throws \Exception
	 */
	public static function migrate_freemium( $course ) {
		if ( false === $course instanceof \TVA_Course_V2 ) {
			return;
		}

		$excluded          = $course->get_excluded();
		$published_lessons = $course->get_ordered_published_lessons( false );

		foreach ( $published_lessons as $published_lesson ) {
			if ( $excluded > 0 ) {
				$published_lesson->freemium = \TVA_Const::FREEMIUM_FREE;
				$module                     = $published_lesson->get_module();
				if ( $module ) {
					$module->freemium = \TVA_Const::FREEMIUM_FREE;
					$module->save();
				}
				$published_lesson->save();
				$excluded --;
			}
		}
	}

	public static function migrate_woo_products() {

		$args = array(
			'posts_per_page' => - 1,
			'post_type'      => 'product',
			'post_status'    => array( 'publish', 'draft' ),
			'meta_query'     => array(
				'relation' => 'OR',
				array(
					'key'     => 'tva_courses',
					'compare' => 'EXISTS',
				),
				array(
					'key'     => 'tva_bundles',
					'compare' => 'EXISTS',
				),
			),
		);

		$woo_products = get_posts( $args );

		foreach ( $woo_products as $woo_product ) {
			$courses_ids = get_post_meta( $woo_product->ID, 'tva_courses', true );
			$bundles_ids = get_post_meta( $woo_product->ID, 'tva_bundles', true );

			$tva_products = [];

			if ( is_array( $courses_ids ) ) {
				foreach ( $courses_ids as $course_id ) {
					if ( ! empty( static::$course_product_cache[ $course_id ] ) ) {
						$tva_products[] = static::$course_product_cache[ $course_id ];
					}
				}
			}

			if ( is_array( $bundles_ids ) ) {
				foreach ( $bundles_ids as $bundle_id ) {
					if ( ! empty( static::$course_bundle_cache[ $bundle_id ] ) ) {
						$tva_products[] = static::$course_bundle_cache[ $bundle_id ]['product_id'];
					}
				}
			}

			if ( ! empty( $tva_products ) ) {
				update_post_meta( $woo_product->ID, 'tva_products', $tva_products );
			}
		}
	}

	/**
	 * Used to update the orders table for WOO and Manual
	 * Used for both migrate and revert
	 *
	 * @param int $new_product_id
	 * @param int $old_product_id
	 */
	public static function migrate_existing_orders( $new_product_id, $old_product_id ) {
		global $wpdb;
		$orders_table      = $wpdb->prefix . 'tva_orders';
		$order_items_table = $wpdb->prefix . 'tva_order_items';

		$sql    = "UPDATE $order_items_table SET product_id = %s WHERE ID IN ( SELECT i.ID FROM (SELECT * FROM $order_items_table) AS i 
        		INNER JOIN $orders_table AS o ON o.ID = i.order_id 
        		WHERE o.gateway IN (%s,%s,%s,%s) and i.product_id = %s )";//Fix issue regarding: Table is specified twice, both as a target for 'UPDATE' and as a separate source for data in mysql
		$params = array( $new_product_id, \TVA_Const::WOOCOMMERCE_GATEWAY, \TVA_Const::MANUAL_GATEWAY, \TVA_Const::THRIVECART_GATEWAY, 'Apprentice Bundle', $old_product_id );

		$wpdb->query( $wpdb->prepare( $sql, $params ) );
	}

	/**
	 * Migrate a single course to product logic
	 *
	 * @param \TVA_Course_V2 $course
	 */
	public static function migrate_course( $course ) {

		if ( false === $course instanceof \TVA_Course_V2 ) {
			return;
		}

		$restricted = get_term_meta( $course->get_id(), 'tva_logged_in', true );

		if ( empty( $restricted ) || (int) $restricted !== 1 ) {
			/**
			 * For free courses update the label to none
			 */
			$course->save_label_id( \TVA_Const::NO_LABEL_ID );

			/**
			 * We only create products for restricted courses
			 */
			return;
		}

		$rule = new Rule( [
			'content_type' => 'term',
			'content'      => \TVA_Const::COURSE_TAXONOMY,
			'field'        => Rule::FIELD_TITLE,
			'operator'     => Rule::OPERATOR_IS,
			'value'        => array( $course->get_id() ),
		] );

		$set = new Set( [
			'post_title'   => $course->name,
			'post_content' => array( $rule->jsonSerialize() ),
		] );

		$set_id = $set->create();

		if ( is_wp_error( $set_id ) ) {
			return;
		}

		/**
		 * Flag that is set on a product to know what product was created from migration
		 */
		update_post_meta( $set_id, 'tva_set_created_from_migration', '1' );

		$product = new Product(
			array(
				'name'  => $course->name,
				'order' => static::$contor ++,
				'rules' => $course->get_rules(),
			)
		);

		$product->access_restrictions = $course->get_access_restrictions()->admin_localize();

		$created_product = $product->save();

		if ( ! is_wp_error( $created_product ) ) {
			/**
			 * Sets the product - set relationship
			 */
			wp_set_object_terms( $set_id, $created_product->get_id(), Product::TAXONOMY_NAME );

			/**
			 * Flag to know that this product has been created from migration
			 */
			update_term_meta( $created_product->get_id(), 'tva_product_created_from_migration', $course->get_id() );

			/**
			 * Add to cache
			 */
			static::$course_product_cache[ $course->get_id() ] = $created_product->get_id();
		}
	}

	/**
	 * @param \TVA_Bundle $bundle
	 */
	public static function migrate_bundle( $bundle ) {
		$rule = new Rule( [
			'content_type' => 'term',
			'content'      => \TVA_Const::COURSE_TAXONOMY,
			'field'        => Rule::FIELD_TITLE,
			'operator'     => Rule::OPERATOR_IS,
			'value'        => array_map(
				function ( $value ) {
					return (int) $value;
				},
				array_values( $bundle->products )
			),
		] );

		$set = new Set( [
			'post_title'   => $bundle->name,
			'post_content' => array( $rule->jsonSerialize() ),
		] );

		$set_id = $set->create();

		if ( is_wp_error( $set_id ) ) {
			return;
		}

		/**
		 * Flag that is set on a product to know what product was created from migration
		 */
		update_post_meta( $set_id, 'tva_set_created_from_migration', '1' );

		$product = new Product(
			array(
				'name'  => sprintf( $bundle->name . ' (%s)', __( 'bundle', 'thrive-apprentice' ) ),
				'order' => static::$contor ++,
				'rules' => array(),
			)
		);

		$created_product = $product->save();

		if ( is_wp_error( $created_product ) ) {
			return;
		}

		/**
		 * Sets the product - set relationship
		 */
		wp_set_object_terms( $set_id, $created_product->get_id(), Product::TAXONOMY_NAME );

		/**
		 * Flag to know that this product has been created from migration
		 */
		update_term_meta( $created_product->get_id(), 'tva_product_created_from_migration', $bundle->number );

		/**
		 * Add to cache
		 */
		static::$course_bundle_cache[ $bundle->id ] = array(
			'product_id'    => $created_product->get_id(),
			'bundle_number' => $bundle->number,
		);
	}

	/**
	 * Revert the migration process
	 */
	public static function revert_migrate() {
		/**
		 * Delete all content sets that were created during the migration process
		 *
		 * @var Set $set
		 */
		foreach ( Set::get_items() as $set ) {
			if ( get_post_meta( $set->ID, 'tva_set_created_from_migration', true ) ) {
				/**
				 * Delete all sets that were created from migration
				 */
				$set->delete();
			}
		}

		/**
		 * Delete all the products that were created during the migration process
		 */
		foreach ( Product::get_items() as $product ) {
			if ( ! empty( get_term_meta( $product->get_id(), 'tva_product_created_from_migration', true ) ) ) {
				static::migrate_existing_orders( get_term_meta( $product->get_id(), 'tva_product_created_from_migration', true ), $product->get_id() );

				$product->delete();
			}
		}

		/**
		 * Revert orders by bundle name
		 */
		foreach ( \TVA_Bundle::get_list() as $bundle ) {
			static::revert_order_items_by_bundle( $bundle );
		}

		/**
		 * Revert the freemium content
		 */
		foreach ( \TVA_Course_V2::get_items() as $course ) {
			static::revert_order_items_by_course( $course );

			static::revert_freemium_migrate( $course );
		}

		/**
		 * Clear cache
		 */
		static::$course_product_cache = array();
		static::$course_bundle_cache  = array();

		delete_option( static::OPTION_NAME );
	}

	/**
	 * Last line of support -> reverts order items by course name
	 *
	 * -> When the revert functionality runs -> checks also if the course name is equal to the product_name in the order_items table
	 * -> If so -> Add the course id as product ID in the order_items table
	 * -> We do this for all gateways except SENDOWL gateway. Sendowl was not affected by the migration
	 *
	 * @param \TVA_Course_V2 $course
	 *
	 * @return void
	 */
	public static function revert_order_items_by_course( $course ) {
		if ( false === $course instanceof \TVA_Course_V2 ) {
			return;
		}

		global $wpdb;
		$orders_table      = $wpdb->prefix . 'tva_orders';
		$order_items_table = $wpdb->prefix . 'tva_order_items';

		$sql = "UPDATE $order_items_table SET product_id = %s WHERE ID IN ( SELECT i.ID FROM (SELECT * FROM $order_items_table) AS i 
        		INNER JOIN $orders_table AS o ON o.ID = i.order_id 
        		WHERE i.product_name = %s AND o.gateway IN (%s,%s,%s,%s))";

		$params = array( $course->get_id(), $course->name, \TVA_Const::WOOCOMMERCE_GATEWAY, \TVA_Const::MANUAL_GATEWAY, \TVA_Const::THRIVECART_GATEWAY, 'Apprentice Bundle' );

		$wpdb->query( $wpdb->prepare( $sql, $params ) );
	}

	/**
	 * @param \TVA_Bundle $bundle
	 *
	 * @return void
	 */
	public static function revert_order_items_by_bundle( $bundle ) {
		if ( false === $bundle instanceof \TVA_Bundle ) {
			return;
		}

		global $wpdb;
		$orders_table      = $wpdb->prefix . 'tva_orders';
		$order_items_table = $wpdb->prefix . 'tva_order_items';

		$sql    = "UPDATE $order_items_table SET product_id = %s WHERE ID IN ( SELECT i.ID FROM (SELECT * FROM $order_items_table) AS i 
        		INNER JOIN $orders_table AS o ON o.ID = i.order_id 
        		WHERE i.product_name = %s AND o.gateway IN (%s,%s,%s,%s))";
		$params = array( $bundle->number, $bundle->name, \TVA_Const::WOOCOMMERCE_GATEWAY, \TVA_Const::MANUAL_GATEWAY, \TVA_Const::THRIVECART_GATEWAY, 'Apprentice Bundle' );

		$wpdb->query( $wpdb->prepare( $sql, $params ) );
	}

	/**
	 * Revert the freemium migration process
	 *
	 * @param \TVA_Course_V2 $course
	 *
	 * @throws \Exception
	 */
	public static function revert_freemium_migrate( $course ) {

		if ( false === $course instanceof \TVA_Course_V2 ) {
			return;
		}

		$excluded          = $course->get_excluded();
		$published_lessons = $course->get_ordered_published_lessons( false );

		foreach ( $published_lessons as $published_lesson ) {
			if ( $excluded > 0 ) {
				$published_lesson->freemium = \TVA_Const::FREEMIUM_INHERIT;
				$published_lesson->save();
				$excluded --;
			}
		}
	}

	/**
	 * Returns true if there exists a product created from migration for a particular item (Course or Bundle)
	 *
	 * @param string|int $item_value
	 *
	 * @return bool
	 */
	public static function is_from_migration( $item_value ) {
		$term_query = new \WP_Term_Query( array(
			'taxonomy'   => \TVA\Product::TAXONOMY_NAME,
			'count'      => true,
			'fields'     => 'count', //We need to return a number here
			'meta_query' => array(
				'relation' => 'AND',
				array(
					'key'   => 'tva_product_created_from_migration',
					'value' => $item_value,
				),
			),
		) );

		return (int) $term_query->get_terms() === 1;
	}

	/**
	 * Validates product ID
	 * Used in Thrive Cart API calls to validate if the ID that comes from the API is from a Apprentice Product ID. If the ID is not an apprentice product ID -> it converts it
	 *
	 * @param numeric|string $product_id can be number or string (ex: course.bundle.601d2a6eed475)
	 *
	 * @return int
	 */
	public static function validate_product_id( $product_id ) {
		$product_id = is_numeric( $product_id ) ? (int) $product_id : $product_id;
		/**
		 * We need to be sure that product_id belongs to a Apprentice Product and not a course|bundle
		 */
		$term = get_term( $product_id );

		/**
		 * Backwards compatible when creating order
		 */
		if ( ( $term instanceof \WP_Term && $term->taxonomy !== \TVA\Product::TAXONOMY_NAME ) || ( is_string( $product_id ) && strpos( $product_id, 'course.bundle' ) !== false ) ) {

			/**
			 * In this case, the product ID is a course ID or a bundle
			 * We need to go through all the products and fetch the product that was created from that particular course or that particular bundle
			 * that the product ID that comes from the request points to
			 */
			foreach ( \TVA\Product::get_items() as $product ) {
				$migration_id = get_term_meta( $product->get_id(), 'tva_product_created_from_migration', true );

				if ( is_numeric( $migration_id ) ) {
					$migration_id = (int) $migration_id;
				}

				if ( ! empty( $migration_id ) && $migration_id === $product_id ) {//here can be string or integer. We do not know what comes from request
					$product_id = (int) $product->get_id();
					break;
				}
			}
		}

		return $product_id;
	}

	/**
	 * @param numeric|string $product_id
	 *
	 * @return numeric|string
	 */
	public static function get_migration_id( $product_id ) {
		return get_term_meta( $product_id, 'tva_product_created_from_migration', true );
	}
}
