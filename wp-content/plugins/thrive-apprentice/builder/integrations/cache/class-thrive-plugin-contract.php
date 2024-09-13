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
 * Interface Thrive_Plugin_Contract
 */
interface Thrive_Plugin_Contract {

	/**
	 * Check if the plugin has the configuration suggested by thrive
	 *
	 * @return bool
	 */
	public function is_configured();

	/**
	 * Return general information about the plugin
	 *
	 * @return array
	 */
	public function get_info();

	/**
	 * Update Plugin settings
	 *
	 * @param array $data
	 * @param bool  $keep_existing - whether or not to keep the existing settings for the plugin
	 *
	 * @return bool
	 */
	public function update_settings( $data = [], $keep_existing = false );
}
