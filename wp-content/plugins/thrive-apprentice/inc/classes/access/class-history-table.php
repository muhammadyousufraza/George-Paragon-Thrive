<?php

namespace TVA\Access;

// TODO Separate general functionality from specific one
class History_Table {

	/**
	 * Maybe rename this
	 */
	use \TD_Singleton;

	/**
	 * @var \wpdb
	 */
	protected $wpdb;

	/**
	 * Table name
	 *
	 * @var string
	 */
	private $name;

	/**
	 * Column formats
	 *
	 * @var string[]
	 */
	private $format = [
		'user_id'    => '%d',
		'product_id' => '%d',
		'course_id'  => '%d',
		'source'     => '%s',
		'status'     => '%d',
		'created'    => '%s',
	];

	public function __construct() {
		global $wpdb;
		$this->wpdb = $wpdb;

		$this->name = $wpdb->prefix . 'tva_' . static::get_table_name();
	}

	/**
	 * Used for initial migration and in prepare table function
	 *
	 * @return string
	 */
	public static function get_table_name() {
		return 'access_history';
	}

	/**
	 * @param array $data
	 *
	 * @return array|false Strings containing the results of the various update queries.
	 */
	public function insert_multiple( $data = [] ) {
		if ( empty( $data ) ) {
			return false;
		}

		$header  = "INSERT INTO $this->name (user_id, product_id, course_id, source, status, created, reason) VALUES ";
		$created = gmdate( 'Y-m-d H:i:s' );
		$values  = [];
		$queries = [];
		foreach ( $data as $info ) {
			$created_info = empty( $info['created'] ) ? $created : $info['created'];
			$reason       = empty( $info['reason'] ) ? 'NULL' : (int) $info['reason'];

			if ( strpos( $created_info, 'SELECT' ) !== false ) {
				/**
				 * Ability to add created info from a select - dynamically on migration
				 */
				$created_info = "($created_info)";
			} else {
				$created_info = "'" . $created_info . "'";
			}

			$values[] = '(' . $info['user_id'] . ', ' . $info['product_id'] . ', ' . $info['course_id'] . ",'" . $info['source'] . "', " . $info['status'] . ", $created_info, '" . $reason . "')";
		}

		$values_chunk = array_chunk( $values, 7000 );

		foreach ( $values_chunk as $chunk ) {
			$queries[] = $header . implode( ' , ', $chunk );
		}

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		return dbDelta( implode( ';', $queries ) );
	}

	/**
	 * @param array $data
	 *
	 * @return int|false The number of rows inserted, or false on error.
	 */
	public function insert( $data = [] ) {

		if ( empty( $data['user_id'] ) || empty( $data['course_id'] ) || empty( $data['source'] ) ) {
			/**
			 * user_id - is a mandatory field for the history table
			 * course_id - For now we only log data for course access change
			 */
			return false;
		}

		$data['created'] = gmdate( 'Y-m-d H:i:s' );

		$format = [];
		foreach ( $data as $key => $value ) {
			$format[] = isset( $this->format[ $key ] ) ? $this->format[ $key ] : '%s';
		}

		return $this->wpdb->insert( $this->name, $data, $format );
	}

	/**
	 * @param array $where
	 *
	 * @return int|false The number of rows deleted, or false on error.
	 */
	public function delete( $where = [] ) {
		if ( empty( $where ) ) {
			return false;
		}

		return $this->wpdb->delete( $this->name, $where );
	}

	public function get_course_enrollments( $filters = [] ) {
		$query = $this->build_report_query( [
			'select'   => [
				'SUM(status) as status',
				'DATE(created) as created',
				'user_id',
				'course_id',
			],
			'where'    => $filters,
			'group_by' => [ 'DATE(created)', 'course_id' ],
		] );

		return $this->wpdb->get_results( $query, ARRAY_A );
	}

	public function get_number_of_entries( $filters = [] ) {
		$query = $this->build_report_query( [
			'select'   => [
				'user_id',
				'SUM(status) as status',
			],
			'where'    => $filters,
			'group_by' => [ 'user_id' ],
		] );

		$results = $this->wpdb->get_results( $query, ARRAY_A );

		$user_ids = [];
		if ( ! empty( $results ) && is_array( $results ) ) {
			foreach ( $results as $result ) {
				$user_ids[ $result['user_id'] ] = $result['status'];
			}
		}

		return $user_ids;
	}

	public function get_course_enrollments_table( $query = [] ) {
		if ( empty( $query['select'] ) ) {
			$query['select'] = [ 'status', 'source', 'created', 'user_id', 'course_id', 'product_id' ];
		}

		$query = $this->build_report_query( $query );

		return $this->wpdb->get_results( $query, ARRAY_A );
	}

	public function get_course_enrollment_dates( $filters = [] ) {
		$query = $this->build_report_query( [
			'select'   => [
				'course_id',
				'DATE(created) as created',
				'COUNT(DISTINCT(user_id)) as count',
			],
			'where'    => $filters,
			'group_by' => [ 'course_id' ],
			'having'   => 'SUM(status) > 0',
		] );

		return $this->wpdb->get_results( $query, ARRAY_A );
	}

