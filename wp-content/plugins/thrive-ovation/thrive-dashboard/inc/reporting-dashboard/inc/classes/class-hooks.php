<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */

namespace TVE\Reporting;

use TVE\Reporting\Automator\Event_Key;
use TVE\Reporting\Automator\Register_Event_Action;
use TVE\Reporting\EventFields\User_Id;

class Hooks {

	protected static $actions = [
		'init',
		'deleted_user'
		/*'thrive_automator_init' - not right now */
	];

	public static function register() {
		foreach ( static::$actions as $action ) {
			if ( is_array( $action ) ) {
				if ( method_exists( __CLASS__, $action[0] ) ) {
					add_action( $action, [ __CLASS__, $action[0] ], $action[1] ?? 10, $action[2] ?? 1 );
				}
			} elseif ( is_string( $action ) && method_exists( __CLASS__, $action ) ) {
				add_action( $action, [ __CLASS__, $action ] );
			}
		}
	}

	public static function init() {
		do_action( 'thrive_reporting_init' );

		do_action( 'thrive_reporting_register_events', Store::get_instance() );

		do_action( 'thrive_reporting_register_report_apps', Store::get_instance() );

		Main::add_shortcodes();
	}

	public static function thrive_automator_init() {
		require __DIR__ . '/automator/class-register-event-action.php';
		require __DIR__ . '/automator/class-event-key.php';

		thrive_automator_register_action( Register_Event_Action::class );
		thrive_automator_register_action_field( Event_Key::class );
	}

	/**
	 * @param $id
	 *
	 * @return void
	 */
	public static function deleted_user( $id ) {
		Logs::get_instance()->remove_by( User_Id::key(), $id );
	}
}
