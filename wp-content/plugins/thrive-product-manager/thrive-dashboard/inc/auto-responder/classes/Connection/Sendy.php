<?php

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Thrive_Dash_List_Connection_Sendy extends Thrive_Dash_List_Connection_Abstract {
	/**
	 * Return the connection type
	 *
	 * @return String
	 */
	public static function get_type() {
		return 'autoresponder';
	}

	/**
	 * @return string the API connection title
	 */
	public function get_title() {
		return 'Sendy';
	}

	/**
	 * output the setup form html
	 *
	 * @return void
	 */
	public function output_setup_form() {
		$this->output_controls_html( 'sendy' );
	}

	/**
	 * should handle: read data from post / get, test connection and save the details
	 *
	 * on error, it should register an error message (and redirect?)
	 *
	 * @return mixed
	 */
	public function read_credentials() {
		$url = ! empty( $_POST['connection']['url'] ) ? sanitize_text_field( $_POST['connection']['url'] ) : '';

		$lists = ! empty( $_POST['connection']['lists'] ) ? array_map( 'sanitize_text_field', $_POST['connection']['lists'] ) : array();
		$lists = array_map( 'trim', $lists );
		$lists = array_filter( $lists );

		if ( empty( $url ) || empty( $lists ) ) {
			return $this->error( 'Invalid URL or Lists IDs' );
		}

		$_POST['connection']['lists'] = $lists;

		$this->set_credentials( $this->post( 'connection' ) );

		$result = $this->test_connection();

		if ( $result !== true ) {
			return $this->error( __( 'Could not connect to Sendy', 'thrive-dash' ) );
		}

		$this->save();

		return $this->success( 'Sendy connected successfully' );
	}

	/**
	 * test if a connection can be made to the service using the stored credentials
	 *
	 * @return bool|string true for success or error message for failure
	 */
	public function test_connection() {
		/** @var Thrive_Dash_Api_Sendy $api */
		$api = $this->get_api();

		return $api->testUrl();
	}

	/**
	 * instantiate the API code required for this connection
	 *
	 * @return mixed
	 */
	protected function get_api_instance() {
		return new Thrive_Dash_Api_Sendy( $this->param( 'url' ) );
	}

	/**
	 * get all Subscriber Lists from this API service
	 *
	 * @return array|bool for error
	 */
	protected function _get_lists() {
		$lists = array();

		foreach ( $this->param( 'lists' ) as $id ) {
			$lists[] = array(
				'id'   => $id,
				'name' => "#" . $id,
			);
		}

		return $lists;
	}

	/**
	 * add a contact to a list
	 *
	 * @param mixed $list_identifier
	 * @param array $arguments
	 *
	 * @return mixed
	 */
	public function add_subscriber( $list_identifier, $arguments ) {
		/** @var Thrive_Dash_Api_Sendy $api */
		$api = new Thrive_Dash_Api_Sendy( $this->param( 'url' ) );

		try {
			$api->subscribe( $arguments['email'], $list_identifier, $arguments['name'], $phone = isset( $arguments['phone'] ) ? $arguments['phone'] : null );

			return true;
		} catch ( Exception $e ) {
			return $e->getMessage();
		}

		return false;
	}

	/**
	 * output any (possible) extra editor settings for this API
	 *
	 * @param array $params allow various different calls to this method
	 */
	public function get_extra_settings( $params = array() ) {
		return $params;
	}

	/**
	 * output any (possible) extra editor settings for this API
	 *
	 * @param array $params allow various different calls to this method
	 */
	public function render_extra_editor_settings( $params = array() ) {
		$this->output_controls_html( 'sendy/note', $params );
	}

	/**
	 * Return the connection email merge tag
	 *
	 * @return String
	 */
	public static function get_email_merge_tag() {
		return '[Email]';
	}
}
