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
 * Class Thrive_Transfer_Layout
 */
class Thrive_Transfer_Layout extends Thrive_Transfer_Base {
	/**
	 * Json filename where to keep the data for the templates
	 *
	 * @var string
	 */
	public static $file = 'layouts.json';

	/**
	 * Element key in the archive
	 *
	 * @var string
	 */
	protected $tag = 'layout';

	/**
	 * Layout id
	 *
	 * @var int
	 */
	private $id;

	/**
	 * Read the layout data for export
	 *
	 * @param $id
	 *
	 * @return $this
	 */
	public function read( $id ) {
		$this->id   = $id;
		$this->item = ( new Thrive_Layout( $id ) )->export();

		$this->replace_id();

		return $this;
	}

	/**
	 * Replace layout id with a hash in order to have it prepared on the other side
	 */
	public function replace_id() {
		$hash    = md5( $this->id );
		$content = json_encode( $this->item );
		$content = str_replace( ".thrive-layout-{$this->id}", ".thrive-layout-{$hash}", $content );

		$this->item = json_decode( $content, true );
	}

	/**
	 * Add layout data to the archive
	 */
	public function add() {
		$layouts                          = $this->archive_data[ $this->tag ];
		$layouts[ md5( $this->id ) ]      = $this->item;
		$this->archive_data[ $this->tag ] = $layouts;
	}

	/**
	 * Save the layouts from the archive in the db
	 *
	 * @throws Exception
	 */
	public function import() {
		$layouts = [];

		foreach ( $this->data as $hash => $layout ) {
			unset( $layout['ID'] );
			$layout_id = wp_insert_post( $layout );

			if ( is_wp_error( $layout_id ) ) {
				throw new Exception( 'Error at layout import' );
			}

			( new Thrive_Layout( $layout_id ) )->replace_id_from_style( $hash );

			wp_set_object_terms( $layout_id, $this->archive_data['skin_id'], SKIN_TAXONOMY );

			$layouts[ $hash ] = $layout_id;
		}

		$this->archive_data[ $this->tag ] = $layouts;
	}
}
