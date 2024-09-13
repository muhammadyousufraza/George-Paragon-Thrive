<?php
/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 12/5/2017
 * Time: 1:26 PM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

class Thrive_AB_Report_Manager {

	static public $_transient_stats_name = 'tab_dashboard_stats';
	/**
	 * @var wpdb
	 */
	protected $_wpdb;

	/**
	 * Database Log Code for Impressions
	 * @var int
	 */
	private $_log_impression = 1;

	/**
	 * Database Log Code for Conversions
	 * @var int
	 */
	private $_log_conversion = 2;

	/**
	 * Thrive_AB_Report_Manager constructor.
	 */
	public function __construct() {

		global $wpdb;

		$this->_wpdb = $wpdb;
	}

	public function conversion_rate( $impressions, $conversions ) {

		$conversions     = (int) $conversions;
		$impressions     = (int) $impressions;
		$conversion_rate = 0.0;

		if ( $conversions !== 0 && $impressions !== 0 ) {
			$conversion_rate = round( 100 * ( $conversions / $impressions ), 2 );
		}

		return $conversion_rate;
	}

	/**
	 * Computes the data necessary for the test in the report view
	 *
	 * @param array $filters
	 *
	 * @return array
	 */
	public function get_test_chart_data( $filters = array() ) {

		$test       = new Thrive_AB_Test( $filters['test_id'] );
		$test_items = $test->get_items();
		$test_data  = $test->get_data();

		$defaults = array(
			'interval'   => 'day',
			'start_date' => date( 'Y-m-d H:i:s', strtotime( $test_data['date_started'] ) ),
		);

		if ( $test_data['status'] !== 'running' ) {
			$defaults['end_date'] = date( 'Y-m-d H:i:s', strtotime( $test_data['date_completed'] ) );
		} else {
			$defaults['end_date'] = date( 'Y-m-d' ) . ' 23:59:59';
		}

		$filters = array_merge( $defaults, $filters );

		$variations = array();
		foreach ( $test_items as $item ) {
			$variations[ $item->variation_id ] = array(
				'name'         => html_entity_decode( $item->title ),
				'revenue'      => $item->revenue,
				'is_active'    => $item->active,
				'stopped_date' => $item->stopped_date,
			);
		}

		$log_data = $this->get_test_chart_db_data( $filters );

		/* generate dates for X Axis and to later fill the empty data */
		$dates = $this->generate_dates_interval( $filters['start_date'], $filters['end_date'], $filters['interval'] );

		$chart_data_temp = array();
		foreach ( $log_data as $interval ) {
			if ( empty( $chart_data_temp[ $interval->variation_id ] ) ) {
				$chart_data_temp[ $interval->variation_id ][ $this->_log_impression ] = array();
				$chart_data_temp[ $interval->variation_id ][ $this->_log_conversion ] = array();
			}

			if ( $filters['interval'] == 'day' ) {
				$interval->date_interval = date( 'd M, Y', strtotime( $interval->date_interval ) );
			}

			if ( $test_data['type'] == 'monetary' ) {
				$chart_data_temp[ $interval->variation_id ]['revenue'][ $interval->date_interval ] = $interval->revenue;
			}
			$chart_data_temp[ $interval->variation_id ][ intval( $interval->event_type ) ][ $interval->date_interval ] = intval( $interval->log_count );
		}

		$chart_data        = array();
		$total_over_time   = 0;
		$total_impressions = 0;
		$total_conversions = 0;
		if ( ! empty( $filters['type'] ) && $filters['type'] == 'conversion_rate' ) {
			$test_data['type'] = $filters['type'];
		}
		foreach ( $variations as $id => $test_items_data ) {
			$item_dates = $dates;
			if ( ! $test_items_data['is_active'] ) {
				$item_dates = $this->generate_dates_interval( $filters['start_date'], $test_items_data['stopped_date'], $filters['interval'] );
			}

			if ( empty( $chart_data[ $id ] ) ) {
				$chart_data[ $id ]['id']   = $id;
				$chart_data[ $id ]['name'] = $test_items_data['name'];
				$chart_data[ $id ]['data'] = array();
			}

			$revenue = $impressions = $conversions = $variation_max_over_time = 0;
			foreach ( $item_dates as $key => $date ) {
				$impressions += empty( $chart_data_temp[ $id ][ $this->_log_impression ][ $date ] ) ? 0 : $chart_data_temp[ $id ][ $this->_log_impression ][ $date ];
				$conversions += empty( $chart_data_temp[ $id ][ $this->_log_conversion ][ $date ] ) ? 0 : $chart_data_temp[ $id ][ $this->_log_conversion ][ $date ];
				$revenue     += empty( $chart_data_temp[ $id ]['revenue'][ $date ] ) ? 0 : $chart_data_temp[ $id ]['revenue'][ $date ];

				$tmp = 0;

				if ( ! empty( $impressions ) && ! empty( $conversions ) ) {
					/* Complete with zero on empty dates */

					$tmp = $this->generate_chart_entry_value( $test_data['type'], $impressions, $conversions, $revenue );
				}

				$chart_data[ $id ]['data'][] = $tmp;
				$variation_max_over_time     = $tmp;
			}
			$total_impressions += $impressions;
			$total_conversions += $conversions;
			$total_over_time   += $variation_max_over_time;
		}

		$return = array(
			'ID'              => $filters['test_id'],
			'title'           => __( 'Total conversions over time', 'thrive-ab-page-testing' ),
			'data'            => $chart_data,
			'x_axis'          => $dates,
			'y_axis'          => __( 'Total visits', 'thrive-ab-page-testing' ),
			'total_over_time' => $test_data['type'] === 'conversion_rate' ? $this->conversion_rate( $total_impressions, $total_conversions ) : $total_over_time,
			'test_type_txt'   => '',
		);

		if ( $test_data['type'] === 'monetary' ) {
			$return['title']         = __( 'Total revenue over time', 'thrive-ab-page-testing' );
			$return['y_axis']        = __( 'Total revenue', 'thrive-ab-page-testing' ) . ' $';
			$return['test_type_txt'] = '$';
		} elseif ( $test_data['type'] === 'optins' ) {
			$return['title']  = __( 'Total subscriptions over time', 'thrive-ab-page-testing' );
			$return['y_axis'] = __( 'Total subscriptions', 'thrive-ab-page-testing' );
		} elseif ( $test_data['type'] === 'conversion_rate' ) {
			$return['title']         = __( 'Page conversion rate', 'thrive-ab-page-testing' );
			$return['y_axis']        = __( 'Conversion rate (%)', 'thrive-ab-page-testing' );
			$return['test_type_txt'] = '%';
		}

		return $return;
	}

