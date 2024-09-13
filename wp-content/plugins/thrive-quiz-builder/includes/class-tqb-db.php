<?php
/**
 * Handles database operations
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 9/22/2016
 * Time: 5:16 PM
 */

global $tqbdb;

use TCB\inc\helpers\FormSettings;

/**
 * Encapsulates the global $wpdb object
 *
 * Class Tho_Db
 */
class TQB_Database {
	/**
	 * @var $wpdb wpdb
	 */
	protected $wpdb = null;

	/**
	 * class constructor
	 */
	public function __construct() {
		global $wpdb;

		$this->wpdb = $wpdb;
	}

	/**
	 * forward the call to the $wpdb object
	 *
	 * @param $method_name
	 * @param $args
	 *
	 * @return mixed
	 */
	public function __call( $method_name, $args ) {
		return call_user_func_array( array( $this->wpdb, $method_name ), $args );
	}

	/**
	 * unserialize fields from an array
	 *
	 * @param array $array  where to search the fields
	 * @param array $fields fields to be unserialized
	 *
	 * @return array the modified array containing the unserialized fields
	 */
	protected function _unserialize_fields( $array, $fields = array() ) {

		foreach ( $fields as $field ) {
			if ( ! isset( $array[ $field ] ) ) {
				continue;
			}
			/* the serialized fields should be trigger_config and tcb_fields */
			$array[ $field ] = empty( $array[ $field ] ) ? array() : unserialize( $array[ $field ] );
			$array[ $field ] = wp_unslash( $array[ $field ] );

			/* extra checks to ensure we'll have consistency */
			if ( ! is_array( $array[ $field ] ) ) {
				$array[ $field ] = array();
			}
		}

		return $array;
	}

	/**
	 *
	 * replace table names in form of {table_name} with the prefixed version
	 *
	 * @param $sql
	 * @param $params
	 *
	 * @return false|null|string
	 */
	public function prepare( $sql, $params ) {
		$prefix = tqb_table_name( '' );
		$sql    = preg_replace( '/\{(.+?)\}/', '`' . $prefix . '$1' . '`', $sql );

		if ( strpos( $sql, '%' ) === false ) {
			return $sql;
		}

		return $this->wpdb->prepare( $sql, $params );
	}

	/**
	 * save a variation into the database
	 *
	 * @param array $model
	 *
	 * @return int
	 */
	public function save_variation( $model = array() ) {

		$_columns = array(
			'id',
			'quiz_id',
			'date_added',
			'date_modified',
			'page_id',
			'parent_id',
			'is_control',
			'post_status',
			'post_title',
			'cache_impressions',
			'cache_optins',
			'cache_optins_conversions',
			'cache_social_shares',
			'cache_social_shares_conversions',
			'tcb_fields',
			'content',
		);
		$data     = array();
		foreach ( $_columns as $key ) {
			if ( isset( $model[ $key ] ) ) {
				$data[ $key ] = $model[ $key ];
			}
		}

		if ( ! empty( $data['tcb_fields'] ) ) {
			$data['tcb_fields'] = serialize( $data['tcb_fields'] );
		} else {
			unset( $data['tcb_fields'] );
		}

		if ( ! empty( $data['content'] ) ) {
			$data['content'] = wp_unslash( $data['content'] );
		}

		if ( ! empty( $data['id'] ) ) {
			$data['date_modified'] = date( 'Y-m-d H:i:s' );
			$update_rows           = $this->wpdb->update( tqb_table_name( 'variations' ), $data, array( 'id' => $data['id'] ) );
			if ( $update_rows !== false ) {
				return $data['id'];
			}

			return $update_rows;
		}

		$this->wpdb->insert( tqb_table_name( 'variations' ), $data );

		return $this->wpdb->insert_id;
	}

	/**
	 * Get the running test items
	 *
	 * @param array $filters
	 * @param       $return_type
	 *
	 * @return array|null|object
	 */
	public function get_test_items( $filters = array(), $return_type = ARRAY_A ) {

		$sql = 'SELECT * FROM ' . tqb_table_name( 'tests_items' ) . ' WHERE 1 ';

		$params = array();

		if ( ! empty( $filters['id'] ) ) {
			$sql .= ' AND id = %s';

			$params [] = $filters['id'];
		}

		if ( ! empty( $filters['test_id'] ) ) {
			$sql .= ' AND test_id = %d';

			$params [] = $filters['test_id'];
		}

		if ( ! empty( $filters['is_control'] ) ) {
			$sql .= ' AND is_control = %s';

			$params [] = $filters['is_control'];
		}

		if ( ! empty( $filters['is_winner'] ) ) {
			$sql .= ' AND is_winner = %s';

			$params [] = $filters['is_winner'];
		}

		if ( isset( $filters['active'] ) ) {
			$sql .= ' AND active = %d';

			$params [] = $filters['active'];
		}

		if ( ! empty( $filters['id'] ) ) {
			return $this->wpdb->get_row( $this->prepare( $sql, $params ), $return_type );
		}

		$sql    .= ' ORDER BY id ASC';
		$models = $this->wpdb->get_results( $this->prepare( $sql, $params ), $return_type );

		return $models;
	}

	/**
	 * Gets the test for checking the auto win settings
	 *
	 * @param array  $filters
	 * @param bool   $single
	 * @param string $return_type
	 *
	 * @return array|null|object|void
	 */
	public function get_tests( $filters = array(), $single = false, $return_type = ARRAY_A ) {
		$params = array();

		$query = 'SELECT tests.*, SUM(items.impressions) as impressions, SUM(items.optins_conversions) as optins_conversions, SUM(items.social_shares_conversions) as social_shares_conversions 
			FROM ' . tqb_table_name( 'tests' ) . ' AS tests 
			INNER JOIN ' . tqb_table_name( 'tests_items' ) . ' AS items ON tests.id = items.test_id 
			WHERE 1 ';

		if ( ! empty( $filters['test_id'] ) ) {
			$query    .= " AND tests.id = '%d'";
			$params[] = $filters['test_id'];
		}

		if ( isset( $filters['status'] ) ) {
			$query    .= " AND tests.status = '%d'";
			$params[] = $filters['status'];
		}

		/*Fetch only the active items*/
		$query    .= " AND items.active = '%d'";
		$params[] = 1;

		$query .= ' GROUP BY tests.id ORDER BY tests.id DESC';

		if ( $single ) {
			return $this->wpdb->get_row( $this->prepare( $query, $params ), $return_type );
		} else {
			return $this->wpdb->get_results( $this->prepare( $query, $params ), $return_type );
		}
	}

	/**
	 *
	 * Gets the quiz page variations
	 *
	 * @param array  $filters
	 * @param string $return_type
	 *
	 * @return array|null|object|void
	 */
	public function get_page_variations( $filters = array(), $return_type = ARRAY_A ) {

		$sql = 'SELECT * FROM ' . tqb_table_name( 'variations' ) . ' WHERE 1 ';

		$params = array();

		if ( ! empty( $filters['id'] ) ) {
			$sql       .= ' AND id = %s';
			$params [] = $filters['id'];
		}

		if ( ! empty( $filters['post_id'] ) ) {
			if ( is_array( $filters['post_id'] ) ) {
				$sql .= ' AND page_id IN (' . implode( ',', $filters['post_id'] ) . ')';
			} else {
				$sql       .= ' AND page_id = %d';
				$params [] = $filters['post_id'];
			}
		}

		if ( ! empty( $filters['post_status'] ) ) {
			$sql       .= ' AND post_status = %s';
			$params [] = $filters['post_status'];
		}

		/*check for parent id*/
		$sql .= ' AND parent_id = %s';
		if ( empty( $filters['parent_id'] ) ) {
			/*fetch only parent variations*/
			$params [] = 0;
		} else {
			/*For child variations*/
			$params [] = $filters['parent_id'];
		}

		/*can be 0 or 1*/
		if ( isset( $filters['is_control'] ) && is_numeric( $filters['is_control'] ) ) {
			$sql       .= ' AND is_control = %d';
			$params [] = $filters['is_control'];
		}

		$sql .= ' ORDER BY id ASC';

		if ( ( ! empty( $filters['id'] ) ) || ( ! empty( $filters['is_control'] ) ) ) {
			return $this->wpdb->get_row( $this->prepare( $sql, $params ), $return_type );
		}
		$models = $this->wpdb->get_results( $this->prepare( $sql, $params ), $return_type );

		foreach ( $models as $key => $model ) {
			if ( is_object( $model ) ) {
				$models[ $key ]->cache_optin_conversion_rate        = tqb_conversion_rate( $model->cache_impressions, $model->cache_optins_conversions );
				$models[ $key ]->cache_social_share_conversion_rate = tqb_conversion_rate( $model->cache_social_shares, $model->cache_social_shares_conversions );
				$models[ $key ]->tcb_fields                         = unserialize( $model->tcb_fields );
			} else {
				$models[ $key ]['cache_optin_conversion_rate']        = tqb_conversion_rate( $model['cache_impressions'], $model['cache_optins_conversions'] );
				$models[ $key ]['cache_social_share_conversion_rate'] = tqb_conversion_rate( $model['cache_social_shares'], $model['cache_social_shares_conversions'] );
				$models[ $key ]['tcb_fields']                         = unserialize( $model['tcb_fields'] );
			}
		}

		return $models;
	}

	/**
	 * Counts the quiz page variations
	 *
	 * @param array $filters
	 *
	 * @return null|string
	 */
	public function count_page_variations( $filters = array() ) {

		$sql    = 'SELECT COUNT(id) FROM ' . tqb_table_name( 'variations' ) . '  WHERE 1 ';
		$params = array();

		if ( ! empty( $filters['post_status'] ) ) {
			$sql       .= ' AND post_status = %s';
			$params [] = $filters['post_status'];
		}

		if ( ! empty( $filters['post_id'] ) ) {
			$sql       .= ' AND page_id = %d';
			$params [] = $filters['post_id'];
		}

		if ( ! empty( $filters['quiz_id'] ) ) {
			$sql       .= ' AND quiz_id = %d';
			$params [] = $filters['quiz_id'];
		}

		/*can be 0 or 1*/
		if ( isset( $filters['is_control'] ) && is_numeric( $filters['is_control'] ) ) {
			$sql       .= ' AND is_control = %d';
			$params [] = $filters['is_control'];
		}

		return $this->wpdb->get_var( $this->prepare( $sql, $params ) );
	}

