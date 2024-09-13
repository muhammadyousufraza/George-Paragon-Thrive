<?php

use Thrive\Automator\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

class TAP_DB_Manager {

	/**
	 * Path to migrations folder
	 *
	 * @var string path
	 */
	protected $_migrations_path;

	/**
	 * Version to which this class should run the migrations
	 *
	 * @var string
	 */
	protected $_required_version;

	/**
	 * Current version of DB; stored in wp_option
	 *
	 * @var string
	 */
	protected $_current_version;

	/**
	 * Option name which holds the version of DB
	 *
	 * @var string
	 */
	protected $_option_name;

	/**
	 * Product name to be included in the error message
	 *
	 * @var string
	 */
	protected $_product_name;

	/**
	 * Prefix to be used for tables
	 *
	 * @var string
	 */
	protected $_table_prefix;

	/**
	 * Reset option parameter name
	 *
	 * @var string
	 */
	protected $_reset_option_param = '';

	/**
	 * Holder for DB error
	 *
	 * @var string
	 */
	protected $_last_db_error;

	public function __construct() {
		$path = __DIR__ . '/migrations';
		if ( is_dir( $path ) === false ) {
			throw new Exception( 'Path to migrations is invalid' );
		}
		$this->_migrations_path    = trailingslashit( $path );
		$this->_option_name        = 'tap_db_version';
		$this->_required_version   = TAP_DB_VERSION;
		$this->_current_version    = $this->_get_current_version();
		$this->_product_name       = TAP_PLUGIN_NAME;
		$this->_table_prefix       = TAP_DB_PREFIX;
		$this->_reset_option_param = 'tap_db_reset';
	}

	protected function _get_current_version( $default = '0.0' ) {
		$this->_current_version = get_option( $this->_option_name, $default );

		return $this->_current_version;
	}

	/**
	 * Reset the wp_option so that all the migrations will run again at next check()
	 *
	 * @return bool
	 */
	protected function _reset() {

		$this->_current_version = '0.0';

		return delete_option( $this->_option_name );
	}

	protected function _update( $value ) {

		$this->_current_version = $value;

		return update_option( $this->_option_name, $value );
	}

	/**
	 * Compare the current db version with the required version and
	 * Runs all the scrips from current version until the required version
	 */
	public function run() {

		if ( is_admin() && ! empty( $_REQUEST[ $this->_reset_option_param ] ) ) {
			$this->_reset();
		}

		if ( version_compare( $this->_current_version, $this->_required_version, '<' ) ) {

			$scripts = $this->_get_scripts( $this->_current_version, $this->_required_version );

			if ( ! empty( $scripts ) ) {
				! defined( 'THRIVE_DB_UPGRADING' ) ? define( 'THRIVE_DB_UPGRADING', true ) : null;
			}

			/** @var $wpdb wpdb */
			global $wpdb;

			/**
			 * We only want to hide the errors not suppress them
			 * in case we need to log them somewhere
			 */
			$wpdb->hide_errors();
			$has_error = false;

			foreach ( $scripts as $file_path ) {
				$migration = new TAP_DB_Migration( $this->_table_prefix, $file_path );
				$has_error = $migration->run() === false;

				if ( $has_error ) {
					/* ERROR: we don't change the DB version option and notify the user about the last error */
					break;
				}
			}

			if ( $has_error ) {
				$this->_last_db_error = $wpdb->last_error;
				add_action( 'admin_notices', array( $this, 'display_admin_error' ) );
			} else {
				$this->_update( $this->_required_version );
			}
		}
	}

	/**
	 * Get all DB update scripts from $from_version to $to_version.
	 *
	 * @param string $from_version from version.
	 * @param string $to_version   to version.
	 *
	 * @return array
	 */
	protected function _get_scripts( $from_version, $to_version ) {

		$scripts = array();
		$dir     = new DirectoryIterator( $this->_migrations_path );

		foreach ( $dir as $file ) {
			/**
			 * DirectoryIterator
			 *
			 * @var $file
			 */
			if ( $file->isDot() ) {
				continue;
			}
			$script_version = $this->_get_script_version( $file->getFilename() );
			if ( empty( $script_version ) ) {
				continue;
			}
			if ( version_compare( $script_version, $from_version, '>' ) && version_compare( $script_version, $to_version, '<=' ) ) {
				$scripts[ $script_version ] = $file->getPathname();
			}
		}

		/**
		 * Sort the scripts in the correct version order
		 */
		uksort( $scripts, 'version_compare' );

		return $scripts;
	}

	/**
	 * Parse the script_ame and returns its version
	 *
	 * @param string $script_name in the following format {name}-{[\d+].[\d+]}.php.
	 *
	 * @return string
	 */
	protected function _get_script_version( $script_name ) {

		if ( ! preg_match( '/(.+?)-(\d+)\.(\d+)(.\d+)?\.php/', $script_name, $m ) ) {
			return false;
		}

		return $m[2] . '.' . $m[3] . ( ! empty( $m[4] ) ? $m[4] : '' );
	}

	/**
	 * Display a WP Notification with last db error
	 */
	public function display_admin_error() {

		if ( ! $this->_last_db_error ) {
			return;
		}

		Utils::tap_template( 'db-error', [
			'error'        => $this->_last_db_error,
			'product_name' => $this->_product_name,
		] );

	}

}

$migration = new TAP_DB_Manager();
$migration->run();
