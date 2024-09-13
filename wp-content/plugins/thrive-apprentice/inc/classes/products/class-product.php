<?php

namespace TVA;

use JsonSerializable;
use ReturnTypeWillChange;
use TVA\Access\Expiry\Base;
use TVA\Buy_Now\Generic;
use TVA\Drip\Campaign;
use TVA\Stripe\Hooks;
use TVA_Access_Restriction;
use TVA_Const;
use TVA_Course_V2;
use TVA_Integration;
use TVA_Manager;
use TVA_Order;
use TVA_Order_Item;
use TVA_SendOwl;
use TVA_Term_Model;
use TVD\Cache\Runtime_Cache;
use TVD\Content_Sets\Set;
use WP_Error;
use WP_Post;
use WP_Term;

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package ${NAMESPACE}
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * @property array                  rules
 * @property array                  buy_now_links
 * @property array                  access_expiry
 * @property int                    order
 * @property int                    customers_count
 * @property TVA_Access_Restriction access_restrictions
 */
class Product implements JsonSerializable {

	use Runtime_Cache;

	const TAXONOMY_NAME = 'tva_product';

	/**
	 * Cache product from set on request
	 *
	 * @var array
	 */
	public static $PRODUCT_FROM_SET_CACHE = array();

	/**
	 * @var array
	 */
	public static $GET_ITEMS_CACHE = [];

	/**
	 * @var array
	 */
	public static $GET_COURSES_CACHE = [];

	/**
	 * @var WP_Term
	 */
	protected $_term;

	/**
	 * @var array
	 */
	protected $_data = array();

	protected $_defaults = array(
		'order' => 0,
	);

	/**
	 * @param int|array|WP_Term|string $args
	 */
	public function __construct( $args ) {

		if ( is_numeric( $args ) ) {
			$term = get_term( (int) $args, static::TAXONOMY_NAME );

			$this->_term = $term ? $term : static::get_product_term_by_identifier( $args );
		} elseif ( $args instanceof WP_Term ) {
			$this->_term                  = $args;
			$this->_data['order']         = $this->order;
			$this->_data['rules']         = $this->rules;
			$this->_data['access_expiry'] = $this->access_expiry;
			$this->_data['buy_now_links'] = $this->buy_now_links;
		} elseif ( is_array( $args ) ) {
			$this->_data = wp_parse_args( $args, $this->_defaults );
		} else if ( is_string( $args ) ) {
			$term = static::get_product_term_by_identifier( $args );

			if ( $term instanceof WP_Term ) {
				$this->_term = $term;
			}
		}

		if ( ! empty( $this->_data['id'] ) && is_numeric( $this->_data['id'] ) ) {
			$term        = get_term( (int) $this->_data['id'], static::TAXONOMY_NAME );
			$this->_term = $term instanceof WP_Term ? $term : null;
		}
	}

	public function __set( $key, $value ) {
		$this->_data[ $key ] = $value;
	}

	public function __get( $key ) {
		$value = null;

		if ( isset( $this->_data[ $key ] ) ) {
			$value = $this->_data[ $key ];
		} elseif ( method_exists( $this, 'get_' . $key ) ) {
			$method_name = 'get_' . $key;
			$value       = $this->$method_name();
		}

		return $value;
	}

	/**
	 * Contains information needed to display products in dashboard
	 *
	 * @return array
	 */
	public function get_main_info() {
		return [
			'id'              => $this->get_id(),
			'name'            => $this->get_name(),
			'customers_count' => $this->customers_count,
			'sets'            => $this->get_content_sets(),
			'order'           => $this->order,
			'access_expiry'   => $this->access_expiry,
		];
	}

	#[ReturnTypeWillChange]
	public function jsonSerialize() {
		return array_merge(
			$this->get_main_info(),
			[
				'identifier'          => $this->get_identifier(),
				'term'                => $this->_term,
				'rules'               => $this->rules,
				'buy_now_links'       => $this->buy_now_links,
				'access_restrictions' => $this->get_access_restrictions()->admin_localize(),
			]
		);
	}

	/**
	 * Counts total users who have bought or have been assigned manually
	 *
	 * @return int
	 */
	public function get_customers_count() {

		if ( ! isset( $this->_data['customers_count'] ) ) {
			$this->_data['customers_count'] = $this->count_users_with_access();
		}

		return (int) $this->_data['customers_count'];
	}

	/**
	 * Retrieves an array of product IDs associated with the current object.
	 *
	 * @return int[] An array of product IDs
	 */
	public function get_product_ids() {
		$product_ids = array( $this->get_id() );

		if ( TVA_SendOwl::is_connected() ) {
			$tva_term = new TVA_Term_Model( $this->get_term() );
			if ( $tva_term->is_protected_by_sendowl() ) {
				$product_ids = array_merge( $product_ids, $tva_term->get_all_sendowl_protection_ids() );
			}
		}

		return $product_ids;
	}