	/**
	 * Depending on the test type, it generates the data entry
	 *
	 * @param $test_type
	 * @param $conversions
	 * @param $impressions
	 * @param $revenue
	 *
	 * @return float|int
	 */
	private function generate_chart_entry_value( $test_type, $impressions, $conversions, $revenue ) {
		$return = $conversions;

		if ( $test_type === 'conversion_rate' ) {
			$return = $this->conversion_rate( $impressions, $conversions );
		} elseif ( $test_type === 'monetary' ) {
			$return = (float) $revenue;
		}

		return $return;
	}

	/**
	 * Generate an array of dates between $start_date and $end_date depending on the $interval
	 *
	 * @param        $start_date
	 * @param        $end_date
	 * @param string $interval - can be 'day', 'week', 'month'
	 *
	 * @return array $dates
	 */
	private function generate_dates_interval( $start_date, $end_date, $interval = 'day' ) {

		/* just to make sure the end day has the latest hour */
		$end_date = date( 'Y-m-d', strtotime( $end_date ) ) . ' 23:59:59';

		switch ( $interval ) {
			case 'day':
				$date_format = 'd M, Y';
				break;
			case 'week':
				$date_format = '\W\e\e\k W, o';
				break;
			case 'month':
				$date_format = 'F Y';
				break;
			default:
				$date_format = 'Y-m-d';
				break;
		}

		$dates = array();
		for ( $i = 0; strtotime( $start_date . ' + ' . $i . 'day' ) <= strtotime( $end_date ); $i ++ ) {
			$timestamp = strtotime( $start_date . ' + ' . $i . 'day' );
			$date      = date( $date_format, $timestamp );

			//remove the 0 from the week number
			if ( $interval == 'week' ) {
				$date = str_replace( 'Week 0', 'Week ', $date );
			}
			if ( ! in_array( $date, $dates ) ) {
				$dates[] = $date;
			}
		}

		return $dates;
	}

