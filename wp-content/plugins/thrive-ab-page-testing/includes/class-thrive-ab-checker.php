<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-ab-page-testing
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

class Thrive_AB_Checker {

	/**
	 * @var array
	 */
	private static $_tar_required = array(
		'active'      => true,
		'min_version' => '2.0.17',
	);

	public static function init() {

		register_activation_hook( THRIVE_AB_PLUGIN_FILE, array( __CLASS__, 'check' ) );
	}

	/**
	 * Check if the requirements for this plugin are fulfilled
	 * If not a notification is pushed into stack for later display
	 *
	 * @return void
	 */
	public static function check() {

		$details = self::get_thrive_architect_details();

		if ( empty( $details['active'] ) ) {
			Thrive_Admin_Notices::push_notice( 'active' );

			return;
		}

		if ( ! self::is_required_version( $details['version'] ) ) {
			Thrive_Admin_Notices::push_notice( 'min_version' );

			return;
		}
	}

	/**
	 * Check if the TAr version is greater or equal with required one
	 *
	 * @param $tar_version
	 *
	 * @return bool
	 */
	protected static function is_required_version( $tar_version ) {

		if ( defined( 'TVE_DEBUG' ) && $tar_version === '0.dev' ) {
			return true;
		}

		return version_compare( $tar_version, self::$_tar_required['min_version'], '>=' );
	}

	/**
	 * @return array
	 */
	public static function get_thrive_architect_details() {

		$_defaults = array(
			'active'  => false,
			'version' => 0,
		);

		$is_active = is_plugin_active( 'thrive-visual-editor/thrive-visual-editor.php' );
		$version   = defined( 'TVE_VERSION' ) ? TVE_VERSION : 0;

		$_defaults['active']  = $is_active;
		$_defaults['version'] = $version;

		return $_defaults;
	}

	public static function get_tar_required_version() {

		return self::$_tar_required['min_version'];
	}
}

Thrive_AB_Checker::init();
