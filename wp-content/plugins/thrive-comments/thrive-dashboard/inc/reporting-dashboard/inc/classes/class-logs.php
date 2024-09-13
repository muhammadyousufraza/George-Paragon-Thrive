<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */

namespace TVE\Reporting;

use TVE\Reporting\EventFields\Item_Id;
use TVE\Reporting\EventFields\Post_Id;
use TVE\Reporting\EventFields\User_Id;

class Logs {

	use \TD_Singleton;

	const TABLE_NAME = 'thrive_reporting_logs';

	/** @var \Tve_Wpdb */
	protected $db;

	/**
	 * @var string
	 */
	private $select;
	/**
	 * @var string
	 */
	private $where = '';
	/**
	 * @var string
	 */
	private $group_by = '';

	/**
	 * @var string
	 */
	private $order_by = '';

	/**
	 * @var string
	 */
	private $limit = '';
	/**
	 * @var string[]
	 */
	private $args = [];

	/**
	 * @var string
	 */
	private $table;

	public function __construct() {
		$this->db = \Tve_Wpdb::instance();

		$this->table = $GLOBALS['wpdb']->prefix . static::TABLE_NAME;
	}

	/**
	 * @param Event|mixed $event
	 *
	 * @return bool|int|\mysqli_result|resource|null
	 */
	public function insert( $event ) {
		$log_data = $event->get_log_data();

		return $this->db->insert( $this->table, $log_data );
	}

	public function update( $event, $id, $fields_to_update ) {
		$log_data = $event->get_log_data( $fields_to_update );

		return $this->db->update( $this->table, $log_data, [ 'id' => $id ] );
	}

	public function get_row() {
		return $this->db->get_row( $this->prepare_query() );
	}

	/**
	 * @param $event_type
	 * @param $field
	 * @param $values
	 *
	 * @return array|object|\stdClass[]|null
	 */
	public function get_fields( $event_type, $field, $values = [] ) {
		$this->args = [];

		$this->where  = "event_type='%s'";
		$this->args[] = $event_type;

		if ( ! empty( $values ) ) {
			$this->where .= sprintf( ' AND %s IN ( %s )', $field, implode( ', ', $values ) );
		}

		//exp:   'SELECT DISTINCT item_id FROM wp_thrive_reporting_logs WHERE event_type = "tqb_quiz_completed"';
		$query = sprintf(
			"SELECT DISTINCT %s as 'value' FROM %s WHERE %s",
			$field,
			$this->table,
			// phpcs:ignore
			$this->db->prepare( $this->where, $this->args )
		);

		/* @codingStandardsIgnoreLine */
		return $this->db->get_results( $query, ARRAY_A );
	}

	/**
	 * Remove logs
	 *
	 * @param $field_key
	 * @param $field_value
	 * @param $format
	 *
	 * @return bool|int|\mysqli_result|resource|null
	 */
	public function remove_by( $field_key, $field_value, $format = '%d' ) {
		return $this->db->delete( $this->table, [ $field_key => $field_value ], [ $format ] );
	}

	/**
	 * @param array $where_args
	 *
	 * @return \Tve_Wpdb
	 */
	public function delete( array $where_args ): \Tve_Wpdb {
		$conditions = [];
		$values     = [];

		if ( isset( $where_args['event_type'] ) ) {
			$conditions[] = 'event_type IN (' . implode( ',', array_fill( 0, count( $where_args['event_type'] ), "'%s'" ) ) . ')';
			$values       = array_values( $where_args['event_type'] );
			unset( $where_args['event_type'] );
		}

		foreach ( $where_args as $field => $value ) {
			$conditions[] = "`$field` = " . $value;
		}

		$conditions = implode( ' AND ', $conditions );

		return $this->db->do_query( $this->db->prepare( "DELETE FROM `$this->table` WHERE $conditions", $values ) );
	}