	/**
	 * Returns a list of users that bought the product
	 *
	 * @return array
	 */
	public function get_customers() {
		global $wpdb;

		$product_ids = $this->get_product_ids();
		$params      = [];

		foreach ( $product_ids as $id ) {
			$params[] = '%s';
		}

		$sql = "select u.ID, i.created_at as item_created, i.product_id FROM " . $wpdb->users . " u
				inner join " . TVA_Order::get_table_name() . " o ON u.ID = o.user_id
				inner join " . TVA_Order_Item::get_table_name() . " i ON o.ID = i.order_id
				where i.product_id IN (" . implode( ',', $params ) . ")	AND o.status = 1 AND i.status = 1 GROUP BY u.id";

		return $wpdb->get_results( $wpdb->prepare( $sql, $product_ids ), ARRAY_A );
	}

	/**
	 * Returns the SQL Part needed to fetch all the users that have access to the product
	 *
	 * Used in counting all the unique users that have access to a protected course
	 *
	 * @return array
	 */
	public function get_users_that_have_access_query_part() {
		$product_ids = $this->get_product_ids();
		$params      = [];

		foreach ( $product_ids as $id ) {
			$params[] = '%s';
		}

		$sql = "ID IN (SELECT o.user_id FROM " . TVA_Order::get_table_name() . " o INNER JOIN " . TVA_Order_Item::get_table_name() . " i ON o.ID = i.order_id WHERE i.product_id IN (" . implode( ',', $params ) . ") AND o.status = 1 AND i.status = 1)";

		global $wpdb;

		$parts = [
			$wpdb->prepare( $sql, $product_ids ),
		];

		return array_merge( $parts, $this->get_users_access_query_from_integration() );
	}

	/**
	 * Fetches the query parts for users with access from integrations
	 *
	 * @return string[]
	 */
	public function get_users_access_query_from_integration() {
		$parts = [];

		foreach ( $this->get_rules() as $rule ) {
			if ( count( $rule['items'] ) > 0 ) {
				$levels = array_map( function ( $rule_item ) {
					return $rule_item['id'];
				}, $rule['items'] );

				$integration = tva_integration_manager()->get_integration( $rule['integration'] );

				if ( false === $integration instanceof TVA_Integration ) {
					continue;
				}

				$part = $integration->get_users_with_level_query_part( $levels );

				if ( ! empty( $part ) ) {
					$parts[] = $part;
				}
			}
		}

		return $parts;
	}


	/**
	 * Returns a list of user IDs with access
	 * WARNING: This is a very consuming query. It should be used with caution
	 *
	 * @return array
	 */
	public function get_users_with_access() {
		global $wpdb;

		$query = "SELECT ID FROM {$wpdb->users} WHERE ";
		$parts = $this->get_users_that_have_access_query_part();

		if ( count( $parts ) > 0 ) {
			$query = $query . implode( ' OR ', $parts );
		}

		$results = $wpdb->get_results( $query, ARRAY_A );

		return array_column( $results, 'ID' );
	}

	/**
	 * Returns a number that represents the users that have access to this product
	 *
	 * @return int
	 */
	public function count_users_with_access() {
		$count = $this->get_meta( 'tva_count_users_with_access_cache' );

		if ( is_numeric( $count ) ) {
			return $count;
		}

		global $wpdb;
		$query = "SELECT COUNT(ID) as user_nr FROM {$wpdb->users} WHERE ";
		$parts = $this->get_users_that_have_access_query_part();


		if ( count( $parts ) > 0 ) {
			$query = $query . implode( ' OR ', $parts );
		}

		$row = $wpdb->get_row( $query, ARRAY_A );

		$count = 0;
		if ( is_array( $row ) ) {
			$count = $row['user_nr'];
		}

		$this->update_meta( 'tva_count_users_with_access_cache', $count );

		return $count;
	}

	/**
	 * @return int|null
	 */
	public function get_id() {
		return $this->_term ? $this->_term->term_id : null;
	}

	/**
	 * @return string|null
	 */
	public function get_identifier() {
		if ( empty( $this->_data['identifier'] ) ) {
			$this->_data['identifier'] = $this->get_meta( 'tva_identifier' );
		}

		return $this->_data['identifier'];
	}

	/**
	 * @return WP_Term|null
	 */
	public function get_term() {
		return $this->_term ? $this->_term : null;
	}

	/**
	 * Reads the name from _term if exists or from _data
	 *
	 * @return string
	 */
	public function get_name() {

		$name = $this->_term ? $this->_term->name : '';

		if ( ! $name ) {
			$name = ! empty( $this->_data['name'] ) ? $this->_data['name'] : '';
		}

		return $name;
	}

	/**
	 * Get the access restrictions settings array
	 *
	 * @return null|TVA_Access_Restriction
	 */
	public function get_access_restrictions() {

		return tva_access_restriction_settings( $this->_term );
	}

	/**
	 * Fetches content sets from DB
	 *
	 * @param array $args
	 *
	 * @return Set[]
	 */
	public function get_content_sets( $args = array() ) {

		if ( empty( $this->_term ) ) {
			return [];
		}

		return Set::get_items( array_merge( array(
			'tax_query' => array(
				array(
					'taxonomy' => static::TAXONOMY_NAME,
					'field'    => 'term_id',
					'terms'    => $this->_term->term_id,
				),
			),
		), $args ) );
	}

