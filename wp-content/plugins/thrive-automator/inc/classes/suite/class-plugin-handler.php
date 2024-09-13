<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-automator
 */

namespace Thrive\Automator\Suite;

use WP_Error;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Plugin_Handler
 */
class Plugin_Handler {
	protected $errors = null;

	protected $notices = [];

	public function __construct() {
	}

	/**
	 * Get folder name where to upload
	 *
	 * @param $post_id
	 *
	 * @return string
	 */
	public static function get_folder_name( $filename ) {
		return sanitize_title( $filename );
	}

	/**
	 * Get target path for the parent folder where all files are uploaded
	 *
	 * @return string
	 */
	public static function get_target_path() {
		return trailingslashit( WP_PLUGIN_DIR );
	}

	/**
	 * Get path
	 *
	 * @param $post_id
	 *
	 * @return string
	 */
	public static function get_folder_path( $folder = '' ) {
		return trailingslashit( static::get_target_path() ) . $folder;
	}

	public static function verify_archive( $file ) {
		if ( ! class_exists( 'ZipArchive', false ) ) {
			return new WP_Error( 'missing_extension', 'ZipArchive extension not enabled' );
		}

		$zip = new \ZipArchive();

		if ( ! in_array( $file['type'], [ 'application/x-zip-compressed', 'application/zip', 'application/zip-compressed' ] ) || ! $zip->open( $file['tmp_name'] ) || ! $zip->locateName( TTW::SLUG ) !== false ) {
			return new WP_Error( 'invalid_archive', 'Invalid archive' );
		}

		return true;
	}

	/**
	 * Check if there is an error
	 *
	 * @param $error
	 *
	 * @return bool|\WP_Error
	 */
	public function check_error( $error ) {
		$file_errors = array(
			0 => __( 'There is no error, the file uploaded with success', 'thrive-automator' ),
			1 => __( 'The uploaded file exceeds the upload_max_files in server settings', 'thrive-automator' ),
			2 => __( 'The uploaded file exceeds the MAX_FILE_SIZE from html form', 'thrive-automator' ),
			3 => __( 'The uploaded file uploaded only partially', 'thrive-automator' ),
			4 => __( 'No file was uploaded', 'thrive-automator' ),
			6 => __( 'Missing a temporary folder', 'thrive-automator' ),
			7 => __( 'Failed to write file to disk', 'thrive-automator' ),
			8 => __( 'A PHP extension stopped file to upload', 'thrive-automator' ),
		);

		if ( $error > 0 ) {
			return new \WP_Error( 'file-error', $file_errors[ $error ] );
		}

		return true;
	}

	public function upload_file( $data ) {
		$is_valid = static::verify_archive( $data['file'] );

		if ( is_wp_error( $is_valid ) ) {
			return $is_valid;
		}

		$result = $this->upload( $data );

		if ( is_wp_error( $result ) ) {
			$this->errors->add( $result->get_error_code(), $result->get_error_message() );
		}

		$this->notices[] = 'Uploaded! Path: ' . $result;

		return $this->notices;
	}

	/**
	 * Upload File
	 *
	 * @param $file
	 *
	 * @return bool|string|true|WP_Error
	 */
	public function upload( $file ) {
		/** @var $wp_filesystem \WP_Filesystem_Direct */
		global $wp_filesystem;

		if ( ! function_exists( 'WP_Filesystem' ) ) {
			require_once( ABSPATH . '/wp-admin/includes/file.php' );
		}

		WP_Filesystem();

		$file_error = $file['file']['error'];

		// Check for Errors
		if ( is_wp_error( $this->check_error( $file_error ) ) ) {
			return $this->check_error( $file_error );
		}

		$file_name     = $file['file']['name'];
		$file_name_arr = explode( '.', $file_name );
		$extension     = array_pop( $file_name_arr );
		$filename      = implode( '.', $file_name_arr ); // File Name
		$zip_file      = sanitize_title( $filename ) . '.' . $extension; //Our File

		if ( 'zip' !== $extension ) {
			return new WP_Error( 'no-zip', __( 'This does not seem to be a ZIP file', 'thrive-automator' ) );
		}

		$temp_name = $file['file']['tmp_name'];

		// Get default folder that contains the zip. Create if it does not exist.
		$default_target = static::get_target_path();
		// Create our default folder if it does not exist
		if ( ! file_exists( $default_target ) && ! mkdir( $default_target ) && ! is_dir( $default_target ) ) {
			throw new \RuntimeException( sprintf( __( 'Directory "%s" was not created', 'thrive-automator' ), $default_target ) );
		}

		// Get folder path
		$upload_path = static::get_folder_path();

		// Folder name where we will upload the ZIP
		$working_dir = static::get_folder_path( static::get_folder_name( $filename ) ) . '-zip';

		// Delete if such folder exists
		if ( $wp_filesystem->is_dir( $working_dir ) ) {
			$wp_filesystem->delete( $working_dir, true );
		}
		// Create the folder to hold our zip file
		$wp_filesystem->mkdir( $working_dir );

		// Uploading ZIP file
		if ( move_uploaded_file( $temp_name, trailingslashit( $working_dir ) . $zip_file ) ) {
			if ( ! function_exists( 'PclZip' ) ) {
				require_once( ABSPATH . '/wp-admin/includes/class-pclzip.php' );
			}

			// Unzip the file to the upload path
			$unzip_result = unzip_file( trailingslashit( $working_dir ) . $zip_file, $upload_path );

			if ( is_wp_error( $unzip_result ) ) {
				return $unzip_result;
			}

			// No errors with unzips, let's delete everything and unzip it again.
			unzip_file( trailingslashit( $working_dir ) . $zip_file, $upload_path );

			// Include the plugin.php file so you have access to the activate_plugin() function
			if ( ! function_exists( 'activate_plugin' ) ) {
				require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
			}

			// Remove the uploaded zip
			@unlink( $working_dir . "/" . $zip_file );
			if ( $wp_filesystem->is_dir( $working_dir ) ) {
				$wp_filesystem->delete( $working_dir, true );
			}

			return $upload_path;
		}

		return new \WP_Error( 'not-uploaded', __( 'Could not upload file', 'thrive-automator' ) );
	}

	/**
	 * Activate TPM Plugin
	 *
	 * @return bool|\WP_Error
	 */
	public static function activate_plugin() {
		/** @var $wp_filesystem \WP_Filesystem_Direct */
		global $wp_filesystem;

		if ( ! function_exists( 'WP_Filesystem' ) ) {
			require_once( ABSPATH . '/wp-admin/includes/file.php' );
		}
		// Get folder path
		$upload_path = static::get_folder_path();

		WP_Filesystem();
		if ( ! $wp_filesystem->is_file( trailingslashit( $upload_path ) . TTW::SLUG ) ) {
			return new WP_Error( 'no-product-manager', __( "You don't have Thrive Product Manager installed", 'thrive-automator' ) );
		}
		if ( ! function_exists( 'activate_plugin' ) ) {
			require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
		}
		// Activate your plugin
		\activate_plugin( TTW::SLUG );

		return is_plugin_active( TTW::SLUG );
	}

}

