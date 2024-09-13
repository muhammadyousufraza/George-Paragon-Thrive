<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

final class TVD_Smart_Const {

	/**
	 * Rest namespage
	 *
	 */
	const REST_NAMESPACE = 'tss/v1';
	/**
	 * Smart Site url with appended file if passed as parameter
	 *
	 * @param string $file
	 *
	 * @return string
	 */
	public static function url( $file = '' ) {
		return untrailingslashit( TVE_DASH_URL ) . '/inc/smart-site' . ( ! empty( $file ) ? '/' : '' ) . ltrim( $file, '\\/' );
	}

	/**
	 * Smart Site path with appended file if passed as parameter
	 *
	 * @param string $file
	 *
	 * @return string
	 */
	public static function path( $file = '' ) {
		return untrailingslashit( plugin_dir_path( __FILE__ ) ) . ( ! empty( $file ) ? '/' : '' ) . ltrim( $file, '\\/' );
	}
}