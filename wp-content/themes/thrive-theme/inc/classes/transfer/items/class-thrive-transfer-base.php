<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

abstract class Thrive_Transfer_Base {

	/**
	 * Json filename where to keep the data for the items
	 *
	 * @var string
	 */
	public static $file;

	/**
	 * @var ZipArchive
	 */
	protected $zip;

	/**
	 * Items to be exported
	 *
	 * @var array
	 */
	protected $items = [];

	/**
	 * Item for export
	 *
	 * @var array
	 */
	protected $item;

	/**
	 * Element key in the archive
	 *
	 * @var string
	 */
	protected $tag;

	/**
	 * @var Thrive_Transfer_Archive_Data
	 */
	protected $archive_data;

	/**
	 * @var Thrive_Transfer_Controller
	 */
	protected $controller;

	/**
	 * Import data
	 *
	 * @var array
	 */
	protected $data = [];

	/**
	 * Options for export / import
	 *
	 * @var array
	 */
	protected $options;

	/**
	 * Default options for each element at import / export
	 *
	 * @var array
	 */
	protected $defaults = [];

	/**
	 * Thrive_Transfer_Base constructor.
	 *
	 * @param Thrive_Transfer_Controller $controller
	 */
	public function __construct( $controller ) {
		$this->archive_data = Thrive_Transfer_Archive_Data::instance();
		$this->controller   = $controller;
	}

	/**
	 * Add items to the archive
	 *
	 * @param array      $items
	 * @param ZipArchive $zip
	 */
	public static function add_to_archive( $items, $zip ) {
		if ( $items ) {
			$type = str_replace( '.json', '', static::$file );
			/**
			 * Allow tweaking exported items just before adding them to the json file
			 *
			 * @param array      $items items being added
			 * @param ZipArchive $zip   ZipArchive instance
			 *
			 * @return array
			 */
			$items = apply_filters( "thrive_skin_export_{$type}", $items, $zip );

			$zip->addFromString( static::$file, apply_filters( 'tcb_theme_modify_export_items', json_encode( $items ) ) );
		}
	}

	/**
	 * Check if an item already exists in the data for the archive
	 *
	 * @param int $id
	 *
	 * @return bool
	 */
	public function exists( $id ) {
		$items = $this->archive_data[ $this->tag ];

		return ! empty( $items[ $id ] );
	}

	/**
	 * @return array
	 */
	public function get_item() {
		return $this->item;
	}

	/**
	 * General validation function
	 * If a json file exists, this shouldn't be empty
	 *
	 * @return $this
	 * @throws Exception
	 */
	public function validate() {
		$json_file = $this->controller->zip->getFromName( static::$file );
		if ( $json_file ) {
			/* First let's see if anyone has something to say about this */
			$json_file = apply_filters( 'tcb_theme_modify_import_items', $json_file );

			$this->data = json_decode( $json_file, true );

			if ( empty( $this->data ) ) {
				throw new Exception( "Invalid archive, no {$this->tag} found!" );
			}
		} else {
			/**
			 * No JSON file - we need to ensure that the archive data has the index set
			 */
			$this->archive_data[ $this->tag ] = [];
		}

		return $this;
	}

	/**
	 * Check if the item was previously saved
	 *
	 * @param int $id
	 *
	 * @return bool
	 */
	public function already_saved( $id ) {
		return ! empty( $this->archive_data[ $this->tag ][ $id ] );
	}

	/**
	 * Setter for extra options used at import / export
	 *
	 * @param array $args
	 *
	 * @return $this
	 */
	public function set_options( $args ) {
		$this->options = wp_parse_args( $args, $this->defaults );

		return $this;
	}

	/**
	 * Getter for extra options used at import / export
	 *
	 * @return array
	 */
	public function get_options() {
		return $this->options;
	}
}
