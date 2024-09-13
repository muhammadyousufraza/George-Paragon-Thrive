<?php

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

namespace TVA;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

interface TVA_Singleton_Interface {

	public static function get_instance();
}