	/**
	 * TODO: cache this function
	 *
	 * Loop through each content set and search for tva_courses
	 *
	 * @param bool $return_ids
	 *
	 * @return TVA_Course_V2[]|int[]
	 */
	public function get_courses( $return_ids = false ) {

		$sets    = $this->get_content_sets();
		$ids     = array();
		$courses = array();

		/** @var Set $set */
		foreach ( $sets as $set ) {
			$_ids = $set->get_tva_courses_ids();
			$ids  = array_merge( $ids, is_array( $_ids ) && ! empty( $_ids ) ? $_ids : array() );
		}

		$ids = array_unique( $ids );

		if ( $return_ids ) {
			return $ids;
		}

		foreach ( $ids as $course_id ) {
			$course = new TVA_Course_V2( $course_id );
			if ( $course->get_id() ) {
				/* make sure the course did not get deleted meanwhile */
				$courses[] = $course;
			}
		}

		return $courses;
	}

	/**
	 * @return TVA_Course_V2[]
	 */
	public function get_courses_from_cache() {


		if ( ! isset( static::$GET_COURSES_CACHE[ $this->get_id() ] ) ) {
			static::$GET_COURSES_CACHE[ $this->get_id() ] = $this->get_courses();
		}

		return static::$GET_COURSES_CACHE[ $this->get_id() ];

	}

	/**
	 * For current product gets the selected drip campaign for a course which is part of the content set
	 * - product | course | campaign
	 * - a many to many relation
	 *
	 * @param TVA_Course_V2|int $course
	 *
	 * @return null|Campaign
	 */
	public function get_drip_campaign_for_course( $course ) {
		$course_id = $course instanceof TVA_Course_V2 ? $course->get_id() : $course;
		$campaign  = null;
		$args      = array(
			'posts_per_page' => - 1,
			'post_type'      => Campaign::POST_TYPE,
			'post_status'    => array( 'publish', 'draft' ),
			'tax_query'      => array(
				array(
					'taxonomy' => TVA_Const::COURSE_TAXONOMY,
					'field'    => 'term_id',
					'terms'    => $course_id,
				),
				array(
					'taxonomy' => static::TAXONOMY_NAME,
					'field'    => 'term_id',
					'terms'    => $this->get_id(),
				),
			),
		);

		$list = TVA_Manager::get_posts_from_cache( $args );

		$current = current( $list );

		if ( true === $current instanceof WP_Post ) {
			$campaign = new Campaign( $current );
		}

		return $campaign;
	}

	/**
	 * Get all applied drip campaigns
	 *
	 * @return Campaign[] associative array of campaign_id => campaign_instance
	 */
	public function get_drip_campaigns() {
		$campaigns = [];
		foreach ( $this->get_courses() as $course ) {
			$campaign = $this->get_drip_campaign_for_course( $course );
			if ( $campaign && $campaign->ID ) {
				$campaigns[ $campaign->ID ] = $campaign;
			}
		}

		return $campaigns;
	}

	/**
	 * Assign a tva_drip_campaign post, which is already assigned/linked to a course taxonomy, to product tax too
	 * - if there is another course drip campaign assigned to current product then it is removed first
	 * - in this way we make sure that only one tva_drip_campaign is assigned to one course and max one product
	 *
	 * @param Campaign|null     $campaign          if empty, it will just un-assign the current campaign associated with the course
	 * @param int|TVA_Course_V2 $course
	 * @param bool              $reschedule_events whether to reschedule the events for the removed campaign
	 *
	 * @return bool
	 */
	public function assign_drip_campaign( $campaign, $course, $reschedule_events = true ) {

		$existing = $this->get_drip_campaign_for_course( $course );

		if ( $existing instanceof Campaign ) {
			wp_remove_object_terms( $existing->ID, $this->get_id(), static::TAXONOMY_NAME );
			if ( $reschedule_events ) {
				$existing->reschedule_events();
			}
		}

		$result = true;
		if ( $campaign && $campaign->ID ) {
			$result = wp_add_object_terms( $campaign->ID, $this->get_id(), static::TAXONOMY_NAME );
			$campaign->reschedule_events();
		}

		/* reset the cache to make sure the following calls return correct results */
		TVA_Manager::$MANAGER_GET_POSTS_CACHE = [];

		return ! is_wp_error( $result );
	}

	public function get_order() {
		if ( ! isset( $this->_data['order'] ) ) {
			$this->_data['order'] = (int) $this->get_meta( 'tva_order' );
		}

		return $this->_data['order'];
	}

	public function get_rules() {
		if ( ! isset( $this->_data['rules'] ) ) {
			$this->_data['rules'] = array_filter( (array) $this->get_meta( 'tva_rules' ) );
			if ( ! empty( $this->_data['rules'] ) ) {
				$this->_data['rules'] = array_filter(
					$this->_data['rules'],
					function ( $rule ) {
						/**
						 * filter out the thrivecart rules
						 */
						return ! empty( $rule['integration'] ) && 'thrivecart' !== $rule['integration'];
					}
				);
				/**
				 * Make sure this is always a numerical indexed array with keys starting from zero. otherwise it's treated as an object in js and all hell breaks loose
				 */
				$this->_data['rules'] = array_values( $this->_data['rules'] );
			}
		}

		return $this->_data['rules'];
	}

	public function get_buy_now_links() {
		if ( ! isset( $this->_data['buy_now_links'] ) ) {
			$this->_data['buy_now_links'] = array_filter( (array) $this->get_meta( 'tva_buy_now_links' ) );
		}

		return $this->_data['buy_now_links'];
	}

