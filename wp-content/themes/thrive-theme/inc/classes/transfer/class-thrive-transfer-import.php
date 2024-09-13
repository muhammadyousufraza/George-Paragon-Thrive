<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Thrive_Transfer_Import {

	/**
	 * Object for working with the archive
	 *
	 * @var ZipArchive
	 */
	protected $zip;

	/**
	 * The imported archive
	 *
	 * @var string
	 */
	protected $archive_file;

	/**
	 * Thrive_Transfer_Import constructor.
	 *
	 * @param $archive_file
	 */
	public function __construct( $archive_file ) {
		Thrive_Transfer_Utils::require_files( __DIR__ );
		$this->archive_file = $archive_file;
	}

	/**
	 * Import main entry point
	 *
	 * @param string $type    Type of the element we want to export
	 * @param array  $options Extra options to take into account when importing
	 *
	 * @return WP_Term|false
	 * @throws Exception
	 */
	public function import( $type, $options = [] ) {
		/* make sure we have enough memory to process the whole skin */
		wp_raise_memory_limit();
		try {

			$this->open_archive();

			$this->validate_archive();

			$controller = new Thrive_Transfer_Controller( $this->zip );
			$response   = Thrive_Transfer_Item_Factory::build( $type, $controller )->validate()->import( $options );

		} catch ( Exception $ex ) {
			throw new Exception( $ex->getMessage() );
		}

		return $response;
	}

	/**
	 * Check if the uploaded archive has a valid content
	 *
	 * @throws Exception
	 */
	public function validate_archive() {
		for ( $i = 0; $i < $this->zip->numFiles; $i ++ ) {
			$stat = $this->zip->statIndex( $i );
			/* A valid archive needs to contain only json files besides the images folder */
			if ( ! preg_match( '/.json$/', basename( $stat['name'] ) ) && strpos( $stat['name'], 'images/' ) === false ) {
				throw new Exception( 'The archive content is not valid' );
			}
		}
	}

	/**
	 * Open the archive file
	 *
	 * @throws Exception
	 */
	public function open_archive() {

		/* let's check if the zip archive extension is enabled on the server */
		if ( ! class_exists( 'ZipArchive' ) ) {
			throw new Exception( __( 'The PHP ZipArchive extension must be enabled in order to use this functionality. Please contact your hosting provider.', 'thrive-theme' ) );
		}

		$this->zip = new ZipArchive();

		if ( $this->zip->open( $this->archive_file ) !== true ) {
			throw new Exception( __( 'Could not open the archive file', 'thrive-theme' ) );
		}
	}
}
