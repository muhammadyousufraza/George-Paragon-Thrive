<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

namespace TCB\Lightspeed;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Compat
 *
 * @package TCB\Lightspeed
 */
class Compat {

	public static function init() {
		if ( Main::is_optimizing() ) {
			add_filter( 'get_post_metadata', [ __CLASS__, 'disable_seo_press' ], 10, 3 );
			add_filter( 'get_term_metadata', [ __CLASS__, 'disable_seo_press' ], 10, 3 );

			add_filter( 'option_wpo_minify_config', [ __CLASS__, 'disable_wpo_css_merge' ] );
			add_filter( 'option_301_redirects', '__return_empty_array' );

			add_action( 'plugins_loaded', [ __CLASS__, 'disable_autoptimize' ], 0 );

			/* disable optimole replace when optimizing */
			add_filter( 'optml_should_replace_page', '__return_false' );
			add_filter( 'optml_extracted_urls', '__return_empty_array' );

			/* Disable wp rocket combine and minify when we optimize assets */
			add_filter( 'get_rocket_option_minify_concatenate_css', '__return_false' );
			add_filter( 'get_rocket_option_minify_css', '__return_false' );

			static::disable_redirection_plugin();
			static::disable_wp_fastest_cache();
			static::disable_litespeed();
		}
	}

	/**
	 * When running an optimization, prevent any redirects from SEO Press
	 *
	 * @param $value
	 * @param $object_id
	 * @param $meta_key
	 *
	 * @return false|mixed
	 */
	public static function disable_seo_press( $value, $object_id, $meta_key ) {
		if ( $meta_key === '_seopress_redirections_enabled' ) {
			$value = false;
		}

		return $value;
	}

	/**
	 * Disable wp optimize css merge when we optimize the css so we can find it in the page
	 *
	 * @param $options
	 *
	 * @return mixed
	 */
	public static function disable_wpo_css_merge( $options ) {
		if ( is_array( $options ) ) {
			$options['enable_merging_of_css']   = false;
			$options['enable_css_minification'] = false;
			$options['inline_css']              = false;
		}

		return $options;
	}

	/**
	 * Disable redirection plugin when we try to optimize a page
	 */
	public static function disable_redirection_plugin() {
		define( 'REDIRECTION_DISABLE', true );
	}

	/**
	 * Disable litespeed css combine when optimization is running
	 */
	public static function disable_litespeed() {
		if ( class_exists( '\LiteSpeed\Conf', false ) ) {
			\LiteSpeed\Conf::get_instance()->force_option( 'optm-css_comb', false );
		}
	}

	/**
	 * Disable fastest cache while optimizing assets
	 */
	public static function disable_wp_fastest_cache() {
		if ( is_array( $GLOBALS['wp_fastest_cache_options'] ) ) {
			$GLOBALS['wp_fastest_cache_options']['wpFastestCacheCombineCss'] = false;
			$GLOBALS['wp_fastest_cache_options']['wpFastestCacheMinifyCss']  = false;
			$GLOBALS['wp_fastest_cache_options']['wpFastestCacheStatus']     = false;
		} else if ( is_object( $GLOBALS['wp_fastest_cache_options'] ) ) {
			$GLOBALS['wp_fastest_cache_options']->wpFastestCacheCombineCss = false;
			$GLOBALS['wp_fastest_cache_options']->wpFastestCacheMinifyCss  = false;
			$GLOBALS['wp_fastest_cache_options']->wpFastestCacheStatus     = false;
		}
	}

	/**
	 * Disable autoptimize when optimizing assets
	 */
	public static function disable_autoptimize() {
		if ( function_exists( 'autoptimize' ) ) {
			remove_action( 'autoptimize_setup_done', array( autoptimize(), 'check_cache_and_run' ) );
		}
	}
}
