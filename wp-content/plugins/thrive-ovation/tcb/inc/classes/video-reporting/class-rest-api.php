<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

namespace TCB\VideoReporting;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Rest_API {
	const  REST_NAMESPACE = 'tcb/v1';
	const  REST_ROUTE     = 'video-reporting/';

	public static function register_routes() {
		$int_parameter_validator = [
			'type'              => 'int',
			'required'          => true,
			'validate_callback' => static function ( $param ) {
				return is_numeric( $param );
			},
		];

		register_rest_route( static::REST_NAMESPACE, static::REST_ROUTE . 'video_started', [
			[
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => [ __CLASS__, 'video_start' ],
				'permission_callback' => '__return_true',
				'args'                => [
					'video_url' => [
						'type'     => 'string',
						'required' => true,
					],
					'user_id'   => $int_parameter_validator,
					'post_id'   => $int_parameter_validator,
				],
			],
		] );

		register_rest_route( static::REST_NAMESPACE, static::REST_ROUTE . 'save_range', [
			[
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => [ __CLASS__, 'save_range' ],
				'permission_callback' => '__return_true',
				'args'                => [
					'video_url'   => [
						'type'     => 'string',
						'required' => true,
					],
					'user_id'     => $int_parameter_validator,
					'post_id'     => $int_parameter_validator,
					'range_start' => $int_parameter_validator,
					'range_end'   => $int_parameter_validator,
				],
			],
		] );
	}

	public static function video_start( $request ) {
		$video_id = Video::get_post_id_by_video_url( $request->get_param( 'video_url' ) );

		if ( empty( $video_id ) ) {
			return new \WP_REST_Response( 'Error on triggering the video start', 500 );
		}

		Video::get_instance_with_id( $video_id )->on_video_start( $request->get_param( 'user_id' ), $request->get_param( 'post_id' ) );

		return new \WP_REST_Response( $video_id );
	}

	/**
	 * @return \WP_REST_Response
	 */
	public static function save_range( $request ) {
		$video_id = Video::get_post_id_by_video_url( $request->get_param( 'video_url' ) );

		if ( empty( $video_id ) ) {
			return new \WP_REST_Response( 'Error on saving the new range', 500 );
		}

		Video::get_instance_with_id( $video_id )
		     ->save_range(
			     $request->get_param( 'user_id' ),
			     $request->get_param( 'post_id' ),
			     (int) $request->get_param( 'range_start' ),
			     (int) $request->get_param( 'range_end' )
		     );

		/**
		 * Send data to the reporting module
		 * Allow people to hook into this action and send the data back to the reporting module
		 */
		return new \WP_REST_Response( apply_filters( 'thrive_video_save_range_response', [ 'video_id' => $video_id ], $request ) );
	}
}