	/**
	 * Get test according to filters
	 *
	 * @param array  $filters
	 * @param bool   $single
	 * @param string $return_type
	 *
	 * @return array|null|object|void
	 */
	public function get_test( $filters = array(), $single = false, $return_type = ARRAY_A ) {

		$params = array();
		$where  = ' 1=1 ';

		if ( ! empty( $filters['id'] ) ) {
			$params ['id'] = $filters['id'];

			$where .= 'AND `id`=%d ';
		}

		if ( ! empty( $filters['page_id'] ) ) {
			$params ['page_id'] = $filters['page_id'];

			$where .= 'AND `page_id`=%d ';
		}

		if ( isset( $filters['status'] ) ) {
			$params ['status'] = $filters['status'];

			$where .= 'AND `status`=%d ';
		}

		$sql = 'SELECT * FROM ' . tqb_table_name( 'tests' ) . ' WHERE ' . $where;

		if ( $single ) {
			return $this->wpdb->get_row( $this->prepare( $sql, $params ), $return_type );
		}

		return $this->wpdb->get_results( $this->prepare( $sql, $params ), $return_type );
	}

	/**
	 * Deletes quiz variations
	 *
	 * @param array $filters
	 *
	 * @return false|int
	 */
	public function delete_variations( $filters = array() ) {
		$params = array();

		if ( ! empty( $filters['id'] ) ) {
			$params ['id'] = $filters['id'];
		}

		if ( ! empty( $filters['quiz_id'] ) ) {
			$params ['quiz_id'] = $filters['quiz_id'];
		}

		if ( ! empty( $filters['page_id'] ) ) {
			$params ['page_id'] = $filters['page_id'];
		}

		/*can be 0 or 1*/
		if ( isset( $filters['parent_id'] ) && is_numeric( $filters['parent_id'] ) ) {
			$params ['parent_id'] = $filters['parent_id'];
		}

		if ( empty( $params ) ) {
			/* we need at least one parameter so we won't empty the table by mistake */
			return 0;
		} else {
			$this->delete_logs( $params );

			return $this->wpdb->delete( tqb_table_name( 'variations' ), $params );
		}
	}

	/**
	 * Deletes logs
	 *
	 * @param array $filters
	 *
	 * @return false|int
	 */
	public function delete_logs( $filters = array() ) {
		$params = array();

		if ( ! empty( $filters['id'] ) ) {
			$params ['variation_id'] = $filters['id'];
		}

		if ( ! empty( $filters['page_id'] ) ) {
			$params ['page_id'] = $filters['page_id'];
		}

		if ( empty( $params ) ) {
			return false;
		}

		return $this->wpdb->delete( tqb_table_name( 'event_log' ), $params );
	}

	public function delete_multiple_logs( $filters ) {

		$sql    = 'DELETE FROM {event_log} WHERE 1=1';
		$where  = '';
		$params = array();

		if ( ! empty( $filters['page_id'] ) && is_array( $filters['page_id'] ) ) {
			$where .= ' AND page_id IN (' . implode( ',', $filters['page_id'] ) . ')';
		}

		$sql .= $where;
		$sql = str_replace( "'", '', $sql );

		$sql = $this->prepare( $sql, $params );

		return $this->wpdb->query( $sql );
	}

	public function get_variation( $id ) {

		$params = array( $id );
		$where  = ' `id`=%d ';
		$sql    = 'SELECT * FROM ' . tqb_table_name( 'variations' ) . ' WHERE ' . $where;

		return $this->wpdb->get_row( $this->prepare( $sql, $params ), ARRAY_A );
	}

	/**
	 * Saves a test in the database
	 *
	 * @param $model
	 *
	 * @return bool|int
	 */
	public function save_test( $model ) {

		/* make sure that we have an array */
		if ( is_object( $model ) ) {
			$model = get_object_vars( $model );
		}

		$_columns = array(
			'id',
			'page_id',
			'date_started',
			'date_added',
			'date_completed',
			'config',
			'status',
			'conversion_goal',
			'title',
			'notes',
			'auto_win_enabled',
			'auto_win_min_conversions',
			'auto_win_min_duration',
			'auto_win_chance_original',
		);

		$data = array();
		foreach ( $_columns as $key ) {
			if ( isset( $model[ $key ] ) ) {
				$data[ $key ] = $model[ $key ];
			}
		}

		if ( ! empty( $data['id'] ) ) {
			$update_rows = $this->wpdb->update( tqb_table_name( 'tests' ), $data, array( 'id' => $data['id'] ) );

			return $update_rows !== false;
		}

		$this->wpdb->insert( tqb_table_name( 'tests' ), $data );

		return $this->wpdb->insert_id;
	}

	/**
	 * Saves a test item in the database
	 *
	 * @param $model
	 *
	 * @return bool|int
	 */

	public function save_test_item( $model ) {

		/* make sure that we have an array */
		if ( is_object( $model ) ) {
			$model = get_object_vars( $model );
		}

		$_columns = array(
			'id',
			'test_id',
			'variation_id',
			'variation_title',
			'is_control',
			'is_winner',
			'impressions',
			'optins_conversions',
			'social_shares',
			'active',
			'stopped_date',
		);

		$data = array();
		foreach ( $_columns as $key ) {
			if ( isset( $model[ $key ] ) ) {
				$data[ $key ] = $model[ $key ];
			}
		}
		unset( $model );

		if ( ! empty( $data['is_winner'] ) ) {

			$test_model         = $this->get_test( array( 'id' => $data['test_id'] ), true, ARRAY_A );
			$stop_test          = $this->stop_test( $test_model );
			$archive_variations = $this->archive_losing_variations( $test_model, $data['variation_id'] );
			$set_winner         = $this->save_variation( array( 'id' => $data['variation_id'], 'is_control' => 1 ) );

			$test                        = $this->get_test( array( 'id' => $data['test_id'] ), true, OBJECT );
			$test->url                   = admin_url( 'admin.php?page=tqb_admin_dashboard' ) . '#dashboard/test/' . $test->id;
			$test->trigger_source        = 'tqb';
			$test_item                   = $this->get_test_items( array( 'id' => $data['id'] ), OBJECT );
			$test_item->variation        = $this->get_variation( $test_item->variation_id );
			$test_item->variation['key'] = $test_item->variation['id'];
			do_action( 'tqb_split_test_ends', $test_item, $test );
		}

		if ( isset( $data['active'] ) && $data['active'] == 0 ) {
			$data['stopped_date'] = date( 'Y-m-d H:i:s' );
			$stopped              = $this->stop_test_if_no_items_left( $data );
			if ( $stopped ) {
				return true;
			}
		}

		if ( ! empty( $data['id'] ) ) {
			$update_rows = $this->wpdb->update( tqb_table_name( 'tests_items' ), $data, array( 'id' => $data['id'] ) );

			return $update_rows !== false;
		}

		$this->wpdb->insert( tqb_table_name( 'tests_items' ), $data );
		$id = $this->wpdb->insert_id;

		return $id;
	}

	/**
	 * Update test item if variation was displayed/acted upon
	 *
	 * @param $data
	 *
	 * @return bool|int
	 */
	public function update_test_item_action_counter( $data ) {
		if ( empty( $data['variation_id'] ) ) {
			return false;
		}
		$fields = '';
		$params = array();

		if ( ! empty( $data['impression'] ) ) {
			$fields .= ' impressions = impressions + 1, social_shares = social_shares + 1  ';
		}
		if ( ! empty( $data['conversion'] ) ) {
			$fields .= ' optins_conversions = optins_conversions + 1 ';
		}

		if ( ! empty( $data['social_shares_conversions'] ) ) {
			$fields .= ' social_shares_conversions = social_shares_conversions + 1 ';
		}
		$where = ' 1 ';

		$params ['active'] = 1;
		$where             .= ' AND `active`=%d ';

		if ( ! empty( $data['variation_id'] ) ) {
			$params ['variation_id'] = $data['variation_id'];
			$where                   .= ' AND `variation_id`=%d ';
		}

		if ( ! empty( $data['test_id'] ) ) {
			$params ['test_id'] = $data['test_id'];
			$where              .= ' AND `test_id`=%d ';
		}

		$sql = 'UPDATE ' . tqb_table_name( 'tests_items' ) . ' SET ' . $fields . ' WHERE ' . $where;

		return $this->wpdb->query( $this->wpdb->prepare( $sql, $params ) );
	}


	/**
	 * Update test item if variation was displayed/acted upon
	 *
	 * @param $data
	 *
	 * @return bool|int
	 */

	public function update_variation_cached_counter( $data ) {
		if ( empty( $data['variation_id'] ) ) {
			return false;
		}
		$fields = '';
		if ( ! empty( $data['impression'] ) ) {
			$fields .= ' cache_impressions = cache_impressions + 1, cache_social_shares = cache_social_shares + 1 ';
		}

		if ( ! empty( $data['conversion'] ) ) {
			$fields .= ' cache_optins_conversions = cache_optins_conversions + 1 ';
		}

		if ( ! empty( $data['social_conversion'] ) ) {
			$fields .= ' cache_social_shares_conversions = cache_social_shares_conversions + 1 ';
		}

		$where = ' `id`= %d';
		$sql   = 'UPDATE ' . tqb_table_name( 'variations' ) . ' SET ' . $fields . ' WHERE ' . $where;

		return $this->wpdb->query( $this->wpdb->prepare( $sql, array( 'id' => $data['variation_id'] ) ) );
	}

	/**
	 * Archive losing variations
	 *
	 * @param array $test_model
	 * @param array $winner_id
	 *
	 * @return false|int
	 */
	public function archive_losing_variations( $test_model, $winner_id ) {
		$test_items = $this->get_test_items( array( 'test_id' => $test_model['id'] ) );
		foreach ( $test_items as $test_item ) {
			if ( $test_item['variation_id'] != $winner_id ) {
				$variation                = $this->get_variation( $test_item['variation_id'] );
				$variation['post_status'] = 'archive';
				$variation['is_control']  = 0;

				unset( $variation['tcb_fields'] );
				$variation = $this->save_variation( $variation );
			}
		}

		return true;
	}

	/**
	 * Delete tests
	 *
	 * @param array $filters
	 *
	 * @return false|int
	 */
	public function delete_tests( $filters = array() ) {
		$params = array();

		if ( ! empty( $filters['id'] ) ) {
			$params ['id'] = $filters['id'];
		}

		if ( ! empty( $filters['page_id'] ) ) {
			$params ['page_id'] = $filters['page_id'];
		}

		if ( ! empty( $params ) ) {
			$this->delete_page_test_items( $params );

			return $this->wpdb->delete( tqb_table_name( 'tests' ), $params );
		}

		return false;
	}

	/**
	 * Delete test items belonging to page
	 *
	 * @param array $filters
	 *
	 * @return false|int
	 */
	public function delete_page_test_items( $filters = array() ) {
		if ( ! empty( $filters['id'] ) ) {
			$params ['test_id'] = $filters['id'];

			return $this->delete_test_items( $params );
		}

		if ( ! empty( $filters['page_id'] ) ) {
			$params ['page_id'] = $filters['page_id'];
			$prepared_statement = $this->wpdb->prepare( 'SELECT id FROM ' . tqb_table_name( 'tests' ) . ' WHERE  page_id = %d', $filters['page_id'] );
			$tests              = $this->wpdb->get_col( $prepared_statement );

			foreach ( $tests as $test ) {
				$params ['test_id'] = $test;
				$this->delete_test_items( $params );
			}
		}

		return true;
	}