	/**
	 * @param array $args
	 *
	 * @return $this
	 */
	public function set_query( array $args = [] ): Logs {

		/* reset query */
		$this->select   = '';
		$this->where    = '';
		$this->group_by = '';
		$this->order_by = '';

		if ( empty( $args['fields'] ) ) {
			$this->select = '*';
		} elseif ( is_string( $args['fields'] ) ) {
			$this->select = $args['fields'];
		} elseif ( is_array( $args['fields'] ) ) {
			$this->select = implode( ', ', $args['fields'] );
		}

		$this->args = [];

		if ( isset( $args['event_type'] ) ) {
			if ( is_array( $args['event_type'] ) ) {
				/* fill an array with %s for each event type */
				$this->where .= 'event_type IN (' . join( ',', array_fill( 0, count( $args['event_type'] ), "'%s'" ) ) . ')';

				$this->args = array_merge( $this->args, array_values( $args['event_type'] ) );
			} else {
				$this->where  = "event_type='%s'";
				$this->args[] = $args['event_type'];
			}
		} else {
			$this->where = '1';
		}

		if ( ! empty( $args['filters'] ) && is_array( $args['filters'] ) ) {
			foreach ( $args['filters'] as $key => $values ) {
				if ( empty( $values ) ) {
					continue;
				}

				$this->set_filter( $key, $values );
			}
		}

		if ( ! empty( $args['group_by'] ) ) {
			$group_by = is_string( $args['group_by'] ) ? $args['group_by'] : implode( ', ', $args['group_by'] );

			$this->group_by = " GROUP BY $group_by";

			$this->select .= ', COUNT(' . $args['count'] . ') AS count';
		}

		if ( empty( $args['page'] ) || empty( $args['items_per_page'] ) ) {
			$this->limit = '';
		} else {
			$items_per_page = (int) $args['items_per_page'];
			$this->limit    = sprintf( ' LIMIT %d, %d', ( (int) $args['page'] - 1 ) * $items_per_page, $items_per_page );
		}

		if ( ! empty( $args['order_by'] ) && ! empty( $args['order_by_direction'] ) ) {
			$this->order_by = ' ORDER BY ' . $args['order_by'] . ' ' . $args['order_by_direction'];
		}

		return $this;
	}

	/**
	 * @param $key
	 * @param $values
	 *
	 * @return void
	 */
	public function set_filter( $key, $values ) {
		switch ( $key ) {
			case 'created':
				if ( ! empty( $values['from'] ) ) {
					/* extracts the date from the full date-time - if we also need the time at some point, modify this */
					$this->where .= " AND DATE(created) >= '%s'";

					$this->args[] = $values['from'];
				}

				if ( ! empty( $values['to'] ) ) {
					$this->where .= " AND DATE(created) <= '%s'";

					$this->args[] = $values['to'];
				}
				break;
			case User_Id::key():
			case Post_Id::key():
			case Item_Id::key():
			default:
				if ( is_array( $values ) ) {
					$this->where .= sprintf( ' AND %s IN ( %s )', $key, implode( ', ', $values ) );
				} else {
					$this->where .= " AND $key='%s'";

					$this->args[] = $values;
				}
				break;
		}
	}

	/**
	 * @return array|object|\stdClass[]|null
	 */
	public function get_results() {
		return $this->db->get_results( $this->prepare_query(), ARRAY_A );
	}

	public function query() {
		$this->db->do_query( $this->prepare_query() );
	}

	public function get_one_row() {
		return $this->db->get_one_row();
	}

	public function count_results() {
		$count = $this->db->do_query( $this->prepare_query( true ) )->num_rows();

		return empty( $count ) ? 0 : $count;
	}

	/**
	 * Get date format depending on the range we use
	 *
	 * @return string
	 */
	public function get_date_format( $min_date = 0, $max_date = 0 ) {
		if ( empty( $this->min_date ) || empty( $this->max_date ) ) {
			$min_max_date = $this->db->do_query( "SELECT MAX(created) AS max_date, MIN(created) AS min_date FROM $this->table" )->get_one_row();

			$this->min_date = empty( $min_max_date->min_date ) ? time() : strtotime( $min_max_date->min_date );
			$this->max_date = empty( $min_max_date->max_date ) ? time() : strtotime( $min_max_date->max_date );
		}

		$from = empty( $min_date ) ? $this->min_date : max( $this->min_date, strtotime( $min_date ) );
		$to   = empty( $max_date ) ? $this->max_date : min( $this->max_date, strtotime( $max_date ) );

		$days = ( $to - $from ) / DAY_IN_SECONDS;

		if ( $days > 30 * 12 * 10 ) {
			/* display years if we have at least 10 */
			$format = 'year';
		} elseif ( $days > 30 * 10 ) {
			/* display months if we have at least 10 */
			$format = 'month';
		} elseif ( $days > 7 * 10 ) {
			/* display weeks if we have at least 10 */
			$format = 'week';
		} else {
			$format = 'day';
		}

		return $format;
	}

	/**
	 * sum all counts from the query
	 *
	 * @return mixed|null
	 */
	public function sum_results_count() {
		$query = $this->prepare_query();

		$query = "SELECT SUM(`items`.count) AS total from ($query) as `items`";

		$row = $this->db->get_row( $query, ARRAY_A );

		return empty( $row ) ? null : $row['total'];
	}

	protected function prepare_query( $count_results = false ) {
		if ( $count_results ) {
			$this->order_by = '';
			$this->limit    = '';
		}

		/* @codingStandardsIgnoreLine */
		$where = "WHERE $this->where $this->group_by $this->order_by $this->limit";

		// phpcs:ignore
		return "SELECT $this->select FROM $this->table " . $this->db->prepare( $where, $this->args );
	}
}
