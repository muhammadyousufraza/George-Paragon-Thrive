<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

class TD_TTW_Update_Manager {

	use TD_Singleton;

	use TD_TTW_Utils;

	const NAME = 'tve_dash_ttw_account';

	const SUITE_URL = 'https://thrivethemes.com/suite/';

	const TTB_DOMAIN = 'thrive-theme';

	/**
	 * @var array
	 */
	protected $_errors = array();

	/**
	 * TD_TTW_Update_Manager constructor.
	 */
	private function __construct() {

		$this->init();
	}

	public function init() {

		$this->_includes();
		$this->_actions();
	}

	/**
	 * Handler for tve_dash_ttw_account section
	 */
	public function tve_dash_ttw_account() {

		if ( ! TD_TTW_Connection::get_instance()->is_connected() ) {
			TD_TTW_Connection::get_instance()->render();
		} else {
			TD_TTW_User_Licenses::get_instance()->render();
		}
	}

	/**
	 * Loads needed files
	 */
	private function _includes() {
		require_once TVE_DASH_PATH . '/inc/ttw-account/classes/class-td-ttw-user-licenses.php';
		require_once TVE_DASH_PATH . '/inc/ttw-account/classes/class-td-ttw-license.php';
		require_once TVE_DASH_PATH . '/inc/ttw-account/classes/class-td-ttw-request.php';
		require_once TVE_DASH_PATH . '/inc/ttw-account/classes/class-td-ttw-proxy-request.php';
		require_once TVE_DASH_PATH . '/inc/ttw-account/classes/class-td-ttw-messages-manager.php';
	}

