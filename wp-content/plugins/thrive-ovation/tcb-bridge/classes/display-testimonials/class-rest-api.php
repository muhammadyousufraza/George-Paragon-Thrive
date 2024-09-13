<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-ovation
 */

namespace TVO\DisplayTestimonials;

use WP_REST_Request;
use WP_REST_Response;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Rest_Api
 *
 * @package TVO\DisplayTestimonials
 */
class Rest_Api {
	public static $namespace = 'tcb/v1';
	public static $route = '/testimonials';

	public static function register_routes() {
		register_rest_route( static::$namespace, static::$route . '/html', array(
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => array( __CLASS__, 'get_testimonials' ),
				'permission_callback' => '__return_true',
			),
		) );

		register_rest_route( static::$namespace, static::$route . '/cloud', array(
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => array( __CLASS__, 'cloud_template' ),
				'permission_callback' => array( __CLASS__, 'route_permission' ),
			),
		) );
	}

	/**
	 * Get testimonials
	 *
	 * @param $request
	 *
	 * @return WP_REST_Response
	 */
	public static function get_testimonials( $request ) {
		/* if we send a template parameter, we're going to print the testimonials after that one */
		$content = $request->get_param( 'content' );

		$has_pagination = $request->get_param( 'has_pagination' );

		if ( ! empty( $content ) ) {
			$content = str_replace( [ '{({', '})}' ], [ '[', ']' ], $content );
		}

		$testimonials = Main::get_testimonials( $request->get_param( 'args' ), $has_pagination );

		$results = [];

		foreach ( $testimonials as $key => $testimonial ) {
			if ( empty( $content ) ) {
				$results[ $testimonial->ID ] = Main::get_testimonial_shortcode_data( $testimonial );

				$results[ $testimonial->ID ]['order'] = $key + 1;
			} else {
				$results[ $key + 1 ] = Main::render_one( $testimonial, $content );
			}
		}

		return new WP_REST_Response( [
			'total_post_count' => Main::$query->found_posts,
			'testimonials'     => $results,
			'count'            => count( $testimonials ),
		] );
	}

	/**
	 * Get the cloud template for the testimonial list
	 * The 'query' parameter is not seen here, but it's also used inside the render function
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response
	 */
	public static function cloud_template( $request ) {
		$template = $request->get_param( 'template' );

		$data = tve_get_cloud_template_data( 'display_testimonials', [
			'id'   => $template,
			'type' => 'display_testimonials',
		] );

		return new WP_REST_Response( $data );
	}

	/**
	 * Check if a given request has access to this route
	 *
	 * @return boolean
	 */
	public static function route_permission() {
		$post_id = isset( $_REQUEST['post_id'] ) ? $_REQUEST['post_id'] : null;

		return \TCB_Product::has_external_access( $post_id );
	}
}
