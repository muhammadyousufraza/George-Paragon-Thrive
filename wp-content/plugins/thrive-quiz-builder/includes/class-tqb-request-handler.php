<?php

/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 10/5/2016
 * Time: 1:27 PM
 */
class TQB_Request_Handler {
	/**
	 * Search the key in request and returns it
	 * If the key does not exists default value is returned
	 *
	 * @param string $key
	 * @param mixed $default
	 *
	 * @return mixed
	 */
	protected function param( $key, $default = null ) {
		if ( isset( $_POST[ $key ] ) ) {
			$value = $_POST[ $key ]; //phpcs:ignore
		} else {
			$value = isset( $_REQUEST[ $key ] ) ? $_REQUEST[ $key ] : $default; //phpcs:ignore
		}

		return map_deep( $value, 'sanitize_text_field' );
	}

	/**
	 * forward the call based on the $action parameter
	 * API entry-point for the template chooser lightbox (from the editor)
	 *
	 * @param string $action
	 */
	public function api( $action ) {
		$method = 'api_' . $action;

		$result = call_user_func( [ $this, $method ] );

		wp_send_json( $result );
	}
}
