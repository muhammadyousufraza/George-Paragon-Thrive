<?php

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

/**
 * Trait TD_TTW_Utils
 */
trait TD_TTW_Utils {

	/**
	 * @param string $file
	 *
	 * @return string
	 */
	public static function path( $file = '' ) {

		return plugin_dir_path( dirname( __FILE__ ) ) . ltrim( $file, '\\/' );
	}

	/**
	 * Returns url relative to plugin url
	 *
	 * @param string $file
	 *
	 * @return string
	 */
	public function url( $file = '' ) {

		return untrailingslashit( TVE_DASH_URL ) . '/inc/ttw-account' . ( ! empty( $file ) ? '/' : '' ) . ltrim( $file, '\\/' );
	}
}
