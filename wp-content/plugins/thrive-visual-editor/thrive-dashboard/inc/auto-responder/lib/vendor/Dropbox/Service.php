<?php

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}


class Thrive_Dash_Api_Dropbox_Service {

	protected $client_id;

	protected $client_secret;

	protected $access_type = 'offline';

	protected $access_token = '';

	const AUTH_URI  = 'https://www.dropbox.com/oauth2/authorize';
	const TOKEN_URI = 'https://api.dropboxapi.com/oauth2/token';

	const BASE_API_URI    = 'https://api.dropboxapi.com/';
	const CONTENT_API_URI = 'https://content.dropboxapi.com/';

	public function __construct( $client_id, $client_secret, $access_token ) {
		$this->client_id     = $client_id;
		$this->client_secret = $client_secret;
		$this->access_token  = $access_token;
	}

	/**
	 * https://www.dropbox.com/developers/documentation/http/documentation#authorization
	 *
	 * @return string
	 */
	public function get_authorize_url() {
		return add_query_arg(
			array(
				'token_access_type' => $this->access_type,
				'response_type'     => 'code',
				'redirect_uri'      => rawurlencode( $this->get_redirect_uri() ),
				'client_id'         => $this->client_id,
				'scope'             => urlencode( 'sharing.write files.content.write files.metadata.write account_info.read' ),
			),
			static::AUTH_URI
		);
	}

	/**
	 * @param array $args
	 *
	 * @return array
	 * @throws Thrive_Dash_Api_Dropbox_Exception
	 */
	protected function _request_token( $args ) {
		$data = wp_parse_args( $args, array(
			'client_id'     => $this->client_id,
			'client_secret' => $this->client_secret,
		) );

		return $this->parse_response( tve_dash_api_remote_post( static::TOKEN_URI, array(
			'body' => $data,
		) ) );
	}

	/**
	 * https://www.dropbox.com/developers/documentation/http/documentation#authorization
	 *
	 * @param string $code
	 *
	 * @return mixed
	 * @throws Thrive_Dash_Api_Dropbox_Exception
	 */
	public function get_access_token( $code ) {
		$post_data = array(
			'code'         => $code,
			'grant_type'   => 'authorization_code',
			'redirect_uri' => $this->get_redirect_uri(),
		);

		return $this->_request_token( $post_data );
	}

	/**
	 * Refresh access token
	 *
	 *
	 * @param string $refresh_token
	 *
	 * @return array
	 * @throws Thrive_Dash_Api_Dropbox_Exception
	 */
	public function refresh_access_token( $refresh_token ) {
		$post_data = array(
			'refresh_token' => $refresh_token,
			'grant_type'    => 'refresh_token',
		);

		$data = $this->_request_token( $post_data );

		/* store the new access token in the instance */
		$this->access_token = $data['access_token'];

		return $data;
	}

	/**
	 * @param $response
	 *
	 * @return array
	 * @throws Thrive_Dash_Api_Dropbox_Exception
	 */
	protected function parse_response( $response ) {
		$response = wp_remote_retrieve_body( $response );
		$json     = json_decode( $response, true );

		if ( empty( $json ) ) {
			throw new Thrive_Dash_Api_Dropbox_Exception( 'Dropbox API Error: ' . $response );
		}

		if ( ! empty( $json['error_summary'] ) ) {
			throw new Thrive_Dash_Api_Dropbox_Exception( 'Dropbox API Error: ' . $json['error_summary'] );
		}

		if ( ! empty( $json['error_description'] ) ) {
			throw new Thrive_Dash_Api_Dropbox_Exception( 'Dropbox API Error: ' . $json['error_description'] );
		}

		return $json;
	}

	public function get_redirect_uri() {
		return admin_url( 'admin.php?page=tve_dash_api_connect&api=dropbox' );
	}

	/**
	 * @param string              $uri
	 * @param array|string        $data
	 * @param array               $headers
	 * @param bool                $auth
	 * @param array|string|object $args
	 *
	 * @return array
	 * @throws Thrive_Dash_Api_Dropbox_Exception
	 */
	public function post( $uri, $data, $headers = array(), $auth = true, $args = array() ) {
		$args     = wp_parse_args( $args, array(
			'body'    => is_array( $data ) ? json_encode( $data ) : $data,
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
	 * Get a list of folders
	 *
	 * @param array $params
	 *
	 * @return array
	 * @throws Thrive_Dash_Api_Dropbox_Exception
	 */
	public function check_user( $params = array( 'query' => 'EchoMessage' ) ) {
		return $this->post(
			static::BASE_API_URI . '2/check/user',
			$params,
			array(
				'Content-type' => 'application/json',
			)
		);
	}

	/**
	 * Perform a multipart file upload
	 *
	 * @param string $contents file contents
	 * @param array  $meta     metadata about the file (name, originalName etc), see https://developers.google.com/drive/api/v3/reference/files/create#request-body
	 *
	 * @return array
	 * @throws Thrive_Dash_Api_Dropbox_Exception
	 */
	public function upload( $contents, $meta = array() ) {
		return $this->post( static::CONTENT_API_URI . '2/files/upload', $contents, array(
			'Content-Type'    => 'application/octet-stream',
			'Dropbox-API-Arg' => json_encode( $meta ),
		) );
	}

	/**
	 * Delete a previously stored file
	 *
	 * @param string $file_id
	 *
	 * @return array
	 * @throws Thrive_Dash_Api_Dropbox_Exception
	 */
	public function delete( $file_id ) {
		return $this->post( static::BASE_API_URI . '2/files/delete_v2', array(
			'path' => $file_id,
		), array(
			'Content-Type' => 'application/json',
		) );
	}

	/**
	 * Get file data
	 *
	 * @param string $file_id
	 *
	 * @return array
	 * @throws Thrive_Dash_Api_Dropbox_Exception
	 */
	public function get_file( $file_id ) {
		$data = $this->post( static::BASE_API_URI . '2/files/get_metadata', array(
			'path'                                => $file_id,
			'include_media_info'                  => false,
			'include_deleted'                     => false,
			'include_has_explicit_shared_members' => false,
		), array(
			'Content-Type' => 'application/json',
		) );

		/* create a sharing link to the file */
		$sharing     = $this->post( static::BASE_API_URI . '2/sharing/create_shared_link_with_settings', array(
			'path'     => $file_id,
			'settings' => array(
				'requested_visibility' => 'public',
				'audience'             => 'public',
				'access'               => 'viewer',
			),
		), array(
			'Content-Type' => 'application/json',
		) );
		$data['url'] = $sharing['url'];

		return $data;
	}

	/**
	 * Rename or move a file
	 *
	 * @param string $from_path
	 * @param string $to_path
	 *
	 * @return array file data
	 * @throws Thrive_Dash_Api_Dropbox_Exception
	 */
	public function move_file( $from_path, $to_path ) {
		$response = $this->post(
			static::BASE_API_URI . '2/files/move_v2',
			array(
				'from_path' => $from_path,
				'to_path'   => $to_path,
			),
			array(
				'Content-Type' => 'application/json',
			)
		);

		if ( ! isset( $response['metadata'] ) ) {
			throw new Thrive_Dash_Api_Dropbox_Exception( 'File move: missing metadata from response' );
		}

		return $response['metadata'];
	}
}
