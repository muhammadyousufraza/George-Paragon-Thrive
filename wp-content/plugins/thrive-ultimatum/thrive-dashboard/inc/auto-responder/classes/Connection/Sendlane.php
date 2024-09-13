<?php

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Thrive_Dash_List_Connection_Sendlane extends Thrive_Dash_List_Connection_Abstract {
	/**
	 * Return the connection type
	 *
	 * @return String
	 */
	public static function get_type() {
		return 'autoresponder';
	}

	/**
	 * @return string
	 */
	public function get_title() {
		return 'SendLane';
	}

	/**
	 * @return bool
	 */
	public function has_tags() {

		return true;
	}

	/**
	 * output the setup form html
	 *
	 * @return void
	 */
	public function output_setup_form() {
		$this->output_controls_html( 'sendlane' );
	}

	/**
	 * @return mixed|Thrive_Dash_List_Connection_Abstract
	 */
	public function read_credentials() {

		$connection = $this->post( 'connection', array() );

		if ( empty( $connection['api_url'] ) || empty( $connection['api_key'] ) || empty( $connection['hash_key'] ) ) {
			return $this->error( __( 'All fields are required!', 'thrive-dash' ) );
		}

		$this->set_credentials( $connection );
		$result = $this->test_connection();

		if ( $result !== true ) {
			return $this->error( __( 'Could not connect to SendLane using the provided details', 'thrive-dash' ) );
		}

		/**
		 * finally, save the connection details
		 */
		$this->save();

		return $this->success( __( 'SendLane connected successfully', 'thrive-dash' ) );
	}

	/**
	 * @return bool
	 */
	public function test_connection() {

		return is_array( $this->_get_lists() );
	}

	/**
	 * @return mixed|Thrive_Dash_Api_Sendlane
	 * @throws Thrive_Dash_Api_Sendlane_Exception
	 */
	protected function get_api_instance() {
		$api_url  = $this->param( 'api_url' );
		$api_key  = $this->param( 'api_key' );
		$hash_key = $this->param( 'hash_key' );

		return new Thrive_Dash_Api_Sendlane( $api_key, $hash_key, $api_url );
	}

	/**
	 * @return array|bool
	 */
	protected function _get_lists() {
		/** @var Thrive_Dash_Api_Sendlane $api */
		$api    = $this->get_api();
		$result = $api->call( 'lists' );

		$api->setConnectionStatus( $result['status'] );

		/**
		 * Invalid connection
		 */
		if ( ! isset( $result['data'] ) || ! is_array( $result['data'] ) ) {
			return false;
		}

		/**
		 * Valid connection but no lists found
		 */
		if ( isset( $result['data']['info'] ) ) {
			return array();
		}

		/**
		 * Add id and name fields for each list
		 */
		foreach ( $result['data'] as $key => $list ) {
			$result['data'][ $key ]['id']   = $list['list_id'];
			$result['data'][ $key ]['name'] = $list['list_name'];
		}

		return $result['data'];
	}

	/**
	 * @param mixed $list_identifier
	 * @param array $arguments
	 *
	 * @return mixed|string
	 */
	public function add_subscriber( $list_identifier, $arguments ) {
		$name_array = array();
		if ( ! empty( $arguments['name'] ) ) {
			list( $first_name, $last_name ) = $this->get_name_parts( $arguments['name'] );
			$name_array = array(
				'first_name' => $first_name,
				'last_name'  => $last_name,
			);
		}
		/** @var Thrive_Dash_Api_Sendlane $api */
		$api  = $this->get_api();
		$args = array(
			'list_id' => $list_identifier,
			'email'   => $arguments['email'],
		);
		$args = array_merge( $args, $name_array );
		if ( isset( $arguments['sendlane_tags'] ) ) {
			$args['tag_names'] = trim( $arguments['sendlane_tags'] );
		}

		if ( isset( $arguments['phone'] ) ) {
			$args['phone'] = $arguments['phone'];
		}

		return $api->call( 'list-subscriber-add', $args );
	}

	/**
	 * Return the connection email merge tag
	 *
	 * @return String
	 */
	public static function get_email_merge_tag() {
		return 'VAR_EMAIL';
	}

	public function get_automator_add_autoresponder_mapping_fields() {
		return array( 'autoresponder' => array( 'mailing_list', 'api_fields', 'tag_input' ) );
	}
}
