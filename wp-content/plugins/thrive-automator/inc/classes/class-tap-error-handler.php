<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-automator
 */

namespace Thrive\Automator;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Error_Log_Handler
 *
 * @package Thrive\Automator
 */
class Error_Log_Handler {

	/**
	 * Instance running with each running automation
	 */
	protected static $_instances = [];

	/**
	 * Error holder
	 */
	protected $errors = [];

	/**
	 * Actual raw data provided by trigger
	 */
	protected $raw_data = [];

	/**
	 * Automation identifier
	 */
	protected $automation_id;

	/**
	 * Automation identifier
	 */
	public $test_environment = false;

	/**
	 * Error log display time intervals
	 */
	public static function get_available_intervals() {
		return [
			'7'   => [
				'label' => __( 'Last 7 days', 'thrive-automator' ),
				'key'   => 7,
			],
			'14'  => [
				'label' => __( 'Last 14 days', 'thrive-automator' ),
				'key'   => 14,
			],
			'30'  => [
				'label' => __( 'Last 30 days', 'thrive-automator' ),
				'key'   => 30,
			],
			'all' => [
				'label' => __( 'All entries', 'thrive-automator' ),
				'key'   => 'all',
			],
		];
	}

	/**
	 * Only one instance available at a time
	 *
	 * @param $aut_id
	 *
	 * @return Error_Log_Handler
	 */
	public static function instance( $aut_id ): Error_Log_Handler {
		if ( empty( static::$_instances[ $aut_id ] ) ) {
			static::$_instances[ $aut_id ] = new self();
		}

		return static::$_instances[ $aut_id ];
	}

	/**
	 * Register each error for the running automation
	 *
	 * @param array $error
	 *
	 */
	public function register( array $error ) {
		if ( empty( $this->errors[ $error['key'] ] ) ) {
			$this->errors[ $error['key'] ] = [];
		}
		$this->errors[ $error['key'] ][ $error['id'] ]['message'] = $error['message'];
		$this->errors[ $error['key'] ][ $error['id'] ]['label']   = $error['class-label'];
	}

	/**
	 * Set raw data provided by the trigger for current instance
	 *
	 * @param int   $id
	 * @param array $raw_data
	 */
	public function set_raw_data( $id, $raw_data ) {
		$this->clear();

		$this->automation_id = $id;
		$this->raw_data      = serialize( $raw_data );
	}

	public function log_success( $success ) {
		$success_log                                                    = [];
		$success_log[ $success['key'] ][ $success['id'] ]['message']    = $success['message'];
		$success_log[ $success['key'] ][ $success['id'] ]['label']      = $success['class-label'];
		$success_log[ $success['key'] ][ $success['id'] ]['is_success'] = true;

		global $wpdb;

		$log_table = $wpdb->prefix . TAP_DB_PREFIX . 'error_log';

		$log_data = array(
			'date_started'  => date( 'Y-m-d H:i:s' ),
			'error'         => json_encode( $success_log ),
			'automation_id' => (int) $this->automation_id,
			'raw_data'      => $this->raw_data,
		);
		if ( ! $this->test_environment ) {
			$wpdb->insert( $log_table, $log_data );
			static::limit_logs();
		}
	}

	/**
	 * Save instance as error log
	 */
	public function log() {
		if ( ! $this->test_environment ) {
			$this->insert_log( $this->errors, $this->raw_data, $this->automation_id );
		}
		$this->clear();
	}


	public function insert_log( $errors = [], $raw_data = [], $automation_id = 0 ) {
		if ( count( $errors ) > 0 ) {
			if ( empty( $automation_id ) ) {
				$automation_id = $this->automation_id;
			}
			global $wpdb;

			$log_table = $wpdb->prefix . TAP_DB_PREFIX . 'error_log';

			$log_data = array(
				'date_started'  => date( 'Y-m-d H:i:s' ),
				'error'         => json_encode( $errors ),
				'automation_id' => (int) $automation_id,
				'raw_data'      => is_array( $raw_data ) ? serialize( $raw_data ) : $raw_data,
			);
			if ( ! $this->test_environment ) {
				$wpdb->insert( $log_table, $log_data );
				static::limit_logs();
			}
		}
	}


	/**
	 * Clear the instance after errors for the automation have been logged
	 */
	public function clear() {
		$this->errors   = [];
		$this->raw_data = [];

		$this->automation_id = null;
	}

	/**
	 * Limit number of logs
	 */
	public static function limit_logs() {
		$settings       = static::get_log_settings();
		$allowed_number = $settings['max_entries'];
		if ( is_numeric( $allowed_number ) && static::get_log_count() > (int) $allowed_number ) {
			static::delete_oldest_log_entries( 1 );
		}
	}

	public static function get_automation_logs( int $aut_id ) {
		global $wpdb;
		$log_table = static::get_table_name();
		$sql       = $wpdb->prepare( "SELECT * FROM {$log_table} WHERE automation_id = %d ORDER BY date_started DESC", $aut_id );

		return $wpdb->get_results( $sql, ARRAY_A );
	}

