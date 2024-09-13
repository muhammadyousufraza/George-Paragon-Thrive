<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

namespace TVE\Architect\ConditionalDisplay;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Main
 *
 * @package TVE\Architect\ConditionalDisplay
 */
class Main {
	public static function init() {
		static::load_classes( 'entities', 'entity' );
		static::load_classes( 'fields', 'field' );
	}

	public static function load_classes( $folder, $type ) {
		$path = __DIR__ . '/' . $folder;
		if ( is_dir( $path ) ) {
			foreach ( array_diff( scandir( $path ), [ '.', '..' ] ) as $item ) {
				if ( static::should_load( $item ) ) {
					require_once $path . '/' . $item;

					if ( preg_match( '/class-(.*).php/m', $item, $m ) && ! empty( $m[1] ) ) {
						$class_name = \TCB_ELEMENTS::capitalize_class_name( $m[1] );

						$class = __NAMESPACE__ . '\\' . ucfirst( $folder ) . '\\' . $class_name;

						$register_fn = 'tve_register_condition_' . $type;
						$register_fn( $class );
					}
				}
			}
		}
	}

	public static function wpfusion_exists() {
		return function_exists( 'wp_fusion' );
	}

	public static function should_load( $filename ) {
		$load = true;
		if ( strpos( basename( $filename, '.php' ), '-wpfusion-' ) !== false && ! static::wpfusion_exists() ) {
			$load = false;
		}

		return $load;
	}
}

