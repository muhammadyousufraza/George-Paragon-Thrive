<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Thrive_Dash_List_Connection_FacebookPixel extends Thrive_Dash_List_Connection_Abstract {

	public function get_title() {
		return 'Facebook (Meta) Marketing API';
	}

	public static function get_type() {
		return 'collaboration';
	}

	public function output_setup_form() {
		$this->output_controls_html( 'facebookpixel' );
	}

	public function read_credentials() {
		$access_token = ! empty( $_REQUEST['connection']['access_token'] ) ? sanitize_text_field( $_REQUEST['connection']['access_token'] ) : '';
		$pixel_id     = ! empty( $_REQUEST['connection']['pixel_id'] ) ? sanitize_text_field( $_REQUEST['connection']['pixel_id'] ) : '';

		if ( empty( $access_token ) || empty( $pixel_id ) ) {
			return $this->error( __( 'Both Pixel ID and Access token fields are required', 'thrive-dash' ) );
		}

		$this->set_credentials( array(
			'pixel_id'     => $pixel_id,
			'access_token' => $access_token,
		) );

		$result = $this->test_connection();
		if ( $result['success'] !== true ) {
			return empty( $result['message'] ) ? $this->error( __( 'Incorrect Pixel ID or Access token, please try again.', 'thrive-dash' ) ) : $result['message'];
		}

		$this->save();

		return $this->success( __( 'Facebook Pixel connected successfully', 'thrive-dash' ) );
	}

	public function test_connection() {
		return $this->get_api()->send_test_event();
	}

	/**
	 * No need to implement this method
	 *
	 * @param $list_identifier
	 * @param $arguments
	 *
	 * @return mixed|void
	 */
	public function add_subscriber( $list_identifier, $arguments ) {

	}

	public function custom_success_message() {
		return ' ';
	}

	/**
	 * Those functions do not apply
	 *
	 * @return Thrive_Dash_Api_FacebookPixel
	 */
	protected function get_api_instance() {

		$params = array(
			'pixel_id'     => $this->param( 'pixel_id' ),
			'access_token' => $this->param( 'access_token' ),
		);

		return new Thrive_Dash_Api_FacebookPixel( $params );
	}

	/**
	 * No need to implement this method
	 */
	protected function _get_lists() {

	}
}
