<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

class TD_DB_Migration {

	/**
	 * @var array
	 */
	protected $_queries = array();

	/**
	 * @var string
	 */
	protected $_table_prefix;

	/**
	 * @var wpdb
	 */
	protected $_wpdb;

	/**
	 * Full path to the migration file
	 *
	 * @var string
	 */
	protected $_file_path;

	/**
	 * TD_DB_Migration constructor.
	 *
	 * Each migration works with prefixed tables
	 *
	 * @param string $table_prefix plugin's table prefix
	 * @param string $file_path    full absolute path to the migration file
	 */
	public function __construct( $table_prefix, $file_path = '' ) {

		global $wpdb;

		$this->_wpdb         = $wpdb;
		$this->_file_path    = $file_path;
		$this->_table_prefix = $this->_wpdb->prefix . ( empty( $table_prefix ) ? '' : rtrim( $table_prefix, '_ ' ) . '_' );
	}

	/**
	 * Based on the prefix sent on initialization returns the name of the table
	 * with {wp_prefix}_{plugin_prefix}_{name}
	 *
	 * @param $name
	 *
	 * @return string
	 */
	public function get_table_name( $name ) {
		$name = preg_replace( '#^' . $this->_table_prefix . '#', '', $name );

		return $this->_table_prefix . $name;
	}

	/**
	 * Adds and sql query to the queue for later execution
	 *
	 * @param string $sql
	 * @param bool   $collate whether or not to add COLLATE specification to the query
	 *
	 * @return TD_DB_Migration allows fluent interface
	 */
	public function add( $sql, $collate = true ) {

		return $this->add_query( trim( $sql ) . ( $collate ? ' ' . $this->_wpdb->get_charset_collate() : '' ) );
	}

	/**
	 * Adds a raw query to the queue
	 *
	 * @param string $query
	 *
	 * @return TD_DB_Migration allows fluent interface
	 */
	public function add_query( $query ) {
		$query             = preg_replace_callback( '#\{(.+?)\}#', array( $this, 'preg_replace_table_name' ), $query );
		$this->_queries [] = $query;

		return $this;
	}

	/**
	 * Adds a CREATE TABLE query to the queue
	 *
	 * @param string $table_name
	 * @param string $spec             full table spec, excluding brackets
	 * @param bool   $add_collate_spec whether to add collate specification
	 *
	 * @return TD_DB_Migration allows fluent interface$this
	 */
	public function create_table( $table_name, $spec, $add_collate_spec = false ) {
		$table_name = $this->_prepare_table_name( $table_name );

		$this->add_query( "CREATE TABLE IF NOT EXISTS `{$table_name}` ({$spec})" . ( $add_collate_spec ? ( ' ' . $this->_wpdb->get_charset_collate() ) : '' ) );

		return $this;
	}

	/**
	 * Adds or modifies a column from a table. First, it checks if the column exists
	 *
	 * @param string $table_name
	 * @param string $column_name
	 * @param string $spec
	 *
	 * @return TD_DB_Migration allows fluent interface
	 */
	public function add_or_modify_column( $table_name, $column_name, $spec ) {
		$table_name = $this->_prepare_table_name( $table_name );
		$sql        = "ALTER TABLE `{$table_name}` ";
		$exists     = $this->column_exists( $table_name, $column_name ) ? "CHANGE `{$column_name}` " : 'ADD COLUMN ';

		$this->add_query( $sql . $exists . "`{$column_name}` {$spec}" );

		return $this;
	}

	/**
	 * Drops column from table if exists
	 *
	 * @param $table_name
	 * @param $column_name
	 *
	 * @return TD_DB_Migration allows fluent interface
	 */
	public function drop_column( $table_name, $column_name ) {

		if ( $this->column_exists( $table_name, $column_name ) ) {
			$table_name = $this->_prepare_table_name( $table_name );
			$this->add_query( "ALTER TABLE `{$table_name}` DROP COLUMN `{$column_name}`;" );
		}

		return $this;
	}

	/**
	 * Checks if a column exists
	 *
	 * @param string $table_name
	 * @param string $column_name
	 *
	 * @return bool
	 */
	public function column_exists( $table_name, $column_name ) {
		$results = $this->_wpdb->get_results( "SHOW FULL COLUMNS FROM `{$this->_prepare_table_name($table_name)}` LIKE '$column_name'" );

		return ! empty( $results );
	}

	/**
	 * Creates an index on table
	 *
	 * @param string       $index_name
	 * @param string       $table_name
	 * @param array|string $columns
	 *
	 * @return TD_DB_Migration allows fluent interface
	 */
	public function create_index( $index_name, $table_name, $columns ) {

		$table_name = $this->_prepare_table_name( $table_name );

		if ( ! is_array( $columns ) ) {
			$columns = array( $columns );
		}

		$str_columns = implode( ',', $columns );

		if ( false === $this->index_exists( $index_name, $table_name ) ) {
			$this->add_query( "CREATE INDEX `{$index_name}` ON `{$table_name}` ({$str_columns})" );
		}

		return $this;
	}

	/**
	 * Checks if an index with name exists on the table
	 *
	 * @param string $index_name
	 * @param string $table_name
	 *
	 * @return bool
	 */
	public function index_exists( $index_name, $table_name ) {

		$table_name = $this->_prepare_table_name( $table_name );
		$index      = $this->_wpdb->get_row( "SHOW INDEX FROM `{$table_name}` WHERE Key_name = '{$index_name}'" );

		return (bool) $index;
	}

	/**
	 * Loops through the queries and executes them
	 *
	 * @return bool
	 */
	public function run() {

		$success = true;

		if ( defined( 'THRIVE_DB_UPGRADING' ) === false ) {
			$this->_wpdb->last_error = 'Cannot run migrations outside of Database Manager';
			$success                 = false;
		}

		if ( $this->_file_path ) {
			$result = require_once $this->_file_path;

			/* backwards compat support */
			if ( $result instanceof TD_DB_Migration ) {
				return $result->run();
			}
		}

		foreach ( $this->_queries as $query ) {
			if ( $success && $this->_wpdb->query( $query ) === false ) {
				$success = false;
				break;
			}
		}

		return $success;
	}

	/**
	 * Prepares a table name for using it in queries
	 *
	 * @param string $table_name
	 *
	 * @return mixed|string
	 */
	protected function _prepare_table_name( $table_name ) {
		return $this->get_table_name( str_replace( '`', '', $table_name ) );
	}

	/**
	 * Replaces {table_name} instances with prefixed_table_name
	 *
	 * @param array $matches
	 *
	 * @return string
	 */
	protected function preg_replace_table_name( $matches ) {
		return $this->get_table_name( $matches[1] );
	}
}
