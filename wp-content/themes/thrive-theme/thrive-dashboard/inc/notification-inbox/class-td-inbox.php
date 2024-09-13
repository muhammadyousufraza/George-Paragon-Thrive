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
 * Class TD_Inbox
 *
 * Main class for: enqueue scripts, autoloader, initialization
 */
final class TD_Inbox {

	const WEBINARJAM_V4 = 4;

	/**
	 * @var TD_Inbox
	 */
	public static $_instance;

	/**
	 * @var array
	 */
	protected $_list = array();

	/**
	 * @var string
	 */
	private $_init_path;

	/**
	 * @var string
	 */
	private $_admin_path;

	/**
	 * @var string
	 */
	private $_namespace;

	/**
	 * @var string
	 */
	private $_version;

	/**
	 * @var array
	 */
	private $_allowed_screens;

	/**
	 * @var array
	 */
	private $_allowed_routes;

	/**
	 * GTW slug
	 *
	 * @var string
	 */
	private $_gtw_slug = 'gotowebinar';

	/**
	 * Ontraport slug
	 *
	 * @var string
	 */
	private $_ontraport_slug = 'ontraport';

	/**
	 * Ontraport slug
	 *
	 * @var string
	 */
	private $_webinarjam_slug = 'webinarjamstudio';

	/**
	 * TD_Inbox constructor.
	 */
	private function __construct() {

		// Setters
		$this->_set_data();

		// Needs global inclusion for push notifications from TTW trough endpoint
		$this->_load();

		// Load data where needed
		$this->load_by_request_type();

		// Add default notifications
		$this->default_notifications();
	}

	/**
	 * @return TD_Inbox
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Loaders and hooks for admin and ajax
	 */
	public function load_by_request_type() {

		// Includes for admin ajax on allowed actions
		if ( $this->is_request( 'admin_ajax' ) && $this->allowed_action() ) {

			$this->_loadAdmin();
			$this->hooks();
		}

		// Includes for admin with screen restrictions
		if ( $this->is_request( 'admin' ) ) {

			add_action( 'current_screen', array( $this, 'load_on_screen' ) );
		}
	}

	/**
	 * Add default notifications
	 */
	public function default_notifications() {

		$gotowebinar_version    = $this->get_api_version( $this->_gtw_slug );
		$webinarjam_version     = $this->get_api_version( $this->_webinarjam_slug );
		$gtw_on_without_version = $this->connected_api_without_version( $this->_gtw_slug, $gotowebinar_version );
		$gtw_version            = $this->api_version_number( $gotowebinar_version );

		// GoToWebinar v1 default messages:
		if ( $gtw_on_without_version || 1 === $gtw_version ) {
			$this->add_notification( 'gtw_warn_default' );
		}

		// Message for active v1 connections one month before GoToWebinar turn off support
		if ( ( $gtw_on_without_version || 1 === $gtw_version ) && date( 'Y-m-d' ) >= date( '2019-09-01' ) ) {
			$this->add_notification( 'gtw_warn_before' );
		}

		// Message for active v1 connections one week before GoToWebinar turn off support
		if ( ( $gtw_on_without_version || 1 === $gtw_version ) && date( 'Y-m-d' ) >= date( '2019-09-21' ) ) {
			$this->add_notification( 'gtw_warn_last' );
		}

		// Message for active old GTW API connections or v1 connections to show on the API connection list
		if ( $gtw_on_without_version || 1 === $gtw_version ) {
			$this->add_notification( 'gtw_warn_connection' );
		}

		if ( $this->api_is_connected( $this->_ontraport_slug ) ) {
			$this->add_notification( 'ontraport_updated' );
		}

		//Message for V4 for WebinarJam api
		if ( $this->api_is_connected( $this->_webinarjam_slug ) && self::WEBINARJAM_V4 !== (int) $webinarjam_version ) {
			$this->add_notification( 'webinarjamstudio_updated' );
		}

		if ( $this->api_is_connected( 'sendinblue' ) ) {
			$this->add_notification( 'sendinblue_outdated' );
		}

		$this->add_notification( 'zoom_temporary_disabled' );
	}