	public function get_total_students( $filters = [] ) {
		$query = $this->build_report_query( [
			'select'   => [
				'user_id',
			],
			'where'    => $filters,
			'group_by' => [ 'user_id' ],
			'having'   => 'SUM(status) > 0',
		] );

		$query_count = "SELECT COUNT(*) as number FROM ($query) as test";

		$count = $this->wpdb->get_row( $query_count, ARRAY_A );

		return (int) $count['number'];
	}

	public function get_students( $filters = [] ) {
		$query = $this->build_report_query( [
			'select'   => [
				'user_id',
				'DATE(created) as created',
			],
			'where'    => $filters,
			'group_by' => [ 'user_id' ],
			'having'   => 'SUM(status) > 0',
		] );

		$query_count = "SELECT COUNT(created) as number, created FROM ($query) as tmp GROUP BY created";

		return $this->wpdb->get_results( $query_count, ARRAY_A );
	}

	public function get_top_students( $filters = [] ) {
		$query = $this->build_report_query( [
			'select'   => [
				'user_id',
				'SUM(status) as sum',
			],
			'where'    => $filters,
			'group_by' => [
				'user_id',
				'course_id',
			],
			'having'   => 'sum > 0',
		] );

		$results = $this->wpdb->get_results( "SELECT user_id, COUNT(user_id) as number FROM ($query) as tmp GROUP BY user_id ORDER BY number DESC", ARRAY_A );

		$user_ids = [];
		if ( ! empty( $results ) && is_array( $results ) ) {
			foreach ( $results as $result ) {
				$user_ids[ $result['user_id'] ] = $result['number'];
			}
		}

		return $user_ids;
	}

	public function get_average_products( $filters = [] ) {
		$users_with_products = $this->build_report_query( [
			'select'   => [ 'user_id', 'product_id', 'created' ],
			'where'    => $filters,
			'group_by' => [ 'product_id', 'user_id' ],
			'having'   => 'SUM(status) > 0',
		] );
		$nb_of_products      = "SELECT users.user_id, COUNT(users.product_id) as products FROM ($users_with_products) as users GROUP BY users.user_id";

		return $this->wpdb->get_row( "SELECT AVG(product_numbers.products) as average FROM ($nb_of_products) as product_numbers", ARRAY_A );
	}

	/**
	 * Get all the customers that have access
	 *
	 * @param int $user_id
	 *
	 * @return array|int|object|\stdClass[]|null
	 */
	public function get_student( $user_id ) {
		if ( is_numeric( $user_id ) ) {
			$result = $this->get_all_students( [
				'filters' => array(
					'user_id' => array( $user_id ),
				),
			] );

			if ( ! empty( $result[0] ) ) {
				return $result[0];
			}
		}

		return null;
	}

	/**
	 * Get all the customers that have access
	 *
	 * @param null|array $args
	 *
	 * @return array|int|object|\stdClass[]
	 */
	public function get_all_students( $args = [] ) {
		/* the inner query returns multiple results per user based on how many products / courses he has access to
		so in the outer query we must get the oldest enrolled date which represents the user's joined date */
		$outer_query = "SELECT ID, COUNT(DISTINCT(course_id)) as courses_count, min(DATE_FORMAT(enrolled, '%d.%m.%Y')) as enrolled FROM (";

		$query = $this->build_report_query( [
			'select'   => [
				'user_id AS ID',
				'course_id',
				'MAX(created) AS enrolled',
				'MAX(created) AS max_created',
			],
			'where'    => empty( $args['filters'] ) ? '' : $args['filters'],
			'group_by' => [ 'user_id', 'product_id', 'course_id' ],
			'having'   => 'SUM(status) > 0',
		] );

		$outer_query .= $query;

		$outer_query .= ') as access GROUP BY ID';

		if ( ! empty ( $args['order_by'] ) ) {
			$outer_query .= $this->build_order_by_clause( $args['order_by'] );
		}

		if ( ! empty ( $args['limit'] ) ) {
			$outer_query .= ' LIMIT ' . implode( ',', $args['limit'] );
		}

		return $this->wpdb->get_results( $outer_query, ARRAY_A );
	}

	/**
	 * Get the products the user has access to based on some type of role
	 *
	 * @param $user_id
	 *
	 * @return array|object|\stdClass[]|null
	 */
	public function get_role_accesses( $user_id ) {
		$query = $this->build_report_query( [
			'select'   => [
				'user_id as ID',
				'product_id',
				'source',
				'MAX(created) as enrolled',
			],
			'where'    => [
				'user_id' => [ $user_id ],
				'source'  => [ 'sendowl_product', 'wishlist', 'memberpress', 'wordpress', 'membermouse', 'membermouse_bundle' ],
			],
			'group_by' => [ 'product_id' ],
			'having'   => 'SUM(status) > 0',
		] );

		return $this->wpdb->get_results( $query, ARRAY_A );
	}

