<?php

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}


class Thrive_Dash_Api_Google_Service {

	protected $client_id;

	protected $client_secret;

	protected $access_type = 'offline';

	protected $access_token = '';

	const AUTH_URI  = 'https://accounts.google.com/o/oauth2/auth';
	const TOKEN_URI = 'https://oauth2.googleapis.com/token';

	const BASE_URI = 'https://www.googleapis.com/';

	public function __construct( $client_id, $client_secret, $access_token ) {
		$this->client_id     = $client_id;
		$this->client_secret = $client_secret;
		$this->access_token  = $access_token;
	}

	public function get_authorize_url( $scopes = array( 'drive.file' ) ) {
		return add_query_arg(
			array(
				'scope'                  => $this->prepare_scopes( $scopes ),
				'state'                  => 'connection_google_drive',
				'access_type'            => $this->access_type,
				'include_granted_scopes' => 'true',
				'response_type'          => 'code',
				'redirect_uri'           => $this->get_redirect_uri(),
				'client_id'              => $this->client_id,
				/* always send `consent` in the prompt parameter in order to always get back a refresh_token */
				'prompt'                 => 'consent',
			),
			static::AUTH_URI
		);
	}

	/**
	 * https://developers.google.com/identity/protocols/oauth2/web-server#exchange-authorization-code
	 *
	 * @param string $code
	 *
	 * @return mixed
	 */
	public function get_access_token( $code ) {
		return $this->post( static::TOKEN_URI, array(
			'client_id'     => $this->client_id,
			'client_secret' => $this->client_secret,
			'code'          => $code,
			'grant_type'    => 'authorization_code',
			'redirect_uri'  => $this->get_redirect_uri(),
		), array(), false );
	}

	/**
	 * Refresh access token
	 *
	 * https://developers.google.com/identity/protocols/oauth2/web-server#offline
	 *
	 * @param string $refresh_token
	 *
	 * @return array
	 */
	public function refresh_access_token( $refresh_token ) {
		$data = $this->post( static::TOKEN_URI, array(
			'client_id'     => $this->client_id,
			'client_secret' => $this->client_secret,
			'refresh_token' => $refresh_token,
			'grant_type'    => 'refresh_token',
		), array(), false );
		/* store the new access token in the instance */
		$this->access_token = $data['access_token'];

		return $data;
	}

	protected function prepare_scopes( $scopes ) {
		if ( ! is_array( $scopes ) ) {
			$scopes = array( $scopes );
		}

		foreach ( $scopes as & $scope ) {
			if ( strpos( $scope, 'https://www.googleapis' ) === false ) {
				$scope = 'https://www.googleapis.com/auth/' . $scope;
			}
		}
		unset( $scope );

		return implode( ' ', $scopes );
	}

	/**
	 * @param $response
	 *
	 * @return array
	 * @throws Thrive_Dash_Api_Google_Exception
	 */
	protected function parse_response( $response ) {

		$response = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( empty( $response ) || ! empty( $response['error'] ) ) {
			$this->throw_error( $response );
		}

		return $response;
	}

	/**
	 * @param $response
	 *
	 * @return string
	 *
	 * @throws Thrive_Dash_Api_Google_Exception
	 */
	protected function throw_error( $response ) {
		if ( ! isset( $response['error'] ) ) {
			$message = 'Unknownerror. Raw response was: ' . print_r( $response, true );
		} elseif ( is_string( $response['error'] ) ) {
			$description = isset( $response['error_description'] ) ? ' (' . $response['error_description'] . ')' : '';
			$message     = $response['error'] . $description;
		} elseif ( is_array( $response['error'] ) ) {
			$message = isset( $response['error']['message'] ) ? $response['error']['message'] : '';
		} else {
			$message = 'Unknown error. Raw response was: ' . print_r( $response, true );
		}

		throw new Thrive_Dash_Api_Google_Exception( 'Google API Error: ' . $message );
	}

	public function get_redirect_uri() {
		return admin_url( 'admin.php?page=tve_dash_api_connect' );
	}

	public function get( $uri, $query = array(), $auth = true ) {
		$params = array(
			'body'    => $query,
			'headers' => $auth ? $this->auth_headers() : array(),
		);

		return $this->parse_response( tve_dash_api_remote_get( $uri, $params ) );
	}

	public function post( $uri, $data, $headers = array(), $auth = true, $args = array() ) {
		$args     = wp_parse_args( $args, array(
			'body'    => $data,
			'headers' => $headers + ( $auth ? $this->auth_headers() : array() ),
		) );
		$response = tve_dash_api_remote_post( $uri, $args );

		return $this->parse_response( $response );
	}

	protected function auth_headers() {
		return array(
			'Authorization' => 'Bearer ' . $this->access_token,
		);
	}

	/** API calls */
	/**
	 * https://developers.google.com/drive/api/v3/reference/files/list
	 *
	 * @param array $params
	 *
	 * @return array
	 */
	public function get_files( $params = array() ) {
		return $this->get( static::BASE_URI . 'drive/v3/files', $params );
	}

	/**
	 * Perform a multipart file upload
	 *
	 * @param string $contents file contents
	 * @param array  $meta     metadata about the file (name, originalName etc), see https://developers.google.com/drive/api/v3/reference/files/create#request-body
	 *
	 * @return array
	 */
	public function multipart_upload( $contents, $meta = array() ) {
		$mime_type = 'application/octet-stream';
		// This is a multipart/related upload.
		$boundary     = uniqid( 'g-file-upload', true );
		$content_type = 'multipart/related; boundary=' . $boundary;
		$body         = "--$boundary\r\n" .
		                "Content-Type: application/json; charset=UTF-8\r\n" .
		                "\r\n" . json_encode( $meta ) . "\r\n" .
		                "--$boundary\r\n" .
		                "Content-Type: $mime_type\r\n" .
		                "Content-Transfer-Encoding: base64\r\n" .
		                "\r\n" . base64_encode( $contents ) . "\r\n" .
		                "--$boundary--";

		return $this->post( static::BASE_URI . 'upload/drive/v3/files?uploadType=multipart', $body, array(
			'Content-Type'   => $content_type,
			'Content-Length' => strlen( $body ),
		) );
	}

	/**
	 * Modify a stored file's metadata
	 *
	 * @param string $file_id  stored file ID
	 * @param array  $metadata data to save
	 *
	 * @return array
	 */
	public function update_file_metadata( $file_id, $metadata ) {
		return $this->post( static::BASE_URI . 'drive/v3/files/' . $file_id, json_encode( $metadata ), array(
			'Content-type' => 'application/json',
		), true, array(
			'method' => 'PATCH',
		) );
	}

	/**
	 * Delete a previously stored file
	 *
	 * @param string $file_id
	 *
	 * @return array
	 */
	public function delete( $file_id ) {
		return $this->post( static::BASE_URI . 'drive/v3/files/' . $file_id, '', array(), true, array(
			'method' => 'DELETE',
		) );
	}

	/**
	 * Get file data
	 *
	 * @param string $file_id
	 * @param string $fields
	 *
	 * @return array
	 */
	public function get_file( $file_id, $fields = 'webViewLink' ) {
		return $this->get( static::BASE_URI . 'drive/v3/files/' . $file_id . '?fields=' . $fields );
	}
}
