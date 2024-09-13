<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-automator
 */

namespace Thrive\Automator\Items;

use Thrive\Automator\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class File_Loader
 *
 * @package Thrive\Automator
 */
class File_Loader {
	/**
	 * Create generic class name from class filename
	 *
	 * @param string $filename
	 *
	 * @return string
	 */
	public static function get_class_name_from_filename( $filename ) {
		$name = str_replace( [ 'class-', '-trigger', '-action', '-filter' ], '', basename( $filename, '.php' ) );

		return str_replace( '-', '_', ucwords( $name, '-' ) );
	}

	/**
	 * Load item files in automator install
	 *
	 * @return array
	 */
	public static function load_local_items( $folder ) {
		if ( empty( $folder ) ) {
			return [];
		}
		$local_items = [];

		foreach ( glob( __DIR__ . '/items/' . $folder . '/*.php' ) as $file ) {
			if ( apply_filters( 'tap_should_load_file', true, $file ) ) {
				require_once $file;

				$class = 'Thrive\Automator\Items\\' . static::get_class_name_from_filename( $file );

				if ( class_exists( $class ) && ! $class::hidden() ) {
					$local_items[] = $class;
				}
			}
		}

		return $local_items;
	}

}
