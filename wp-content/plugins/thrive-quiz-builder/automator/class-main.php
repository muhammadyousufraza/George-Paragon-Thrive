<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

namespace TQB\Automator;

use function thrive_automator_register_action;
use function thrive_automator_register_action_field;
use function thrive_automator_register_data_field;
use function thrive_automator_register_data_object;
use function thrive_automator_register_trigger;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Main
 *
 * @package TVE\Automator
 */
class Main {

	public static function init() {
		if ( defined( 'THRIVE_AUTOMATOR_RUNNING' )
		     && ( ( defined( 'TVE_DEBUG' ) && TVE_DEBUG )
		          || ( defined( 'TAP_VERSION' ) && version_compare( TAP_VERSION, '1.0', '>=' ) ) ) ) {
			static::add_hooks();
		}
	}

	/**
	 * @param string $subpath
	 *
	 * @return string
	 */
	public static function get_integration_path( $subpath = '' ) {
		return tqb()->plugin_path( 'automator/' . $subpath );
	}

	public static function add_hooks() {
		add_action( 'tap_output_extra_svg', array( 'TQB\Automator\Main', 'display_icons' ) );
		add_filter( 'tvd_automator_api_data_sets', array( 'TQB\Automator\Main', 'dashboard_sets' ), 10, 1 );
		static::load_apps();
		static::load_data_objects();
		static::load_fields();
		static::load_triggers();

	}

	public static function load_apps() {
		foreach ( static::load_files( 'apps' ) as $app ) {
			thrive_automator_register_app( new $app() );
		}
	}

	public static function load_triggers() {
		foreach ( static::load_files( 'triggers' ) as $trigger ) {
			thrive_automator_register_trigger( new $trigger() );
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

		$local_classes = array();

		foreach ( glob( $integration_path . '/*.php' ) as $file ) {
			require_once $file;

			$class = 'TQB\Automator\\' . static::get_class_name_from_filename( $file );

			if ( class_exists( $class ) ) {
				$local_classes[] = $class;
			}
		}

		return $local_classes;
	}

	public static function get_class_name_from_filename( $filename ) {
		$name = str_replace( array( 'class-', '-trigger', '-action' ), '', basename( $filename, '.php' ) );

		return str_replace( '-', '_', ucwords( $name, '-' ) );
	}

	public static function display_icons() {
		include static::get_integration_path( 'icons.svg' );
	}

	/**
	 * Enroll quiz_data as data that can be used in TD for Automator actions
	 *
	 * @param $sets
	 *
	 * @return mixed
	 */
	public static function dashboard_sets( $sets ) {
		$sets[] = 'quiz_data';

		return $sets;
	}
}