	/**
	 * Delete test items
	 *
	 * @param array $filters
	 *
	 * @return false|int
	 */
	public function delete_test_items( $filters = array() ) {
		$params = array();

		if ( ! empty( $filters['test_id'] ) ) {
			$params ['test_id'] = $filters['test_id'];
		}

		if ( ! empty( $params ) ) {
			return $this->wpdb->delete( tqb_table_name( 'tests_items' ), $params );
		}

		return false;
	}

	/**
	 * Stop test if no items left
	 *
	 * @param array $model
	 *
	 * @return false|int
	 */
	public function stop_test_if_no_items_left( $model ) {

		$test_items = $this->get_test_items( array( 'test_id' => $model['test_id'], 'active' => 1 ) );

		if ( count( $test_items ) < 3 ) {
			foreach ( $test_items as $item ) {
				if ( $item['id'] !== $model['id'] ) {
					$this->set_winner( $item );
				}
			}
			$test = $this->get_test( array( 'id' => $model['test_id'] ), true );
			$this->stop_test( $test );

			return true;
		}

		return false;
	}

	/**
	 * Stop test
	 *
	 * @param array $test
	 *
	 * @return false|int
	 */
	public function stop_test( $test ) {
		$test['status']         = 0;
		$test['date_completed'] = date( 'Y-m-d H:i:s' );

		return $this->save_test( $test );
	}

	/**
	 * Set winner
	 *
	 * @param array $item
	 *
	 * @return false|int
	 */
	public function set_winner( $item ) {

		$item['is_winner'] = 1;

		return $this->save_test_item( $item );
	}

	/**
	 * Returns a count of event_types from a group in a time period
	 *
	 * @param $filter Array of filters for the result
	 *
	 * @return Array with number of conversions per group_id in a period of time
	 */
	public function get_report_data_count_event_type( $filter ) {
		$date_interval = '';
		switch ( $filter['interval'] ) {
			case 'month':
				$date_interval = 'CONCAT(MONTHNAME(`log`.`date`)," ", YEAR(`log`.`date`)) as date_interval';
				break;
			case 'week':
				$year          = 'IF( WEEKOFYEAR(`log`.`date`) = 1 AND MONTH(`log`.`date`) = 12, 1 + YEAR(`log`.`date`), YEAR(`log`.`date`) )';
				$date_interval = "CONCAT('Week ', WEEKOFYEAR(`log`.`date`), ', ', {$year}) as date_interval";
				break;
			case 'day':
				$date_interval = 'DATE(`log`.`date`) as date_interval';
				break;
		}

		$sql = 'SELECT IFNULL(COUNT( DISTINCT log.id ), 0) AS log_count, event_type, log.' . $filter['data_group'] . ' AS data_group, ' . $date_interval;

		if ( ! empty( $filter['unique_email'] ) && $filter['unique_email'] == 1 ) {
			/* count if this email is added for the first time. if so, this is a lead, else it's just a simple conversion */
			$sql .= ', SUM( IF( t_log.id IS NOT NULL , 1, 0) ) AS leads ';
		}

		$sql .= ' FROM ' . tqb_table_name( 'event_log' ) . ' AS `log` ';

		if ( ! empty( $filter['unique_email'] ) && $filter['unique_email'] == 1 ) {
			/* t_logs - temporary select to see if an email is added for the first time or not */
			$sql .= ' LEFT JOIN (SELECT user, MIN(id) AS id FROM ' . tqb_table_name( 'event_log' ) . ' GROUP BY user) AS t_log ON log.user=t_log.user AND log.id=t_log.id ';
		}

		$sql .= '  WHERE 1 ';

		$params = array();

		if ( ! empty( $filter['event_type'] ) ) {
			$sql       .= 'AND `event_type` = %d ';
			$params [] = $filter['event_type'];
		}

		if ( ! empty( $filter['variation_id'] ) ) {
			$sql       .= 'AND `variation_id` = %d ';
			$params [] = $filter['variation_id'];
		}

		if ( ! empty( $filter['conversion_goal'] ) ) {
			if ( $filter['conversion_goal'] === Thrive_Quiz_Builder::CONVERSION_GOAL_SOCIAL ) {
				$sql .= 'AND `social_share` = 1 ';
			} else {
				$sql .= 'AND `optin` = 1 ';
			}
		}

		if ( ! empty( $filter['page_id'] ) ) {
			$sql       .= 'AND `page_id` = %d ';
			$params [] = $filter['page_id'];
		}

		if ( ! empty( $filter['start_date'] ) && ! empty( $filter['end_date'] ) ) {
			$timezone_diff = current_time( 'timestamp' ) - time();

			$sql       .= 'AND `date` BETWEEN %s AND %s ';
			$params [] = $filter['start_date'];
			$params [] = date( 'Y-m-d H:i:s', ( strtotime( '+1 day', strtotime( $filter['end_date'] ) - 1 ) + $timezone_diff ) );
		}

		if ( ! empty( $filter['group_by'] ) && count( $filter['group_by'] ) > 0 ) {
			$sql .= 'GROUP BY ' . implode( ', ', $filter['group_by'] );
		}

		$sql .= ' ORDER BY `log`.`date` DESC';

		return $this->wpdb->get_results( $this->prepare( $sql, $params ) );
	}

	public function create_event_log_entry( $model ) {
		$_columns = array(
			'date',
			'event_type',
			'variation_id',
			'page_id',
			'post_id',
			'user_unique',
			'optin',
			'social_share',
			'duplicate',
		);
		$data     = array();
		foreach ( $_columns as $key ) {
			if ( isset( $model[ $key ] ) ) {
				$data[ $key ] = $model[ $key ];
			}
		}
		unset( $model );

		$params = array( 'user_unique' => $data['user_unique'], 'event_type' => $data['event_type'], 'page_id' => $data['page_id'] );
		$where  = ' AND  `user_unique`=%s AND `event_type`=%d AND `page_id`=%d';
		$sql    = 'SELECT * FROM ' . tqb_table_name( 'event_log' ) . " WHERE 1 {$where}";

		$event_log = $this->wpdb->get_row( $this->prepare( $sql, $params ), ARRAY_A );
		if ( ! empty( $event_log ) ) {
			$update_row = $this->wpdb->update( tqb_table_name( 'event_log' ), $data, array( 'id' => $event_log['id'] ) );

			return $event_log;
		}

		return $this->wpdb->insert( tqb_table_name( 'event_log' ), $data );
	}

	public function get_quiz_user( $unique, $quiz_id ) {

		$params = array( 'random_identifier' => $unique, 'quiz_id' => $quiz_id );
		$where  = ' AND  `random_identifier`=%s AND `quiz_id`=%d';
		$sql    = 'SELECT * FROM ' . tqb_table_name( 'users' ) . " WHERE 1 {$where}";

		return $this->wpdb->get_row( $this->prepare( $sql, $params ), ARRAY_A );
	}


	/**
	 * save a quiz user into the database
	 *
	 * @param array $model
	 *
	 * @return int
	 */
	public function save_quiz_user( $model = array() ) {

		$_columns = array(
			'id',
			'quiz_id',
			'random_identifier',
			'social_badge_link',
			'email',
			'points',
			'quiz_id',
			'completed_quiz',
			'ignore_user',
			'wp_user_id',
			'object_id',
		);
		$data     = array();
		foreach ( $_columns as $key ) {
			if ( isset( $model[ $key ] ) ) {
				$data[ $key ] = $model[ $key ];
			}
		}

		if ( ! isset( $data['wp_user_id'] ) && is_user_logged_in() ) {
			$data['wp_user_id'] = get_current_user_id();
		}

		if ( ! empty( $data['completed_quiz'] ) ) {
			$data['date_finished'] = date( 'Y-m-d H:i:s' );
		}

		if ( ! empty( $data['id'] ) ) {
			$update_rows = $this->wpdb->update( tqb_table_name( 'users' ), $data, array( 'id' => $data['id'] ) );
			if ( $update_rows !== false ) {
				return $data['id'];
			}

			return $update_rows;
		}
		$data['date_started'] = date( 'Y-m-d H:i:s' );
		$this->wpdb->insert( tqb_table_name( 'users' ), $data );
		$user_id = $this->wpdb->insert_id;

		return $user_id;
	}

	/**
	 * save a quiz user's answer
	 *
	 * @param array $model
	 *
	 * @return int
	 */
	public function save_user_answer( $model = array() ) {

		$_columns = array(
			'id',
			'quiz_id',
			'user_id',
			'answer_id',
			'question_id',
			'answer_text',
		);
		$data     = array();
		foreach ( $_columns as $key ) {
			if ( isset( $model[ $key ] ) ) {
				$data[ $key ] = $model[ $key ];
			}
		}
		unset( $model );

		if ( ! empty( $data['id'] ) ) {
			$update_rows = $this->wpdb->update( tqb_table_name( 'user_answers' ), $data, array( 'id' => $data['id'] ) );
			if ( $update_rows !== false ) {
				return $data['id'];
			}

			return $update_rows;
		}

		$this->wpdb->insert( tqb_table_name( 'user_answers' ), $data );
		$answer_id = $this->wpdb->insert_id;

		return $answer_id;
	}

	/**
	 * generate dummy data for tests
	 *
	 * @return false|int
	 */
	public function generate_dummy_data( $test_id, $entry_count, $min_date, $max_date ) {
		$test_items = $this->get_test_items( array( 'test_id' => $test_id ) );
		$test       = $this->get_test( array( 'id' => $test_id ), true );
		for ( $i = 1; $i <= $entry_count; $i ++ ) {
			$random = rand( 1, ( count( $test_items ) ) );

			$data['date']         = $this->rand_date( $min_date, $max_date );
			$data['event_type']   = ( rand( 1, 3 ) % 2 ) == 0 ? 2 : 1;
			$data['variation_id'] = $test_items[ $random - 1 ]['variation_id'];
			$data['user']         = 'dummy@dummy.dumb';
			$data['page_id']      = $test['page_id'];

			$this->wpdb->insert( tqb_table_name( 'event_log' ), $data );

			$field_type = $data['event_type'] == 1 ? 'impressions' : 'optins_conversions';
			$test_items[ $random - 1 ][ $field_type ] ++;
			$this->wpdb->update(
				tqb_table_name( 'tests_items' ),
				array( $field_type => ( $test_items[ $random - 1 ][ $field_type ] ) ),
				array(
					'id' => $test_items[ $random - 1 ]['id'],
				)
			);
		}
	}

