<?php

/*
 * Plugin Name: Thrive Comments
 * Plugin URI: https://thrivethemes.com
 * Version: 2.28
 * Author: <a href="https://thrivethemes.com">Thrive Themes</a>
 * Description: Gamify your comments while simplifying moderation to grow an audience of true fans
 * Text Domain: thrive-comments
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden.
}

require_once( 'includes/class-thrive-comments-constants.php' );


/**
 * Main Thrive Comments Class
 */
final class Thrive_Comments extends Thrive_Comments_Constants {

	/**
	 * The single instance of the class.
	 *
	 * @var Thrive_Comments singleton instance.
	 */
	protected static $_instance = null;

	/**
	 * Main Thrive Comments Instance.
	 * Ensures only one instance of Thrive Comments is loaded or can be loaded.
	 *
	 * @return Thrive_Comments
	 */
	public static function instance() {
		if ( empty( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Thrive Comments Constructor.
	 */
	private function __construct() {
		$this->includes();
		$this->hooks();
	}


	/**
	 * Include files need for the plugin
	 */
	private function includes() {

		require_once( 'includes/class-thrive-comments-front.php' );
		require_once( 'includes/database/class-tcm-database-manager.php' );
		require_once( 'includes/class-thrive-comments-db.php' );
		require_once( 'includes/frontend/classes/helpers/class-thrive-comment-helper.php' );
		require_once( 'includes/frontend/classes/helpers/class-thrive-comment-conversion.php' );
		require_once( 'includes/frontend/classes/helpers/class-thrive-comment-settings.php' );

		require_once( 'includes/admin/classes/helpers/class-thrive-admin-helper.php' );
		require_once( 'includes/admin/classes/helpers/class-thrive-moderation-helper.php' );
		require_once( 'includes/admin/classes/class-thrive-comments-admin.php' );
		require_once( 'includes/admin/classes/class-thrive-comments-version-check.php' );
		require_once( 'includes/admin/classes/class-thrive-comments-privacy.php' );

		/**
		 * Include TCB
		 */
		$this->include_tcb();

		$this->include_rest_routes();

	}

	/**
	 * Add initial plugin hooks
	 */
	private function hooks() {
		add_action( 'init', array( $this, 'init' ) );
		add_action( 'plugins_loaded', array( $this, 'load_dashboard_module' ) );
		add_action( 'thrive_dashboard_loaded', array( $this, 'dashboard_loaded' ) );
	}

	/**
	 * Do init stuff specific for the plugin
	 */
	public function init() {
		do_action( 'tcm_init' );

		$this->load_plugin_textdomain();
		$this->update_checker();
	}

	/**
	 * Load text domain and set the translate files location
	 */
	private function load_plugin_textdomain() {
		$locale = apply_filters( 'plugin_locale', get_locale(), self::T );

		load_textdomain( self::T, WP_LANG_DIR . '/thrive/' . self::T . '-' . $locale . '.mo' );
		load_plugin_textdomain( self::T, false, plugin_basename( dirname( __FILE__ ) ) . '/languages' );
	}

	/**
	 * Called after dash has been loaded
	 */
	public function dashboard_loaded() {
		require_once( 'includes/admin/classes/class-thrive-comments-product.php' );
	}

	/**
	 * Checks for updates
	 */
	public function update_checker() {
		/** plugin updates script **/

		new TVE_PluginUpdateChecker(
			'http://service-api.thrivethemes.com/plugin/update',
			__FILE__,
			'thrive-comments',
			12,
			'',
			'thrive_comments'
		);

		add_filter( 'puc_request_info_result-thrive-comments', array( $this, 'thrive_comments_set_product_icon' ) );
	}

	/**
	 * Adding the product icon for the update core page
	 *
	 * @param $info
	 *
	 * @return mixed
	 */
	public function thrive_comments_set_product_icon( $info ) {
		$info->icons['1x'] = tcm()->plugin_url( 'assets/images/tcm-logo-icon.svg' );

		return $info;
	}

	/**
	 * Include the dashborad files to be shared between the products
	 */
	public function load_dashboard_module() {

		$tve_dash_path      = __DIR__ . '/thrive-dashboard';
		$tve_dash_file_path = $tve_dash_path . '/version.php';

		if ( is_file( $tve_dash_file_path ) ) {
			$version                                  = require_once( $tve_dash_file_path );
			$GLOBALS['tve_dash_versions'][ $version ] = array(
				'path'   => $tve_dash_path . '/thrive-dashboard.php',
				'folder' => '/thrive-comments',
				'from'   => 'plugins',
			);
		}
	}

	/**
	 * Check if the user has a valid license
	 *
	 * @return bool
	 */
	public function license_activated() {
		return TVE_Dash_Product_LicenseManager::getInstance()->itemActivated( TVE_Dash_Product_LicenseManager::TCM_TAG );
	}

	/**
	 * Call rest controllers
	 */
	public function include_rest_routes() {
		require_once $this->plugin_path( 'includes/frontend/classes/class-tcm-rest-controller.php' );
		require_once $this->plugin_path( 'includes/frontend/classes/endpoints/class-tcm-rest-comments-controller.php' );
		require_once $this->plugin_path( 'includes/admin/classes/endpoints/class-tcm-rest-settings-controller.php' );
		require_once $this->plugin_path( 'includes/admin/classes/endpoints/class-tcm-rest-conversion-settings-controller.php' );
		require_once $this->plugin_path( 'includes/admin/classes/endpoints/class-tcm-rest-moderation-controller.php' );
	}

	/**
	 * Returns the table name(wp prefix + tcm prefix + table name)
	 *
	 * @param string $table Table name.
	 *
	 * @return string
	 */
	public function tcm_table_name( $table ) {
		global $wpdb;

		return $wpdb->prefix . self::DB_PREFIX . $table;
	}

	/**
	 * Return complete url for route endpoint
	 *
	 * @param string $endpoint Rest endpoint.
	 * @param int    $id       Specific endpoint.
	 * @param array  $args     Additional arguments.
	 *
	 * @return string
	 */
	public function tcm_get_route_url( $endpoint, $id = 0, $args = array() ) {

		$url = get_rest_url() . self::TCM_REST_NAMESPACE . '/' . $endpoint;

		if ( ! empty( $id ) && is_numeric( $id ) ) {
			$url .= '/' . $id;
		}

		if ( ! empty( $args ) ) {
			add_query_arg( $args, $url );
		}

		return $url;
	}

	/**
	 * Wrapper over the wp_enqueue_script function
	 * It will add the plugin version to the script source if no version is specified
	 *
	 * @param string           $handle    Name of the script. Should be unique.
	 * @param string           $src       Full URL of the script, or path of the script relative to the WordPress root directory.
	 * @param array            $deps      Optional. An array of registered script handles this script depends on. Default empty array.
	 * @param string|bool|null $ver       Optional. String specifying script version number, if it has one, which is added to the URL
	 *                                    as a query string for cache busting purposes. If version is set to false, a version
	 *                                    number is automatically added equal to current installed WordPress version.
	 *                                    If set to null, no version is added.
	 * @param bool             $in_footer Optional. Whether to enqueue the script before </body> instead of in the <head>.
	 *                                    Default 'false'.
	 */
	public function tcm_enqueue_script( $handle, $src = '', $deps = array(), $ver = false, $in_footer = false ) {
		if ( false === $ver ) {
			$ver = Thrive_Comments_Constants::PLUGIN_VERSION;
		}

		if ( defined( 'TVE_DEBUG' ) && TVE_DEBUG ) {
			$src = preg_replace( '/\.min.js$/', '.js', $src );
		}

		wp_enqueue_script( $handle, $src, $deps, $ver, $in_footer );
	}

	/**
	 * Wrapper over the wp enqueue_style function
	 * It will add the version
	 *
	 * @param string           $handle Name of the stylesheet. Should be unique.
	 * @param string           $src    Full URL of the stylesheet, or path of the stylesheet relative to the WordPress root directory.
	 * @param array            $deps   Optional. An array of registered stylesheet handles this stylesheet depends on. Default empty array.
	 * @param string|bool|null $ver    Optional. String specifying stylesheet version number, if it has one, which is added to the URL
	 *                                 as a query string for cache busting purposes. If version is set to false, a version
	 *                                 number is automatically added equal to current installed WordPress version.
	 *                                 If set to null, no version is added.
	 * @param string           $media  Optional. The media for which this stylesheet has been defined.
	 *                                 Default 'all'. Accepts media types like 'all', 'print' and 'screen', or media queries like
	 *                                 '(orientation: portrait)' and '(max-width: 640px)'.
	 */
	function tcm_enqueue_style( $handle, $src, $deps = array(), $ver = false, $media = 'all' ) {
		if ( false === $ver ) {
			$ver = Thrive_Comments_Constants::PLUGIN_VERSION;

		}
		wp_enqueue_style( $handle, $src, $deps, $ver, $media );
	}

	/**
	 * Include TCB
	 */
	private function include_tcb() {
		if ( tcms()->tcm_get_setting_by_name( 'activate_comments' ) ) {
			require_once( 'tcb-bridge/tc-class-comments-hooks.php' );
		}
	}

	/**
	 * Whether or not TC is active on the site
	 *
	 * @return bool
	 */
	public function is_active() {

		$is_active = tcm()->license_activated() && tcms()->tcm_get_setting_by_name( 'activate_comments' );

		/**
		 * Enable the possibility for others to disable TC
		 *
		 * @param bool $is_active license is activated and "enabled" setting is turned on
		 *
		 * @return bool
		 */
		return apply_filters( 'tcm_active', $is_active );
	}

	/**
	 * Check if Recaptcha is connected and has the required version 3
	 *
	 * @return false|Thrive_Dash_List_Connection_Abstract
	 */
	public function recaptcha() {
		$recaptcha = Thrive_Dash_List_Manager::connection_instance( 'recaptcha' );
		if ( $recaptcha === null ) {
			return false;
		}

		$credentials = $recaptcha->get_credentials();
		if ( empty( $credentials['connection']['version'] ) || empty( $credentials['site_key'] ) || $credentials['connection']['version'] !== 'v3' ) {
			return false;
		}

		return $recaptcha;
	}
}

/**
 *  Main instance of Thrive Comments.
 *
 * @return Thrive_Comments
 */
function tcm() {
	return Thrive_Comments::instance();
}

tcm();