	/**
	 * Return numeric API version
	 *
	 * @param $api_version
	 *
	 * @return int
	 */
	public function api_version_number( $api_version ) {

		$api_nr = 0;

		if ( ! empty( $api_version['version'] ) ) {
			$api_nr = (int) $api_version['version'];
		}

		return $api_nr;
	}

	/**
	 * Verify for connected API without version set [by slug]
	 *
	 * @param $api_name
	 *
	 * @return bool
	 */
	public function connected_api_without_version( $api_name, $api_version ) {

		if ( empty( $api_name ) || ! is_string( $api_name ) ) {
			return false;
		}

		if ( $this->api_is_connected( $api_name ) && empty( $api_version['version'] ) ) {
			return true;
		}

		return false;
	}


	/**
	 * Add inbox notification using inbox manager
	 *
	 * @param string $type
	 *
	 * @return bool
	 * @throws Exception
	 */
	public function add_notification( $type = '' ) {

		if ( empty( $type ) ) {
			return false;
		}

		$message       = array();
		$inbox_manager = TVE_Dash_InboxManager::instance();

		switch ( $type ) {
			case 'gtw_warn_default':
				$message = array(
					'title' => __( 'Your GoToWebinar Connection will Expire', 'thrive-dash' ),
					'info'  => __(
						'Your GoToWebinar API connection will no longer work after 1st of October, 2019 because GoToWebinar are deactivating their service for this type of connection.<br /><br />  
						 
						You solve this by going to your <a href="' . esc_url( add_query_arg( 'page', 'tve_dash_api_connect', admin_url( 'admin.php' ) ) ) . '" target="_self">API dashboard</a> and re-connecting your existing GoToWebinar account.  After you\'ve connected, your existing forms will carry on working as they are now.<br /><br />
						 
						<a href="https://thrivethemes.com/tkb_item/how-to-upgrade-gotowebinar-and-what-does-this-upgrade-involve/" target="_blank">Click here</a> to learn more about this upgrade and if you have any further questions, get in touch with our <a href="https://thrivethemes.com/forums/forum/general-discussion/" target="_blank">support team</a> and we\'ll help you out.<br /><br />
						 
						From your team at Thrive Themes',
                        'thrive-dash'
					),
					'type'  => TD_Inbox_Message::TYPE_INBOX, // to be shown on API list
				);

				break;
			case 'gtw_warn_before':
				$message = array(
					'title' => __( 'You have one Month to Update your GoToWebinar Connection', 'thrive-dash' ),
					'info'  => __(
						'This is just a quick reminder of the message we sent a few months ago.  You only have one month left to connect through their new API.<br /><br />

						Your existing GoToWebinar API connection will no longer work after 1st of October, 2019 because GoToWebinar are deactivating their service for this type of connection.<br /><br />  
						 
						You solve this by going to your <a href="' . esc_url( add_query_arg( 'page', 'tve_dash_api_connect', admin_url( 'admin.php' ) ) ) . '" target="_self">API dashboard</a> and re-connecting your existing GoToWebinar account.  After you\'ve connected, your existing forms will carry on working as they are now.<br /><br />
						 
						<a href="https://thrivethemes.com/tkb_item/how-to-upgrade-gotowebinar-and-what-does-this-upgrade-involve/" target="_blank">Click here</a> to learn more about this upgrade and if you have any further questions, get in touch with our <a href="https://thrivethemes.com/forums/forum/general-discussion/" target="_blank">support team</a> and we\'ll help you out.<br /><br />
						 
						From your team at Thrive Themes',
                        'thrive-dash'
					),
					'type'  => TD_Inbox_Message::TYPE_INBOX, // to be shown on API list
				);
				break;
			case 'gtw_warn_last':
				$message = array(
					'title' => __( 'Urgent: Your GoToWebinar forms will stop working', 'thrive-dash' ),
					'info'  => __(
						'Just to remind you that your GoToWebinar forms will no longer work if you don’t upgrade to their new API connection.<br /><br />
 
						You solve this by going to your <a href="' . esc_url( add_query_arg( 'page', 'tve_dash_api_connect', admin_url( 'admin.php' ) ) ) . '" target="_self">API dashboard</a> and re-connecting your existing GoToWebinar account.  
						After you\'ve connected, your existing forms will carry on working as they are now.',
                        'thrive-dash'
					),
					'type'  => TD_Inbox_Message::TYPE_INBOX, // to be shown on API list
				);
				break;
			case 'gtw_warn_connection':
				$message = array(
					'title' => __( 'Your GoToWebinar Connection will Expire!', 'thrive-dash' ),
					'info'  => '<span>' . __( 'Important Note:', 'thrive-dash' ) . ' </span>' .
					           __( 'GoToWebinar are no longer supporting this type of connection. You have until October 1st 2019 to connect trough the new api. <a href="https://thrivethemes.com/tkb_item/how-to-upgrade-gotowebinar-and-what-does-this-upgrade-involve/" target="_blank">Learn more about this</a><br /><br />
									<a class="tvd-api-edit tvd-inbox-btn">Connect to new API</a>',
                                   'thrive-dash'
					           ),
					'type'  => TD_Inbox_Message::TYPE_API, // to be shown on API connection list
					'slug'  => 'gotowebinar',
				);
				break;
			case'ontraport_updated':
				$message = array(
					'title' => __( 'Your Ontraport connection has been updated in order for it to work with the latest Ontraport release.', 'thrive-dash' ),
					'info'  => __(
						'We have updated the API connection you have previously made between Ontraport and your Thrive product(s), in order to make sure it works with the latest Ontraport release.<br /><br />
							Due to these changes, now, whenever you connect a "Lead Generation" element to Ontraport, you can choose between sequences and campaigns you have previously added in Ontraport. <br />
							This way, your users will be able to subscribe to these when using the "Lead Generation" element.<br />
							For more information about the Ontraport release, please visit their website <a href="https://ontraport.com/service-status" target="_blank">here</a> .<br/><br/>
							We highly recommend that you sign up through one of your opt-in forms to make sure that everything is working as expected.',
                        'thrive-dash'
					),
					'type'  => TD_Inbox_Message::TYPE_INBOX, // to be shown on API list
				);
				break;
			case'webinarjamstudio_updated':
				$message = array(
					'title' => __( 'Urgent: Your WebinarJam forms will stop working', 'thrive-dash' ),
					'info'  => __(
						'The 3.0 Version of the WebinarJam platform is closing on March 31st. On this date, the systems will be wiped and any replays or information you have will be permanently deleted.<br /><br />
							This is why it is crucial for you to migrate all your content to the 4.0 Version before then. It is critical that you go to your WebinarJam account today and upgrade to the new version. This way, you will give yourself ample time to move your content over. <br /><br />
							After upgrading your WebinarJam account, please make sure to delete and re-add the WebinarJam connection, within your Thrive Dashboard, using the v4 option. Then, you will have to update the connection for all forms on your website that you have previously connected to WebinarJam. <br/>',
                        'thrive-dash'
					),
					'type'  => TD_Inbox_Message::TYPE_INBOX, // to be shown on API list
				);
				break;
			case'zoom_temporary_disabled':
				$message = array(
					'title' => __( 'The Zoom integration was temporarily removed, we are sorry for any inconvenience!', 'thrive-dash' ),
					'info'  => '',
					'type'  => TD_Inbox_Message::TYPE_INBOX, // to be shown on API list
				);
				break;

			case'sendinblue_outdated':
				$message = array(
					'title' => __( 'Starting with 25th June SendinBlue API v2 will be deprecated. In order to switch to v3, please navigate to Thrive Dashboard, API connections and under SendInBlue, click on "I want to use API v3".', 'thrive-dash' ),
					'info'  => '',
					'type'  => TD_Inbox_Message::TYPE_INBOX, // to be shown on API list
				);
				break;
		}

		if ( empty( $message ) ) {
			return false;
		}

		try {
			$message_obj = new TD_Inbox_Message( $message );
			$inbox_manager->prepend( $message_obj );
			$inbox_manager->push_notifications();
		} catch ( Exception $e ) {
		}
	}

