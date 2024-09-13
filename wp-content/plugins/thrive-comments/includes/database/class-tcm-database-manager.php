<?php

/**
 * Database manager file
 */
class Thrive_Comment_Database_Manager {
	/**
	 * Database version
	 *
	 * @var string
	 */
	protected static $current_db_version;

	/**
	 * Last db error
	 *
	 * @var string
	 */
	protected static $last_db_error = '';

	/**
	 * Get the current version of database tables
	 * If there is no version saved 0.0 is returned
	 *
	 * @return string
	 */
	public static function db_version() {
		if ( empty( self::$current_db_version ) ) {
			self::$current_db_version = self::get_option();
		}

		return self::$current_db_version;
	}

	/**
	 * Compare db version with code version
	 * Runs all the scrips of old db version until the current code version
	 */
	public static function check() {
		if ( is_admin() && ! empty( $_REQUEST['tcm_db_reset'] ) ) {
			self::reset_option();
		}

		if ( version_compare( self::db_version(), Thrive_Comments_Constants::DB_VERSION, '<' ) ) {
			$scripts = self::get_scripts( self::db_version(), Thrive_Comments_Constants::DB_VERSION );
			if ( ! empty( $scripts ) ) {
				define( 'TCM_DB_UPGRADING', true );
			}
			global $wpdb;
			$wpdb->hide_errors();
			foreach ( $scripts as $file_path ) {
				$result = require_once $file_path;
				if ( false === $result ) {
					$has_error = true;
					break;
				}
			}
			if ( isset( $has_error ) ) {
				self::$last_db_error = $wpdb->last_error;
				add_action( 'admin_notices', array( 'Thrive_Comment_Database_Manager', 'display_admin_error' ) );

				return;
			}

			self::update_option( Thrive_Comments_Constants::DB_VERSION );
		}
	}

	/**
	 * Get all DB update scripts from $fromVersion to $toVersion
	 *
	 * @param $from_version
	 * @param $to_version
	 *
	 * @return array
	 */
	protected static function get_scripts( $from_version, $to_version ) {
		$scripts = array();
		$dir     = new DirectoryIterator( dirname( __FILE__ ) . '/migrations' );
		foreach ( $dir as $file ) {
			if ( $file->isDot() ) {
				continue;
			}
			$script_version = self::get_script_version( $file->getFilename() );
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
	 * Parse the scriptName and return the version
	 *
	 * @param string $script_name in the following format {name}-{[\d+].[\d+]}.php.
	 *
	 * @return string
	 */
	protected static function get_script_version( $script_name ) {
		if ( ! preg_match( '/(.+?)-(\d+)\.(\d+)(.\d+)?\.php/', $script_name, $m ) ) {
			return false;
		}

		return $m[2] . '.' . $m[3] . ( ! empty( $m[4] ) ? $m[4] : '' );
	}

	/**
	 * Gets the database option.
	 *
	 * @param string $default default value 0.0.
	 *
	 * @return mixed|void
	 */
	protected static function get_option( $default = '0.0' ) {
		if ( empty( $default ) ) {
			$default = '0.0';
		}

		return get_option( 'tcm_db_version', $default );
	}

	/**
	 * Sets the Thrive comment database version.
	 *
	 * @param string $value value to be updated in database.
	 *
	 * @return bool
	 */
	protected static function update_option( $value ) {
		if ( self::db_version() === $value ) {
			return true;
		}

		return update_option( 'tcm_db_version', $value );
	}

	/**
	 * Resets the Thrive comment database version.
	 *
	 * @return bool
	 */
	protected static function reset_option() {
		return delete_option( 'tcm_db_version' );
	}

	/**
	 * Display a error message in the admin panel notifying the user that the DB update script was not successful.
	 */
	public static function display_admin_error() {
		if ( ! self::$last_db_error ) {
			return;
		}

		echo '<div class="notice notice-error is-dismissible"><p>' .
		     sprintf(
			     __( 'There was an error while updating the database tables needed by Thrive Comment. Detailed error message: %s. If you continue seeing this message, please contact %s', Thrive_Comments_Constants::T ),
			     '<strong>' . self::$last_db_error . '</strong>',
			     '<a target="_blank" href="https://thrivethemes.com/forums/">' . __( 'Thrive Themes Support', Thrive_Comments_Constants::T ) . '</a>'
		     ) .
		     '</p></div>';
	}


}

Thrive_Comment_Database_Manager::check();
