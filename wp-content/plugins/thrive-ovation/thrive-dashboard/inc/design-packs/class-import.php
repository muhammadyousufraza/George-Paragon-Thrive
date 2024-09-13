<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */

namespace TVE\Dashboard\Design_Packs;

use Exception;
use function unzip_file;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Handle the import of each item type
 */
class Import {
	/**
	 * Get zip and open it
	 *
	 * @param $data
	 *
	 * @return \ZipArchive|null
	 */
	public static function get_zip( $data ) {
		$attachment = get_attached_file( $data['zip_id'], true );
		$zip        = new \ZipArchive();

		return $zip->open( $attachment ) ? $zip : null;
	}

	/**
	 * Validate the uploaded file and get its configuration
	 *
	 * @param $data
	 *
	 * @return array|mixed
	 * @throws Exception
	 */
	public static function validate( $data ) {
		$config = [];
		if ( isset( $data['zip_id'] ) ) {
			$zip = static::get_zip( $data );
			if ( $zip && $zip->locateName( Main::CFG_NAME ) !== false ) {
				static::unzip_archive( $zip );
				$config = json_decode( $zip->getFromName( Main::CFG_NAME ), true );
			}
		}

		return $config;
	}

	/**
	 * Get the path to the unzipped archive
	 *
	 * @param $zip
	 *
	 * @return string
	 */
	public static function get_unzipped_name( $zip ) {
		$wp_uploads_dir = Main::get_imported_dir_path();
		defined( 'FS_METHOD' ) || define( 'FS_METHOD', 'direct' );
		if ( FS_METHOD !== 'ssh2' ) {
			$wp_uploads_dir = str_replace( ABSPATH, '', $wp_uploads_dir );
		}

		return $wp_uploads_dir . basename( $zip->filename );
	}

	/**
	 * @throws Exception
	 */
	public static function unzip_archive( $zip ) {
		if ( ! function_exists( '\WP_Filesystem' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/file.php' );
		}
		global $wp_filesystem;

		defined( 'FS_METHOD' ) || define( 'FS_METHOD', 'direct' );
		if ( FS_METHOD !== 'direct' ) {
			\WP_Filesystem( array(
				'hostname' => defined( 'FTP_HOST' ) ? FTP_HOST : '',
				'username' => defined( 'FTP_USER' ) ? FTP_USER : '',
				'password' => defined( 'FTP_PASS' ) ? FTP_PASS : '',
			) );
		} else {
			\WP_Filesystem();
		}

		if ( $wp_filesystem->errors instanceof WP_Error && ! $wp_filesystem->connect() ) {
			throw new Exception( $wp_filesystem->errors->get_error_message() );
		}

		return unzip_file( $zip->filename, static::get_unzipped_name( $zip ) );
	}

	/**
	 * Clear the folder once the import is done
	 *
	 * @param $data
	 *
	 * @return bool
	 */
	public static function remove_data( $data ): bool {
		$zip    = static::get_zip( $data );
		$folder = static::get_unzipped_name( $zip );

		return static::delete_directory( $folder );
	}

	/**
	 * Delete a directory and all its content
	 *
	 * @param $dir
	 *
	 * @return bool
	 */
	public static function delete_directory( $dir ): bool {
		$deleted = false;
		if ( is_dir( $dir ) ) {
			if ( substr( $dir, strlen( $dir ) - 1, 1 ) !== '/' ) {
				$dir .= '/';
			}
			$files = glob( $dir . '*', GLOB_MARK );
			foreach ( $files as $file ) {
				if ( is_dir( $file ) ) {
					static::delete_directory( $file );
				} else {
					unlink( $file );
				}
			}
			$deleted = rmdir( $dir );
		}

		return $deleted;
	}


	/**
	 * generic handle for the import
	 *
	 * @param $data
	 *
	 * @return array
	 */
	public static function handle_import( $data ) {
		$zip      = static::get_zip( $data );
		$imported = [];
		/**
		 * Use an existing symbol map
		 */
		if ( ! empty( $data['tcb_symbol_map'] ) ) {
			$GLOBALS['tcb_symbol_map'] = $data['tcb_symbol_map'];
		}
		if ( $zip ) {
			$content_file = static::get_unzipped_name( $zip ) . '/' . $data['file_type'] . '/' . $data['filename'] . '.zip';

			$fn = 'import_' . $data['file_type'];

			if ( ! method_exists( __CLASS__, $fn ) ) {
				$fn = 'import_content';
			}

			$response = static::{$fn}( $content_file );
			if ( is_array( $response ) ) {
				$imported = array_merge( $imported, $response );
			}
			$zip->close();
		}

		/**
		 * Preserve the symbol map
		 */
		if ( isset( $GLOBALS['tcb_symbol_map'] ) ) {
			$imported['tcb_symbol_map'] = $GLOBALS['tcb_symbol_map'];
		}

		return $imported;
	}

	/**
	 * Import a skin
	 *
	 * @param $content_file
	 *
	 * @return false|\WP_Term
	 * @throws \Exception
	 */
	public static function import_skin( $content_file ) {
		return ( new \Thrive_Transfer_Import( $content_file ) )->import( 'skin' );
	}

	/**
	 * Import a template
	 *
	 * @param $content_file
	 *
	 * @return array
	 * @throws \Exception
	 */
	public static function import_content( $content_file ) {
		return ( new \TCB_Content_Handler() )->import( $content_file, 0 );
	}

	/**
	 * Import a landing page
	 *
	 * @param $content_file
	 *
	 * @return array
	 * @throws \Exception
	 */
	public static function import_landing_page( $content_file ) {
		$lp_data = ( new \TCB_Landing_Page_Transfer() )->import( $content_file, 0 );
		/**
		 * So we know if the template is an imported one or not
		 */
		if ( isset( $lp_data['template_id'] ) ) {
			update_post_meta( $lp_data['template_id'], 'tve_kit_imported', 1 );
		}

		return $lp_data;
	}
}
