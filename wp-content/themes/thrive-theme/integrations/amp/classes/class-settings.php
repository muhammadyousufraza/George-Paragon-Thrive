<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

namespace Thrive\Theme\AMP;

use Thrive_Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Settings
 *
 * @package Thrive\Theme\AMP
 */
class Settings {

	/* keyword for the landing page post types */
	const LP_KEY = 'lp';

	/**
	 * Option where to keep all the amp settings
	 */
	const THRIVE_AMP_OPTION = 'thrive_amp';

	/**
	 * Data needed in admin dashboard
	 *
	 * @return array
	 */
	public static function localize() {
		return [
			'settings'   => static::get_all(),
			'post_types' => array_merge( Thrive_Utils::get_post_types(), [ static::LP_KEY => esc_html__( 'Landing Pages', 'thrive-theme' ) ] ),
		];
	}

	/**
	 * Return all the amp options
	 *
	 * @return array
	 */
	public static function get_all() {
		$settings = maybe_unserialize( get_option( self::THRIVE_AMP_OPTION, [] ) );

		$settings = static::prepare_default_settings( $settings );

		return $settings ?: [];
	}

	/**
	 * Set 'posts' to be active by default.
	 *
	 * @param array $settings
	 *
	 * @return array
	 */
	public static function prepare_default_settings( $settings = [] ) {

		if ( ! is_array( $settings ) ) {
			$settings = [];
		}

		if ( empty( $settings['post_types'] ) ) {
			$settings['post_types'] = [ 'post' => true ];
		}

		return $settings;
	}

	/**
	 * Get a specific setting based on a key
	 *
	 * @param string $key
	 *
	 * @return mixed|string
	 */
	public static function get_setting( $key ) {
		$settings = static::get_all();

		return empty( $settings[ $key ] ) ? '' : $settings[ $key ];
	}

	/**
	 * Check if AMP is enabled site wide
	 *
	 * @return mixed|string
	 */
	public static function is_enabled() {
		return static::get_setting( 'enabled' );
	}

	/**
	 * @return mixed|string
	 */
	public static function is_internal_linking_enabled() {
		return ! empty( static::get_setting( 'internal_linking' ) );
	}

	/**
	 * Return google analytics for amp, but only if it's enabled.
	 * This has to be added inside <body>.
	 *
	 * @return mixed|string
	 */
	public static function get_analytics() {
		$script = '';

		if ( ! empty( static::get_setting( 'analytics_enabled' ) ) ) {
			$script = static::get_setting( 'analytics' );

			/* remove the 'custom-element' part because it's added in the Script class, inside <head> */
			$script = str_replace( Scripts::ANALYTICS_SCRIPT, '', $script );
		}

		return $script;
	}

	/**
	 * Check if we can have amp enabled on a specific post type
	 *
	 * @param string $post_id
	 *
	 * @return bool
	 */
	public static function enabled_on_post_type( $post_id ) {
		if ( empty( tve_post_is_landing_page( $post_id ) ) ) {
			$post_type = get_post_type( $post_id );
		} else {
			$post_type = static::LP_KEY;
		}

		$post_types_settings = static::get_setting( 'post_types' );

		return ! empty( $post_types_settings[ $post_type ] ) && $post_types_settings[ $post_type ];
	}
}
