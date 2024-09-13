<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */

namespace TVD\Autoresponder\FacebookPixel;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

interface SessionInterface {

	/**
	 * @return array
	 */
	public function getRequestParameters();
}
