<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

namespace Thrive\Theme\ConditionalDisplay;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Main
 *
 * @package Thrive\Theme\ConditionalDisplay
 */
class Main {
	public static function init() {
		static::load_classes( 'entities', 'entity' );
		static::load_classes( 'fields', 'field' );
	}

	public static function load_classes( $folder, $type ) {
		$path = __DIR__ . '/' . $folder;

		foreach ( array_diff( scandir( $path ), [ '.', '..' ] ) as $item ) {
			require_once $path . '/' . $item;

			if ( preg_match( '/class-(.*).php/m', $item, $m ) && ! empty( $m[1] ) ) {
				$class_name = \TCB_Elements::capitalize_class_name( $m[1] );

				$class = __NAMESPACE__ . '\\' . ucfirst( $folder ) . '\\' . $class_name;

				$register_fn = 'tve_register_condition_' . $type;
				$register_fn( $class );
			}
		}
	}
}
