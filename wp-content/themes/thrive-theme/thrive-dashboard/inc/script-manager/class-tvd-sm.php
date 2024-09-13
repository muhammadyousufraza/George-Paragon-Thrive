<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

final class TVD_SM {


	/**
	 * The single instance of the class.
	 *
	 * @var TVD_SM singleton instance.
	 */
	protected static $_instance = null;

	/**
	 * Constructor.
	 */
	private function __construct() {
		add_action( 'thrive_dashboard_loaded', array( $this, 'includes' ) );
	}

	/**
	 * Main Instance.
	 * Ensures only one instance is loaded or can be loaded.
	 *
	 * @return TVD_SM
	 */
	public static function instance() {
		if ( empty( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Include needed files.
	 */
	public function includes() {

		require_once TVE_DASH_PATH . '/inc/script-manager/class-tvd-sm-constants.php';
		require_once TVE_DASH_PATH . '/inc/script-manager/includes/admin/classes/class-tvd-sm-admin-helper.php';

		$features = tve_dash_get_features();

		if ( isset( $features['script_manager'] ) ) {
			require_once TVE_DASH_PATH . '/inc/script-manager/includes/admin/classes/class-tvd-sm-admin.php';
		}

		/**
		 * Allows loading frontend code only when required
		 */
		if ( apply_filters( 'td_include_script_manager', false ) ) {
			require_once TVE_DASH_PATH . '/inc/script-manager/includes/frontend/classes/class-tvd-sm-frontend.php';
		}
	}
}

function TVD_SM() {
	return TVD_SM::instance();
}

TVD_SM();
