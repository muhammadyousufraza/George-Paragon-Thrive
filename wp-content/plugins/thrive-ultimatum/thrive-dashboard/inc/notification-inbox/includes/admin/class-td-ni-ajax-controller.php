<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

class TD_NI_Ajax_Controller {

	/**
	 * @var TD_NI_Ajax_Controller
	 */
	protected static $_instance;

	/**
	 * @var int
	 */
	public $version = 1;

	/**
	 * @var string
	 */
	public $namespace = 'notification-inbox/v';

	/**
	 * TD_NI_Ajax_Controller constructor.
	 */
	private function __construct() {
	}

	/**
	 * @return TD_NI_Ajax_Controller
	 */
	public static function instance() {
		if ( empty( static::$_instance ) ) {
			static::$_instance = new static();
		}

		return static::$_instance;
	}

	/**
	 * @return mixed
	 */
	public function handle() {

		if ( ! check_ajax_referer( 'td_ni_admin_ajax_request', '_nonce', false ) ) {
			$this->error( sprintf( __( 'Invalid request', 'thrive-dash' ) ) );
		}

		$route       = $this->param( 'action' );
		$route       = preg_replace( '#([^a-zA-Z0-9-])#', '', $route );
		$method_name = $route . '_action';

		if ( ! method_exists( $this, $method_name ) ) {
			$this->error( sprintf( __( 'Method %s not implemented', 'thrive-dash' ), $method_name ) );
		}

		$model = json_decode( file_get_contents( 'php://input' ), true );

		wp_send_json( $this->{$method_name}( $model ) );
	}

	/**
	 * Handle ajax route for read inbox message read
	 *
	 * @return false|mixed|string|void
	 */
	public function thrvnotifications_action() {

		$this->_verify_nonce();

		if ( empty( $_REQUEST['notification_id'] ) ) {
			$this->error( __( 'Missing parameter [notification_id] in ajax request', 'thrive-dash' ) );
		}

		try {
			TVE_Dash_InboxManager::instance()->set_read( $this->param( 'notification_id' ) );

			$return = array( 'total_unread' => TVE_Dash_InboxManager::instance()->count_unread() );

			return json_encode( $return );
		} catch ( Exception $e ) {
			$this->error( $e->getMessage() );
		}

		return $this->error( __( 'An error ocurred on updating notification', 'thrive-dash' ) );
	}

	/**
	 * Buld read action
	 *
	 * @return bool
	 */
	public function thrvbulkread_action() {

		$this->_verify_nonce();

		TVE_Dash_InboxManager::instance()->bulk_read();
		$response = array( 'total_unread' => TVE_Dash_InboxManager::instance()->count_unread() );

		return json_encode( $response );
	}

	/**
	 * @return bool
	 */
	public function thrvloadmore_action() {

		$this->_verify_nonce();

		$offset = $this->param( 'offset' );
		$limit  = $this->param( 'limit' );

		return array_values( TVE_Dash_InboxManager::instance()->load_more( $offset, $limit ) );
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
	 * @param        $message
	 * @param string $status
	 */
	protected function error( $message, $status = '404 Not Found' ) {
		status_header( 400 );
		wp_send_json( array(
			'error' => $message,
		) );
		
		return $message;
	}

	/**
	 * Verify nonce
	 */
	private function _verify_nonce() {

		if ( ! check_ajax_referer( 'td_ni_admin_ajax_request', '_nonce', false ) ) {
			$this->error( sprintf( __( 'Invalid request', 'thrive-dash' ) ) );
		}
	}
}
