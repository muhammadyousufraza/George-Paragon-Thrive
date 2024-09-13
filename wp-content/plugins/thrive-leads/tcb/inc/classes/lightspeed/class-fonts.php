<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

namespace TCB\Lightspeed;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Fonts {

	public static $fonts_rendered = false;

	public static $font_families = [];
	public static $font_subsets  = [];

	const ENABLE_FONTS_OPTIMIZATION = '_tve_enable_fonts_optimization';
	const ENABLE_ASYNC_FONTS_LOAD   = '_tve_enable_fonts_async_load';
	const DISABLE_GOOGLE_FONTS      = 'tve_google_fonts_disable_api_call';

	/**
	 * Check if we deliver optimized fonts
	 * @return bool
	 */
	public static function is_enabled() {
		return ! empty( get_option( static::ENABLE_FONTS_OPTIMIZATION, 0 ) );
	}

	/**
	 * Check if fonts are delivered async or not
	 * @return bool
	 */
	public static function is_loading_fonts_async() {
		return ! empty( get_option( static::ENABLE_ASYNC_FONTS_LOAD, 0 ) );
	}

	/**
	 * Check if we remove google fonts from editor css
	 * @return bool
	 */
	public static function is_blocking_google_fonts() {
		return ! empty( get_option( static::DISABLE_GOOGLE_FONTS, 0 ) );
	}

	/**
	 * Save google fonts from inline css
	 *
	 * @param string $inline_css
	 *
	 * @return string
	 */
	public static function parse_google_fonts( $inline_css ) {

		if ( static::$fonts_rendered || wp_doing_ajax() || \TCB_Utils::is_rest() || ! static::is_enabled() || is_editor_page() ) {
			return $inline_css;
		}

		preg_match_all( '/@import url\("[^"]*fonts\.googleapis\.com\/css\?family=([^&]*)&subset=([^"]*)"\);/m', $inline_css, $matches );

		if ( ! empty( $matches ) && count( $matches ) === 3 ) {
			list( $font_string, $font_families, $subsets ) = $matches;

			if ( ! empty( $font_families ) ) {
				static::$font_families = array_unique( array_merge( static::$font_families, $font_families ) );
				static::$font_subsets  = array_unique( array_merge( static::$font_subsets, $subsets ) );

				$inline_css = str_replace( $font_string, '', $inline_css );
			}
		}

		return $inline_css;
	}

	/**
	 * Merge google fonts into one request
	 */
	public static function render_optimized_google_fonts() {
		if ( ! empty( static::$font_families ) ) {

			echo '<link href="https://fonts.gstatic.com" crossorigin rel="preconnect" />';

			if ( static::is_loading_fonts_async() ) {
				/* if we want to preload fonts async */
				$attr = 'rel="preload" as="style" onload="this.rel=\'stylesheet\'"';
			} else {
				/* load fonts normally */
				$attr = 'rel="stylesheet"';
			}

			echo sprintf( '<link type="text/css" %s href="https://fonts.googleapis.com/css?family=%s&subset=%s&display=swap">',
				$attr,
				implode( '|', static::$font_families ),
				implode( '&', static::$font_subsets )
			);

			static::$fonts_rendered = true;
		}
	}

}
