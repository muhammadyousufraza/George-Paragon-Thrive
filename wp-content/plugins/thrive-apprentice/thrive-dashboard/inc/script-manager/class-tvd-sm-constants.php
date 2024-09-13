<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden.
}

/**
 * Class TVD_Constants
 */
class TVD_SM_Constants {

	const HEAD_PLACEMENT       = 'head';
	const BODY_OPEN_PLACEMENT  = 'body_open';
	const BODY_CLOSE_PLACEMENT = 'body_close';

	public static function is_ttb_active() {
		return defined( 'THRIVE_TEMPLATE' );
	}

	public static function is_architect_active() {
		return defined( 'TVE_PLUGIN_FILE' );
	}

	/**
	 * SM path with appended file if passed as parameter
	 *
	 * @param string $file
	 *
	 * @return string
	 */
	public static function path( $file = '' ) {
		return plugin_dir_path( dirname( __FILE__ ) ) . 'script-manager/' . ltrim( $file, '\\/' );
	}

	/**
	 * SM url with appended file if passed as parameter
	 *
	 * @param string $file
	 *
	 * @return string
	 */
	public static function url( $file = '' ) {
		return untrailingslashit( TVE_DASH_URL ) . '/inc/script-manager' . ( ! empty( $file ) ? '/' : '' ) . ltrim( $file, '\\/' );
	}


	/*
	 * return the scripts that are currently recognized
	 */
	public static function get_recognized_scripts_keywords() {

		return array(
			'Google Analytics'           => array( 'Google Analytics', 'GoogleAnalyticsObject', 'gtag' ),
			'Google Tag Manager'         => array( 'Google Tag Manager', 'gtm.start', 'gtm', 'GTM', 'gtm.js' ),
			'Active Campaign'            => array( 'actid', 'trackcmp' ),
			'Clicky Analytics'           => array( 'clicky', 'getclicky' ),
			'CrazyEgg'                   => array( 'script.crazyegg.com' ),
			'Customer.io'                => array( 'cio', '_cio' ),
			'Drip'                       => array( 'getdrip' ),
			'Facebook Tracking Pixel'    => array( 'fbq', 'facebook' ),
			'Google Website Experiments' => array( 'utmx', 'ga_exp', 'expid' ),
			'Heap Analytics'             => array( 'heap', 'heapanalytics' ),
			'Hotjar'                     => array( 'hotjar', 'hjSettings' ),
			'Hubspot'                    => array( 'hs-script' ),
			'Intercom'                   => array( 'Intercom', 'intercomSettings' ),
			'Mixpanel'                   => array( 'mixpanel' ),
			'Optimizely'                 => array( 'optimizely' ),
			'Piwik Analytics'            => array( 'piwik.php', 'piwik.js' ),
			'Matamo'                     => array( 'matomo', '_paq', 'matomo.js', 'matomo.php' ),
			'Twitter Website Tag'        => array( 'twq', 'twitter' ),
			'Visual Website Optimizer'   => array( 'vwo', 'Visual' ),
		);
	}

	/*
	 * return the data of the scripts that are currently recognized
	 */
	public static function get_recognized_scripts_data() {

		return array(
			'Google Analytics'           => array( 'placement' => 'head', 'icon' => 'google-analytics' ),
			'Google Tag Manager'         => array( 'placement' => 'head', 'icon' => 'google-tag-manager' ),
			'Active Campaign'            => array( 'placement' => 'head', 'icon' => 'active-campaign' ),
			'Clicky Analytics'           => array( 'placement' => 'body_close', 'icon' => 'clicky-analytics' ),
			'CrazyEgg'                   => array( 'placement' => 'head', 'icon' => 'crazyegg' ),
			'Customer.io'                => array( 'placement' => 'body_close', 'icon' => 'customer-io' ),
			'Drip'                       => array( 'placement' => 'body_close', 'icon' => 'drip' ),
			'Facebook Tracking Pixel'    => array( 'placement' => 'body_open', 'icon' => 'facebook-tracking-pixel' ),
			'Google Website Experiments' => array( 'placement' => 'head', 'icon' => 'google-website-experiments' ),
			'Heap Analytics'             => array( 'placement' => 'head', 'icon' => 'heap-analytics' ),
			'Hotjar'                     => array( 'placement' => 'head', 'icon' => 'hotjar' ),
			'Hubspot'                    => array( 'placement' => 'body_close', 'icon' => 'hubspot' ),
			'Intercom'                   => array( 'placement' => 'body_close', 'icon' => 'intercom' ),
			'Mixpanel'                   => array( 'placement' => 'head', 'icon' => 'mixpanel' ),
			'Optimizely'                 => array( 'placement' => 'head', 'icon' => 'optimizely' ),
			'Piwik Analytics'            => array( 'placement' => 'body_open', 'icon' => 'piwik-analytics' ),
			'Matamo'                     => array( 'placement' => 'body_open', 'icon' => 'matomo' ),
			'Twitter Website Tag'        => array( 'placement' => 'head', 'icon' => 'twitter-website-tag' ),
			'Visual Website Optimizer'   => array( 'placement' => 'head', 'icon' => 'visual-website-optimizer' ),
		);
	}
}
