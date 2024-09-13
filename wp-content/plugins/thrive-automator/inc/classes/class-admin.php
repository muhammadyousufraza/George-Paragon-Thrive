<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-automator
 */

namespace Thrive\Automator;

use Thrive\Automator\Suite\TTW;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Admin
 *
 * @package Thrive\Automator
 */
class Admin {

	const PAGE_SLUG = 'toplevel_page_thrive_automator';

	public static function includes() {

		require_once TAP_PLUGIN_PATH . 'inc/functions.php';

		require_once TAP_PLUGIN_PATH . 'inc/traits/trait-automation-item.php';

		require_once TAP_PLUGIN_PATH . 'inc/classes/class-hooks.php';

		require_once TAP_PLUGIN_PATH . 'inc/classes/items/abstract/class-app.php';
		require_once TAP_PLUGIN_PATH . 'inc/classes/items/abstract/class-action-field.php';
		require_once TAP_PLUGIN_PATH . 'inc/classes/items/abstract/class-action.php';
		require_once TAP_PLUGIN_PATH . 'inc/classes/items/abstract/class-data-object.php';
		require_once TAP_PLUGIN_PATH . 'inc/classes/items/abstract/class-data-field.php';
		require_once TAP_PLUGIN_PATH . 'inc/classes/items/abstract/class-filter.php';
		require_once TAP_PLUGIN_PATH . 'inc/classes/items/abstract/class-trigger.php';
		require_once TAP_PLUGIN_PATH . 'inc/classes/items/abstract/class-trigger-field.php';

		require_once TAP_PLUGIN_PATH . 'inc/classes/items/class-delay.php';

		require_once TAP_PLUGIN_PATH . 'inc/classes/items/class-automations.php';
		require_once TAP_PLUGIN_PATH . 'inc/classes/items/class-automation.php';
		require_once TAP_PLUGIN_PATH . 'inc/classes/items/class-automation-data.php';

		require_once TAP_PLUGIN_PATH . 'inc/classes/class-tap-internal-rest-controller.php';
		require_once TAP_PLUGIN_PATH . 'inc/classes/class-tap-integrations-rest-controller.php';
		require_once TAP_PLUGIN_PATH . 'inc/classes/class-tap-errorlog-rest-controller.php';
		require_once TAP_PLUGIN_PATH . 'inc/classes/class-tap-utils.php';
		require_once TAP_PLUGIN_PATH . 'inc/classes/class-tap-db.php';
		require_once TAP_PLUGIN_PATH . 'inc/classes/class-tap-error-handler.php';
		require_once TAP_PLUGIN_PATH . 'inc/classes/class-tap-limitations-handler.php';
		require_once TAP_PLUGIN_PATH . 'inc/classes/class-file-loader.php';
		require_once TAP_PLUGIN_PATH . 'inc/classes/class-tap-reset.php';

		require_once TAP_PLUGIN_PATH . 'inc/classes/database/class-tap-db-migration.php';
		require_once TAP_PLUGIN_PATH . 'inc/classes/database/class-tap-database-manager.php';
		require_once TAP_PLUGIN_PATH . 'inc/classes/suite/class-ttw.php';
		require_once TAP_PLUGIN_PATH . 'inc/classes/class-tap-tracking.php';
		require_once TAP_PLUGIN_PATH . 'inc/classes/class-tap-deactivate.php';

	}

	public static function init() {
		static::includes();
		static::load_items();

		Hooks::init();
		TTW::init();
		Tracking::init();
		Deactivate::init();
	}

	/**
	 * Load all automator used classes
	 * Only called for automations, as in other cases they're being loaded locally
	 */
	public static function load_items() {
		Items\App::load( 'apps' );
		Items\Filter::load( 'filters' );
		Items\Data_Field::load( 'data-fields' );
		Items\Trigger_Field::load( 'trigger-fields' );
		Items\Action_Field::load( 'action-fields' );
		Items\Data_Object::load( 'data-objects' );
		Items\Trigger::load( 'triggers' );
		Items\Action::load( 'actions' );
	}

	/**
	 * Return the capabilities needed for user to run plugin
	 *
	 * @return string
	 */
	public static function get_capability(): string {
		return class_exists( 'TVE_Dash_Product_Abstract', false ) ? TAP_Product::cap() : 'manage_options';
	}

}