	/**
	 * Retreive versioning saved data
	 *
	 * @param string $api_name
	 *
	 * @return array
	 */
	public function get_api_version( $api_name = '' ) {

		if ( empty( $api_name ) ) {
			return array();
		}

		$saved = get_option( 'thrive_mail_list_api', array() );
		$data  = array(
			'version'    => ! empty( $saved[ $api_name ]['version'] ) ? $saved[ $api_name ]['version'] : '',
			'versioning' => ! empty( $saved[ $api_name ]['versioning'] ) ? $saved[ $api_name ]['versioning'] : '',
		);

		return $data;
	}

	/**
	 * Verify if API connection exists
	 *
	 * @param string $api_name
	 *
	 * @return bool
	 */
	public function api_is_connected( $api_name = '' ) {

		if ( empty( $api_name ) ) {
			return false;
		}

		$saved = get_option( 'thrive_mail_list_api', array() );

		if ( empty( $saved[ $api_name ] ) ) {
			return false;
		}

		return true;
	}

	/**
	 * @return bool
	 */
	public function allowed_action() {
		$action = $this->param( 'action', null );

		return in_array( $action, $this->_allowed_routes, true );
	}

	/**
	 * Load dash inbox on the allowed screens
	 */
	public function load_on_screen() {

		// Load on allowed screens
		if ( $this->allowed_on_screen() ) {

			$this->_loadAdmin();
			$this->hooks();
		}
	}

