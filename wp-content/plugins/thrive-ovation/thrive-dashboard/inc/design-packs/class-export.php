<?php

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */

namespace TVE\Dashboard\Design_Packs;

use Exception;
use ZipArchive;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Handle the export of each item type
 */
class Export {
	const DEFAULT_NAME  = 'thrive-design-pack-archive';
	const EXPORT_SUFFIX = 'tve_exp_';
	/**
	 * Name of the archive
	 *
	 * @var
	 */
	public $zip_filename;

	/**
	 * Path of the archive
	 *
	 * @var
	 */
	public $zip_path;

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
	 * Exported item data
	 *
	 * @var
	 */
	public $item_id;
	public $item_type;
	public $item_name;

	/**
	 * @throws Exception
	 */
	public function __construct( $zip_filename, $ensure_filename = true ) {
		$this->set_zip_name( $zip_filename, $ensure_filename );
		$this->open_zip();
	}

	/**
	 * Open the export zip archive
	 *
	 * @throws Exception
	 */
	public function open_zip() {
		require_once( ABSPATH . 'wp-admin/includes/file.php' );

		WP_Filesystem();

		$this->umask = umask( 0 );
		$this->zip   = new ZipArchive();
		if ( $this->zip->open( $this->zip_path, ZipArchive::CREATE ) !== true ) {
			throw new Exception( 'Could not create zip archive' );
		}
	}

	/**
	 * Close the archive and returns the url
	 *
	 * @return string
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

		return Main::get_exported_dir_url() . $this->zip_filename;
	}

	/**
	 * Get the exported name of an item
	 *
	 * @param $name
	 *
	 * @return string
	 */
	public function get_item_export_name( $name ) {
		$name = htmlspecialchars_decode( $name );

		return $name . ' ' . uniqid( static::EXPORT_SUFFIX );
	}

	/**
	 * Handle an item export
	 *
	 * @param $id
	 * @param $type
	 *
	 * @return mixed
	 * @throws Exception
	 */
	public function export_item( $id, $type ) {
		$this->set_item_data( $id, $type );

		$fn = 'export_' . $type;

		if ( ! method_exists( $this, $fn ) ) {
			$fn = 'export_content';
		}

		$item_data = $this->{$fn}( $id );

		$this->write_archive( $item_data );

		return $this->close_archive();
	}

	/**
	 * Set the item data
	 *
	 * @param $id
	 * @param $type
	 *
	 * @return void
	 */
	public function set_item_data( $id, $type ) {
		$this->item_id   = $id;
		$this->item_type = $type;
		if ( $type === 'skin' ) {
			$term            = get_term( $id );
			$this->item_name = $this->get_item_export_name( $term->name );
		} else {
			$_REQUEST['post_id'] = $id;
			$_POST['post_id']    = $id;
			$_GET['post_id']     = $id;
			$_REQUEST['id']      = $id;
			$_POST['id']         = $id;
			$_GET['id']          = $id;
			$this->item_name     = $this->get_item_export_name( htmlspecialchars_decode( get_the_title( $id ) ) );
		}
	}

	/**
	 * Add a file to the archive
	 *
	 * @param $content_data
	 *
	 * @return void
	 */
	public function write_archive( $content_data ) {
		if ( $this->zip->locateName( $this->item_type . '/' ) !== false ) {
			$this->zip->addEmptyDir( $this->item_type );
		}

		$config = [];
		/**
		 * If files exists use it
		 */
		if ( $this->zip->locateName( Main::CFG_NAME ) !== false ) {
			$config = json_decode( $this->zip->getFromName( Main::CFG_NAME ), true );
		}

		/**
		 * Add each item to the archive's config
		 */
		$config[ $this->item_type ]   = $config[ $this->item_type ] ?? [];
		$config[ $this->item_type ][] = $this->item_name;
		$this->zip->addFromString( Main::CFG_NAME, json_encode( $config ) );

		if ( ! empty( $content_data['path'] ) ) {
			$this->zip->addFile( $content_data['path'], $this->item_type . '/' . $this->item_name . '.zip' );
		}
	}

	/**
	 * Export a page/post
	 *
	 * @throws Exception
	 */
	public function export_content( $id ) {
		return ( new \TCB_Content_Handler() )->export( $id, $this->item_name );
	}

	/**
	 * Export a landing page
	 *
	 * @throws Exception
	 */
	public function export_landing_page( $id ) {
		return ( new \TCB_Landing_Page_Transfer() )->export( $id, $this->item_name );
	}

	/**
	 * @throws Exception
	 */
	public function export_skin( $id ) {
		return ( new \Thrive_Transfer_Export( $this->item_name ) )->export( 'skin', $id );
	}


	/**
	 * Set the name of the archive
	 *
	 * @param      $name
	 * @param bool $ensure_filename - if true the name will be sanitized & suffixed with date
	 *
	 * @return void
	 */
	public function set_zip_name( $name, bool $ensure_filename = true ) {
		if ( $ensure_filename ) {
			$name               = sanitize_file_name( $name );
			$name               = $name ?: static::DEFAULT_NAME;
			$this->zip_filename = str_replace( ' ', '-', $name ) . '-' . gmdate( 'Y-m-d-H-i-s' ) . '.zip';
		} else {
			$this->zip_filename = $name;
		}

		$this->zip_path = Main::get_exported_dir_path() . $this->zip_filename;
	}

}
