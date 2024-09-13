<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

namespace TVA\Automator;

use TVA_Const;
use TVA_Course_V2;
use TVA_Manager;
use function thrive_automator_register_action;
use function thrive_automator_register_action_field;
use function thrive_automator_register_app;
use function thrive_automator_register_data_field;
use function thrive_automator_register_data_object;
use function thrive_automator_register_trigger;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Main
 *
 * @package TVA\Automator
 */
class Main {

	public static function init() {
		if ( defined( 'THRIVE_AUTOMATOR_RUNNING' )
			 && ( ( defined( 'TVE_DEBUG' ) && TVE_DEBUG )
				  || defined( 'TAP_VERSION' ) && version_compare( TAP_VERSION, '0.11', '>=' ) ) ) {

			static::add_hooks();
		}
	}

	/**
	 * @param string $subpath
	 *
	 * @return string
	 */
	public static function get_integration_path( $subpath = '' ) {
		return TVA_Const::plugin_path( 'inc/classes/managers/automator/' . $subpath );
	}

	public static function add_hooks() {
		static::load_apps();
		static::load_data_objects();
		static::load_fields();
		static::load_action_fields();
		static::load_actions();
		static::load_triggers();

		add_action( 'tap_output_extra_svg', array( 'TVA\Automator\Main', 'display_icons' ) );
	}

	public static function load_triggers() {
		foreach ( static::load_files( 'triggers' ) as $trigger ) {
			thrive_automator_register_trigger( new $trigger() );
		}
	}

	public static function load_apps() {
		foreach ( static::load_files( 'apps' ) as $app ) {
			thrive_automator_register_app( new $app() );
		}
	}

	public static function load_actions() {
		foreach ( static::load_files( 'actions' ) as $action ) {
			thrive_automator_register_action( new $action() );
		}
	}

	public static function load_action_fields() {
		foreach ( static::load_files( 'action-fields' ) as $field ) {
			thrive_automator_register_action_field( new $field() );
		}
	}

	public static function load_fields() {
		foreach ( static::load_files( 'fields' ) as $field ) {
			thrive_automator_register_data_field( new $field() );
		}
	}

	public static function load_data_objects() {
		foreach ( static::load_files( 'data-objects' ) as $data_object ) {
			thrive_automator_register_data_object( new $data_object() );
		}
	}

	public static function load_files( $type ) {
		$integration_path = static::get_integration_path( $type );

		$local_classes = [];
		foreach ( glob( $integration_path . '/*.php' ) as $file ) {
			require_once $file;

			$class     = 'TVA\Automator\\' . static::get_class_name_from_filename( $file );
			$is_hidden = method_exists( $class, 'hidden' ) ? $class::hidden() : false;
			if ( class_exists( $class ) && ! $is_hidden ) {
				$local_classes[] = $class;
			}

		}

		return $local_classes;
	}

	public static function get_class_name_from_filename( $filename ) {
		$name = str_replace( [ 'class-', '-action', '-trigger' ], '', basename( $filename, '.php' ) );

		return str_replace( '-', '_', ucwords( $name, '-' ) );
	}

	public static function display_icons() {
		include static::get_integration_path( 'icons.svg' );
	}

	/**
	 * Get lessons for automator format
	 *
	 * @return array
	 */
	public static function get_lessons() {
		$data    = [];
		$courses = TVA_Course_V2::get_items( [ 'status' => 'publish' ] );
		foreach ( $courses as $course ) {
			$course_id = $course->get_id();

			$modules = TVA_Manager::get_course_modules( $course->get_wp_term() );
			if ( empty( $modules ) ) {
				$lessons = $course->get_all_lessons();
				foreach ( $lessons as $lesson ) {
					$data[ $course_id ]['items'][ $lesson->ID ] = [
						'label' => $lesson->post_title,
						'value' => $lesson->ID,
					];
				}
			} else {
				foreach ( $modules as $module ) {
					$lessons                                    = TVA_Manager::get_all_module_items( $module, [
						'post_type' => TVA_Const::LESSON_POST_TYPE,
					] );
					$data[ $course_id ]['items'][ $module->ID ] = [
						'label' => $module->post_title,
						'items' => [],
					];
					if ( ! empty( $lessons ) ) {
						foreach ( $lessons as $lesson ) {
							$data[ $course_id ]['items'][ $module->ID ]['items'][ $lesson->ID ] = [
								'label' => $lesson->post_title,
								'value' => $lesson->ID,
							];
						}
					}
				}
			}
			if ( ! empty( $data[ $course_id ]['items'] ) ) {
				$data[ $course_id ]['label'] = $course->name;
			}
		}

		return $data;
	}

	/**
	 * Get modules for automator format
	 *
	 * @return array
	 */
	public static function get_modules() {
		$data    = [];
		$courses = TVA_Course_V2::get_items( [ 'status' => 'publish' ] );
		foreach ( $courses as $course ) {
			$course_id = $course->get_id();

			$modules = TVA_Manager::get_course_modules( $course->get_wp_term() );
			if ( ! empty( $modules ) ) {
				$data[ $course_id ] = [
					'label' => $course->name,
					'items' => [],
				];
				foreach ( $modules as $module ) {
					$data[ $course_id ]['items'][ $module->ID ] = [
						'label' => $module->post_title,
						'value' => $module->ID,
					];
				}
			}
		}

		return $data;
	}
}
