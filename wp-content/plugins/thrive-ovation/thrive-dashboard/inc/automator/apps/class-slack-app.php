<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */

namespace TVE\Dashboard\Automator;
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

use Thrive\Automator\Items\App;

class Slack_App extends App {
	public static function get_id() {
		return 'slack';
	}

	public static function get_name() {
		return 'Slack';
	}

	public static function get_description() {
		return 'Slack';
	}

	public static function get_logo() {
		return 'tap-slack-logo';
	}

	public static function has_access() {
		$slack_instance = \Thrive_Dash_List_Manager::connection_instance( 'slack' );

		return $slack_instance !== null && $slack_instance->is_connected();
	}

	public static function get_acccess_url() {
		return admin_url( 'admin.php?page=tve_dash_api_connect' );
	}
}
