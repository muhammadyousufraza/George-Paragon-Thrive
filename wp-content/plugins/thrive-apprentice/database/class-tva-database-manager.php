<?php

class TVA_Database_Manager {

	/**
	 * Last db error
	 *
	 * @var string
	 */
	protected static $last_db_error = '';

	/**
	 * Runs migrations with TD_DB_Manager
	 */
	public static function push_db_manager() {

		try {
			TD_DB_Manager::add_manager(
				__DIR__ . '/migrations',
				'tva_db_version',
				TVA_Const::DB_VERSION,
				'Thrive Apprentice',
				'tva_',
				'tva_db_reset'
			);
		} catch ( Exception $e ) {
			self::$last_db_error = $e->getMessage();
			add_action( 'admin_notices', array( 'TVA_Database_Manager', 'display_admin_error' ) );
		}
	}

	/**
	 * Display a error message in the admin panel notifying the user that the DB update script was not successful.
	 */
	public static function display_admin_error() {

		if ( ! self::$last_db_error ) {
			return;
		}

		/* translators: %s: db error %s link html */
		$string = __( 'There was an error while updating the database tables needed by Thrive Apprentice. Detailed error message: %1$s. If you continue seeing this message, please contact %2$s', 'thrive-apprentice' );

		$message = sprintf(
			$string,
			'<strong>' . self::$last_db_error . '</strong>',
			'<a target="_blank" href="https://thrivethemes.com/forums/">' . __( 'Thrive Themes Support', 'thrive-apprentice' ) . '</a>'
		);

		echo '<div class="notice notice-error is-dismissible"><p>' . $message . '</p></div>';
	}
}
