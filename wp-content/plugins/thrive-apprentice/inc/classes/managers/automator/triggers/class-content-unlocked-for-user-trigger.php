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

class Content_Unlocked_For_User extends Trigger {

	/**
	 * Get the trigger identifier
	 *
	 * @return string
	 */
	public static function get_id() {
		return 'thrive/content_unlock_for_user';
	}

	/**
	 * Get the trigger hook
	 *
	 * @return string
	 */
	public static function get_wp_hook() {
		return 'tva_drip_content_unlocked_for_specific_user';
	}

	/**
	 * Get the trigger provided params
	 *
	 * @return array
	 */
	public static function get_provided_data_objects() {
		return [ 'user_data', 'unlocked_content_data', 'product_data' ];
	}

	/**
	 * Get the number of params
	 *
	 * @return int
	 */
	public static function get_hook_params_number() {
		return 3;
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
		return 'Content is unlocked for a specific user';
	}

	/**
	 * Get the trigger description
	 *
	 * @return string
	 */
	public static function get_description() {
		return 'Trigger will be fired when a user fulfills requirements to unlock a specific piece of content. Will not be fired if content is unlocked sitewide.';
	}

	/**
	 * Get the trigger logo
	 *
	 * @return string
	 */
	public static function get_image() {
		return 'tap-apprentice-logo';
	}
}
