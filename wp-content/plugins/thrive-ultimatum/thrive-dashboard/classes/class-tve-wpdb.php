<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */

class Tve_Wpdb extends wpdb {

	/**
	 * @var Tve_Wpdb
	 */
	protected static $instance;

	public static function instance() {
		if ( static::$instance === null ) {
			$dbuser     = defined( 'DB_USER' ) ? DB_USER : '';
			$dbpassword = defined( 'DB_PASSWORD' ) ? DB_PASSWORD : '';
			$dbname     = defined( 'DB_NAME' ) ? DB_NAME : '';
			$dbhost     = defined( 'DB_HOST' ) ? DB_HOST : '';

			static::$instance = new static( $dbuser, $dbpassword, $dbname, $dbhost );
		}

		return static::$instance;
	}

	/**
	 * Just run a query, without anything else
	 *
	 * @param $query
	 *
	 * @return $this
	 */
	public function do_query( $query ) {
		if ( ! empty( $this->dbh ) ) {
			if ( $this->use_mysqli ) {
				/* @codingStandardsIgnoreLine */
				$this->result = mysqli_query( $this->dbh, $query );
			} else {
				/* @codingStandardsIgnoreLine */
				$this->result = mysql_query( $query, $this->dbh );
			}
		}

		$this->num_queries ++;

		return $this;
	}

	/**
	 * Return only one row from result
	 *
	 * @return $1|false|mixed|object|stdClass|null
	 */
	public function get_one_row() {
		/* @codingStandardsIgnoreLine */
		if ( $this->use_mysqli && $this->result instanceof mysqli_result ) {
			/* @codingStandardsIgnoreLine */
			return mysqli_fetch_object( $this->result );
		} elseif ( is_resource( $this->result ) ) {
			/* @codingStandardsIgnoreLine */
			return mysql_fetch_object( $this->result );
		}

		return null;
	}

	/**
	 * count results
	 *
	 * @return mixed
	 */
	public function num_rows() {
		return $this->result->num_rows;
	}
}
