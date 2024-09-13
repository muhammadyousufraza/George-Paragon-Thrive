<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Thrive_Post_Rest
 */
class Thrive_Post_Rest {

	public static $namespace        = TTB_REST_NAMESPACE;
	public static $route            = '/post';
	public static $template_actions = '/template';

	public static function register_routes() {
		register_rest_route( static::$namespace, static::$route . '/(?P<post_id>[\d]+)' . static::$template_actions . '/(?P<template_id>[\d]+)', [
			[
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => [ __CLASS__, 'change_template' ],
				'permission_callback' => [ __CLASS__, 'route_permission' ],
			],
		] );

		register_rest_route( static::$namespace, static::$route . '/format/audio', [
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ __CLASS__, 'save_audio' ],
				'permission_callback' => [ __CLASS__, 'route_permission' ],
			],
		] );

		register_rest_route( static::$namespace, static::$route . '/format/video', [
			[
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => [ __CLASS__, 'save_video' ],
				'permission_callback' => [ __CLASS__, 'route_permission' ],
			],
		] );

		register_rest_route( static::$namespace, static::$route . '/format/image', [
			[
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => [ __CLASS__, 'save_image' ],
				'permission_callback' => [ __CLASS__, 'route_permission' ],
			],
		] );
	}

	/**
	 * Change the assigned template of this page.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public static function change_template( $request ) {
		$template_id = $request->get_param( 'template_id' );
		$post_id     = $request->get_param( 'post_id' );

		$post = new Thrive_Post( $post_id );

		/* If there is no template ID set, or it's 0, delete the meta. */
		if ( empty( $template_id ) ) {
			$post->delete_meta( THRIVE_META_POST_TEMPLATE );
		} else {
			$post->set_meta( THRIVE_META_POST_TEMPLATE, (int) $template_id );
		}

		return new WP_REST_Response( true, 200 );
	}

	/**
	 * Save audio settings
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_REST_Response
	 */
	public static function save_image( $request ) {
		$post_id = $request->get_param( 'post_id' );

		$attachment_id = $request->get_param( 'attachment' );

		if ( empty( $post_id ) || empty( $attachment_id ) || ! current_user_can( 'edit_post', $post_id ) ) {
			return new WP_REST_Response( 'Undefined image.', 404 );
		}
		$featured_image = thrive_image_post_format( $post_id );
		$response       = [
			'success' => $featured_image->save_image( $attachment_id ),
			'html'    => $featured_image->render(),
			'data'    => $featured_image->get_image(),
		];

		return new WP_REST_Response( $response, 200 );
	}

	/**
	 * Save audio settings
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_REST_Response
	 */
	public static function save_audio( $request ) {
		$post_id = $request->get_param( 'post_id' );

		if ( empty( $post_id ) || ! current_user_can( 'edit_post', $post_id ) ) {
			$response = new WP_REST_Response( "User can't edit post.", 404 );
		} else {
			$settings = [];
			$type     = $request->get_param( 'source' );

			if ( $type ) {
				$params = $request->get_param( 'params' );
				foreach ( $params as $key => $param ) {
					$settings[ Thrive_Audio_Post_Format_Main::AUDIO_META_PREFIX . '_' . $type . '_' . $key ] = $param;
				}
			}
			thrive_audio_post_format( $type, $post_id )->save_options( $settings );

			$response = new WP_REST_Response( [
				'html'    => Thrive_Audio_Post_Format_Main::render( $type, $post_id ),
				'success' => true,
			], 200 );
		}

		return $response;
	}

	/**
	 * Save video settings
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_REST_Response
	 */
	public static function save_video( $request ) {
		$post_id = $request->get_param( 'post_id' );

		if ( empty( $post_id ) || ! current_user_can( 'edit_post', $post_id ) ) {
			$response = new WP_REST_Response( "User can't edit post.", 404 );
		} else {
			$settings = [];
			$type     = $request->get_param( 'source' );
			if ( $type ) {
				$params   = $request->get_param( 'params' );
				$settings = thrive_video_post_format( $type, $post_id )->process_options( $params, $type );
			}

			thrive_video_post_format( $type, $post_id )->save_options( $settings );

			$response = new WP_REST_Response( [
				'html'    => thrive_video_post_format( $type, $post_id )->render( false ),
				'success' => true,
			], 200 );
		}

		return $response;
	}

	/**
	 * Check if a given request has access to the route.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|bool
	 */
	public static function route_permission( $request ) {
		return Thrive_Theme_Product::has_access();
	}
}
