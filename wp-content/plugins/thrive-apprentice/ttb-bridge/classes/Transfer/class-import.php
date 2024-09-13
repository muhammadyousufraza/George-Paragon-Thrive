<?php

namespace TVA\TTB\Transfer;

require_once THEME_PATH . '/inc/classes/transfer/class-thrive-transfer-import.php';


/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Import extends \Thrive_Transfer_Import {
	/**
	 * Import main entry point
	 *
	 * @param string $type    Type of the element we want to export
	 * @param array  $options Extra options to take into account when importing
	 *
	 * @return \WP_Term|false
	 * @throws \Exception
	 */
	public function import( $type, $options = [] ) {
		/* make sure we have enough memory to process the whole skin */
		wp_raise_memory_limit();

		$this->open_archive();

		$this->validate_archive();

		$skin_transfer = new Skin( new \Thrive_Transfer_Controller( $this->zip ) );

		return $skin_transfer->validate()->import();
	}


}
