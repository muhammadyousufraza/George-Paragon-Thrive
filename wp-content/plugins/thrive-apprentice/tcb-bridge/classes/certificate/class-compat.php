<?php

namespace TVA\Architect\Certificate;

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * The purpose of this class is to solve incompatibilities between generated PDFs and other plugins & themes
 * Can be used to disable third party user consent content, lightboxes and ribbons that come from different plugins and themes
 */
class Compat {

	/**
	 * Holds actions and filters
	 *
	 * @return void
	 */
	public static function init() {
		add_filter( 'cmplz_is_preview', [ __CLASS__, 'disable_third_party_consent' ] );
		add_filter( 'cli_show_cookie_bar_only_on_selected_pages', [ __CLASS__, 'disable_cookie_law_info' ], 10, 2 );

		/**
		 * Compatibility with Cookie Notice & Compliance for GDPR / CCPA plugin
		 * https://wordpress.org/plugins/cookie-notice/
		 *
		 * When the the active post type is the certificate post type we force the plugin in preview mode so the assets of the plugin is not loaded
		 */
		add_filter( 'cn_is_preview_mode', [ __CLASS__, 'disable_third_party_consent' ] );

		/**
		 * Remove actions that interfere with certificate logic
		 * This will be triggered after WP is fully loaded to have the certificate post type
		 */
		add_action( 'wp', static function () {
			if ( get_post_type() === \TVA_Course_Certificate::POST_TYPE ) {

				/**
				 * Compatibility with CookieYes | GDPR Cookie Consent & Compliance Notice (CCPA Ready) 1+million active installations
				 * Disables the cookie popup on certificate pages
				 *
				 * https://wordpress.org/plugins/cookie-law-info/
				 * https://www.cookieyes.com/
				 */
				if ( ! defined( 'ET_FB_ENABLED' ) ) {
					define( 'ET_FB_ENABLED', true );
				}

				if ( function_exists( 'is_plugin_active' ) && is_plugin_active( 'borlabs-cookie/borlabs-cookie.php' ) ) {
					/**
					 * Compatibility with Borlabs Cookie plugin
					 *
					 * https://borlabs.io/borlabs-cookie/
					 */
					add_filter( 'borlabsCookie/buffer/active', function ( $status ) {
						return false;
					} );

					remove_action( 'wp_footer', [ \BorlabsCookie\Cookie\Frontend\JavaScript::getInstance(), 'registerFooter' ] );
					remove_action( 'wp_footer', [ \BorlabsCookie\Cookie\Frontend\CookieBox::getInstance(), 'insertCookieBox' ] );
				}

				/**
				 * For certificate type remove the woo notification
				 */
				remove_action( 'wp_footer', 'woocommerce_demo_store' );

				if ( \Thrive_Theme::is_active() ) {
					/**
					 * If ThriveTheme is active remove the hooks where theme adds scripts to the certificate page
					 */
					remove_action( 'theme_after_body_open', [ thrive_theme(), 'theme_after_body_open' ] );
					remove_action( 'wp_footer', [ thrive_theme(), 'wp_footer' ] );
					remove_action( 'wp_head', [ thrive_theme(), 'wp_head' ] );
				}
			}
		} );
	}

	/**
	 * Disable cookie HTML for Certificates
	 *
	 * @param string $notify_html
	 * @param string $post_slug
	 *
	 * @return string
	 */
	public static function disable_cookie_law_info( $notify_html, $post_slug ) {
		if ( get_post_type() === \TVA_Course_Certificate::POST_TYPE ) {
			$notify_html = '';
		}

		return $notify_html;
	}

	/**
	 * Disables third party consent markup for certificate page
	 * Fixes the cases where cookie banners appear in the generated PDF
	 *
	 * @param boolean $should_disable
	 *
	 * @return boolean
	 */
	public static function disable_third_party_consent( $should_disable ) {

		if ( get_post_type() === \TVA_Course_Certificate::POST_TYPE ) {
			$should_disable = true;
		}

		return $should_disable;
	}
}