	/**
	 * Get buy integrations which are properly set
	 *
	 * @return array
	 */
	public function get_valid_buy_integrations() {
		$links               = $this->get_buy_now_links();
		$integrations        = [];
		$integrations_labels = Generic::get_integrations();

		foreach ( $links as $link_data ) {
			$integration_instance = Generic::get_instance( $link_data );

			if ( $integration_instance instanceof Generic && $integration_instance->is_valid() ) {
				$integrations[ $link_data['integration'] ] = $integrations_labels[ $link_data['integration'] ];
			}
		}

		return $integrations;
	}

	/**
	 * Returns true if the product has access expiry enabled
	 *
	 * @return bool
	 */
	public function has_access_expiry() {
		$expiry = $this->get_access_expiry();

		if ( empty( $expiry ) || ! is_array( $expiry ) ) {
			return false;
		}

		return isset( $expiry['enabled'] ) && (int) $expiry['enabled'] === 1;
	}

	/**
	 * @param string $condition
	 *
	 * @return bool
	 */
	public function has_access_expiry_condition( $condition ) {
		if ( ! $this->has_access_expiry() ) {
			return false;
		}

		$expiry_data = $this->get_access_expiry();

		return $expiry_data['expiry']['cond'] === $condition;
	}

	/**
	 * Returns true if reminder is enabled to access expiry
	 *
	 * @return bool
	 */
	public function has_access_expiry_reminder() {
		if ( ! $this->has_access_expiry() ) {
			return false;
		}

		$expiry_data = $this->get_access_expiry();

		if ( empty( $expiry_data['reminder'] ) || empty( $expiry_data['reminder']['enabled'] ) || empty( $expiry_data['reminder']['number'] ) || empty( $expiry_data['reminder']['unit'] ) ) {
			return false;
		}

		return (int) $expiry_data['reminder']['enabled'] === 1;
	}

	/**
	 * Returns the access expiry redirect link or false
	 *
	 * @return false|string
	 */
	public function has_access_expiry_redirect() {
		if ( ! $this->has_access_expiry() ) {
			return false;
		}

		$expiry_data = $this->get_access_expiry();

		if ( ! empty( $expiry_data['redirect'] ) && ! empty( $expiry_data['redirect']['enabled'] ) ) {
			$redirect_value = $expiry_data['redirect']['value'];

			if ( is_numeric( $redirect_value ) && get_post( $redirect_value ) instanceof WP_Post ) {
				return get_permalink( $redirect_value );
			}

			return filter_var( $redirect_value, FILTER_VALIDATE_URL );

		}

		return false;
	}

	/**
	 * @return array
	 */
	public function get_access_expiry() {
		if ( ! isset( $this->_data['access_expiry'] ) ) {
			$this->_data['access_expiry'] = array_filter( (array) $this->get_meta( 'access_expiry' ) );
		}

		if ( ! empty( $this->_data['access_expiry'] ) && ! empty( $this->_data['access_expiry']['redirect'] ) && is_numeric( $this->_data['access_expiry']['redirect']['value'] ) ) {
			$this->_data['access_expiry']['redirect']['title'] = get_post( $this->_data['access_expiry']['redirect']['value'] )->post_title;
		}

		return $this->_data['access_expiry'];
	}

	/**
	 * Returns the rules integration IDs of integration that is given as a parameter
	 *
	 * @param string      $integration
	 * @param array|false $rules
	 *
	 * @return array
	 */
	public function get_ids_of_integration( $integration, $rules = false ) {
		$rules_by_integration = current( $this->get_rules_by_integration( $integration, $rules ) );

		$ids = [];

		if ( ! empty( $rules_by_integration ) ) {
			$ids = array_column( $rules_by_integration['items'], 'id' );
		}

		return $ids;
	}

	/**
	 * Returns product rules by integration
	 *
	 * @param string $integration
	 *
	 * @return array
	 */
	public function get_rules_by_integration( $integration, $rules = false ) {
		if ( $rules === false ) {
			$rules = $this->get_rules();
		}

		if ( empty( $rules ) || ! is_array( $rules ) ) {
			return [];
		}

		return array_filter(
			$rules,
			static function ( $rule ) use ( $integration ) {
				return ! empty( $rule ) && ! empty( $rule['integration'] ) && $rule['integration'] === $integration;
			}
		);
	}

	/**
	 * @param $integration
	 * @param $level
	 *
	 * @return bool
	 */
	public function is_protected_by( $integration, $level ) {
		$rule      = current( $this->get_rules_by_integration( $integration ) );
		$protected = false;
		foreach ( (array) $rule['items'] as $item ) {

			if ( ! empty( $item['id'] ) && (string) $item['id'] === (string) $level ) {
				$protected = true;
				break;
			}
		}

		return $protected;
	}

