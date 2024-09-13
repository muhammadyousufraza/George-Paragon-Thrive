<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

namespace TCB\ConditionalDisplay;

use TCB\ConditionalDisplay\PostTypes\Conditional_Display_Group;
use TCB\ConditionalDisplay\PostTypes\Global_Conditional_Set;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Main {
	const ABSTRACT_PATH = __DIR__ . '/abstract';
	const TRAIT_PATH    = __DIR__ . '/traits';

	public static $entities = [];

	public static function init() {
		static::includes();

		Conditional_Display_Group::register();
		Global_Conditional_Set::register();

		Hooks::add_filters();
		Hooks::add_actions();

		Shortcode::add();

		do_action( 'tcb_conditional_display_register' );
	}

	public static function includes() {
		require_once __DIR__ . '/post-types/class-conditional-display-group.php';
		require_once __DIR__ . '/post-types/class-global-conditional-set.php';
		require_once __DIR__ . '/class-shortcode.php';
		require_once __DIR__ . '/class-hooks.php';
		require_once __DIR__ . '/rest-api/class-general-data.php';
		require_once __DIR__ . '/rest-api/class-global-sets.php';

		require_once static::TRAIT_PATH . '/class-item.php';

		require_once static::ABSTRACT_PATH . '/class-entity.php';
		require_once static::ABSTRACT_PATH . '/class-field.php';
		require_once static::ABSTRACT_PATH . '/class-condition.php';

		require_once __DIR__ . '/tve-conditional-display-global.php';

		static::load_files_from_path( 'entities', Entity::class );

		foreach ( [ 'user', 'time', 'request', 'referral' ] as $field_group ) {
			static::load_files_from_path( 'fields/' . $field_group, Field::class );
		}

		static::load_files_from_path( 'conditions', Condition::class );
	}

	/**
	 * @param $path
	 * @param $type_class
	 */
	public static function load_files_from_path( $path, $type_class ) {
		$full_path = __DIR__ . '/' . $path;

		/* convert path to namespace: '/fields/user' -> '\\Fields\\User' */
		$namespace = implode( '/', array_map( 'ucfirst', explode( '/', $path ) ) );
		$namespace = str_replace( '/', '\\', $namespace );

		static::include_classes_from_folder_recursive( $full_path, $namespace, $type_class );
	}

	/**
	 * @param $path
	 * @param $namespace
	 * @param $type_class
	 */
	public static function include_classes_from_folder_recursive( $path, $namespace, $type_class ) {
		$items = array_diff( scandir( $path ), [ '.', '..' ] );

		foreach ( $items as $item ) {
			$item_path = $path . '/' . $item;

			/* if the item is a folder, enter it and do recursion */
			if ( is_dir( $item_path ) ) {
				static::include_classes_from_folder_recursive( $item_path, $namespace, $type_class );
			}

			/* if the item is a file, include it */
			if ( is_file( $item_path ) && substr( $item_path, - 3 ) === 'php' ) {
				require_once $item_path;

				/* for each file, dynamically call the init function of the class */
				if ( preg_match( '/class-(.*).php/m', $item, $m ) && ! empty( $m[1] ) ) {
					$class_name = \TCB_ELEMENTS::capitalize_class_name( $m[1] );

					$class = __NAMESPACE__ . '\\' . $namespace . '\\' . $class_name;

					$type_class::register( $class );
				}
			}
		}
	}

	/**
	 * Get all the data that we want to localize for Conditional Display - the rest is fetched on demand through REST
	 *
	 * @return array
	 */
	public static function get_localized_data() {
		return [
			'entities' => Entity::get_data_to_localize(),
		];
	}
}
