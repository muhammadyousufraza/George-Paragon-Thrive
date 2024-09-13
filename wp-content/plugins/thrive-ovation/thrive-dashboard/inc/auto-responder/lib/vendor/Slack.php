<?php

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Thrive_Dash_Api_Slack {

	public function __construct() {

	}

	public function verify_token( $token = false ) {
		try {
			$request = $this->make_request( 'https://slack.com/api/auth.test', array( 'token' => $token ), 'post' );
		} catch ( Exception $e ) {
			return $e->getMessage();
		}

		return ! empty( $request->ok );
	}

	public function get_channel_list( $token = false ) {
		try {
			$request = $this->make_request( 'https://slack.com/api/conversations.list',
				array(
					'token'            => $token,
					'exclude_archived' => true,
					'limit'            => 500,
				), 'get' );
		} catch ( Exception $e ) {
			return $e->getMessage();
		}

		return $request->channels;
	}

	public function post_message( $token, $channel, $args ) {

		$blocks = $this->create_blocks( $args );

		$params = array(
			'token'   => $token,
			'channel' => $channel,
			'blocks'  => json_encode( $blocks ),
		);
		if ( ! empty( $args['text'] ) ) {
			$params['text'] = $args['text'];
		}
		try {
			$request = $this->make_request( 'https://slack.com/api/chat.postMessage', $params, 'post' );
		} catch ( Exception $e ) {
			return $e->getMessage();
		}

		return $request;
	}

	public function create_blocks( $args ) {
		$blocks = array();
		if ( ! empty( $args['text'] ) ) {
			$blocks[] = array(
				'type' => 'section',
				'text' => array(
					'type' => 'mrkdwn',
					'text' => stripslashes( $args['text'] ),
				),
			);
		}

		if ( ! empty( $args['fields'] ) && is_array( $args['fields'] ) ) {
			$fields = array();
			foreach ( $args['fields'] as $field ) {
				if ( ! empty( $field['key'] ) ) {
					$fields[] = array(
						'type' => 'mrkdwn',
						'text' => $field['key'] . ': ' . stripslashes( $field['value'] ),
					);
				}

			}
			if ( ! empty( $fields ) ) {
				$blocks[] = array(
					'type'   => 'section',
					'fields' => $fields
				);
			}
		}

		if ( ! empty( $args['button'] ) ) {
			$blocks[] = array(
				'type'     => 'actions',
				'elements' => [
					array(
						'type' => 'button',
						'text' => array(
							'type' => 'plain_text',
							'text' => stripslashes( $args['button']['text'] ),
						),
						'url'  => $args['button']['value']
					)
				]
			);
		}


		return $blocks;
	}

	public function get_access_token() {

		return sanitize_text_field( $_GET['token'] );
	}

	public function make_request( $path, $params = array(), $type = 'post', $extra = array() ) {

		$args = array(
			'headers' => array(
				'Content-Type'  => 'application/x-www-form-urlencoded',
				'Authorization' => 'Bearer ' . $params['token'],
			),

		);

		switch ( $type ) {
			case 'get':
				unset( $params['token'] );
				$fn  = 'tve_dash_api_remote_get';
				$url = $path . "?" . http_build_query( array_merge( $params, $extra ) );
				break;
			default:

				$args['body'] = http_build_query( $params );
				$fn           = 'tve_dash_api_remote_post';
				$url          = $path;
				break;
		}

		$response = $fn( $url, $args );

		if ( $response instanceof WP_Error ) {
			throw new Exception( $response->get_error_message() );
		}

		$http_code = $response['response']['code'];

		if ( ! ( $http_code == '200' || $http_code == '201' || $http_code == '204' ) ) {
			throw new Exception( 'API call failed. Server returned status code ' . $http_code );
		}

		return json_decode( $response['body'] );
	}
}