	public function check_expiry_modified( $new_expiry, $old_expiry ) {
		$expiry_template = [
			'enabled'  => 0,
			'expiry'   => [
				'cond'          => '',
				'cond_purchase' => [
					'number' => '7',
					'unit'   => 'd',
				],
				'cond_datetime' => '',
			],
			'reminder' => [
				'enabled' => '',
				'number'  => '',
				'unit'    => '',
			],
			'redirect' => [
				'enabled' => '',
				'value'   => '',
				'title'   => '',
			],
		];

		if ( ! is_array( $new_expiry ) || empty( $new_expiry ) ) {
			$new_expiry = $expiry_template;
		}

		if ( ! is_array( $old_expiry ) || empty( $old_expiry ) ) {
			$old_expiry = $expiry_template;
		}

		/**
		 * We need to execute the logic only if the expiry settings have been modified
		 */
		$modified = ( md5( serialize( $new_expiry ) ) !== md5( serialize( $old_expiry ) ) );
		if ( $modified ) {

			if ( (int) $new_expiry['enabled'] !== $old_expiry['enabled'] ) {
				//Start or stop all
				$settings  = $this->parse_expiry_settings( $new_expiry['expiry'] );
				$condition = $new_expiry['expiry']['cond'];

				Base::factory( $this, $condition )->toggle( (int) $new_expiry['enabled'], $settings );
			} else {
				$expiry_modified = ( md5( serialize( $new_expiry['expiry'] ) ) !== md5( serialize( $old_expiry['expiry'] ) ) );

				if ( $expiry_modified ) {
					//Remove old expiry
					Base::factory( $this, $old_expiry['expiry']['cond'] )->remove( $this->parse_expiry_settings( $old_expiry['expiry'] ) );

					//Add the new expiry
					Base::factory( $this, $new_expiry['expiry']['cond'] )->add( $this->parse_expiry_settings( $new_expiry['expiry'] ) );
				} else {
					/**
					 * Captures the case when access expiry condition has not been modified but reminder settings have been modified
					 */
					$reminder_modified = ( md5( serialize( $new_expiry['reminder'] ) ) !== md5( serialize( $old_expiry['reminder'] ) ) );

					if ( $reminder_modified ) {
						Base::factory( $this, $new_expiry['expiry']['cond'] )->reminder_modified( $this->parse_expiry_settings( $new_expiry['expiry'] ), $old_expiry['reminder'], $new_expiry['reminder'] );
					}
				}
			}
		}

		return $modified;
	}

	private function parse_expiry_settings( $expiry = [] ) {
		$condition = $expiry['cond'];
		$settings  = null;

		switch ( $condition ) {
			case 'after_purchase':
				$settings = $expiry['cond_purchase'];
				break;
			case 'specific_time':
				$settings = $expiry['cond_datetime'];
				break;

		}

		return $settings;
	}

	/**
	 * Check if the rules have been modified.
	 * If the rules had indeed been modified, trigger some hooks to signal this event
	 *
	 * @param array $new_rules
	 * @param array $old_rules
	 *
	 * @return boolean
	 */
	public function check_access_rules_modified( $new_rules, $old_rules ) {
		$modified = false;

		foreach ( $new_rules as $new_rule ) {

			if ( empty( $new_rule['integration'] ) ) {
				continue;
			}

			$integration = $new_rule['integration'];

			$new_integration_rules = current( $this->get_rules_by_integration( $integration, $new_rules ) );
			$old_integration_rules = current( $this->get_rules_by_integration( $integration, $old_rules ) );

			$new_ids = [];
			if ( ! empty( $new_integration_rules ) ) {
				/**
				 * Can be empty when product access requirement is empty (new product)
				 */
				array_multisort( $new_integration_rules['items'] );

				$new_ids = array_column( $new_integration_rules['items'], 'id' );
			}

			$old_ids = [];
			if ( ! empty( $old_integration_rules ) ) {
				/**
				 * Can be empty when product access requirement is empty (new product)
				 */
				array_multisort( $old_integration_rules['items'] );

				$old_ids = array_column( $old_integration_rules['items'], 'id' );
			}


			$new_levels     = array_values( array_unique( array_diff( $new_ids, $old_ids ) ) );
			$removed_levels = array_values( array_unique( array_diff( $old_ids, $new_ids ) ) );


			if ( ! empty( $new_levels ) ) {
				/**
				 * Triggered when a new access requirement rule is added to a product
				 *
				 * Used to check and log access this might give to existing users
				 */
				do_action( 'tva_products_' . $integration . '_integration_add_access', $this, $new_levels );

				$modified = true;
			}


			if ( ! empty( $removed_levels ) ) {
				/**
				 * Triggered when an access requirement rule is removed from a product
				 *
				 * Used to check and log access this might have been thus removed for existing users
				 */
				do_action( 'tva_products_' . $integration . '_integration_removed_access', $this, $removed_levels );

				$modified = true;
			}
		}

		return $modified;
	}

	/**
	 * Gets term meta value
	 *
	 * @param string $meta
	 *
	 * @return mixed
	 */
	public function get_meta( $meta ) {
		return get_term_meta( $this->get_id(), $meta, true );
	}

	/**
	 * @param string $meta_key
	 * @param mixed  $meta_value
	 *
	 * @return void
	 */
	public function update_meta( $meta_key, $meta_value ) {
		update_term_meta( $this->get_id(), $meta_key, $meta_value );
	}