	/**
	 * Add needed action for ttw section
	 */
	private function _actions() {

		add_action( 'admin_menu', array( $this, 'register_section' ), 10 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ), PHP_INT_MAX );
		add_action( 'current_screen', array( $this, 'try_process_connection' ) );
		add_action( 'current_screen', array( $this, 'try_set_url' ) );
		add_action( 'current_screen', array( $this, 'try_logout' ) );
		add_action( 'admin_init', array( $this, 'ensure_license_details' ) );
		add_filter( 'auto_update_plugin', array( $this, 'auto_update_plugin' ), 10, 2 );
		add_filter( 'auto_update_theme', array( $this, 'auto_update_theme' ), 10, 2 );
		add_filter( 'wp_prepare_themes_for_js', array( $this, 'wp_prepare_themes_for_js' ) );
		add_filter( 'site_transient_update_themes', array( $this, 'hide_theme_updates' ), 10, 2 );
	}

	/**
	 * @param stdClass $transient
	 *
	 * @return stdClass
	 */
	public function hide_theme_updates( $transient ) {

		if ( static::is_wp_core_update_screen() && false === static::has_access_to_updates( TD_TTW_User_Licenses::TTB_TAG ) ) {
			unset( $transient->response[ self::TTB_DOMAIN ] );
		}

		return $transient;
	}

	/**
	 * Checks if there is a membership which can_update
	 * or check if there is a license with $tag which can_update
	 *
	 * On false - another logic for reason message has to be applied
	 *
	 * @param string $tag
	 *
	 * @return bool
	 */
	public static function has_access_to_updates( $tag ) {

		$is_connected = TD_TTW_Connection::get_instance()->is_connected();

		/** @var TD_TTW_User_Licenses $licenses */
		$licenses = TD_TTW_User_Licenses::get_instance();

		$membership           = $licenses->get_membership();
		$has_valid_membership = $membership && true === $membership->can_update();

		$ttb_license       = $licenses->get_license( $tag );
		$has_valid_license = $ttb_license && true === $ttb_license->can_update();

		return $is_connected && ( $has_valid_membership || $has_valid_license );
	}

	/**
	 * @param array $themes
	 *
	 * @return array
	 */
	public function wp_prepare_themes_for_js( $themes ) {

		$is_ttb_installed = isset( $themes[ self::TTB_DOMAIN ] );
		$is_ttb_installed = $is_ttb_installed && is_array( $themes[ self::TTB_DOMAIN ] );

		//ttb is installed and doesn't have access to updates
		//then display update message - why it doesn't have access to updates
		if ( true === $is_ttb_installed && false === static::has_access_to_updates( TD_TTW_User_Licenses::TTB_TAG ) ) {
			$themes[ self::TTB_DOMAIN ]['hasPackage'] = 0;
			$themes[ self::TTB_DOMAIN ]['update']     = $this->_get_theme_update_message( $themes[ self::TTB_DOMAIN ] );
		}

		return $themes;
	}

	/**
	 * @param array $data
	 *
	 * @return false|string|null
	 */
	private function _get_theme_update_message( $data ) {

		if ( ! is_array( $data ) || empty( $data ) || ! current_user_can( 'update_themes' ) || is_multisite() ) {
			return null;
		}

		$themes_update = get_site_transient( 'update_themes' );

		if ( ! isset( $themes_update->response[ self::TTB_DOMAIN ] ) ) {
			return null;
		}

		$themes_update = $themes_update->response[ self::TTB_DOMAIN ];
		$details_url   = add_query_arg(
			array(
				'TB_iframe' => 'true',
				'width'     => 1024,
				'height'    => 800,
			),
			$themes_update['url']
		);

		$template      = 'theme/update';
		$template_data = array(
			'name'        => $data['name'],
			'version'     => $themes_update['new_version'],
			'details_url' => $details_url,
			'recheck_url' => add_query_arg(
				array(
					'theme' => 'thrive-theme',
				),
				TD_TTW_User_Licenses::get_instance()->get_recheck_url( 'themes.php' )
			),
		);

		$membership = static::get_membership();
		/** @var TD_TTW_User_Licenses $licenses */
		$licenses      = TD_TTW_User_Licenses::get_instance();
		$theme_license = $licenses->get_license( TD_TTW_User_Licenses::TTB_TAG );

		if ( false === TD_TTW_Connection::get_instance()->is_connected() ) {
			$template = 'theme/disconnected';
		} elseif ( $membership && false === $membership->is_active() ) {
			$template = 'theme/membership-expired';
		} elseif ( $theme_license && false === $theme_license->can_update() ) {
			$template = 'theme/license-expired';
		} elseif ( ! $membership && ! $theme_license ) {
			$template = 'theme/no-license-found';
		}

		$error = thrive_get_transient( 'td_ttw_connection_error' );
		if ( ! empty( $error ) ) {
			$template                       = 'error';
			$template_data['error_message'] = $error;
		}

		return TD_TTW_Messages_Manager::render(
			$template,
			true,
			$template_data
		);
	}

	/**
	 * @return TD_TTW_License|null
	 */
	public static function get_membership() {

		return TD_TTW_User_Licenses::get_instance()->get_membership();
	}

	/**
	 * @return bool
	 */
	public static function allow_membership_updates() {
		/**  @var TD_TTW_Connection $connection */
		$connection = TD_TTW_Connection::get_instance();

		if ( ! $connection->is_connected() ) {
			return false;
		}

		/**  @var TD_TTW_User_Licenses $licenses */
		$licenses = TD_TTW_User_Licenses::get_instance();

		return $licenses->get_membership() && $licenses->get_membership()->can_update();
	}

	/**
	 * Handle auto update plugin action
	 *
	 * @param bool   $update
	 * @param object $item
	 *
	 * @return bool
	 */
	public function auto_update_plugin( $update, $item ) {

		$doing_cron  = apply_filters( 'wp_doing_cron', defined( 'DOING_CRON' ) && DOING_CRON );
		$auto_update = doing_action( 'wp_maybe_auto_update' );

		if ( ( ! $doing_cron && ! $auto_update ) || ! isset( $item->plugin ) ) {
			return $update;
		}

		$file = wp_normalize_path( WP_PLUGIN_DIR . '/' . $item->plugin );

		if ( ! is_file( $file ) ) {
			return $update;
		}

		$plugin_data = get_plugin_data( $file );

		if ( isset( $plugin_data['PluginURI'] ) && false !== strpos( $plugin_data['PluginURI'], 'thrivethemes.com' ) && ! self::allow_membership_updates() ) {
			/* stop auto update only if the user doesn't have membership updates */
			$update = false;
		}

		return $update;
	}

	/**
	 * Handle auto update theme action
	 *
	 * @param bool     $update
	 * @param stdClass $item
	 *
	 * @return bool
	 */
	public function auto_update_theme( $update, $item ) {

		$doing_cron  = apply_filters( 'wp_doing_cron', defined( 'DOING_CRON' ) && DOING_CRON );
		$auto_update = doing_action( 'wp_maybe_auto_update' );
		$stop        = ! $update || ( ! $doing_cron && ! $auto_update );

		if ( ! $stop && isset( $item->theme ) && self::TTB_DOMAIN === $item->theme && ! self::allow_membership_updates() ) {
			/* stop auto update if the user doesn't have membership updates */
			$update = false;
		}

		return $update;
	}

	/**
	 * Ensure license details
	 */
	public function ensure_license_details() {

		/** @var $connection TD_TTW_Connection */
		$connection = TD_TTW_Connection::get_instance();
		/** @var $licenses TD_TTW_User_Licenses */
		$licenses = TD_TTW_User_Licenses::get_instance();

		if ( $connection->is_connected() && empty( $licenses->get() ) ) {
			$licenses->get_licenses_details();
		}
	}

	/**
	 * Check if current screen is wp bulk updates screen
	 *
	 * @return bool
	 */
	public static function is_wp_core_update_screen() {
		global $current_screen;

		return isset( $current_screen->id ) && 'update-core' === $current_screen->id;
	}

	/**
	 * Check if the updates should be available in WP Updates screen
	 *
	 * @return bool
	 */
	public static function can_see_updates() {

		return false === static::is_wp_core_update_screen();
	}

	/**
	 * Register ttw section
	 */
	public function register_section() {

		if ( empty( $_REQUEST['page'] ) || self::NAME !== $_REQUEST['page'] ) {
			return;
		}

		add_submenu_page(
			'',
			'',
			'',
			'manage_options',
			self::NAME,
			array( $this, 'tve_dash_ttw_account' )
		);
	}

	/**
	 * Process ttw connection
	 */
	public function try_process_connection() {

		if ( ! $this->is_known_page() ) {
			return;
		}

		/**  @var $connection TD_TTW_Connection */
		$connection = TD_TTW_Connection::get_instance();

		if ( ! empty( $_REQUEST['td_token'] ) ) {

			$processed = $connection->process_request();

			if ( true === $processed ) {

				/** @var $licenses TD_TTW_User_Licenses */
				$licenses = TD_TTW_User_Licenses::get_instance();

				$licenses->get_licenses_details(); //get licenses details

				if ( $licenses->has_membership() && $licenses->is_membership_active() ) {
					$connection->push_message( 'Your account has been successfully connected.', 'success' );
				}

				wp_redirect( $this->get_admin_url() );
				die();
			}
		}
	}

	/**
	 * Log out ttw account
	 */
	public function try_logout() {

		if ( ! $this->is_known_page() ) {
			return;
		}

		if ( ! empty( $_REQUEST['td_disconnect'] ) ) {

			/** @var TD_TTW_Connection $connection */
			$connection = TD_TTW_Connection::get_instance();

			$params  = array(
				'website' => get_site_url(),
			);
			$request = new TD_TTW_Request( '/api/v1/public/disconnect/' . $connection->ttw_id, $params );
			$request->set_header( 'Authorization', $connection->ttw_salt );

			$proxy_request = new TD_TTW_Proxy_Request( $request );
			$proxy_request->execute( '/tpm/proxy' );

			$connection->disconnect();

			wp_redirect( admin_url( 'admin.php?page=' . TD_TTW_Update_Manager::NAME ) );
			die;
		}
	}

	public function try_set_url() {

		if ( ! current_user_can( 'manage_options' ) || ! TD_TTW_Connection::is_debug_mode() || ! $this->is_known_page() ) {
			return;
		}

		if ( ! empty( $_REQUEST['url'] ) && ! empty( $_REQUEST['td_action'] ) && sanitize_text_field( $_REQUEST['td_action'] ) === 'set_url' ) {

			update_option( 'tpm_ttw_url', sanitize_url( $_REQUEST['url'] ) );

			wp_redirect( $this->get_admin_url() );
			die;
		}
	}

	/**
	 * @return string|void
	 */
	public function get_admin_url() {

		return admin_url( 'admin.php?page=' . self::NAME );
	}

	/**
	 * Enqueue scripts
	 */
	public function enqueue_scripts() {

		if ( ! $this->is_known_page() ) {
			return;
		}

		wp_enqueue_style( 'td-ttw-style', $this->url( 'css/admin.css' ), array(), uniqid() );
	}

	/**
	 * Check if the screen is ttw account screen
	 *
	 * @return bool
	 */
	public function is_known_page() {
		return isset( $_REQUEST['page'] ) && self::NAME === $_REQUEST['page'];
	}
}

TD_TTW_Update_Manager::get_instance();