	public function rand_date( $min_date, $max_date ) {

		$min_epoch = strtotime( $min_date );
		$max_epoch = strtotime( $max_date );

		$rand_epoch = rand( $min_epoch, $max_epoch );

		return date( 'Y-m-d H:i:s', $rand_epoch );
	}

	/**
	 * Delete all the results from DB based on quiz_id
	 *
	 * @param array $filters
	 *
	 * @return false|int
	 */
	public function delete_quiz_results( $filters = array() ) {
		return $this->wpdb->delete( tqb_table_name( 'results' ), $filters );
	}

	/**
	 * Delete quiz users
	 *
	 * @param array $filters
	 *
	 * @return false|int
	 */
	public function delete_quiz_users( $filters = array() ) {
		$params = array();

		if ( ! empty( $filters['quiz_id'] ) ) {
			$params ['quiz_id'] = $filters['quiz_id'];
		}

		if ( ! empty( $params ) ) {
			return $this->wpdb->delete( tqb_table_name( 'users' ), $filters );
		}

		return false;
	}

	/**
	 * Deletes user answers
	 *
	 * @param array $filters
	 *
	 * @return bool|false|int
	 */
	public function delete_user_answers( $filters = array() ) {
		$params = array();

		if ( ! empty( $filters['quiz_id'] ) ) {
			$params ['quiz_id'] = $filters['quiz_id'];
		}

		if ( ! empty( $filters['user_id'] ) ) {
			$params ['user_id'] = $filters['user_id'];
		}

		if ( ! empty( $filters['question_id'] ) ) {
			$params ['question_id'] = $filters['question_id'];
		}

		if ( ! empty( $params ) ) {
			return $this->wpdb->delete( tqb_table_name( 'user_answers' ), $params );
		}

		return false;
	}

	/**
	 * Insert results into DB
	 *
	 * @param int   $quiz_id
	 * @param array $results
	 *
	 * @return array
	 */
	public function save_quiz_results( $quiz_id, $results ) {
		$return = array();

		foreach ( $results as $result ) {
			if ( empty( $result['id'] ) ) {
				$inserted = $this->insert_new_quiz_result( $quiz_id, $result['text'] );
			} else {
				$this->wpdb->update( tqb_table_name( 'results' ), array( 'text' => $result['text'] ), array( 'id' => $result['id'] ) );
				$inserted = $result['id'];
			}

			$return[] = array(
				'id'      => $inserted,
				'quiz_id' => $quiz_id,
				'text'    => $result['text'],
			);
		}

		return $return;
	}

	/**
	 * Add new Result for a Quiz
	 *
	 * @param int    $quiz_id
	 * @param string $result
	 *
	 * @return int new result id
	 */
	public function insert_new_quiz_result( $quiz_id, $result ) {

		$this->wpdb->insert( tqb_table_name( 'results' ), array(
			'quiz_id' => $quiz_id,
			'text'    => $result,
		) );

		return $this->wpdb->insert_id;
	}

	/**
	 * Get and array with quiz results from DB
	 *
	 * @param $quiz_id
	 *
	 * @return array|null
	 */
	public function get_quiz_results( $quiz_id ) {

		$where = ' WHERE quiz_id = %d';

		$params['quiz_id'] = $quiz_id;

		$sql = 'SELECT * FROM ' . tqb_table_name( 'results' ) . $where . ' ORDER BY id';
		$sql = $this->prepare( $sql, $params );

		return $this->wpdb->get_results( $sql, ARRAY_A );
	}

	/**
	 * Get and array with quiz results from DB
	 *
	 * @param $result_id
	 *
	 * @return array|null
	 */
	public function get_quiz_results_single( $result_id ) {

		$where = ' WHERE id = %d';

		$params['id'] = $result_id;

		$sql = 'SELECT * FROM ' . tqb_table_name( 'results' ) . $where;
		$sql = $this->prepare( $sql, $params );

		return $this->wpdb->get_row( $sql, ARRAY_A );
	}

	/**
	 * Get explicit result
	 *
	 * @param $points
	 *
	 * @return int|string
	 */
	public function get_explicit_result( $points ) {

		if ( ! empty( $points['result_id'] ) ) {
			$result = $this->get_quiz_results_single( $points['result_id'] );
			if ( ! empty( $result['text'] ) ) {
				return $result['text'];
			}
		}
		if ( isset( $points['max_points'] ) && isset( $points['min_points'] ) ) {
			$range = $points['max_points'] - $points['min_points'];
			if ( ! $range ) {
				$result_percent = 100;
			} else {
				$result_percent = max( ( (int) $points['user_points'] - $points['min_points'] ), 0 ) * 100 / $range;
			}

			if ( isset( $points['quiz_completed'] ) && ! $points['quiz_completed'] ) {
				return 'incomplete';
			}

			return ( round( $result_percent, 2 ) . $points['extra'] );
		}

		if ( isset( $points['quiz_type'] ) && $points['quiz_type'] === Thrive_Quiz_Builder::QUIZ_TYPE_RIGHT_WRONG ) {
			$processed = isset( $points['total_questions'] ) && isset( $points['total_valid_questions'] ) ? $points['total_valid_questions'] . '/' . $points['total_questions'] : '';

			return ! empty( $processed ) ? $processed : $points['user_points'];
		}

		return $points['user_points'];
	}

	/**
	 * Get completed quiz count from DB
	 *
	 * @param       $quiz_id
	 * @param array $filters
	 *
	 * @return string|null
	 */
	public function get_completed_quiz_count( $quiz_id, $filters = [] ) {
		$where = ' WHERE quiz_id = %d AND completed_quiz = 1 AND ignore_user IS NULL ';

		if ( isset( $filters['since']['date'] ) ) {
			$where .= " AND date_started > '" . $this->wordpress_to_server_date( $filters['since']['date'] ) . "'";
		}

		if ( ! empty( $filters['location'] ) ) {
			$where .= ' AND object_id = ' . esc_sql( $filters['location'] );
		}

		$params['quiz_id'] = $quiz_id;
		$sql               = 'SELECT COUNT(*) FROM ' . tqb_table_name( 'users' ) . $where;
		$sql               = $this->prepare( $sql, $params );

		return $this->wpdb->get_var( $sql );
	}

	public function wordpress_to_server_date( $date ) {

		$timezone_diff = current_time( 'timestamp' ) - time();
		$date          = date( 'Y-m-d H:i:s', ( strtotime( $date ) - $timezone_diff ) );

		return $date;
	}

	/**
	 * Build the sql for selecting data for the flow report based on the filters
	 *
	 * @param        $page_id
	 * @param array  $filters
	 * @param string $where
	 *
	 * @return false|string|null
	 */
	private function build_sql_for_flow_related_data( $page_id, $filters, $where ) {
		$join = '';

		if ( isset( $filters['since']['date'] ) ) {
			$where .= " AND date > '" . $this->wordpress_to_server_date( $filters['since']['date'] ) . "'";
		}

		if ( ! empty( $filters['location'] ) && empty ( $filters['no_splash'] ) ) {
			$join  = ' INNER JOIN ' . tqb_table_name( 'users' ) . ' AS users ON event_log.user_unique = users.random_identifier';
			$where .= ' AND users.object_id = ' . esc_sql( $filters['location'] );
		} else if ( ! empty( $filters['location'] ) && ! empty ( $filters['no_splash'] ) ) {
			/**
			 * if there is no splash page we have to count all the impressions on a qna page and display it for each location
			 */
			$where .= ' AND event_log.post_id = ' . esc_sql( $filters['location'] );
		}

		$where .= ' GROUP BY event_type';

		$params['page_id'] = $page_id;
		$sql               = 'SELECT IFNULL(COUNT(*), 0) as count, event_type FROM ' . tqb_table_name( 'event_log' ) . ' AS event_log' . $join . $where;

		return $this->prepare( $sql, $params );
	}

	/**
	 * Get flow data from DB
	 *
	 * @param $page_id
	 * @param $filters
	 *
	 * @return array|null
	 */
	public function get_flow_data( $page_id, $filters ) {
		$where = ' WHERE page_id = %d ';

		$sql = $this->build_sql_for_flow_related_data( $page_id, $filters, $where );

		$result = $this->wpdb->get_results( $sql );

		$standard = array(
			Thrive_Quiz_Builder::TQB_IMPRESSION,
			Thrive_Quiz_Builder::TQB_CONVERSION,
			Thrive_Quiz_Builder::TQB_SKIP_OPTIN,
		);
		$data     = array();
		foreach ( $standard as $event_type ) {
			$data[ $event_type ] = 0;
			foreach ( $result as $event ) {
				if ( $event->event_type == $event_type ) {
					$data[ $event_type ] = $event->count;
				}
			}
		}

		return $data;
	}

	/**
	 * Get flow data from DB
	 *
	 * @param $page_id
	 * @param $filters
	 *
	 * @return array|null
	 */
	public function get_flow_splash_impressions( $page_id, $filters ) {
		$where = ' WHERE page_id = %d ';

		if ( isset( $filters['since']['date'] ) ) {
			$where .= " AND date > '" . $this->wordpress_to_server_date( $filters['since']['date'] ) . "'";
		}

		if ( ! empty( $filters['location'] ) ) {
			$where .= ' AND post_id = ' . esc_sql( $filters['location'] );
		}

		$where .= ' AND event_type = 1';

		$params['page_id'] = $page_id;
		$sql               = 'SELECT IFNULL(COUNT(*), 0) as count, event_type FROM ' . tqb_table_name( 'event_log' ) . ' AS event_log' . $where;

		$sql = $this->prepare( $sql, $params );

		$result                                      = $this->wpdb->get_results( $sql );
		$data                                        = array();
		$data[ Thrive_Quiz_Builder::TQB_IMPRESSION ] = $result[0]->count;

		return $data;
	}

	/**
	 * Get page subscribers
	 *
	 * @param       $page_id
	 * @param array $filters
	 *
	 * @return array|null
	 */
	public function get_page_subscribers( $page_id, $filters = [] ) {
		$where = ' WHERE page_id = %d AND event_type = 2 AND optin = 1 ';

		$sql = $this->build_sql_for_flow_related_data( $page_id, $filters, $where );

		return $this->wpdb->get_var( $sql );
	}

	/**
	 * Get social shares for results page
	 *
	 * @param $page_id
	 * @param $filters
	 *
	 * @return array|null
	 */
	public function get_page_social_shares( $page_id, $filters ) {
		$where = ' WHERE page_id = %d AND event_type = 2 AND social_share = 1 ';

		$sql = $this->build_sql_for_flow_related_data( $page_id, $filters, $where );

		return $this->wpdb->get_var( $sql );
	}