	/**
	 * Save the Product
	 *
	 * @return Product|WP_Error
	 */
	public function save() {

		$result = new WP_Error(
			'invalid_product_for_saving',
			'Invalid properties for Product instance'
		);

		if ( $this->_term ) {
			$result = wp_update_term( $this->_term->term_id, static::TAXONOMY_NAME, $this->_data );
		} elseif ( ! empty( $this->get_name() ) ) {
			$name = $this->get_name();

			/**
			 * From UI we have situations where the a term with a name that is generated on the spot may exists in our database.
			 * In this case, we need to make it different
			 */
			if ( term_exists( $name, static::TAXONOMY_NAME ) ) {
				$name .= '-' . rand( 0, 10 );
			}

			$result = wp_insert_term( $name, static::TAXONOMY_NAME, $this->_data );
		}

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		$this->_term = get_term( $result['term_id'] );

		if ( isset( $this->_data['order'] ) ) {
			update_term_meta( $this->_term->term_id, 'tva_order', $this->_data['order'] );
		}

		if ( isset( $this->_data['rules'] ) ) {
			$old_rules = $this->get_meta( 'tva_rules' );
			$new_rules = $this->_data['rules'];

			update_term_meta( $this->_term->term_id, 'tva_rules', $new_rules );

			/**
			 * Check if the rules have been modified and trigger the necessary hooks if they were
			 */
			$modified = $this->check_access_rules_modified( $new_rules, $old_rules );

			if ( $modified ) {
				/**
				 * If the access rules have been updated, we need to delete the enrolled users cache for the course
				 */
				TVA_Course_V2::delete_count_enrolled_users_cache( 0 );
				/**
				 * Delete also the cache for numbers of users with access
				 */
				static::delete_count_users_with_access_cache( $this->_term->term_id );
				/**
				 * Delete also from cache, so the system can re-calculate the count
				 */
				unset( $this->_data['customers_count'] );

				/**
				 * Count the number of protected products with Stripe integration
				 */
				Hooks::count_protected_products();
			}
		}

		if ( isset( $this->_data['buy_now_links'] ) ) {
			update_term_meta( $this->_term->term_id, 'tva_buy_now_links', $this->_data['buy_now_links'] );
		}

		if ( isset( $this->_data['access_expiry'] ) ) {
			$old_expiry = $this->get_meta( 'access_expiry' );
			$old_expiry = empty( $old_expiry ) ? [] : (array) $old_expiry;

			$new_expiry = $this->_data['access_expiry'];

			update_term_meta( $this->_term->term_id, 'access_expiry', $new_expiry );

			$this->check_expiry_modified( $new_expiry, $old_expiry );
		}

		if ( $this->access_restrictions && is_array( $this->access_restrictions ) ) {
			$this->get_access_restrictions()->set( $this->access_restrictions )->save();
		}

		return $this;
	}

	/**
	 * Create a new content set for this product
	 *
	 * @return void
	 */
	public function ensure_set() {
		$created_set = new Set( [
			'post_title' => $this->get_name(),
		] );
		$set_id      = $created_set->create();
		wp_set_object_terms( $set_id, $this->_term->term_id, static::TAXONOMY_NAME, true );
	}

	/**
	 * Update the content sets for a active product
	 *
	 * @return Product
	 */
	public function update_sets() {
		if ( ! empty( $this->_term ) ) {
			$original_campaigns = $this->get_drip_campaigns();

			foreach ( $this->get_content_sets() as $set ) {
				wp_remove_object_terms( $set->ID, $this->_term->term_id, static::TAXONOMY_NAME );
			}

			if ( isset( $this->_data['sets'] ) && is_array( $this->_data['sets'] ) ) {
				foreach ( $this->_data['sets'] as $set ) {
					if ( empty( $set['ID'] ) ) {
						$created_set = new Set( $set );
						$set['ID']   = $created_set->create();
					}

					wp_set_object_terms( $set['ID'], $this->_term->term_id, static::TAXONOMY_NAME, true );
				}
			}
			$new_campaigns = $this->get_drip_campaigns();

			/* for each drip campaign that has been added or removed, reschedule its cron events */
			$removed_campaigns = array_diff_key( $original_campaigns, $new_campaigns );
			$added_campaigns   = array_diff_key( $new_campaigns, $original_campaigns );

			foreach ( array_merge( $removed_campaigns, $added_campaigns ) as $campaign ) {
				$campaign->reschedule_events();
			}
		}

		return $this;
	}

	/**
	 * Returns light products that are used for localization
	 *
	 * @param array $args
	 *
	 * @return array
	 */
	public static function get_light_items( $args = [] ) {
		return array_map( static function ( $product ) {
			return $product->get_main_info();
		}, static::get_items( $args ) );
	}

	/**
	 * Fetches items from DB
	 *
	 * @param array   $filters
	 * @param boolean $count
	 *
	 * @return Product[]|int|WP_Term[]
	 */
	public static function get_items( $filters = array(), $count = false ) {

		$items = array();
		$terms = static::get_terms( $filters );

		if ( true === $count ) {
			return count( $terms );
		}

		if ( ! empty( $filters['return_terms'] ) ) {
			return $terms;
		}

		if ( ! is_wp_error( $terms ) ) {
			foreach ( $terms as $term ) {
				$items[] = new static( $term );
			}
		}

		return $items;
	}

