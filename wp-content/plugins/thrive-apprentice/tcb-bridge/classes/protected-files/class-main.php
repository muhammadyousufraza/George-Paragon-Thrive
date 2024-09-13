<?php

namespace TVA\Architect\Protected_Files;

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Protected files - main entrypoint
 */
class Main {

	/**
	 * Initialize the hooks and adds shortcodes
	 *
	 * @return void
	 */
	public static function init() {
		Hooks::init();
		Shortcodes::init();
	}

	/**
	 * Download a protected file
	 *
	 * @param \TVA_Protected_File $file
	 * @param boolean             $should_fire_hook
	 *
	 * @return void
	 */
	public static function download( $file, $should_fire_hook = true ) {

		$file_with_path = $file->get_uploaded_file();

		if ( ! is_readable( $file_with_path ) ) {
			wp_redirect( add_query_arg( [ 'protected_file_not_found' => 1 ], home_url() ) );
		}

		if ( $should_fire_hook ) {
			/**
			 * Fired when a protected file is downloaded
			 * Used in reporting functionality. Logs when a file has been downloaded
			 *
			 * @param \TVA_Protected_File $file
			 * @param array               $user_details
			 */
			do_action( 'tva_protected_file_download', $file, tvd_get_current_user_details() );

			/**
			 * Increment the download counter before downloading
			 */
			$file->increment_download_counter();
		}

		header( 'Content-Description: File Transfer' );
		header( 'Content-Type: application/octet-stream' );
		header( 'Content-Disposition: attachment; filename="' . sanitize_title( $file->post_title ) . '.' . $file->file_extension . '"' );
		header( 'Content-Length: ' . filesize( $file_with_path ) );
		if ( ob_get_contents() ) {
			ob_end_clean();
		}

		readfile( $file_with_path );
		exit();
	}
}
