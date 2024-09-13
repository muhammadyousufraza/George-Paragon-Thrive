<?php

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}
/**
 * Place where CONSTANTS, ACTIONS and FILTERS are defined
 * Implementations of all of those are placed into inc/hooks.php
 * Loads dependencies files
 */

/**
 * CONSTANTS
 */
defined( 'TVE_DASH_PATH' ) || define( 'TVE_DASH_PATH', $GLOBALS['tve_dash_loaded_from'] === 'plugins' ? rtrim( plugin_dir_path( __FILE__ ), '/\\' ) : rtrim( get_template_directory(), '/\\' ) . '/thrive-dashboard' );
defined( 'TVE_DASH_CAPABILITY' ) || define( 'TVE_DASH_CAPABILITY', 'tve-use-td' );
defined( 'TVE_DASH_EDIT_CPT_CAPABILITY' ) || define( 'TVE_DASH_EDIT_CPT_CAPABILITY', 'tve-edit-cpt' );

defined( 'TVE_DASH_VERSION' ) || define( 'TVE_DASH_VERSION', require dirname( __FILE__ ) . '/version.php' );
defined( 'TVE_SECRET' ) || define( 'TVE_SECRET', 'tve_secret' );

/**
 * Dashboard Database Version
 * Meanwhile the 1.0.4 migration was deleted, so the next file should be 1.0.5
 */
defined( 'TVE_DASH_DB_VERSION' ) || define( 'TVE_DASH_DB_VERSION', '1.0.4' );

/**
 * REQUIRED FILES
 */
require_once TVE_DASH_PATH . '/traits/trait-singleton.php';

require_once TVE_DASH_PATH . '/classes/class-tve-wpdb.php';

require_once TVE_DASH_PATH . '/rest-api/init.php';
require_once TVE_DASH_PATH . '/inc/util.php';
require_once TVE_DASH_PATH . '/inc/hooks.php';
require_once TVE_DASH_PATH . '/inc/functions.php';
require_once TVE_DASH_PATH . '/inc/crons.php';
require_once TVE_DASH_PATH . '/inc/plugin-updates/plugin-update-checker.php';
require_once TVE_DASH_PATH . '/inc/notification-manager/class-td-nm.php';
require_once TVE_DASH_PATH . '/inc/db-manager/class-td-db-migration.php';
require_once TVE_DASH_PATH . '/inc/db-manager/class-td-db-manager.php';
require_once TVE_DASH_PATH . '/inc/script-manager/class-tvd-sm.php';
require_once TVE_DASH_PATH . '/inc/login-editor/classes/class-main.php';
require_once TVE_DASH_PATH . '/inc/coming-soon/classes/class-main.php';
require_once TVE_DASH_PATH . '/inc/auth-check/class-tvd-auth-check.php';
require_once TVE_DASH_PATH . '/inc/smart-site/classes/class-tvd-smart-shortcodes.php';
require_once TVE_DASH_PATH . '/inc/smart-site/classes/class-tvd-global-shortcodes.php';
require_once TVE_DASH_PATH . '/inc/smart-site/classes/class-tvd-smart-db.php';
require_once TVE_DASH_PATH . '/inc/smart-site/classes/class-tvd-smart-site.php';
require_once TVE_DASH_PATH . '/inc/smart-site/class-tvd-smart-const.php';
require_once TVE_DASH_PATH . '/inc/smart-site/classes/class-tvd-rest-controller.php';
require_once TVE_DASH_PATH . '/inc/smart-site/classes/endpoints/class-tvd-groups-controller.php';
require_once TVE_DASH_PATH . '/inc/smart-site/classes/endpoints/class-tvd-fields-controller.php';
require_once TVE_DASH_PATH . '/inc/access-manager/class-tvd-access-manager.php';
require_once TVE_DASH_PATH . '/inc/marketing/functions.php';
require_once TVE_DASH_PATH . '/inc/ttw-account/traits/trait-magic-methods.php';
require_once TVE_DASH_PATH . '/inc/ttw-account/traits/trait-ttw-utils.php';
require_once TVE_DASH_PATH . '/inc/ttw-account/classes/class-td-ttw-connection.php';
require_once TVE_DASH_PATH . '/inc/ttw-account/classes/class-td-ttw-update-manager.php';
require_once TVE_DASH_PATH . '/inc/automator/class-main.php';
require_once TVE_DASH_PATH . '/inc/design-packs/class-main.php';
require_once TVE_DASH_PATH . '/inc/smart-site/classes/class-tvd-content-sets.php';
require_once TVE_DASH_PATH . '/inc/cache/meta-cache.php';
require_once TVE_DASH_PATH . '/inc/cache/runtime-cache.php';
require_once TVE_DASH_PATH . '/inc/access-manager/class-tvd-am-functionality.php';
require_once TVE_DASH_PATH . '/inc/access-manager/class-tvd-am-admin-bar-visibility.php';
require_once TVE_DASH_PATH . '/inc/access-manager/class-tvd-am-login-redirect.php';
require_once TVE_DASH_PATH . '/inc/pdf/class-pdf-from-url.php';
require_once TVE_DASH_PATH . '/inc/metrics/class-main.php';

