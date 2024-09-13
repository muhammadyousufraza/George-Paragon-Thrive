<?php

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Thrive_Dash_List_Connection_FileUpload_Dropbox
	extends Thrive_Dash_List_Connection_Abstract
	implements Thrive_Dash_List_Connection_FileUpload_Interface {

	public static function get_type() {
		return 'storage';
	}

	public function get_title() {
		return 'Dropbox';
	}

	/**
	 * whether or not this list is connected to the service (has been authenticated)
	 *
	 * @return bool
	 */
	public function is_connected() {
		return (bool) $this->param( 'access_token' );
	}

	public function output_setup_form() {
		$this->output_controls_html( 'dropbox' );
	}

	/**
	 * Builds an authorization URI - the user will be redirected to that URI and asked to give app access
	 *
	 * @return string
	 */
	public function getAuthorizeUrl() {
		$this->save(); // save the client_id and client_secret for later use

		return $this->get_api()->get_authorize_url();
	}

	/**
	 * Called during the redirect from dropbox oauth flow
	 *
	 * _REQUEST contains a `code` parameter which needs to be sent back to g.api in exchange for an access token
	 *
	 * @return bool|mixed|string|Thrive_Dash_List_Connection_Abstract
	 */
	public function read_credentials() {
		$code = empty( $_REQUEST['code'] ) ? '' : sanitize_text_field( $_REQUEST['code'] );

		if ( empty( $code ) ) {
			return $this->error( 'Missing `code` parameter' );
		}

		try {
			/* get access token from dropbox */
			$response = $this->get_api()->get_access_token( $code );
			if ( empty( $response['access_token'] ) ) {
				throw new Thrive_Dash_Api_Dropbox_Exception( 'Missing token from response data' );
			}
			$this->_credentials = array(
				'client_id'     => $this->param( 'client_id' ),
				'client_secret' => $this->param( 'client_secret' ),
				'access_token'  => $response['access_token'],
				'expires_at'    => time() + $response['expires_in'],
				'refresh_token' => $response['refresh_token'],
			);
			$this->save();
		} catch ( Thrive_Dash_Api_Dropbox_Exception $e ) {

			$this->_credentials = array();
			$this->save();

			$this->error( $e->getMessage() );

			return false;
		}

		return true;
	}

	public function test_connection() {

		$result = array(
			'success' => true,
			'message' => __( 'Connection works', 'thrive-dash' ),
		);
		try {
			/**
			 * Just trigger a "check user" API call
			 * https://www.dropbox.com/developers/documentation/http/documentation#check-user
			 */
			$this->get_api()->check_user();
		} catch ( Thrive_Dash_Api_Dropbox_Exception $e ) {
			$result['success'] = false;
			$result['message'] = $e->getMessage();
		}

		return $result;
	}

	/**
	 * Upload a file to the storage
	 *
	 * @param string $file_contents contents of the uploaded file
	 * @param string $folder_id     folder identification
	 * @param array  $metadata      file metadata props, such as name
	 *
	 * @return string|WP_Error stored file id or WP_Error if any exceptions occured
	 */
	public function upload( $file_contents, $folder_id, $metadata ) {

		$dropbox_request = array(
			'path'       => trim( $folder_id, '/' ) . '/' . $metadata['name'],
			'autorename' => false,
		);

		try {
			$file = $this->get_api()->upload( $file_contents, $dropbox_request );
		} catch ( Thrive_Dash_Api_Dropbox_Exception $e ) {
			if ( $folder_id && strpos( $e->getMessage(), 'path/no_write_permission' ) !== false ) {
				/* try again, uploading to the root folder of the app */
				return $this->upload( $file_contents, '', $metadata );
			}

			return new WP_Error( 'tcb_file_upload_error', $e->getMessage() );
		}

		return $file['id'];
	}

	/**
	 * Rename an uploaded file by applying a callback function on its name
	 * The callback function should return the new filename
	 *
	 * @param string   $file_id  file ID from dropbox
	 * @param callable $callback function to apply to get the new filename
	 *
	 * @return array information about the renamed file
	 */
	public function rename_file( $file_id, $callback ) {
		$file = $this->get_file_data( $file_id );

		if ( ! is_callable( $callback ) || empty( $file['path'] ) ) {
			return $file;
		}

		try {
			$new_name = $callback( $file['name'] );
			$new_path = rtrim( dirname( $file['path'] ), '/' ) . '/' . $new_name;

			if ( $new_name !== $file['name'] ) {
				$this->get_api()->move_file( $file['path'], $new_path );
				$file['url']  = dirname( $file['url'] ) . '/' . $callback( basename( $file['url'] ) );
				$file['path'] = $new_path;
				$file['name'] = $new_name;
			}

		} catch ( Thrive_Dash_Api_Dropbox_Exception $e ) {
		}

		return $file;
	}

	/**
	 * Deletes an uploaded file
	 *
	 * @param string $file_id
	 *
	 * @return true|WP_Error
	 */
	public function delete( $file_id ) {
		try {
			$this->get_api()->delete( $file_id );
			$result = true;
		} catch ( Thrive_Dash_Api_Dropbox_Exception $e ) {
			$result = new WP_Error( 'tcb_file_upload_error', $e->getMessage() );
		}

		return $result;
	}

	/**
	 * Retrieve the full URL to a file stored on drive
	 *
	 * @param string $file_id
	 *
	 * @return array containing URL and original name
	 */
	public function get_file_data( $file_id ) {

		// fallback to a default home url (??)
		$data = array(
			'url'  => 'https://www.dropbox.com/home',
			'name' => $file_id,
		);

		try {
			$file         = $this->get_api()->get_file( $file_id );
			$data['url']  = $file['url'];
			$data['name'] = $file['name'];
			$data['path'] = $file['path_display'];
		} catch ( Thrive_Dash_Api_Dropbox_Exception $e ) {
            $data['error'] = $e->getMessage();
		}

		return $data;
	}

	public function add_subscriber( $list_identifier, $arguments ) {
	}

	/**
	 * Instantiate the service and set any available data
	 *
	 * @return Thrive_Dash_Api_Dropbox_Service
	 * @throws Thrive_Dash_Api_Dropbox_Exception
	 */
	protected function get_api_instance() {
		$api = new Thrive_Dash_Api_Dropbox_Service(
			$this->param( 'client_id' ),
			$this->param( 'client_secret' ),
			$this->param( 'access_token' )
		);

		/* check for expired token and renew it */
		if ( $this->param( 'refresh_token' ) && $this->param( 'expires_at' ) && time() > (int) $this->param( 'expires_at' ) ) {
			$data                               = $api->refresh_access_token( $this->param( 'refresh_token' ) );
			$this->_credentials['access_token'] = $data['access_token'];
			$this->_credentials['expires_at']   = time() + $data['expires_in'];
			$this->save();
		}

		return $api;
	}

	protected function _get_lists() {
	}
}
