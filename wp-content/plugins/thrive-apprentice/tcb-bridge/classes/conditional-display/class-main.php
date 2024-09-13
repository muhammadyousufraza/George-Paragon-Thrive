<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

namespace TVA\Architect\ConditionalDisplay;

use TCB_ELEMENTS;
use TVA\Architect\Visual_Builder\Hooks;
use TVA_Const;
use TVA_Course_Overview_Post;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Main
 *
 * @package TVA\Architect\ConditionalDisplay
 */
class Main {
	public static function init() {
		static::load_classes( 'entities', 'entity' );
		static::load_classes( 'fields', 'field' );

		Main::add_hooks();
	}

	private static function add_hooks() {
		add_filter( 'tcb_conditional_display_post_excluded_types', [ __CLASS__, 'exclude_post_types' ] );

		add_action( 'tcb_set_query_vars_data', [ __CLASS__, 'set_query_vars_data' ] );
	}

	public static function exclude_post_types( $excluded_post_types ) {
		array_push( $excluded_post_types, TVA_Const::ASSESSMENT_POST_TYPE, TVA_Const::MODULE_POST_TYPE, TVA_Const::LESSON_POST_TYPE, TVA_Const::CHAPTER_POST_TYPE, TVA_Course_Overview_Post::POST_TYPE );

		return $excluded_post_types;
	}

	public static function set_query_vars_data() {
		add_filter( 'tva_visual_builder_is_editor_ajax', '__return_true' );

		( new Hooks() )->set_objects();
	}

	public static function load_classes( $folder, $type ) {
		$path = __DIR__ . '/' . $folder;

		foreach ( array_diff( scandir( $path ), [ '.', '..' ] ) as $item ) {

			if ( preg_match( '/class-(.*).php/m', $item, $m ) && ! empty( $m[1] ) ) {
				$class_name = TCB_ELEMENTS::capitalize_class_name( $m[1] );

				$class = __NAMESPACE__ . '\\' . ucfirst( $folder ) . '\\' . $class_name;

				$register_fn = 'tve_register_condition_' . $type;
				$register_fn( $class );
			}
		}
	}
}