	/**
	 * Get quiz social share count from DB
	 *
	 * @param $quiz_id
	 *
	 * @return array|null
	 */
	public function get_quiz_social_shares_count( $quiz_id ) {

		$results_page = get_posts( array( 'post_parent' => $quiz_id, 'post_type' => Thrive_Quiz_Builder::QUIZ_STRUCTURE_ITEM_RESULTS ) );
		if ( empty( $results_page[0] ) ) {
			return 0;
		}

		$where = ' WHERE page_id = %d AND social_share = 1';

		$params['page_id'] = $results_page[0]->ID;
		$sql               = 'SELECT COUNT(*) FROM ' . tqb_table_name( 'event_log' ) . $where;
		$sql               = $this->prepare( $sql, $params );

		return $this->wpdb->get_var( $sql );
	}

	/**
	 * Get total quiz users count from DB
	 *
	 * @param $quiz_id
	 * @param $completed_quiz
	 *
	 * @return array|null
	 */
	public function get_quiz_users_count( $quiz_id, $completed_quiz = false ) {
		$where = ' WHERE quiz_id = %d AND ignore_user IS NULL ';

		if ( $completed_quiz ) {
			$where .= 'AND completed_quiz=1 ';
		}

		$params['quiz_id'] = $quiz_id;
		$sql               = 'SELECT COUNT(*) FROM ' . tqb_table_name( 'users' ) . $where;
		$sql               = $this->prepare( $sql, $params );
		$this->wpdb->get_var( $sql );

		return $this->wpdb->get_var( $sql );
	}

	/**
	 * Get filtered quiz users count from DB
	 *
	 * @param int   $quiz_id
	 * @param array $completed_quiz
	 *
	 * @return array|null
	 */
	public function get_filtered_users_count( $quiz_id, $params = array() ) {
		$select_users = $this->get_sql_for_quiz_users( $quiz_id, $params );

		$order = ' ORDER BY id DESC ';
		if ( ! empty( $params['per_page'] ) && is_numeric( $params['per_page'] ) ) {
			$order .= ' LIMIT ' . $params['per_page'];
			if ( ! empty( $params['offset'] ) && is_numeric( $params['offset'] ) ) {
				$order .= ' OFFSET ' . $params['offset'];
			}
		}

		$sql = 'SELECT COUNT(users.id) AS total_items FROM (' . $select_users . ') AS users' . $order;

		return $this->wpdb->get_results( $sql );
	}

	/**
	 * Get quiz users from DB based on the filters set
	 *
	 * @param $quiz_id
	 * @param $params
	 *
	 * @return array|null
	 */
	public function get_quiz_users( $quiz_id, $params = array() ) {
		$select_users = $this->get_sql_for_quiz_users( $quiz_id, $params );

		$order = ' ORDER BY id DESC ';
		if ( ! empty( $params['per_page'] ) && is_numeric( $params['per_page'] ) ) {
			$order .= ' LIMIT ' . $params['per_page'];
			if ( ! empty( $params['offset'] ) && is_numeric( $params['offset'] ) ) {
				$order .= ' OFFSET ' . $params['offset'];
			}
		}

		$sql = $select_users . $order;

		return $this->wpdb->get_results( $sql );
	}

	/**
	 * Returns sql for filtering users
	 *
	 * @param int   $quiz_id
	 * @param array $params
	 *
	 * @return string
	 */
	protected function get_sql_for_quiz_users( $quiz_id, $params = array() ) {
		$where_guest = ' WHERE quiz_id = ' . $quiz_id . ' AND ignore_user IS NULL AND wp_user_id = 0 ';
		$where_user  = ' WHERE quiz_id = ' . $quiz_id . ' AND ignore_user IS NULL AND wp_user_id != 0 ';

		$where = '';

		if ( ! empty( $params['progress'] ) ) {
			switch ( $params['progress'] ) {
				case 'in_progress':
					$where .= 'AND completed_quiz IS NULL ';
					break;
				case 'completed':
					$where .= 'AND completed_quiz=1 ' . $this->get_sql_for_results_filtering( $params );
					break;
				case 'all':
				default:
					break;
			}
		}

		if ( ! empty( $params['date_started'] ) ) {
			$where .= 'AND date_started >= "' . esc_sql( $params['date_started'] ) . '" ';
		}

		if ( ! empty( $params['date_finished'] ) ) {
			$where .= 'AND DATE(date_started) <= "' . esc_sql( $params['date_finished'] ) . '" ';
		}

		if ( ! empty( $params['location'] ) ) {
			$where .= 'AND object_id = "' . esc_sql( $params['location'] ) . '" ';
		}

		$select_guest_users        = 'SELECT *, 1 as nb_of_tries FROM ' . tqb_table_name( 'users' ) . $where_guest . $where;
		$select_latest_start_dates = 'SELECT wp_user_id, COUNT(id) as nb_of_tries, MAX(date_started) as date_started FROM ' . tqb_table_name( 'users' )
		                             . $where_user . $where . ' GROUP BY wp_user_id';
		$select_users              = 'SELECT ' . tqb_table_name( 'users' ) . '.*, latest_completions.nb_of_tries FROM ' . tqb_table_name( 'users' )
		                             . ' INNER JOIN ( ' . $select_latest_start_dates . ' ) latest_completions ON '
		                             . tqb_table_name( 'users' ) . '.wp_user_id = latest_completions.wp_user_id AND '
		                             . tqb_table_name( 'users' ) . '.date_started = latest_completions.date_started';

		return $select_guest_users . ' UNION ' . $select_users;
	}

	/**
	 * Returns sql for a WHERE clause used for filtering quiz users based on points
	 *
	 * @param array $params
	 *
	 * @return string
	 */
	protected function get_sql_for_results_filtering( $params ) {
		$where = '';

		switch ( $params['quiz_type'] ) {
			case 'number':
				if ( ! empty( $params['result_min'] ) ) {
					$where .= 'AND points >=' . esc_sql( $params['result_min'] ) . ' ';
				}
				if ( ! empty( $params['result_max'] ) ) {
					$where .= 'AND points <=' . esc_sql( $params['result_max'] ) . ' ';
				}
				break;
			case 'percentage':
				if ( ! empty( $params['result_min'] ) ) {
					$where .= 'AND SUBSTRING_INDEX(points, "%", 1) >=' . esc_sql( $params['result_min'] ) . ' ';
				}
				if ( ! empty( $params['result_max'] ) ) {
					$where .= 'AND SUBSTRING_INDEX(points, "%", 1) <=' . esc_sql( $params['result_max'] ) . ' ';
				}
				break;
			case 'right_wrong':
				if ( ! empty( $params['result_min'] ) ) {
					$where .= 'AND SUBSTRING_INDEX(points, "/", 1) >=' . esc_sql( $params['result_min'] ) . ' ';
				}
				if ( ! empty( $params['result_max'] ) ) {
					$where .= 'AND SUBSTRING_INDEX(points, "/", 1) <=' . esc_sql( $params['result_max'] ) . ' ';
				}
				break;
			case 'personality':
				if ( ! empty( $params['categories'] ) ) {
					$numCat = count( $params['categories'] ) - 1;
					$where  .= 'AND ( ';
					foreach ( $params['categories'] as $key => $category ) {
						$where .= 'points="' . esc_sql( $category ) . '"';
						$where .= $key === $numCat ? ' ) ' : ' OR ';
					}
				}
				break;
			default:
				break;
		}

		return $where;
	}

	/**
	 * Returns quiz users from the database
	 *
	 * @param array  $filters
	 * @param string $return_type
	 *
	 * @return array|null|object
	 */
	public function get_users( $filters = array(), $return_type = ARRAY_A ) {
		$params = array();

		$sql = 'SELECT * FROM ' . tqb_table_name( 'users' ) . ' WHERE 1 ';

		if ( ! empty( $filters['email'] ) ) {
			$sql       .= ' AND email = %s';
			$params [] = $filters['email'];
		}

		if ( ! empty( $filters['quiz_id'] ) ) {
			$sql       .= ' AND quiz_id = %s';
			$params [] = $filters['quiz_id'];
		}

		if ( isset( $filters['completed_quiz'] ) && is_numeric( $filters['completed_quiz'] ) ) {
			if ( $filters['completed_quiz'] ) {
				$sql .= ' AND completed_quiz = 1';
			} else {
				$sql .= ' AND completed_quiz IS NULL';
			}
		}

		if ( ! empty( $filters['id'] ) ) {
			$sql       .= ' AND id = %d';
			$params [] = $filters['id'];
		}

		if ( ! empty( $filters['wp_user_id'] ) ) {
			$sql       .= ' AND wp_user_id = %d';
			$params [] = $filters['wp_user_id'];
		}

		if ( ! empty( $filters['random_identifier'] ) ) {
			$sql       .= ' AND random_identifier = %s';
			$params [] = $filters['random_identifier'];
		}

		if ( ! empty( $filters['object_id'] ) ) {
			$sql       .= ' AND object_id = %d';
			$params [] = $filters['object_id'];
		}

		if ( ! empty( $filters['order_by'] ) && ! empty( $filters['order_direction'] ) ) {
			$sql .= ' ORDER BY ' . $this->wpdb->_escape( $filters['order_by'] ) . ' ' . $this->wpdb->_escape( $filters['order_direction'] );
		}

		if ( ! empty( $filters['limit'] ) && is_numeric( $filters['limit'] ) ) {
			$sql       .= ' LIMIT %d ';
			$params [] = $filters['limit'];
		}

		$sql = $this->prepare( $sql, $params );

		return $this->wpdb->get_results( $sql, $return_type );
	}

	/**
	 * Get quiz user answer from DB
	 *
	 * @param $params
	 *
	 * @return array|null
	 */
	public function get_user_answers( $params = array() ) {
		if ( empty( $params['quiz_id'] ) || empty( $params['user_id'] ) ) {
			return false;
		}
		$where = ' WHERE quiz_id = %d AND user_id =%d ';

		$data['quiz_id'] = $params['quiz_id'];
		$data['user_id'] = $params['user_id'];
		if ( ! empty( $params['question_id'] ) ) {
			$where               .= ' AND question_id=%d';
			$data['question_id'] = $params['question_id'];
		}
		if ( ! empty( $params['answer_id'] ) ) {
			$where             .= ' AND answer_id=%d';
			$data['answer_id'] = $params['answer_id'];
		}
		if ( ! empty( $params['limit'] ) ) {
			$where         .= ' LIMIT %d';
			$data['limit'] = $params['limit'];
		}
		$sql = 'SELECT * FROM ' . tqb_table_name( 'user_answers' ) . $where;
		$sql = $this->prepare( $sql, $data );

		return ! empty( $params['limit'] ) && $params['limit'] === 1 ? $this->wpdb->get_row( $sql, ARRAY_A ) : $this->wpdb->get_results( $sql, ARRAY_A );
	}

