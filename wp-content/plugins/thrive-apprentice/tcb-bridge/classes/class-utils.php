<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

namespace TVA\Architect;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Utils
 *
 * @package  TVA\Architect
 * @project  : thrive-apprentice
 */
class Utils {
	/**
	 * @param string $subpath
	 *
	 * @return string
	 */
	public static function get_integration_path( $subpath = '' ) {
		return \TVA_Const::plugin_path( 'tcb-bridge/' ) . $subpath;
	}

	/**
	 * @param string $root_path
	 * @param string $path
	 *
	 * @return array
	 */
	public static function get_tcb_elements( $root_path, $path = null ) {
		/* if it's not a recursive call, use the root path */
		$path = ( $path === null ) ? $root_path : $path;

		$items    = array_diff( scandir( $path ), [ '.', '..' ] );
		$elements = array();

		foreach ( $items as $item ) {
			$item_path = $path . '/' . $item;
			/* if the item is a folder, enter it and do recursion */
			if ( is_dir( $item_path ) ) {
				$elements = array_merge( $elements, static::get_tcb_elements( $item_path ) );
			}

			/* if the item is a file, include it */
			if ( is_file( $item_path ) ) {
				$element = require_once $item_path;

				if ( ! empty( $element ) ) {
					$elements[ $element->tag() ] = $element;
				}
			}
		}

		return $elements;
	}
}