	/**
	 * @param string $file
	 *
	 * @return string
	 */
	public function url( $file = '' ) {
		return untrailingslashit( TVE_DASH_URL ) . '/inc/notification-inbox' . ( ! empty( $file ) ? '/' : '' ) . ltrim( $file, '\\/' );
	}

	/**
	 * @param string $file
	 *
	 * @return string
	 */
	public function path( $file = '' ) {
		return untrailingslashit( plugin_dir_path( __FILE__ ) ) . ( ! empty( $file ) ? '/' : '' ) . ltrim( $file, '\\/' );
	}

	/**
	 * Initialize hooks
	 */
	public function hooks() {

		$this->_register_routes();

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueueScripts' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueueStyles' ) );

		add_action( 'tvd_notification_inbox', array( $this, 'notification_button' ) );
		add_action( 'admin_print_footer_scripts', array( $this, 'admin_backbone_templates' ) );
	}

	/**
	 * Screen restriction
	 *
	 * @return bool
	 */
	public function allowed_on_screen() {
		return in_array( tve_get_current_screen_key(), $this->_allowed_screens, true );
	}

	/**
	 * Backbone templates
	 */
	public function admin_backbone_templates() {

		$templates = tve_dash_get_backbone_templates( $this->path( 'views/templates/backbone' ) );
		tve_dash_output_backbone_templates( $templates );
	}

	/**
	 * Admin ajax
	 */
	public function admin_create_rest_routes() {
		if ( ! current_user_can( TVE_DASH_CAPABILITY ) ) {
			wp_die( '' );
		}
		TD_NI_Ajax_Controller::instance()->handle();
	}

	/**
	 * Inbox template
	 *
	 * @param bool $return
	 *
	 * @return string
	 */
	public function notification_button( $return = false ) {

		$template = dirname( __FILE__ ) . '/views/templates/notification-inbox-button.php';

		ob_start();
		if ( file_exists( $template ) ) {
			include $template;
		}
		$html = ob_get_clean();

		if ( $return ) {
			return $html;
		}

		echo $html; // phpcs:ignore
	}

	/**
	 * Enqueue styles
	 */
	public function enqueueStyles() {

		tve_dash_enqueue_style( 'td-ni-admin', $this->url( 'assets/css/notification-inbox.css' ) );
	}

	/**
	 * Enqueue scripts
	 */
	public function enqueueScripts() {

		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'backbone' );

		$js_prefix = tve_dash_is_debug_on() ? '.js' : '.min.js';

		tve_dash_enqueue_script( 'td-ni-admin', $this->url( 'assets/dist/admin' . $js_prefix ), array(
			'tve-dash-main-js',
			'jquery',
			'backbone',
		), false, true );

		$limit         = 10;
		$offset        = 0;
		$notifications = array_values( TVE_Dash_InboxManager::instance()->get_data( TD_Inbox_Message::TYPE_INBOX ) );

		$total_unread = TVE_Dash_InboxManager::instance()->count_unread( $notifications );
		$total        = count( $notifications );

		if ( $total > $limit ) {
			$notifications = array_slice( $notifications, $offset, $limit );
		}

		$params = array(
			't'            => include $this->path( 'i18n.php' ),
			'ajaxurl'      => admin_url( 'admin-ajax.php' ),
			'dash_url'     => admin_url( 'admin.php?page=tve_dash_section' ),
			'url'          => $this->url(),
			'admin_nonce'  => wp_create_nonce( 'td_ni_admin_ajax_request' ),
			'data'         => $notifications,
			'total'        => $total,
			'total_unread' => $total_unread,
			'limit'        => $limit,
			'offset'       => $offset + $limit,
		);

		wp_localize_script( 'td-ni-admin', 'TD_Inbox', $params );
	}

	/**
	 * @param      $key
	 * @param null $default
	 *
	 * @return null
	 */
	protected function param( $key, $default = null ) {
		return isset( $_POST[ $key ] ) ? map_deep( $_POST[ $key ], 'sanitize_text_field' ) : ( isset( $_REQUEST[ $key ] ) ? map_deep( $_REQUEST[ $key ], 'sanitize_text_field' ) : $default );
	}

	/**
	 * @param $type
	 *
	 * @return bool
	 */
	protected function is_request( $type ) {
		switch ( $type ) {
			case 'admin':
				return is_admin();
			case 'admin_ajax':
				return is_admin() && defined( 'DOING_AJAX' ) && DOING_AJAX;
			case 'ajax':
				return defined( 'DOING_AJAX' );
			case 'cron':
				return defined( 'DOING_CRON' );
			case 'frontend':
				return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' );
		}
	}

	/**
	 * Register admin ajax actions
	 */
	private function _register_routes() {
		if ( is_array( $this->_allowed_routes ) ) {
			foreach ( $this->_allowed_routes as $action ) {
				add_action( 'wp_ajax_' . $action, array( $this, 'admin_create_rest_routes' ) );
			}
		}
	}

	/**
	 * Setters
	 */
	private function _set_data() {

		$this->_init_path  = TVE_DASH_PATH . '/inc/notification-inbox/includes/init/';
		$this->_admin_path = TVE_DASH_PATH . '/inc/notification-inbox/includes/admin/';
		$this->_namespace  = 'notification-inbox/';
		$this->_version    = 1;

		$this->_allowed_screens = array(
			'admin_page_tve_dash_api_connect',
			'toplevel_page_tve_dash_section',
			'thrive-dashboard_page_tve_dash_general_settings_section',
		);

		$this->_allowed_routes = array(
			'thrv_notifications',
			'thrv_bulkread',
			'thrv_load_more',
		);
	}

	/**
	 * Includes
	 *
	 * @param string $path
	 */
	private function _load( $path = '' ) {

		$path = $path ? $path : $this->_init_path;
		$dir  = new DirectoryIterator( $path );

		foreach ( $dir as $file ) {

			if ( $file->isDot() ) {
				continue;
			}

			if ( file_exists( $file->getPathname() ) && $file->isFile() ) {
				require_once( $file->getPathname() );
			}
		}
	}

	/**
	 * Load ajax controller
	 */
	private function _loadAdmin() {
		$this->_load( $this->_admin_path );
	}
}
