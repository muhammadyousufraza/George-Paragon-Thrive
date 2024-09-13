<?php

namespace TVA\Architect\Protected_Files;

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Shortcodes class
 */
class Shortcodes {

	/**
	 * @var string[]
	 */
	private static $_shortcodes = [
		'tva_protected_file_link'         => 'protected_file_link', //From froala inline shortcodes
		'tva_protected_file_dynamic_link' => 'protected_file_dynamic_link', //From dynamic links
	];

	/**
	 * Shortcodes initialization
	 *
	 * @return void
	 */
	public static function init() {
		foreach ( static::$_shortcodes as $shortcode => $function ) {
			add_shortcode( $shortcode, [ __CLASS__, $function ] );
		}
	}

	/**
	 * Protected file link shortcode callback
	 *
	 * @param array $attr
	 *
	 * @return string
	 */
	public static function protected_file_link( $attr = [] ) {
		$label = '';
		if ( is_array( $attr ) && ! empty( $attr['file-id'] ) && is_numeric( $attr['file-id'] ) ) {
			$file = new \TVA_Protected_File( (int) $attr['file-id'] );

			if ( ! $file->is_valid() ) {
				/**
				 * If the protected file was deleted return nothing
				 */
				return '';
			}

			$url = is_editor_page_raw() ? '#' : add_query_arg( [ 'ret' => rawurlencode( get_permalink() ) ], $file->download_url );
			if ( ! empty( $attr['static-link'] ) && is_string( $attr['static-link'] ) ) {
				$attr['static-link'] = str_replace( '#', $url, $attr['static-link'] );
			}

			$label = $file->post_title;
		}

		return \TVD_Global_Shortcodes::maybe_link_wrap( $label, $attr );
	}

	/**
	 * Protected file dynamic link shortcode callback
	 *
	 * @param array $attr
	 *
	 * @return string
	 */
	public static function protected_file_dynamic_link( $attr = [] ) {
		$url = '#';

		if ( is_array( $attr ) && ! empty( $attr['id'] ) && is_numeric( $attr['id'] ) ) {
			$file = new \TVA_Protected_File( (int) $attr['id'] );

			if ( ! $file->is_valid() ) {
				return add_query_arg( [
					'tva_invalid_file' => 1,
				], home_url() );
			}

			$url = is_editor_page_raw() ? '#' : add_query_arg( [ 'ret' => rawurlencode( get_permalink() ) ], $file->download_url );
		}

		return $url;
	}

	/**
	 * Returns the list of shortcodes needed for allow shortcodes filter
	 *
	 * @return string[]
	 */
	public static function get() {
		return array_keys( static::$_shortcodes );
	}
}