	/**
	 * @param array $filters
	 *
	 * @return Product[]|WP_Term[]
	 */
	public static function get_items_from_cache( $filters = [] ) {
		$key = md5( json_encode( $filters ) );

		if ( ! isset( static::$GET_ITEMS_CACHE[ $key ] ) ) {
			static::$GET_ITEMS_CACHE[ $key ] = static::get_items( $filters );
		}

		return static::$GET_ITEMS_CACHE[ $key ];
	}

	/**
	 * @param $filters
	 *
	 * @return int[]|string|string[]|WP_Error|WP_Term[]
	 */
	public static function get_terms( $filters = [] ) {
		$defaults = array(
			'taxonomy'   => static::TAXONOMY_NAME,
			'hide_empty' => false,
			'meta_query' => array(
				'relation'         => 'AND',
				'tva_order_clause' => array(
					'key' => 'tva_order',
				),
			),
			'orderby'    => 'meta_value_num',
			'order'      => 'DESC', // backwards compat ordering
		);
		$filters  = wp_parse_args( $filters, $defaults );

		return get_terms( $filters );
	}

	/**
	 * @return WP_Error|true
	 */
	public function delete() {

		foreach ( $this->get_drip_campaigns() as $campaign ) {
			$campaign->unschedule_events( $this->get_id() );
		}

		/**
		 * Delete the content sets associated with this product if the content sets are only used in this product
		 */
		foreach ( $this->get_content_sets() as $set ) {
			$terms = get_the_terms( $set->ID, static::TAXONOMY_NAME );
			if ( is_array( $terms ) && count( $terms ) === 1 ) {
				/**
				 * If the set is only used in 1 product,
				 * it means that it is only used in this product.
				 *
				 * We can safely delete the content set
				 */
				$set->delete();
			}
		}

		$error   = new WP_Error(
			'invalid_product_for_delete',
			'Invalid product for deleting'
		);
		$deleted = wp_delete_term( $this->get_id(), static::TAXONOMY_NAME );

		if ( false === $deleted ) {
			$deleted = $error;
		}

		if ( true === $deleted ) {
			delete_term_meta( $this->get_id(), 'tva_rules' );
			delete_term_meta( $this->get_id(), 'tva_buy_now_links' );
			delete_term_meta( $this->get_id(), 'tva_order' );
			delete_term_meta( $this->get_id(), 'tva_identifier' );
			delete_term_meta( $this->get_id(), TVA_Access_Restriction::DB_KEY_NAME );
			$this->_term = null;
		}

		return $deleted;
	}

	/**
	 * Identify if the current queried object is part of a product (via a content set). If yes, it returns the TVA Product instance
	 *
	 *
	 * @return static|null the first product that matched the content set, NULL if nothing is found
	 */
	public static function get_for_request() {
		$product = static::get_from_set();

		if ( empty( $product ) ) {
			return null;
		}

		return $product;
	}

	/**
	 * Returns the products associated with a set
	 *
	 * @param int[]                   $sets
	 * @param boolean|WP_Post|WP_Term $return_all
	 *
	 * @return null|static|static[]
	 */
	public static function get_from_set( $sets = array(), $options = array(), $post_or_term = false ) {
		if ( empty( $sets ) ) {
			$sets = Set::get_for_request();
		}

		/**
		 * Allow other functionality to hook here.
		 * Returns the matched content sets from request
		 *
		 * @param array                 $sets
		 * @param false|WP_Post|WP_Term $sets
		 */
		$sets = apply_filters( 'tva_access_manager_get_content_sets_from_request', $sets, $post_or_term );

		$number = 0;

		if ( ! empty( $options['return_all'] ) ) {
			$number = 1;
		} else if ( ! empty( $options['return_all_bought'] ) ) {
			$number = 2;
		}

		$key = md5( json_encode( $sets ) ) . '_' . $number;

		if ( isset( static::$PRODUCT_FROM_SET_CACHE[ $key ] ) ) {
			return static::$PRODUCT_FROM_SET_CACHE[ $key ];
		}

		/**
		 * Returns all product terms in the order specified by the admin
		 */
		$terms = wp_get_object_terms( $sets, static::TAXONOMY_NAME, array(
			'orderby'    => 'meta_value_num',
			'order'      => 'DESC',
			'meta_query' => [
				[
					'key'  => 'tva_order',
					'type' => 'NUMERIC',
				],
			],
		) );

		if ( ! empty( $options['return_all'] ) ) {
			return array_map( static function ( $term ) {
				return new static( $term );
			}, $terms );
		}

		$bought_product = null;

		if ( is_user_logged_in() ) {
			/**
			 * We need to temporarily store the current product from access manager, to check the user's access to other products, then we set it back to the original
			 * we need this in order to avoid an infinite loop
			 */
			$og_product = tva_access_manager()->get_current_product();

			/**
			 * From the list of terms the content is matched upon first we need to identify the ones the user bought
			 * If there are none that the user bought we return the first one
			 */
			foreach ( $terms as $term ) {
				$product = new static ( $term );
				tva_access_manager()->set_product( $product );
				if ( tva_access_manager()->check_rules() ) {
					if ( empty( $options['return_all_bought'] ) ) {
						$bought_product = $product;
						break;
					} else {
						$bought_product[] = $product;
					}
				}
			}

			tva_access_manager()->set_product( $og_product );
		}

		$product = $bought_product ? $bought_product : reset( $terms );

		if ( $product instanceof WP_Term ) {
			$product = new static( $product );
		}

		static::$PRODUCT_FROM_SET_CACHE[ $key ] = $product;

		return static::$PRODUCT_FROM_SET_CACHE[ $key ];
	}