	/**
	 * Retrieve error log entries for specific automation and specific interval
	 *
	 * @param numeric $id
	 * @param string  $interval
	 * @param int     $page
	 *
	 * @return array
	 */
	public static function get_error_log( $id, string $interval = 'all', int $page = 1 ): array {
		$sql_interval = '';
		if ( ! empty( $interval ) && is_numeric( $interval ) ) {
			$sql_interval = ' AND date_started >= (NOW() - INTERVAL ' . $interval . ' DAY)';
		}
		$settings = static::get_log_settings();
		$no_rows  = (int) $settings['rows'];
		$page     = $page ?? 0;

		global $wpdb;
		if ( ! empty( $id ) && is_numeric( $id ) ) {
			$query       = $wpdb->prepare( 'SELECT * FROM ' . static::get_table_name() . ' WHERE automation_id = %d' . $sql_interval . ' ORDER BY id DESC LIMIT %d OFFSET %d', [
				$id,
				$no_rows,
				$page * $no_rows,
			] );
			$total_count = static::get_log_count( $id );
		} else {
			$query       = $wpdb->prepare( 'SELECT * FROM ' . static::get_table_name() . ' WHERE 1 = %d ' . $sql_interval . ' ORDER BY id DESC  LIMIT %d OFFSET %d', [
				1,
				$no_rows,
				$page * $no_rows,
			] );
			$total_count = static::get_log_count();
		}

		$query_results = $wpdb->get_results( $query, ARRAY_A );
		$data          = [];
		if ( ! empty( $query_results ) ) {
			foreach ( $query_results as $result ) {
				$result['error']    = json_decode( $result['error'] );
				$result['raw_data'] = var_export( unserialize( $result['raw_data'] ), true );
				$data[]             = $result;
			}
		}


		return array( 'count' => $total_count, 'logs' => $data );
	}

	/**
	 * Get log entries count
	 *
	 * @param int $automation_id
	 *
	 * @return int
	 */
	public static function get_log_count( int $automation_id = null ): int {
		if ( $automation_id !== null ) {
			$sql = 'SELECT COUNT(*) FROM ' . static::get_table_name() . ' WHERE automation_id =' . $automation_id;
		} else {
			$sql = 'SELECT COUNT(*) FROM ' . static::get_table_name() . ' WHERE 1=1';
		}

		global $wpdb;

		return (int) $wpdb->get_var( $sql );
	}

	/**
	 * Delete specific log entry
	 *
	 * @param int $log_id
	 *
	 * @return bool
	 */
	public static function delete_error_log( int $log_id ): bool {

		$delete_result = false;
		if ( ! empty( $log_id ) ) {

			global $wpdb;

			$delete_result = $wpdb->delete( static::get_table_name(), [ 'id' => $log_id ], [ '%d' ] );
		}

		return $delete_result;
	}

	/**
	 * Clear log table
	 *
	 * @return bool
	 */
	public static function clear_log(): bool {
		return static::delete_oldest_log_entries( 'all' );
	}

	/**
	 * Delete oldest log entries
	 *
	 * @param $count
	 *
	 * @return bool
	 */
	public static function delete_oldest_log_entries( $count ): bool {
		global $wpdb;
		if ( is_numeric( $count ) ) {
			$sql = 'DELETE FROM ' . static::get_table_name() . ' ORDER BY id ASC LIMIT ' . $count;
		} else {
			$sql = 'TRUNCATE TABLE ' . static::get_table_name();
		}

		return $wpdb->query( $sql );
	}

	/**
	 * Get error log table name
	 *
	 * @return string
	 */
	public static function get_table_name(): string {
		global $wpdb;

		return $wpdb->prefix . TAP_DB_PREFIX . 'error_log';
	}

	/**
	 * Get error log allowed entries count
	 *
	 * @return string
	 */
	public static function get_log_settings() {
		return get_option( 'tap_log_settings', [ 'rows' => 10, 'max_entries' => 500 ] );
	}

	/**
	 * Save error log allowed entries count
	 *
	 * @param array $settings
	 *
	 * @return bool
	 */
	public static function save_log_settings( array $settings ): bool {
		$old_settings = static::get_log_settings();
		$settings     = array_merge( $old_settings, $settings );

		if ( is_numeric( $settings['max_entries'] ) ) {
			$current_count = static::get_log_count();
			if ( $current_count > $settings['max_entries'] ) {
				static::delete_oldest_log_entries( $current_count - $settings['max_entries'] );
			}
		}

		update_option( 'tap_log_settings', $settings, 'no' );

		return true;
	}

	/**
	 * Get error log table name
	 *
	 * @param $class
	 *
	 * @return string
	 */
	public function get_nice_class_name( $class ): string {
		return str_replace( '_', ' ', basename( $class ) );
	}


	public function set_test_environment( $is_test = true ) {
		$this->test_environment = $is_test;
	}


}

function tap_logger( $automation_id = 0 ): Error_Log_Handler {
	return Error_Log_Handler::instance( $automation_id );
}
