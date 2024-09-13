<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

namespace TVA\Automator;

use Thrive\Automator\Items\Trigger;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Content_Unlocked_Sitewide extends Trigger {

	/**
	 * Get the trigger identifier
	 *
	 * @return string
	 */
	public static function get_id() {
		return 'thrive/content_unlock_sitewide';
	}

	/**
	 * Get the trigger hook
	 *
	 * @return string
	 */
	public static function get_wp_hook() {
		return 'tva_drip_content_unlocked_sitewide';
	}

	/**
	 * Get the trigger provided params
	 *
	 * @return array
	 */
	public static function get_provided_data_objects() {
		return [ 'unlocked_content_data', 'product_data' ];
	}

	/**
	 * Get the number of params
	 *
	 * @return int
	 */
	public static function get_hook_params_number() {
		return 2;
	}

	/**
	 * Get the name of the app to which the hook belongs
	 *
	 * @return string
	 */
	public static function get_app_id() {
		return Apprentice_App::get_id();
	}

	/**
	 * Get the trigger name
	 *
	 * @return string
	 */
	public static function get_name() {
		return 'Content is unlocked for everyone';
	}

	/**
	 * Get the trigger description
	 *
	 * @return string
	 */
	public static function get_description() {
		return 'Trigger will be fired when content is unlocked sitewide. Will not be fired when content is unlocked for a specific user.';
	}

	/**
	 * Get the trigger logo
	 *
	 * @return string
	 */
	public static function get_image() {
		return 'tap-apprentice-logo';
	}

	public static function hidden() {
		return true;
	}
}
