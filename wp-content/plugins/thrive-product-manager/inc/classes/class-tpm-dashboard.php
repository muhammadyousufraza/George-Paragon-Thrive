<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-product-manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}


class TPM_Dashboard {

	public static function init() {
		/* the priority here must be lower than the one set from thrive-dashboard/version.php */
		add_action( 'plugins_loaded', [ __CLASS__, 'load_dash_version' ], 1 );
	}

	/**
	 * Check the current version of the dashboard and decide if we load this one or a newer one
	 */
	public static function load_dash_version() {
		$tpm_dash_path = thrive_product_manager()->path() . '/thrive-dashboard';

		$tve_dash_file_path = $tpm_dash_path . '/version.php';

		if ( is_file( $tve_dash_file_path ) ) {
			$version                                  = require_once( $tve_dash_file_path );
			$GLOBALS['tve_dash_versions'][ $version ] = array(
				'path'   => $tpm_dash_path . '/thrive-dashboard.php',
				'folder' => '/thrive-product-manager',
				'from'   => 'plugins',
			);
		}
	}
}