	/**
	 * Interrogates the DB and returns data necessary for the chart
	 *
	 * @param $filters
	 *
	 * @return array|null|object
	 */
	public function get_test_chart_db_data( $filters ) {
		$date_interval = '';
		switch ( $filters['interval'] ) {
			case 'month':
				$date_interval = 'CONCAT(MONTHNAME(`log`.`date`)," ", YEAR(`log`.`date`)) as date_interval';
				break;
			case 'week':
				$year          = 'IF( WEEKOFYEAR(`log`.`date`) = 1 AND MONTH(`log`.`date`) = 12, 1 + YEAR(`log`.`date`), YEAR(`log`.`date`) )';
				$date_interval = "CONCAT('Week ', WEEKOFYEAR(`log`.`date`), ', ', {$year}) as date_interval";
				break;
			default:
			case 'day':
				$date_interval = 'DATE(`log`.`date`) as date_interval';
				break;
		}

		$sql = 'SELECT IFNULL(COUNT( DISTINCT log.id ), 0) AS log_count, variation_id, event_type, SUM( log.revenue ) as revenue, ' . $date_interval . '
 				FROM ' . thrive_ab()->table_name( 'event_log' ) . ' AS log WHERE 1 ';

		$params = array();
		if ( ! empty( $filters['test_id'] ) ) {
			$sql       .= 'AND `test_id` = %d ';
			$params [] = $filters['test_id'];
		}

		if ( ! empty( $filters['start_date'] ) && ! empty( $filters['end_date'] ) ) {
			$sql       .= 'AND `date` BETWEEN %s AND %s ';
			$params [] = $filters['start_date'];
			$params [] = $filters['end_date'];
		}

		$sql .= ' GROUP BY variation_id, event_type, date_interval';

		return $this->_wpdb->get_results( $this->_wpdb->prepare( $sql, $params ) );
	}

	public function get_admin_dashboard_stats() {

		if ( false !== ( $results = get_transient( self::$_transient_stats_name ) ) ) {
			return $results;
		}

		$query  = 'SELECT * FROM ' . thrive_ab()->table_name( 'event_log' ) . ' WHERE DATE(date) >= DATE(DATE_SUB(NOW(), INTERVAL 1 MONTH))';
		$return = array();

		$return['unique_visitors']   = 0;
		$return['total_conversions'] = 0;
		$return['conversion_rate']   = 0;

		$results = $this->_wpdb->get_results( $query, ARRAY_A );

		if ( empty( $results ) ) {
			return $return;
		}

		foreach ( $results as $entry ) {

			if ( (int) $entry['event_type'] === $this->_log_impression ) {
				$return['unique_visitors'] ++;
			} elseif ( (int) $entry['event_type'] === $this->_log_conversion ) {
				$return['total_conversions'] ++;
			}
		}

		if ( $return['total_conversions'] !== 0 && $return['unique_visitors'] !== 0 ) {
			$return['conversion_rate'] = round( 100 * ( $return['total_conversions'] / $return['unique_visitors'] ), 2 );
		}

		set_transient( self::$_transient_stats_name, $return, 8 * HOUR_IN_SECONDS );

		return $return;

	}
}
