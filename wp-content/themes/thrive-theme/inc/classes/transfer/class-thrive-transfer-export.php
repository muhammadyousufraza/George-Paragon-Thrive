<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Thrive_Transfer_Export
 */
class Thrive_Transfer_Export {

	/**
	 * Upload path for the archive
	 *
	 * @var string
	 */
	private $upload_path = UPLOAD_DIR_PATH . '/' . THEME_FOLDER . '/';

	/**
	 * Upload url for the archive
	 *
	 * @var string
	 */
	private $upload_url = UPLOAD_DIR_URL . '/' . THEME_FOLDER . '/';

	/**
	 * Name of the archive
	 *
	 * @var
	 */
	public $zip_filename;

	/**
	 * Keeps the umask
	 *
	 * @var
	 */
	private $umask;

	/**
	 * The archive where the export will be saved
	 *
	 * @var ZipArchive
	 */
	public $zip;

	/**
	 * Thrive_Transfer_Export constructor.
	 *
	 * @param $zip_filename
	 *
	 * @throws Exception
	 */
	public function __construct( $zip_filename ) {
		Thrive_Transfer_Utils::require_files( __DIR__ );
		$this->ensure_folder();
		$this->set_name( $zip_filename );

	}

	/**
	 * Export main entry point
	 *
	 * @param string $type    Type of the element we want to export
	 * @param mixed  $id      Id of the element we want to export
	 * @param array  $options Extra options to take into account when exporting
	 *
	 * @return string
	 * @throws Exception
	 */
	public function export( $type, $id, $options = [] ) {

		/**
		 * Action that is fired before a theme related item is exported
		 *
		 * Allows some magic to happen on ThemesBuild website
		 *
		 * @param string $type
		 * @param int    $id
		 * @param array  $options
		 */
		do_action( 'tcb_before_thrive_theme_export', $type, $id, $options );

		try {
			$this->open_archive();

			/* Prepare a new instance for the next export */
			Thrive_Transfer_Archive_Data::reset();

			$controller = new Thrive_Transfer_Controller( $this->zip );
			Thrive_Transfer_Item_Factory::build( $type, $controller )->read( $id, $options )->add();

			$this->write_archive();

			$archive = $this->close_archive();

		} catch ( Exception $ex ) {
			throw new Exception( $ex->getMessage() );
		}

		/**
		 * Action that is fired after a theme related item is exported
		 *
		 * Allows some magic to happen on ThemesBuild website
		 */
		do_action( 'tcb_after_thrive_theme_export' );

		return $archive;
	}

	/**
	 * Open archive where all the items will be saved
	 *
	 * @throws Exception
	 */
	public function open_archive() {

		/* remove file with same name */
		if ( is_file( $this->upload_path . $this->zip_filename ) ) {
			unlink( $this->upload_path . $this->zip_filename );
		}

		require_once( ABSPATH . 'wp-admin/includes/file.php' );

		WP_Filesystem();

		$this->umask = umask( 0 );
		$this->zip   = new ZipArchive();
		if ( $this->zip->open( $this->upload_path . $this->zip_filename, ZipArchive::CREATE ) !== true ) {
			throw new Exception( 'Could not create zip archive' );
		}
	}

	/**
	 * Write all the data gathered in the zip archive
	 */
	public function write_archive() {
		$archive_data = Thrive_Transfer_Archive_Data::instance();

		foreach ( $archive_data->get_data() as $key => $data ) {
			call_user_func( [ 'Thrive_Transfer_' . ucfirst( $key ), 'add_to_archive' ], $data, $this->zip );
		}
	}

	/**
	 * Close the archive and returns the url
	 *
	 * @return string[]
	 * @throws Exception
	 */
	public function close_archive() {
		try {
			if ( ! $this->zip->close() ) {
				throw new Exception( 'Could not write the zip file' );
			}
		} catch ( Exception $e ) {
			throw new Exception( $e->getMessage() );
		}

		umask( $this->umask );

		return [
			'path' => $this->upload_path . $this->zip_filename,
			'url'  => $this->upload_url . $this->zip_filename,
		];
	}

	/**
	 * We generate a unique name for each archive to avoid caching when we make a request for the archive
	 *
	 * @param $name
	 */
	public function set_name( $name ) {
		$this->zip_filename = str_replace( ' ', '-', $name ) . '-' . gmdate( 'Y-m-d-H-i-s' ) . '.zip';
	}

	/**
	 * Ensure the folder in which we will save the archive exists
	 *
	 * @throws Exception
	 */
	public function ensure_folder() {
		/**
		 * first make sure we can save the archive
		 */
		$upload = wp_upload_dir();
		if ( ! empty( $upload['error'] ) ) {
			throw new Exception( $upload['error'] );
		}

		$base = trailingslashit( $upload['basedir'] ) . THEME_FOLDER . '/';

		if ( ! is_dir( $base ) ) {
			mkdir( $base, 0777, true );
		}
	}
}
