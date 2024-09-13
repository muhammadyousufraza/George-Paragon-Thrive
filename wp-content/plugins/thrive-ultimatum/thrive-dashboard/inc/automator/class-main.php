<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

namespace TVE\Dashboard\Automator;

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
 * @package TVE\Dashboard\Automator
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
		return TVE_DASH_PATH . '/inc/automator/' . $subpath;
	}

	public static function add_hooks() {
		add_filter( 'td_automator_should_load_file', [ __CLASS__, 'should_load_slack_files' ], 10, 2 );
		static::load_extra_classes();

		static::load_apps();
		static::load_data_objects();
		static::load_fields();
		static::load_action_fields();
		static::load_actions();
		static::load_trigger_fields();
		static::load_triggers();

		add_action( 'tap_output_extra_svg', array( 'TVE\Dashboard\Automator\Main', 'display_icons' ) );
	}

	/**
	 * Load additional classes needed for integrations
	 *
	 * @return void
	 */
	public static function load_extra_classes() {
		require_once TVE_DASH_PATH . '/inc/automator/class-tap-elementor.php';
		Elementor::init();

		require_once TVE_DASH_PATH . '/inc/automator/class-tap-woo.php';
		Woo::init();

		require_once TVE_DASH_PATH . '/inc/automator/class-tap-facebook.php';
		Facebook::init();
	}

	public static function load_triggers() {
		foreach ( static::load_files( 'triggers' ) as $trigger ) {
			thrive_automator_register_trigger( new $trigger() );
		}
	}

	public static function load_trigger_fields() {
		foreach ( static::load_files( 'trigger-fields' ) as $trigger ) {
			thrive_automator_register_trigger_field( new $trigger() );
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

	public static function load_apps() {
		foreach ( static::load_files( 'apps' ) as $app ) {
			thrive_automator_register_app( new $app() );
		}
	}

	public static function load_files( $type ) {
		$integration_path = static::get_integration_path( $type );

		$local_classes = array();
		foreach ( glob( $integration_path . '/*.php' ) as $file ) {

			if ( static::should_load( $file ) ) {
				require_once $file;

				$class = 'TVE\Dashboard\Automator\\' . static::get_class_name_from_filename( $file );

				if ( class_exists( $class ) && ! $class::hidden() ) {
					$local_classes[] = $class;
				}
			}
		}


		return $local_classes;
	}

	public static function get_class_name_from_filename( $filename ) {
		$name = str_replace( array( 'class-', '-action', '-trigger' ), '', basename( $filename, '.php' ) );

		return str_replace( '-', '_', ucwords( $name, '-' ) );
	}

	public static function display_icons() {
		include static::get_integration_path( 'icons.svg' );
	}

	public static function should_load( $filename ) {
		return apply_filters( 'td_automator_should_load_file', true, $filename );
	}


	/**
	 * Filter the data objects that might provide user data
	 */
	public static function get_email_data_sets() {
		$data_sets = apply_filters( 'tvd_automator_api_data_sets', [] );
		/**
		 * Make sure that user_data is always the last item
		 */
		$data_sets   = array_diff( $data_sets, [ 'email_data', 'user_data' ] );
		$data_sets[] = 'email_data';
		$data_sets[] = 'user_data';

		return $data_sets;
	}

	public static function should_load_slack_files( $load, $filename ) {
		if ( strpos( basename( $filename, '.php' ), '-slack-' ) !== false && ! static::slack_exists() ) {
			$load = false;
		}

		return $load;
	}

	/**
	 * ['key','value'] => ['key' => 'value']
	 *
	 *
	 * @param $data
	 *
	 * @return array
	 */
	public static function extract_mapping( $data ) {
		$mapping = [];
		foreach ( $data as $content ) {
			$mapping[ $content['key'] ] = $content['value'];
		}

		return $mapping;
	}

	public static function slack_exists() {
		$slack_instance = \Thrive_Dash_List_Manager::connection_instance( 'slack' );

		return $slack_instance !== null && $slack_instance->is_connected();
	}
}

add_action( 'thrive_automator_init', array( 'TVE\Dashboard\Automator\Main', 'init' ) );

