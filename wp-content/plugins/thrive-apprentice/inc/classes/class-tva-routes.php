<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class TVA_Routes {

	const OPTION_PREFIX = 'tva_route_';

	const FORBIDDEN_ROUTES = [
		'login',
	];

	/**
	 * Default routes
	 *
	 * @return array
	 */
	public static function get_defaults() {
		return [
			TVA_Const::LESSON_POST_TYPE     => 'course',
			TVA_Const::MODULE_POST_TYPE     => 'module',
			TVA_Course_Completed::POST_TYPE => TVA_Course_Completed::REWRITE_SLUG,
		];
	}

	/**
	 * Get the labels for the routes
	 *
	 * @return array
	 */
	public static function get_labels() {
		return [
			TVA_Const::LESSON_POST_TYPE     => __( 'Course route', 'thrive-apprentice' ),
			TVA_Const::MODULE_POST_TYPE     => __( 'Module route', 'thrive-apprentice' ),
			TVA_Course_Completed::POST_TYPE => __( 'Course completion route', 'thrive-apprentice' ),
		];
	}

	/**
	 * Get current routes
	 *
	 * @return array
	 */
	public static function get_all() {
		$keys = array_keys( static::get_defaults() );
		$data = [];
		foreach ( $keys as $key ) {
			$data[ $key ] = static::get_route( $key );
		}

		return $data;
	}

	public static function get_option_name( $name ) {
		return static::OPTION_PREFIX . $name;
	}

	/**
	 * Get the route for a given name
	 *
	 * @param $name
	 *
	 * @return false|mixed|string|null
	 */
	public static function get_route( $name ) {
		$value = get_option( static::get_option_name( $name ), '' );

		if ( ! $value ) {
			$defaults      = static::get_defaults();
			$default_value = isset( $defaults[ $name ] ) ? $defaults[ $name ] : '';

			if ( $default_value ) {
				static::update_option( $name, $default_value );
				$value = $default_value;
			}

		}

		return $value;
	}

	/**
	 * Get the route for a given name, prepended with a slash
	 *
	 * @param $name
	 *
	 * @return string
	 */
	public static function get_route_for_url( $name ) {
		$value = static::get_route( $name );

		return $value ? "/$value/" : '';
	}

	/**
	 * Update the option for a given name
	 *
	 * @param $name
	 * @param $value
	 *
	 * @return bool
	 */
	public static function update_option( $name, $value ) {
		return update_option( static::get_option_name( $name ), $value );
	}

	/**
	 * Get the forbidden routes
	 *
	 * @return array
	 */
	public static function get_forbidden_routes() {
		return apply_filters( 'tva_forbidden_routes', static::FORBIDDEN_ROUTES );
	}

	/**
	 * Validate a route by checking if it isn't used already in the WP Routing
	 *
	 * @param $slug
	 *
	 * @return bool
	 */
	public static function is_valid( $slug, $identifier = '' ) {
		$is_valid = true;

		// prevent starting with special characters
		if ( preg_match( '/^[^a-z0-9]/i', $slug ) ) {
			return false;
		}

		if ( in_array( $slug, static::get_forbidden_routes(), true ) ) {
			return false;
		}

		//prevent settings the default value from another category
		if ( $identifier ) {
			$defaults = static::get_defaults();

			$is_valid = ! in_array( $slug, array_values( $defaults ), true );

			if ( ! $is_valid && isset( $defaults[ $identifier ] ) ) {
				$is_valid = $slug === $defaults[ $identifier ];
			}
		}

		if ( $is_valid ) {
			$rewrite_rules = get_option( 'rewrite_rules', [] );
			$slug          = preg_quote( $slug, '/' );

			foreach ( $rewrite_rules as $rule => $rewrite ) {
				if ( preg_match( '/(?<=\W|^)' . $slug . '(?=\W)/', $rule ) ) {
					return false;
				}
			}
		}

		return $is_valid;
	}

	/**
	 * Update a route if it's valid
	 *
	 * @param $identifier
	 * @param $route
	 *
	 * @return true|WP_Error
	 */
	public static function update_route( $identifier, $route ) {
		$route    = trim( $route, '/' );
		$is_valid = static::is_valid( $route, $identifier );

		if ( $is_valid ) {
			static::update_option( $identifier, $route );
			delete_option( 'tva_flush_rewrite_rules_version' );

			return true;
		} else {
			return new WP_Error( 'invalid_route', __( 'This route is not available', 'thrive-apprentice' ) );
		}
	}
}