	/**
	 * Builds the WHERE clause for the history table
	 *
	 * @param $filters
	 *
	 * @return array[]
	 */
	private function build_where_clause( $filters = [] ) {
		$where  = [];
		$params = [];

		$allowed_where = [ 'IS NOT NULL', 'IS NULL' ];

		foreach ( [ 'user_id', 'product_id', 'course_id', 'status' ] as $filter_key ) {

			if ( ! empty( $filters[ $filter_key ] ) ) {
				if ( is_array( $filters[ $filter_key ] ) ) {
					$ids = [];
					foreach ( $filters[ $filter_key ] as $id ) {
						$ids[]    = '%d';
						$params[] = $id;
					}
					$where[] = "$filter_key IN(" . implode( ',', $ids ) . ')';
				} else if ( is_numeric( $filters[ $filter_key ] ) ) {
					$where[]  = "$filter_key='%d'";
					$params[] = $filters[ $filter_key ];
				} else if ( is_string( $filters[ $filter_key ] ) && in_array( $filters[ $filter_key ], $allowed_where ) ) {
					$where[] = "$filter_key {$filters[ $filter_key ]}";
				}
			}
		}

		if ( ! empty( $filters['date'] ) && count( $filters['date'] ) > 1 ) {

			list( $from, $to ) = array_values( $filters['date'] );

			if ( ! empty( $from ) && ! empty( $to ) ) {

				$params[] = gmdate( 'Y-m-d', strtotime( $from ) );
				$params[] = gmdate( 'Y-m-d', strtotime( $to ) );

				$where[] = 'DATE(created) BETWEEN %s AND %s';
			}
		}

		/**
		 * Search by user email
		 */
		if ( ! empty( $filters['s'] ) && is_string( $filters['s'] ) ) {
			global $wpdb;
			$where[]  = "user_id IN(SELECT ID FROM $wpdb->users WHERE user_email LIKE '%%%s%%' OR display_name LIKE '%%%s%%')";
			$params[] = trim( $filters['s'] );
			$params[] = trim( $filters['s'] );
		}

		/**
		 * filter by source
		 */
		if ( ! empty( $filters['source'] ) && is_array( $filters['source'] ) ) {
			$sources         = [];
			$source_operator = empty( $filters['source_operator'] ) || ! in_array( $filters['source_operator'], [ 'IN', 'NOT IN' ] ) ? 'IN' : $filters['source_operator'];

			foreach ( array_filter( $filters['source'] ) as $source ) { //array_filter to remove empty values
				$sources[] = '%s';
				$params[]  = $source;
			}
			if ( ! empty( $sources ) ) {
				$where[] = "source $source_operator(" . implode( ',', $sources ) . ')';
			}
		}

		return [
			$where,
			$params,
		];
	}

	/**
	 * Builds the ORDER BY clause for the history table
	 *
	 * @param $filters
	 *
	 * @return string
	 */
	private function build_order_by_clause( $filters = [] ) {
		$order_by = '';

		if ( ! empty( $filters ) && is_array( $filters ) ) {

			$order = [];
			foreach ( $filters as $order_key => $order_dir ) {

				$dir = strtoupper( trim( $order_dir ) );

				if ( in_array( $dir, [ '', 'ASC', 'DESC' ] ) ) {
					$order[] = "$order_key $dir";
				}
			}

			if ( ! empty( $order ) ) {
				$order_by .= ' ORDER BY ' . implode( ',', $order );
			}
		}

		return $order_by;
	}

	/**
	 * @param array $filters
	 *
	 * @return string|null
	 */
	private function build_report_query( $filters ) {
		$filters = array_merge( [
			'select'   => [],
			'where'    => [],
			'group_by' => [],
			'having'   => [],
			'order_by' => [],
			'limit'    => [],
		], $filters );

		list( $select, $where, $group_by, $having, $order_by, $limit ) = [
			$filters['select'],
			$filters['where'],
			$filters['group_by'],
			$filters['having'],
			$filters['order_by'],
			$filters['limit'],
		];

		$query = 'SELECT ' . implode( ',', $select ) . " FROM $this->name";

		list( $where, $params ) = $this->build_where_clause( $where );

		if ( ! empty( $where ) ) {
			$query .= ' WHERE ' . implode( ' AND ', $where );
		}

		if ( ! empty( $group_by ) ) {
			$query .= ' GROUP BY ' . implode( ',', $group_by );
		}

		if ( ! empty( $having ) ) {
			$query .= ' HAVING ' . $having;
		}

		if ( ! empty( $order_by ) && is_array( $order_by ) ) {
			$query .= $this->build_order_by_clause( $order_by );
		}

		if ( ! empty( $limit ) ) {
			$query .= ' LIMIT ' . implode( ',', $limit );
		}

		if ( empty( $params ) ) {
			return $query;
		} else {
			return $this->wpdb->prepare( $query, $params );
		}
	}
}
