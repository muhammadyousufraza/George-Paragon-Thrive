<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

namespace Thrive\Theme\AMP;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Fonts
 * @package Thrive\Theme\AMP
 */
class Fonts {

	/* used in order to get the font URL */
	const IMPORT_STRING_PATTERN = '/\@import url\("(.*)"\);/U';

	/**
	 * Extract the fonts from the styles and wrap them in AMP-compatible <link> tags before returning everything as a string
	 *
	 * @param $post_id
	 *
	 * @return string
	 */
	public static function get_fonts( $post_id ) {
		$theme_fonts = array_merge( static::get_typography_fonts(), static::get_template_fonts() );

		$fonts = [];

		foreach ( $theme_fonts as $font_string ) {
			if ( preg_match( static::IMPORT_STRING_PATTERN, $font_string, $matches ) && ! empty( $matches[1] ) ) {
				$fonts[] = $matches[1];
			}
		}

		/* the post fonts are already extracted from the import string, so they skip the previous step and only appear here */
		$fonts = array_merge( $fonts, static::get_post_fonts( $post_id ) );

		$fonts = array_unique( $fonts );

		$fonts_html_array = [];

		foreach ( $fonts as $font_url ) {
			$font_url = static::prepare_font_url( $font_url );

			$fonts_html_array[] = '<link rel="stylesheet" href="' . $font_url . '">';
		}

		return implode( '', $fonts_html_array );
	}

	/**
	 * If the url doesn't start with https, add it ( there's no 'http:' case here, it either comes as '//...' or 'https://...'
	 *
	 * @param $font_url
	 *
	 * @return string
	 */
	public static function prepare_font_url( $font_url ) {

		if ( strpos( $font_url, 'https' ) !== 0 ) {
			$font_url = 'https:' . $font_url;
		}

		return $font_url;
	}

	/**
	 * Get the typography fonts from the typography styles
	 *
	 * @return array
	 */
	public static function get_typography_fonts() {
		$styles = tcb_default_style_provider()->get_processed_styles( null, 'object', false );

		return empty( $styles['@imports'] ) ? [] : $styles['@imports'];
	}

	/**
	 * Get the template fonts from the template styles
	 *
	 * @return array
	 */
	public static function get_template_fonts() {
		$template_style = thrive_template()->get_meta( 'style' );

		return empty( $template_style['fonts'] ) ? [] : $template_style['fonts'];
	}

	/**
	 * Get the post fonts from the post styles. Parse the fonts from the post style string first
	 *
	 * @param $post_id
	 *
	 * @return array
	 */
	public static function get_post_fonts( $post_id ) {
		$lp_key = tve_post_is_landing_page();

		$css_meta_key = empty( $lp_key ) ? 'tve_custom_css' : 'tve_custom_css_' . $lp_key;

		$post_styles = get_post_meta( $post_id, $css_meta_key, true );

		$post_fonts = [];

		if ( preg_match_all( static::IMPORT_STRING_PATTERN, $post_styles, $matches ) && ! empty( $matches[1] ) ) {
			$post_fonts = $matches[1];
		}

		return $post_fonts;
	}
}