	/**
	 * Update the rules of a product
	 * Called outside the class context
	 *
	 * @param int   $product_id
	 * @param array $rules
	 *
	 * @return bool|int|WP_Error
	 */
	public static function update_rules( $product_id, $rules = array() ) {
		return update_term_meta( $product_id, 'tva_rules', $rules );
	}

	/**
	 * Deletes the number of users with access cache
	 * For product_id = 0, deletes all cache keys from term_meta table
	 *
	 * @param integer $product_id
	 *
	 * @return void
	 */
	public static function delete_count_users_with_access_cache( $product_id = 0 ) {
		$delete_all = $product_id === 0;

		delete_metadata( 'term', $product_id, 'tva_count_users_with_access_cache', '', $delete_all );
	}

	/**
	 * Prepares the environment for TVA\Products
	 * - registers taxonomy
	 */
	public static function init() {
		register_taxonomy(
			static::TAXONOMY_NAME,
			array( 'tva_content_set' ),
			array(
				'labels'      => array(
					'name' => 'Apprentice Products',
				),
				'description' => 'Products which group Thrive Content Sets and allows user to sell group of resources',
				'public'      => false,
			)
		);
	}

	/**
	 * @param string $integration
	 */
	public static function get_protected_products_by_integration( $integration ) {
		return static::get_from_global_cache( [ __FUNCTION__, $integration ], function () use ( $integration ) {
			$products           = static::get_items();
			$protected_products = array();
			foreach ( $products as $product ) {
				$rules = $product->get_rules();
				foreach ( $rules as $rule ) {
					if ( $rule['integration'] === $integration && ! empty( $rule['items'] ) ) {
						$protected_products[] = $product;
					}
				}
			}

			return $protected_products;
		} );
	}

	/**
	 * Returns the term of the product with the provided identifier or null
	 *
	 * @param string $identifier
	 *
	 * @return WP_Term|null
	 */
	public static function get_product_term_by_identifier( $identifier ) {
		$args = array(
			'meta_query' => array(
				array(
					'key'     => 'tva_identifier',
					'value'   => $identifier,
					'compare' => '=',
				),
			),
			'hide_empty' => false,
			'taxonomy'   => static::TAXONOMY_NAME,
		);

		$terms = get_terms( $args );

		if ( empty( $terms ) || is_wp_error( $terms ) ) {
			return null;
		}

		foreach ( $terms as $term ) {
			/**
			 * It can happen that the meta_value field in the termmeta table is case insensitive
			 * in that case if the db identifier = 'identifier' and new identifier = 'IDENTIFIER' they will be considered equal, and the user will not be able to save the identifier
			 */
			if ( get_term_meta( $term->term_id, 'tva_identifier', true ) === $identifier ) {
				return $term;
			}
		}

		return null;
	}

	/**
	 * @param string $identifier
	 *
	 * @return bool|int|WP_Error|null
	 */
	public function update_identifier( $identifier ) {
		if ( strlen( $identifier ) < 4 ) {
			return null;
		}

		return update_term_meta( $this->get_id(), 'tva_identifier', $identifier );
	}

	/**
	 * @return bool|int|WP_Error|null
	 */
	public function delete_identifier() {
		return delete_term_meta( $this->get_id(), 'tva_identifier' );
	}

	/**
	 * Filter posts array to contain only published posts
	 *
	 * @return array
	 */
	public function get_published_courses( $return_ids = false ) {
		$courses = $this->get_courses();

		$published_courses = array_filter(
			$courses,
			static function ( $course ) {
				return in_array( $course->get_status(), [ 'publish', 'hidden' ] );
			}
		);

		return $return_ids === true ? array_map( static function ( $course ) {
			return $course->get_id();
		}, $published_courses ) : $published_courses;
	}

	public function should_display_buy_now_link() {
		$access_restriction = $this->get_access_restrictions();
		$cta_display        = $access_restriction->get_applicable_settings( 'action_button_display' );

		return ! empty( $cta_display ) && ! empty( $cta_display['option'] ) && $cta_display['option'] === 'buy_action';
	}

	/**
	 * @return string
	 */
	public function get_buy_link( $provider = '' ) {
		$link  = '#';
		$links = $this->get_buy_now_links();
		/**
		 * If the provider is not specified, we need to get the applicable settings
		 */
		if ( empty( $provider ) ) {
			$access_restriction = $this->get_access_restrictions();
			$settings           = $access_restriction->get_applicable_settings( 'action_button_display' );
			$provider           = isset( $settings['buy_action']['provider'] ) ? $settings['buy_action']['provider'] : '';
		}

		if ( $provider ) {
			$integration = array_filter( $links, static function ( $link ) use ( $provider ) {
				return $link['integration'] === $provider;
			} );

			if ( count( $integration ) ) {
				$integration      = array_values( $integration );
				$integration_data = $integration[0];
				/**
				 * @var $integration_instance Generic|null
				 */
				$integration_instance = Generic::get_instance( $integration_data );

				if ( $integration_instance ) {
					$link = $integration_instance->get_url();
				}
			}
		}


		return $link;
	}
}
