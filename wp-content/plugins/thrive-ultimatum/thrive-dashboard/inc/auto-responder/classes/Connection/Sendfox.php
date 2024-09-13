<?php

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Thrive_Dash_List_Connection_Sendfox extends Thrive_Dash_List_Connection_Abstract {

	/**
	 * Return api connection title
	 *
	 * @return string
	 */
	public function get_title() {

		return 'Sendfox';
	}

	/**
	 * Output the setup form html
	 *
	 * @return void
	 */
	public function output_setup_form() {

		$this->output_controls_html( 'sendfox' );
	}

	/**
	 * read data from post, test connection and save the details
	 *
	 * show error message on failure
	 *
	 * @return mixed|Thrive_Dash_List_Connection_Abstract
	 */
	public function read_credentials() {

		if ( empty( $_POST['connection']['api_key'] ) ) {
			return $this->error( __( 'Api key is required', 'thrive-dash' ) );
		}

		$this->set_credentials( $this->post( 'connection' ) );

		$result = $this->test_connection();

		if ( $result !== true ) {
			return $this->error( __( 'Could not connect to Sendfox using provided api key.', 'thrive-dash' ) );
		}

		/**
		 * finally, save the connection details
		 */
		$this->save();

		return $this->success( __( 'Sendfox connected successfully', 'thrive-dash' ) );
	}

	/**
	 * @return bool|string
	 */
	public function test_connection() {

		return is_array( $this->_get_lists() );
	}

	public function add_subscriber( $list_identifier, $arguments ) {

		try {

			/**
			 * @var $api Thrive_Dash_Api_Sendfox
			 */
			$api = $this->get_api();

			list( $first_name, $last_name ) = $this->get_name_parts( $arguments['name'] );

			$subscriber_args = array(
				'email'      => $arguments['email'],
				'first_name' => $first_name,
				'last_name'  => $last_name,
			);

			$api->add_subscriber( $list_identifier, $subscriber_args );

			return true;
		} catch ( Exception $e ) {
			return false;
		}
	}

	/**
	 * @return mixed|Thrive_Dash_Api_Sendfox
	 * @throws Exception
	 */
	protected function get_api_instance() {
		$api_key = $this->param( 'api_key' );

		return new Thrive_Dash_Api_Sendfox( $api_key );
	}

	/**
	 * @return array|bool
	 */
	protected function _get_lists() {

		$result = array();

		try {

			/**
			 * @var $api Thrive_Dash_Api_Sendfox
			 */
			$api   = $this->get_api();
			$lists = $api->getLists();

			if ( isset( $lists['data'] ) && is_array( $lists['data'] ) ) {
				/* First page of lists */
				$result = $lists['data'];

				/* For multiple pages */
				if ( ! empty( $lists['total'] ) ) {
					$lists_total       = (int) $lists['total'];
					$list_per_page     = (int) $lists['per_page'];
					$pagination_needed = (int) ( $lists_total / $list_per_page ) + 1;

					/* Request pages >=2 and merge lists */
					if ( $pagination_needed >= 2 ) {
						for ( $i = 2; $i <= $pagination_needed; $i ++ ) {
							$response_pages = $api->getListsOnPage( $i );

							if ( isset( $response_pages['data'] ) && is_array( $response_pages['data'] ) ) {
								$result = array_merge( $result, $response_pages['data'] );
							}
						}
					}
				}
			}
		} catch ( Exception $e ) {

		}

		return $result;
	}

}
