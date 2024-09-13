<?php

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Thrive_Dash_List_Connection_Slack extends Thrive_Dash_List_Connection_Abstract {
	const SERVICE_URL = 'https://service-api.thrivethemes.com/integrations/slack';

	/**
	 * Return the connection type
	 *
	 * @return String
	 */
	public static function get_type() {
		return 'collaboration';
	}

	/**
	 * get the authorization URL for the Slack Application
	 *
	 * @return string
	 */
	public function get_authorize_url() {
		$query_params = [
			'redirect_uri' => admin_url( 'admin.php?page=tve_dash_api_connect&api=slack' ),
		];

		$query_params['p'] = $this->calc_hash( $query_params );
		$query_params      = http_build_query(
			$query_params,
			'&'
		);

		return sprintf(
			'%s%s%s',
			static::SERVICE_URL,
			false === strpos( static::SERVICE_URL, '?' ) ? '?' : '&',
			$query_params
		);

	}

	public function calc_hash( $data ) {
		$secret_key = '@#$()%*%$^&*(#@$%@#$%93827456MASDFJIK3245';

		return md5( $secret_key . serialize( $data ) . $secret_key );
	}

	/**
	 * @return bool|void
	 */
	public function is_connected() {
		return ! empty( $this->param( 'token' ) );
	}

	/**
	 * @return string the API connection title
	 */
	public function get_title() {
		return 'Slack';
	}

	/**
	 * output the setup form html
	 *
	 * @return void
	 */
	public function output_setup_form() {
		$this->output_controls_html( 'slack' );
	}

	/**
	 * should handle: read data from post / get, test connection and save the details
	 *
	 * on error, it should register an error message (and redirect?)
	 *
	 * @return mixed
	 */
	public function read_credentials() {

		$slack = $this->get_api();

		try {
			$access_token = $slack->get_access_token();
			$this->set_credentials( array(
				'token' => $access_token,
			) );
		} catch ( Exception $e ) {
			$this->error( $e->getMessage() );

			return false;
		}

		$result = $this->test_connection();

		if ( $result !== true ) {
			$this->error( sprintf( __( 'Could not test Slack connection: %s', 'thrive-dash' ), $result ) );

			return false;
		}

		$this->save();

		return true;
	}

	/**
	 * test if a connection can be made to the service using the stored credentials
	 *
	 * @return bool|string true for success or error message for failure
	 */
	public function test_connection() {
		$slack = $this->get_api();

		try {
			return $slack->verify_token( $this->param( 'token' ) );
		} catch ( Exception $e ) {
			return $e->getMessage();
		}

	}

	/**
	 * instantiate the API code required for this connection
	 *
	 * @return mixed
	 */
	protected function get_api_instance() {
		return new Thrive_Dash_Api_Slack();
	}

	public function _get_lists() {
		return false;
	}

	public function add_subscriber( $list_identifier, $arguments ) {
		return false;
	}


	public function get_channel_list() {
		$slack       = $this->get_api();
		$credentials = $this->get_credentials();
		if ( ! empty( $credentials['token'] ) ) {
			return $slack->get_channel_list( $credentials['token'] );
		}

		return [];
	}

	public function post_message( $channel, $args ) {
		$slack       = $this->get_api();
		$credentials = $this->get_credentials();

		if ( ! empty( $credentials['token'] ) ) {
			return $slack->post_message( $credentials['token'], $channel, $args );
		}

		return [];
	}

	public function custom_success_message() {
		$link = '<a href="http://help.thrivethemes.com/en/articles/6593007-how-to-set-up-a-notification-in-slack-using-thrive-automator">' . __( 'See how this is done', 'thrive-dash' ) . '</a>';

		return __( 'You can now use Slack actions in Thrive Automator', 'thrive-dash' ) .' '. $link;
	}
}