	/**
	 * Get quiz user answer from DB
	 *
	 * @param $params
	 *
	 * @return array|null
	 */
	public function get_detailed_user_answers_( $params = array() ) {
		if ( empty( $params['quiz_id'] ) || empty( $params['user_id'] ) ) {
			return false;
		}
		$where = ' WHERE ua.quiz_id = %d AND user_id =%d';

		$sql = "SELECT * FROM " . tqb_table_name( 'user_answers' ) . " ua
		LEFT JOIN " . tge_table_name( 'answers' ) . " as a
		ON ua.answer_id = a.id";


		$data['quiz_id'] = $params['quiz_id'];
		$data['user_id'] = $params['user_id'];
		$sql             .= $where;
		$sql             = $this->prepare( $sql, $data );

		return $this->wpdb->get_results( $sql, ARRAY_A );
	}

	/**
	 * Get user's points from a quiz
	 *
	 * @param $user_unique
	 * @param $quiz_id
	 *
	 * @return array|null
	 */
	public function calculate_user_points( $user_unique, $quiz_id ) {

		$user = $this->get_quiz_user( $user_unique, $quiz_id );
		if ( empty( $user ) ) {
			return false;
		}

		$sql = 'SELECT IFNULL(SUM( answer.points ), 0) AS user_points, answer.result_id ';

		$sql .= ' FROM ' . tge_table_name( 'answers' ) . ' AS answer ';
		$sql .= ' INNER JOIN ' . tge_table_name( 'questions' ) . ' AS question ON question.id = answer.question_id ';
		$sql .= ' INNER JOIN ' . tqb_table_name( 'user_answers' ) . ' AS user_answers ON answer.id = user_answers.answer_id ';

		$sql .= '  WHERE (answer.result_id !=0 || answer.result_id IS NULL) 
		AND user_answers.quiz_id = ' . $quiz_id . ' 
		AND user_answers.user_id = ' . $user['id'] . ' 
		AND question.q_type != 3';

		$sql .= ' GROUP BY answer.result_id';

		$data = $this->wpdb->get_results( $this->prepare( $sql, array() ), ARRAY_A );

		$quiz_type = TQB_Post_meta::get_quiz_type_meta( $quiz_id );

		$end_result['user_points']    = null;
		$end_result['result_id']      = null;
		$end_result['quiz_completed'] = $user['completed_quiz'] == 1;

		if ( empty( $data ) && Thrive_Quiz_Builder::QUIZ_TYPE_PERSONALITY == $quiz_type['type'] ) {
			$results                   = $this->get_quiz_results( $quiz_id );
			$end_result['result_id']   = isset( $results[0]['id'] ) ? $results[0]['id'] : null;
			$end_result['user_points'] = true;
		} else {
			foreach ( $data as $result ) {
				if ( empty( $end_result['user_points'] ) || $result['user_points'] > $end_result['user_points'] ) {
					$end_result = $result;
				}
			}
		}
		$end_result['quiz_id']   = $quiz_id;
		$end_result['quiz_type'] = $quiz_type['type'];

		$end_result['extra'] = '';
		if ( Thrive_Quiz_Builder::QUIZ_TYPE_PERCENTAGE == $quiz_type['type'] ) {
			$end_result['extra'] = '%';
			$question_manager    = new TGE_Question_Manager( $quiz_id );
			$min_max             = $question_manager->get_min_max_flow();

			$end_result['max_points'] = intval( $min_max['max'] );
			$end_result['min_points'] = intval( $min_max['min'] );
		} else if ( Thrive_Quiz_Builder::QUIZ_TYPE_RIGHT_WRONG == $quiz_type['type'] ) {
			$end_result['total_questions']       = $this->count_user_answered_questions( $user['id'], $quiz_id );
			$end_result['total_valid_questions'] = 0;
			$question_manager                    = new TGE_Question_Manager( $quiz_id );
			$questions                           = $question_manager->get_quiz_questions();
			foreach ( $questions as $question ) {
				if ( ! in_array( (int) $question['q_type'], [ 1, 2 ] ) ) {
					continue;
				}

				$user_answer_for_question = $this->get_user_answers( [
					'quiz_id'     => $quiz_id,
					'user_id'     => $user['id'],
					'question_id' => $question['id'],
				] );

				if ( empty( $user_answer_for_question ) ) {
					continue;
				}

				$settings = json_decode( $question['settings'], true );

				if ( ! isset( $settings['allowed_answers'] ) || (int) $settings['allowed_answers'] === 1 ) {
					/**
					 * Old way of calculating
					 * We check if there is at least one correct answer to the question
					 */
					$query = 'SELECT count(ua.id) FROM ' . tqb_table_name( 'user_answers' ) . ' AS ua
							INNER JOIN ' . tge_table_name( 'answers' ) . ' a ON ua.answer_id = a.id
							INNER JOIN ' . tge_table_name( 'questions' ) . ' q ON ua.question_id = q.id
							WHERE ua.quiz_id = %d AND ua.user_id = %d AND q.id = %d AND a.is_right = 1';
					$query = $this->prepare( $query, [ $quiz_id, $user['id'], $question['id'] ] );
					if ( (int) $this->wpdb->get_var( $query ) > 0 ) {
						$end_result['total_valid_questions'] ++;
					}
				} else {
					/**
					 * New way of calculating
					 * We check if the user answers has at least one wrong answer
					 */
					$query = 'SELECT COUNT(a.id) FROM ' . tge_table_name( 'answers' ) . ' a INNER JOIN ' . tge_table_name( 'questions' ) . ' q1 on q1.id = a.question_id 
					 LEFT JOIN ' . tqb_table_name( 'user_answers' ) . ' ua  ON a.id = ua.answer_id AND ua.user_id = %d
					 WHERE a.quiz_id = %d AND a.question_id = %d AND ( ( a.is_right = 1 AND ua.id IS NULL ) OR ( a.is_right = 0 AND ua.id IS NOT NULL))';
					$query = $this->prepare( $query, [ $user['id'], $quiz_id, $question['id'] ] );

					if ( (int) $this->wpdb->get_var( $query ) === 0 ) {
						$end_result['total_valid_questions'] ++;
					}
				}
			}

			$end_result['user_points'] = $end_result['total_valid_questions'];

		}

		return $end_result;
	}

	/**
	 * Get user's points from a quiz
	 *
	 * @param $user_unique
	 * @param $quiz_id
	 *
	 * @return array|null
	 */
	public function get_user_points( $user_unique, $quiz_id ) {

		$quiz_type = TQB_Post_meta::get_quiz_type_meta( $quiz_id, true );

		/**
		 * No points for Survey Quiz
		 */
		if ( 'survey' === $quiz_type ) {
			return false;
		}

		$user = $this->get_quiz_user( $user_unique, $quiz_id );
		if ( empty( $user ) ) {
			return false;
		}

		return isset( $user['points'] ) ? $user['points'] : '-';
	}

	/**
	 * Update the user's points from a quiz
	 *
	 * @param $answer
	 * @param $user
	 *
	 * @return array|null
	 */
	public function update_user_points( $answer, $user ) {
		$user['points'] = isset( $user['points'] ) ? $user['points'] : 0;

		return $this->save_quiz_user( array( 'id' => $user['id'], 'points' => ( $user['points'] + $answer['points'] ) ) );
	}

	/**
	 * Clone variation database method
	 *
	 * @param int $id
	 *
	 * @return int
	 */
	public function clone_variation( $id ) {

		$query = 'INSERT INTO ' . tqb_table_name( 'variations' ) . ' (quiz_id, date_added, date_modified, page_id, parent_id, post_title,tcb_fields, content) 
		SELECT quiz_id, NOW(), NOW(), page_id, parent_id, CONCAT("' . __( 'Copy of ', 'thrive-quiz-builder' ) . '",post_title),tcb_fields, content FROM ' . tqb_table_name( 'variations' ) . ' WHERE id = %d';

		$query = $this->prepare( $query, array( 'id' => $id ) );
		$this->wpdb->query( $query );
		/* Store the variation ID for the case when we perform another insert unrelated to variation */
		$variation_id = $this->wpdb->insert_id;

		$this->replace_variation_id( $id, $variation_id );

		return $variation_id;
	}


	/**
	 * Replace variation id inside content
	 *
	 * @param $initial
	 * @param $after
	 *
	 * @return int
	 */
	public function replace_variation_id( $initial, $after ) {
		$variation = $this->get_variation( $after );

		if ( empty( $variation ) ) {
			return false;
		}

		/* We need to save the form settings from the clone */
		if ( method_exists( FormSettings::class, 'save_form_settings_from_duplicated_content' ) ) {
			$variation['content'] = FormSettings::save_form_settings_from_duplicated_content( $variation['content'], (int) $variation['page_id'] );
		}

		$content = str_replace( 'name="tqb-variation-variation_id" class="tqb-hidden-form-info" value="' . $initial . '"', 'name="tqb-variation-variation_id" class="tqb-hidden-form-info" value="' . $after . '"', $variation['content'] );

		return $this->save_variation( array( 'id' => $after, 'content' => $content ) );
	}

