<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-ultimatum
 */

namespace TU\Automator;
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Campaign_Started extends \Thrive\Automator\Items\Trigger {
	/**
	 * @inheritDoc
	 */
	public static function get_id() {
		return 'ultimatum/campaign_started';
	}

	/**
	 * @inheritDoc
	 */
	public static function get_wp_hook() {
		return 'thrive_ultimatum_specific_user_evergreen_campaign_start';
	}

	/**
	 * @inheritDoc
	 */
	public static function get_provided_data_objects() {
		return array( 'campaign_data', 'user_data' );
	}

	/**
	 * @inheritDoc
	 */
	public static function get_hook_params_number() {
		return 2;
	}

	public static function get_app_id() {
		return Ultimatum_App::get_id();
	}

	/**
	 * @inheritDoc
	 */
	public static function get_name() {
		return __( 'User triggers Ultimatum evergreen campaign', 'thrive-ult');
	}

	/**
	 * @inheritDoc
	 */
	public static function get_description() {
		return __( 'This trigger will be fired when a user triggers an evergreen Ultimatum campaign by either visiting a landing page, entering their details into a lead generation form or when triggered by 3rd party events', 'thrive-ult');
	}

	/**
	 * @inheritDoc
	 */
	public static function get_image() {
		return 'tap-ultimatum-logo';
	}
}
