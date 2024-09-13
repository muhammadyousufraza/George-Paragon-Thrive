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
 * Class Thrive_Transfer_Palettes
 */
class Thrive_Transfer_Palettes extends Thrive_Transfer_Base {

	public static $file = 'palettes.json';

	/**
	 * Element key in the archive
	 *
	 * @var string
	 */
	public $tag = 'palettes';

	/**
	 * Add Palettes to Archive
	 */
	public function add() {
		$this->archive_data['palettes'] = $this->get_palette()->export_palette();
	}

	/**
	 * Import palettes taken from the archive
	 */
	public function import() {
		if ( ! empty( $this->data ) ) {
			$this->get_palette()->maybe_update( $this->data );
		}
	}

	/**
	 * Returns the needed palette
	 *
	 * @return Thrive_Palette
	 */
	private function get_palette() {
		/**
		 * Used in other plugins (thrive-apprentice) for calling the right palette for the transfer
		 */
		return apply_filters( 'thrive_theme_palette_transfer_get_palette', thrive_palettes() );
	}
}