	/**
	 * get data for completion report
	 *
	 * @param array $filters
	 *
	 * @return array
	 */
	public function get_quiz_completion_report( $quiz_id, $filters = array() ) {

		$timezone_diff = current_time( 'timestamp' ) - time();

		if ( empty( $filters['interval'] ) ) {
			$filters['interval'] = 'day';
		}

		switch ( $filters['interval'] ) {
			case 'month':
				$date_interval = 'CONCAT(MONTHNAME(`user`.`date_started`)," ", YEAR(`user`.`date_started`)) as date_interval';
				break;
			case 'week':
				$year          = 'IF( WEEKOFYEAR(`user`.`date_started`) = 1 AND MONTH(`user`.`date_started`) = 12, 1 + YEAR(`user`.`date_started`), YEAR(`user`.`date_started`) )';
				$date_interval = "CONCAT('Week ', WEEKOFYEAR(`user`.`date_started`), ', ', {$year}) as date_interval";
				break;
			case 'day':
				$date_interval = 'DATE(`user`.`date_started`) as date_interval';
				break;
		}

		if ( empty( $filters['location'] ) || $filters['location'] === 'all' ) {
			$quiz_location = '';
		} else {
			$quiz_location = ' AND object_id=' . $filters['location'];
		}

		$sql = 'SELECT IFNULL(COUNT( user.id ), 0) AS user_count, quiz_id, ' . $date_interval;

		$sql .= ' FROM ' . tqb_table_name( 'users' ) . ' AS `user` ';

		$sql .= '  WHERE 1 AND completed_quiz=1 AND ignore_user IS NULL';

		$params = array();

		if ( empty( $filters['date'] ) ) {
			$filters['date'] = Thrive_Quiz_Builder::TQB_LAST_7_DAYS;
		}

		$data_interval = $this->get_report_date_interval( $filters );
		$sql           .= $data_interval['date_interval'];

		if ( ! empty( $quiz_id ) ) {
			$sql       .= ' AND quiz_id = %d';
			$params [] = $quiz_id;
		}

		$sql .= $quiz_location;
		$sql .= ' GROUP BY quiz_id, date_interval ORDER BY date_interval ';

		$data  = $this->wpdb->get_results( $this->prepare( $sql, $params ), ARRAY_A );
		$dates = tqb_generate_dates_interval( $data_interval['start_date'], $data_interval['end_date'], $filters['interval'] );

		$quizzes    = array();
		$table_quiz = array();
		foreach ( $data as $i => $quiz ) {

			if ( empty( $quizzes[ $quiz['quiz_id'] ] ) ) {
				$quiz_post = get_post( $quiz['quiz_id'] );
				if ( empty( $quiz_post ) ) {
					unset( $data[ $i ] );
					continue;
				}
				$table_quiz[ $quiz['quiz_id'] ] = intval( $quiz['user_count'] );

				$quizzes[ $quiz['quiz_id'] ] = array(
					'data' => array( $quiz['date_interval'] => intval( $quiz['user_count'] ) ),
					'name' => $quiz_post->post_title,
					'id'   => $quiz_post->ID,
				);

				$data[ $i ]['name'] = $quiz_post->post_title;

			} else {
				$quizzes[ $quiz['quiz_id'] ]['data'][ $quiz['date_interval'] ] = intval( $quiz['user_count'] );
				$table_quiz[ $quiz['quiz_id'] ]                                += intval( $quiz['user_count'] );
				$data[ $i ]['name']                                            = $quizzes[ $quiz['quiz_id'] ]['name'];
			}
		}

		//add zeros
		foreach ( $quizzes as $key => $quiz ) {
			$count_array = array();

			foreach ( $dates as $k => $date ) {
				$count_array[ $k ] = 0;
				foreach ( $quiz['data'] as $t => $count ) {

					if ( $filters['interval'] == 'day' ) {
						$t = date( 'd M, Y', strtotime( $t ) );
					}
					if ( $date == $t ) {
						$count_array[ $k ] = $count;
					}
				}
			}
			$quizzes[ $key ]['name'] = $quizzes[ $key ]['name'] . ': ' . $table_quiz[ $key ];
			$quizzes[ $key ]['data'] = $count_array;
		}

		return array( 'graph_quiz' => $quizzes, 'intervals' => $dates, 'table_quizzes' => $data );
	}

	public function get_quiz_locations( $quiz_id ) {
		$sql = 'SELECT GROUP_CONCAT( DISTINCT object_id ) AS locations, quiz_id FROM ' . tqb_table_name( 'users' ) . ' AS `user` ';
		$sql .= '  WHERE 1 AND completed_quiz=1 AND ignore_user IS NULL';

		$params = array();

		if ( ! empty( $quiz_id ) ) {
			$sql       .= ' AND quiz_id = %d';
			$params [] = $quiz_id;
		}

		$sql .= ' GROUP BY quiz_id ';

		$data = $this->wpdb->get_results( $this->prepare( $sql, $params ), ARRAY_A );

		$locations = array();

		if ( ! empty( $data ) ) {
			$post_ids = array_map( 'intval', explode( ",", $data[0]['locations'] ) );

			foreach ( $post_ids as $post_id ) {
				if ( $post_id ) {
					$post = get_post( $post_id );

					if ( ! empty( $post ) ) {
						$details = array( 'post_id' => $post_id, 'post_title' => $post->post_title );

						/**
						 * Gets the course name that the course overview post belongs to
						 */
						$details = apply_filters( 'tqb_get_course_overview_details', $details, $post );

						$locations[ $post->post_type ] [] = $details;
					}
				}
			}
		}

		return $locations;
	}

	public function get_report_date_interval( $filter ) {
		$date_interval = '';
		$end_date      = '';
		$timezone_diff = current_time( 'timestamp' ) - time();
		switch ( $filter['date'] ) {
			case Thrive_Quiz_Builder::TQB_LAST_7_DAYS :
				$start_date    = date( 'Y-m-d', ( strtotime( '-7 days' ) + $timezone_diff ) );
				$date_interval = ' AND `user`.`date_started` >= "' . $start_date . '" ';
				break;
			case Thrive_Quiz_Builder::TQB_LAST_30_DAYS :
				$start_date    = date( 'Y-m-d', ( strtotime( '-30 days' ) + $timezone_diff ) );
				$date_interval = ' AND `user`.`date_started` >= "' . $start_date . '" ';
				break;
			case Thrive_Quiz_Builder::TQB_THIS_MONTH :
				$start_date    = date( 'Y-m-d', ( strtotime( date( '01-m-Y' ) ) + $timezone_diff ) );
				$date_interval = ' AND `user`.`date_started` >= "' . $start_date . '" ';
				break;
			case Thrive_Quiz_Builder::TQB_LAST_MONTH :
				$start_date    = date( 'Y-m-d', ( strtotime( 'first day of last month' ) + $timezone_diff ) );
				$end_date      = date( 'Y-m-d', ( strtotime( '01-m-Y' ) ) + $timezone_diff );
				$date_interval = ' AND `user`.`date_started` >= "' . $start_date . '" AND `user`.`date_started` < "' . $end_date . '" ';
				break;
			case Thrive_Quiz_Builder::TQB_THIS_YEAR :
				$start_date    = date( 'Y-m-d', ( strtotime( date( 'Y-01-01' ) ) + $timezone_diff ) );
				$date_interval = ' AND `user`.`date_started` >= "' . $start_date . '" ';
				break;
			case Thrive_Quiz_Builder::TQB_LAST_YEAR :
				$year          = date( 'Y' ) - 1;
				$start_date    = date( 'Y-m-d', ( mktime( 0, 0, 0, 1, 1, $year ) + $timezone_diff ) );
				$end_date      = date( 'Y-m-d', ( mktime( 0, 0, 0, 12, 31, $year ) + $timezone_diff ) );
				$date_interval = ' AND `user`.`date_started` >= "' . $start_date . '" AND `user`.`date_started` < "' . $end_date . '" ';
				break;
			case Thrive_Quiz_Builder::TQB_LAST_12_MONTHS :

				$start_date    = date( 'Y-m-d', ( strtotime( '-1 year', time() ) + $timezone_diff ) );
				$date_interval = ' AND `user`.`date_started` >= ' . $start_date . ' ';
				break;
			case Thrive_Quiz_Builder::TQB_CUSTOM_DATE_RANGE :
				$start_date    = $filter['start_date'];
				$end_date      = date( 'Y-m-d H:i:s', ( strtotime( '+1 day', ( strtotime( $filter['end_date'] ) - 1 ) ) + $timezone_diff ) );
				$date_interval = ' AND `user`.`date_started` >= "' . $start_date . '" AND `user`.`date_started` < "' . $end_date . '" ';
				break;
		}

		return array(
			'date_interval' => $date_interval,
			'start_date'    => $start_date,
			'end_date'      => empty( $end_date ) ? date( 'Y-m-d', ( time() + $timezone_diff ) ) : $end_date,
		);
	}

	/**
	 * Get quiz data for questions report
	 *
	 * @param       $quiz_id
	 * @param array $params
	 *
	 * @return false|array
	 */
	public function get_questions_report_data( $quiz_id, $params ) {
		$sql = 'SELECT
		IFNULL(COUNT( user_answer.id ), 0) AS answer_count,
		answer.question_id,
		answer.id AS answer_id,
		answer.text AS answer_text,
		answer.image AS answer_image,
		question.text AS question_text,
		question.views AS question_views,
		question.q_type AS question_type
		';

		$sql .= ' FROM ' . tge_table_name( 'answers' ) . ' AS answer ';


		$sql .= ' LEFT JOIN ' . tqb_table_name( 'user_answers' ) . ' AS user_answer ON answer.id = user_answer.answer_id ';
		$sql .= ' INNER JOIN ' . tqb_table_name( 'users' ) . ' AS user ON user.id = user_answer.user_id ';
		$sql .= ' LEFT JOIN ' . tge_table_name( 'questions' ) . ' AS question ON question.id = answer.question_id ';

		$sql .= '  WHERE answer.quiz_id = ' . $quiz_id . ' AND user.ignore_user IS NULL';// ' AND user.completed_quiz = 1';

		if ( ! empty( $params['location'] ) ) {
			$sql .= ' AND user.object_id = ' . esc_sql( $params['location'] );
		}

		$sql .= ' GROUP BY answer.question_id, answer.id ';


		$answers_sql = 'SELECT * FROM ' . tqb_table_name( 'user_answers' ) . ' WHERE question_id = %d AND answer_id = %s';

		$data = $this->wpdb->get_results( $this->prepare( $sql, array( 'quiz_id' => $quiz_id ) ), ARRAY_A );

		$questions = array();

		$colors = tqb()->chart_colors();
		foreach ( $data as $entry ) {
			$image = json_decode( (string) $entry['answer_image'] );
			if ( empty( $image ) ) {
				$image = array( 'url' => $entry['answer_image'] );
			}
			if ( empty( $questions[ $entry['question_id'] ] ) ) {

				$structure_manager = new TQB_Structure_Manager( $quiz_id );
				$structure         = $structure_manager->get_quiz_structure_meta();

				$questions[ $entry['question_id'] ] = array(
					'text'          => $entry['question_text'],
					'answers'       => array(
						$entry['answer_id'] => array(
							'text'  => $entry['answer_text'],
							'count' => $entry['answer_count'],
							'image' => $image,
						),
					),
					'total'         => $entry['answer_count'],
					'views'         => $entry['question_views'],
					'id'            => $entry['question_id'],
					'views_counted' => isset( $structure['count_views'] ) ? $structure['count_views'] : false,
				);

			} else {
				$questions[ $entry['question_id'] ]['answers'][ $entry['answer_id'] ] = array(
					'text'  => $entry['answer_text'],
					'count' => $entry['answer_count'],
					'image' => $image,
				);
				$questions[ $entry['question_id'] ]['total']                          += $entry['answer_count'];
			}

			$questions[ $entry['question_id'] ]['question_type'] = $entry['question_type'];

			if ( intval( $entry['question_type'] ) === 3 ) {
				$users_answers = $this->wpdb->get_results(
					$this->prepare(
						$answers_sql,
						array(
							$entry['question_id'],
							$entry['answer_id'],
						)
					),
					ARRAY_A
				);

				foreach ( $users_answers as &$temp_answer ) {
					$temp_answer['answer_text'] = nl2br( sanitize_textarea_field( stripslashes( $temp_answer['answer_text'] ) ) );
				}

				$questions[ $entry['question_id'] ]['user_answers'] = $users_answers ? $users_answers : array();
			}
		}
		foreach ( $questions as $key => $question ) {
			$index = 0;
			foreach ( $question['answers'] as $id => $answer ) {
				if ( $question['total'] ) {
					$questions[ $key ]['answers'][ $id ]['percent'] = round( $answer['count'] * 100 / $question['total'], 2 );
					$questions[ $key ]['answers'][ $id ]['color']   = $colors[ $index % count( $colors ) ];
					$index ++;
				}
			}
		}

		return $questions;
	}