require_once TVE_DASH_PATH . '/inc/reporting-dashboard/functions.php';

/**
 * AUTO-LOADERS
 */
spl_autoload_register( 'tve_dash_autoloader' );

/**
 * Allow other products to hook in after the main dashboard files have been loaded
 * done here because the next call to `tve_dash_get_features()` is hooked into every product, and each product needs the thrive dashboard ProductAbstract
 */
do_action( 'thrive_dashboard_loaded' );

if ( is_admin() ) {
	$features = tve_dash_get_features();
	if ( isset( $features['api_connections'] ) ) {
		require_once TVE_DASH_PATH . '/inc/auto-responder/admin.php';
	}
	if ( isset( $features['icon_manager'] ) ) {
		require_once( TVE_DASH_PATH . '/inc/icon-manager/classes/Tve_Dash_Thrive_Icon_Manager.php' );
	}
	/**
	 * Icon library
	 */
	require_once( TVE_DASH_PATH . '/inc/icon-manager/classes/Tve_Dash_Icon_Manager.php' );
	/**
	 * Inbox notifications
	 */
	require_once TVE_DASH_PATH . '/inc/notification-inbox/class-td-inbox.php';
	TD_Inbox::instance();
}

if ( wp_doing_ajax() || apply_filters( 'tve_leads_include_auto_responder', true ) ) {  // I changed this for NM. We should always include autoresponder code in the solution
	require_once TVE_DASH_PATH . '/inc/auto-responder/misc.php';
}

/**
 * ACTIONS
 */
add_action( 'init', 'tve_dash_init_action' );
add_action( 'init', 'tve_dash_load_text_domain' );
/* priority -1 so we can be compatible with WP Cerber */
add_action( 'init',
	function () {
		TVD\Login_Editor\Main::init();
		TVD\Coming_Soon\Main::init();
		TVD\Dashboard\Access_Manager\Main::init();
		TVD\Dashboard\Access_Manager\Admin_Bar_Visibility::init();
		TVD\Dashboard\Access_Manager\Login_Redirect::init();
		TVE\Dashboard\Design_Packs\Main::init();
		TVE\Dashboard\Metrics\Main::init();
	}, - 1 );
if ( defined( 'WPSEO_FILE' ) ) {
	/* Yoast SEO plugin installed -> use a hook provided by the plugin for configuring meta "robots" */
	add_filter( 'wpseo_robots_array', function ( $robots ) {
		if ( ! tve_dash_should_index_page() ) {
			$robots = array( 'index' => 'noindex' );
		}

		return $robots;
	} );
} else {
	/* Default behaviour: add a meta "robots" noindex if needed */
	add_action( 'wp_head', 'tve_dash_custom_post_no_index' );
}
add_action( 'wp_enqueue_scripts', 'tve_dash_frontend_enqueue' );

if ( is_admin() ) {
	require TVE_DASH_PATH . '/inc/db-updater/init.php';
	add_action( 'init', 'tve_dash_check_default_cap' );
	add_action( 'admin_menu', 'tve_dash_admin_menu', 10 );
	add_action( 'admin_enqueue_scripts', 'tve_dash_admin_enqueue_scripts' );
	add_action( 'admin_enqueue_scripts', 'tve_dash_admin_dequeue_conflicting', 90000 );
	add_action( 'wp_ajax_tve_dash_backend_ajax', 'tve_dash_backend_ajax' );


	add_action( 'wp_ajax_tve_dash_front_ajax', 'tve_dash_frontend_ajax_load' );
	add_action( 'wp_ajax_nopriv_tve_dash_front_ajax', 'tve_dash_frontend_ajax_load' );

	add_action( 'current_screen', 'tve_dash_current_screen' );
}

/**
 * Hook when a user submits a wordpress login form & the login has been successful
 *
 * Adds a user meta with last login timestamp
 */
add_action( 'wp_login', 'tve_dash_on_user_login', 10, 2 );

/**
 * Hook when a user submits a wordpress login form & the login has been failed
 */
add_action( 'wp_login_failed', 'tve_dash_on_user_login_failed', 10, 2 );
