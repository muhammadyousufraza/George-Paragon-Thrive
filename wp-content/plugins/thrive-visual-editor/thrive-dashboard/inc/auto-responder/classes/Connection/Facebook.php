<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

class Thrive_Dash_List_Connection_Facebook extends Thrive_Dash_List_Connection_Abstract {

	private static $scopes = 'email,public_profile';

	protected $_key = 'facebook';

	public $success_message = 'You are cool!';

	/**
	 * Thrive_Dash_List_Connection_Facebook constructor.
	 */
	public function __construct() {
		$this->set_credentials( Thrive_Dash_List_Manager::credentials( $this->_key ) );
	}

	/**
	 * Return the connection type
	 *
	 * @return String
	 */
	public static function get_type() {
		return 'social';
	}

	/**
	 * @return string the API connection title
	 */
	public function get_title() {
		return 'Facebook';
	}

	/**
	 * output the setup form html
	 *
	 * @return void
	 */
	public function output_setup_form() {
		$this->output_controls_html( 'facebook' );
	}

	/**
	 * should handle: read data from post / get, test connection and save the details
	 *
	 * on error, it should register an error message (and redirect?)
	 *
	 * @return mixed
	 */
	public function read_credentials() {
		$app_id     = ! empty( $_REQUEST['app_id'] ) ? sanitize_text_field( $_REQUEST['app_id'] ) : '';
		$app_secret = ! empty( $_REQUEST['app_secret'] ) ? sanitize_text_field( $_REQUEST['app_secret'] ) : '';

		if ( empty( $app_id ) || empty( $app_secret ) ) {
			return $this->error( __( 'Both Client ID and Client Secret fields are required', 'thrive-dash' ) );
		}

		$this->set_credentials( array(
			'app_id'     => $app_id,
			'app_secret' => $app_secret,
		) );

		/* app has been authorized */
		if ( isset( $_REQUEST['code'] ) ) {

			$this->get_api()->getUser();

			$this->save();

			return true;
		}

		$result = $this->test_connection();

		if ( $result !== true ) {
			return $this->error( __( 'You must give access to Facebook <a target="_blank" href="' . $this->getAuthorizeUrl() . '">here</a>.', 'thrive-dash' ) );
		}

		/**
		 * finally, save the connection details
		 */
		$this->save();

		return $this->success( __( 'Facebook connected successfully!', 'thrive-dash' ) );
	}

	/**
	 * test if the secret key is correct and it exists.
	 *
	 * @return bool|string true for success or error message for failure
	 */
	public function test_connection() {
		/** @var Thrive_Dash_Api_Facebook $api */
		$api = $this->get_api();

		$user = $api->getUser();

		$ready = false;

		if ( $user ) {
			try {
				$info = $api->api( '/me' );

				if ( is_array( $info ) ) {
					$ready = true;
				}
			} catch ( Tve_Facebook_Api_Exception $e ) {
				$ready = array(
					'success' => false,
					'message' => __( 'You must give access to Facebook <a target="_blank" href="' . $this->getAuthorizeUrl() . '">here</a>.', 'thrive-dash' ),
				);
			}
		} else {

			$ready = array(
				'success' => false,
				'message' => __( 'You must give access to Facebook <a target="_blank" href="' . $this->getAuthorizeUrl() . '">here</a>.', 'thrive-dash' ),
			);
		}

		return $ready;
	}

	/**
	 * @return string
	 */
	public function getAuthorizeUrl() {

		/** @var Thrive_Dash_Api_Facebook $api */
		$api = $this->get_api();

		return $api->getLoginUrl( array(
			'scope'        => self::$scopes,
			'redirect_uri' => add_query_arg( array(
				'page'       => 'tve_dash_api_connect',
				'api'        => 'facebook',
				'app_id'     => $this->param( 'app_id' ),
				'app_secret' => $this->param( 'app_secret' ),
			), admin_url( 'admin.php' ) ),
		) );
	}

	/**
	 * Those functions do not apply
	 *
	 * @return Thrive_Dash_Api_Facebook
	 */
	protected function get_api_instance() {

		$params = array(
			'appId'  => $this->param( 'app_id' ),
			'secret' => $this->param( 'app_secret' ),
		);

		return new Thrive_Dash_Api_Facebook( $params );
	}

	/**
	 * @param $fbid
	 * @param $comment_id
	 *
	 * @return array|string|void
	 */
	public function get_comment( $fbid, $comment_id ) {

		/** @var Thrive_Dash_Api_Facebook $api */
		$api = $this->get_api();

		$comment = array();

		$user = $api->getUser();
		if ( $user ) {
			try {
				$response = $api->api( '/' . $fbid . '_' . $comment_id );

				if ( is_array( $response ) ) {
					$comment = array(
						'id'      => $response['from']['id'],
						'name'    => $response['from']['name'],
						'picture' => 'https://graph.facebook.com/' . $response['from']['id'] . '/picture?type=large',
						'message' => $response['message'],
					);
				}
			} catch ( Tve_Facebook_Api_Exception $e ) {
				$comment = __( 'Error! The Facebook link provided is invalid', 'thrive-dash' );
			}
		} else {
			$comment = __( 'Your Facebook connection expired. Go to API Connections to reactivate it!', 'thrive-dash' );
		}

		return $comment;

	}

	/**
	 * @return string
	 */
	public function custom_success_message() {
		return ' ';
	}

	protected function _get_lists() {
	}

	public function add_subscriber( $list_identifier, $arguments ) {
	}
}