	/**
	 * @param       $quiz_id
	 * @param array $filters
	 *
	 * @return array
	 */
	public function get_full_questions_report_data( $quiz_id, $filters = array() ) {

		// The query
		$sql = 'SELECT
		IFNULL(COUNT( user_answer.id ), 0) AS answer_count,
		answer.question_id,
		answer.id AS answer_id,
		answer.text AS answer_text,
		answer.image AS answer_image,
		question.id AS q_id,
		question.text AS question_text,
		question.views AS question_views,
		question.q_type AS question_type
		';

		// Filter columns in sql
		if ( ! empty( $filters['columns'] ) && is_array( $filters['columns'] ) ) {
			$columns = implode( ', ', array_map(
				function ( $v, $k ) {
					return sprintf( "%s AS %s", esc_sql( $k ), esc_sql( $v ) );
				},
				$filters['columns'],
				array_keys( $filters['columns'] )
			) );

			$sql = "SELECT {$columns}";
		}

		$sql .= ' FROM ' . tge_table_name( 'answers' ) . ' AS answer ';
		$sql .= ' LEFT JOIN ' . tqb_table_name( 'user_answers' ) . ' AS user_answer ON answer.id = user_answer.answer_id ';
		$sql .= ' INNER JOIN ' . tqb_table_name( 'users' ) . ' AS `user` ON user.id = user_answer.user_id ';
		$sql .= ' LEFT JOIN ' . tge_table_name( 'questions' ) . ' AS question ON question.id = answer.question_id ';
		$sql .= ' WHERE answer.quiz_id = ' . $quiz_id . ' AND user.ignore_user IS NULL';

		// Filter GROUP BY in sql
		if ( ! empty( $filters['group_by'] ) && is_array( $filters['group_by'] ) ) {

			$group_by = implode( ', ', array_map(
				function ( $v ) {
					return sprintf( "%s", esc_sql( $v ) );
				},
				$filters['group_by']
			) );

			$sql .= " GROUP BY {$group_by}";
		} else {
			$sql .= ' GROUP BY answer.question_id, answer.id ';
		}

		$answers_sql = 'SELECT * FROM ' . tqb_table_name( 'user_answers' ) . ' WHERE question_id = %d AND answer_id = %s';
		$data        = $this->wpdb->get_results( $this->prepare( $sql, array( 'quiz_id' => $quiz_id ) ), ARRAY_A );

		// Build questions and answers array as required
		$questions = array();
		foreach ( $data as $entry ) {

			switch ( (int) $entry['question_type'] ) {
				// Multiple with image
				case 2:

					if ( ! empty( $entry['answer_image'] ) ) {
						$image_obj = json_decode( $entry['answer_image'] );
						if ( ! empty( $image_obj->sizes ) && ! empty( $image_obj->sizes->thumbnail ) && ! empty( $image_obj->sizes->thumbnail->url ) ) {
							$img_arr                                                                      = ( explode( '/', $image_obj->sizes->thumbnail->url ) );
							$img_name                                                                     = end( $img_arr );
							$questions[ $entry['question_text'] . "__{$entry['q_id']}" ][ $entry['uid'] ] = ! empty( $img_name ) ? $img_name : 'Image X';
						}
					}

					break;
				// Open ended question
				case 3:
					$oe_data       = array();
					$users_answers = $this->wpdb->get_results(
						$this->prepare(
							$answers_sql,
							array(
								$entry['question_id'],
								$entry['answer_id'],
							)
						),
						ARRAY_A
					);

					foreach ( $users_answers as $temp_answer ) {
						$oe_data[ $temp_answer['user_id'] ] = ! empty( $temp_answer['answer_text'] ) ? nl2br( sanitize_textarea_field( $temp_answer['answer_text'] ) ) : '';
					}
					$questions[ $entry['question_text'] . "__{$entry['q_id']}" ] = $oe_data;

					break;
				default:
					$questions[ $entry['question_text'] . "__{$entry['q_id']}" ][ $entry['uid'] ] = ! empty( $entry['answer_text'] ) ? $entry['answer_text'] : '';

					break;
			}
		}

		if ( empty( $questions ) ) {
			return array();
		}

		// Add all user id's that have answered
		$all_uids = array();
		foreach ( $questions as $key => $question ) {
			foreach ( $question as $u_id => $answer ) {
				if ( ! in_array( $u_id, $all_uids ) ) {
					$all_uids[] = $u_id;
				}
			}
		}
		sort( $all_uids );

		/**
		 * Loop trough all users that answered and make sure that every question has all user id's for positioning in the csv file
		 */
		$headers = array();
		foreach ( $all_uids as $user_id ) {
			foreach ( $questions as $q_key => $answer ) {
				// Build headers
				$headers[ $q_key ] = substr( $q_key, 0, strpos( $q_key, "__" ) ); // for questions with the same name

				// Build body
				if ( ! isset( $questions[ $q_key ][ $user_id ] ) ) {
					$questions[ $q_key ][ $user_id ] = '';
				}
				ksort( $questions[ $q_key ] );
			}
		}

		$return = array(
			'headers' => array_values( $headers ),
			'body'    => array_values( array_map( 'array_values', $questions ) ),
		);

		return $return;
	}

	/**
	 * @param array $args
	 *
	 * @return array|object|null
	 */
	public function get_user_answers_with_questions( $args = array() ) {

		$user_answers = tqb_table_name( 'user_answers' );
		$questions    = tge_table_name( 'questions' );
		$answers      = tge_table_name( 'answers' );

		$user_id = ! empty( $args['user_id'] ) ? $args['user_id'] : 0;
		$quiz_id = ! empty( $args['quiz_id'] ) ? $args['quiz_id'] : 0;

		$sql = "SELECT ua.id, ua.question_id, ua.answer_text, q.text as q_text, q.q_type, a.text as a_text FROM {$user_answers} as ua ";
		$sql .= "LEFT JOIN {$questions} as q ON ua.question_id = q.id ";
		$sql .= "LEFT JOIN {$answers} as a ON a.question_id = ua.question_id ";
		$sql .= 'WHERE ua.user_id = %d AND ua.quiz_id = %d AND ua.answer_id = a.id ';
		$sql .= 'ORDER BY ua.id ';

		$data = $this->wpdb->get_results(
			$this->prepare(
				$sql,
				array(
					'user_id' => $user_id,
					'quiz_id' => $quiz_id,
				)
			),
			ARRAY_A
		);

		return $data;
	}

	/**
	 * Get a rough map of the quiz's questions and answers with the next question ids
	 * This array is oriented towards displaying the user's answers in the email sent at the end
	 *
	 * @param int $quiz_id
	 *
	 * @return array|object|null
	 */
	public function get_quiz_map( $quiz_id ) {
		$params[]  = $quiz_id;
		$questions = tge_table_name( 'questions' );
		$answers   = tge_table_name( 'answers' );

		$sql = 'SELECT q.id as question_id, a.id as answer_id, q.start,  q.next_question_id as q_next_id, a.next_question_id as a_next_id';
		$sql .= ", a.text as a_text, q.text as q_text, q.q_type as q_type FROM {$questions} AS q ";
		$sql .= " JOIN {$answers} as a on q.id = a.question_id ";
		$sql .= 'WHERE q.quiz_id = %d ';
		$sql .= 'ORDER BY q.start DESC ';

		return $this->wpdb->get_results( $this->prepare( $sql, $params ), ARRAY_A );
	}

	/**
	 * @param $user_id
	 *
	 * @return array|null
	 */
	public function get_last_user_answer( $user_id ) {
		$user_answers = tqb_table_name( 'user_answers' );
		$answers      = tge_table_name( 'answers' );

		$sql = "SELECT * from {$answers} WHERE id = (SELECT answer_id from {$user_answers} WHERE user_id = %d ORDER BY id DESC LIMIT 1)";

		return $this->wpdb->get_row( $this->prepare( $sql, array( 'user_id' => $user_id ) ), ARRAY_A );
	}

	/**
	 * Check if we have a design variation containing the specific string
	 *
	 * @param $string
	 *
	 * @return boolean
	 */
	public function search_string_in_designs( $string ) {
		$sql = 'SELECT `id` FROM ' . tqb_table_name( 'variations' ) . ' WHERE content LIKE %s';

		$this->wpdb->query( $this->prepare( $sql, [ "%$string%" ] ) );

		return $this->wpdb->num_rows > 0;
	}

	/**
	 * @param array $filters
	 *
	 * @return array|null
	 */
	public function get_log_by_filters( $filters = array() ) {
		$params = array();
		$where  = '';

		if ( ! empty( $filters['user_unique'] ) ) {
			$params[] = $filters['user_unique'];
			$where    .= ' AND  user_unique=%s';
		}

		if ( ! empty( $filters['event_type'] ) ) {
			if ( is_array( $filters['event_type'] ) ) {
				$where .= ' AND event_type IN (' . implode( ',', array_map( 'absint', $filters['event_type'] ) ) . ')';
			} else {
				$params[] = $filters['event_type'];
				$where    .= ' AND event_type=%d';
			}
		}

		if ( ! empty( $filters['page_id'] ) ) {
			$params[] = $filters['page_id'];
			$where    .= ' AND page_id=%d';
		}

		if ( ! empty( $filters['limit'] ) ) {
			$params[] = $filters['limit'];
			$where    .= ' LIMIT %d';
		}

		$sql = $this->prepare( 'SELECT * FROM ' . tqb_table_name( 'event_log' ) . " WHERE 1 {$where}", $params );

		return ! empty( $filters['limit'] ) && $filters['limit'] === 1 ? $this->wpdb->get_row( $sql, ARRAY_A ) : $this->wpdb->get_results( $sql, ARRAY_A );
	}

	/**
	 * Returns the total number of answered questions from the database
	 * This query counts DISTINCT questions in order to support the multiple answer feature
	 * It is used to display the result for a R/W quiz
	 * Example 0/3
	 *
	 * @param integer $user_id
	 * @param integer $quiz_id
	 *
	 * @return int
	 */
	public function count_user_answered_questions( $user_id, $quiz_id ) {
		$table_user_answers = tqb_table_name( 'user_answers' );
		$table_answers      = tge_table_name( 'answers' );
		$table_questions    = tge_table_name( 'questions' );

		$sql = $this->wpdb->prepare( "SELECT COUNT(DISTINCT ua.question_id) FROM {$table_user_answers} AS ua INNER JOIN {$table_answers} AS a on ua.answer_id = a.id INNER JOIN {$table_questions} as q ON ua.question_id = q.id WHERE ua.quiz_id = %d AND ua.user_id = %d AND q.q_type != 3", [ $quiz_id, $user_id ] );

		return (int) $this->wpdb->get_var( $sql );
	}
}

$tqbdb = new TQB_Database();

