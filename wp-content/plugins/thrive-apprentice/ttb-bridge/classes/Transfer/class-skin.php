<?php

namespace TVA\TTB\Transfer;
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Skin extends \Thrive_Transfer_Skin {
	protected $skin_class = \TVA\TTB\Skin::class;

	/**
	 * Check to see if the imported skin needs to be set as active and set it
	 *
	 * @param \Thrive_Skin $skin
	 */
	protected function maybe_make_active( $skin ) {
		// nothing needed here
	}

}
