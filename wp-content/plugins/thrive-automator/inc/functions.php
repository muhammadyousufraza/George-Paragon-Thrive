<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-university
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

use Thrive\Automator\Items\Action;
use Thrive\Automator\Items\Action_Field;
use Thrive\Automator\Items\Data_Field;
use Thrive\Automator\Items\Data_Object;
use Thrive\Automator\Items\Filter;
use Thrive\Automator\Items\Trigger;
use Thrive\Automator\Items\Trigger_Field;

/**
 * Register new app
 *
 * @param Action|string $app
 */
function thrive_automator_register_app( $app ) {
	Thrive\Automator\Items\App::register( $app );
}

/**
 * Register new action
 *
 * @param Action|string $action
 */
function thrive_automator_register_action( $action ) {
	Thrive\Automator\Items\Action::register( $action );
}

/**
 * Register new trigger
 *
 * @param Trigger|string $trigger
 */
function thrive_automator_register_trigger( $trigger ) {
	Thrive\Automator\Items\Trigger::register( $trigger );
}

/**
 * Register new data_field
 *
 * @param Data_Field|string $field
 */
function thrive_automator_register_data_field( $field ) {
	Thrive\Automator\Items\Data_Field::register( $field );
}

/**
 * Register new data_object
 *
 * @param Data_Object|string $data_object
 */
function thrive_automator_register_data_object( $data_object ) {
	Thrive\Automator\Items\Data_Object::register( $data_object );
}

/**
 * Register new action_field
 *
 * @param Action_Field|string $action_field
 */
function thrive_automator_register_action_field( $action_field ) {
	Thrive\Automator\Items\Action_Field::register( $action_field );
}

/**
 * Register new trigger_field
 *
 * @param Trigger_Field|string $trigger_field
 */
function thrive_automator_register_trigger_field( $trigger_field ) {
	Thrive\Automator\Items\Trigger_Field::register( $trigger_field );
}

/**
 * Register new filter
 *
 * @param Filter|string $filter
 */
function thrive_automator_register_filter( $filter ) {
	Thrive\Automator\Items\Filter::register( $filter );
}
