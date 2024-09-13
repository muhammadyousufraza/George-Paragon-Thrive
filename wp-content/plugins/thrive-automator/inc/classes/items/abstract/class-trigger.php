<?php

namespace Thrive\Automator\Items;

use Exception;
use Thrive\Automator\Traits\Automation_Item;
use Thrive\Automator\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Trigger
 *
 * base class for all triggers
 * basic shape of a trigger
 */
abstract class Trigger {

	use Automation_Item;

	/**
	 * Actual trigger settings, most cases this will contain the identifier
	 */
	protected $data;

	/**
	 * Initial filters set at trigger level
	 */
	protected $conditions = [];
	/**
	 * Current automation id
	 *
	 * @var int|mixed
	 */
	protected $aut_id;

	public function __construct( array $data = [], $aut_id = 0 ) {
		if ( ! empty( $data ) ) {
			$this->data = $data['extra_data'] ?? [];
			if ( ! empty( $data['conditions'] ) ) {
				$this->set_conditions( Automation::get_filter_instances( $data['conditions'], [], $aut_id ) );
			}
		}
		$this->aut_id = $aut_id;
	}

	/**
	 * Return the trigger id which will be used to uniquely identify it
	 *
	 * @return string
	 */
	abstract public static function get_id();

	/**
	 * Return a string containing a wordpress action that will be used.
	 * If your current functionality doesn't use a wordpress action, one should be implemented so we can hook to it and execute an automation based on it
	 *
	 * @return string
	 */
	abstract public static function get_wp_hook();

	/**
	 * An array of data_object keys that this trigger provides for actions.
	 * Only actions that require those params will be displayed to interact with the trigger.
	 * Keys from this array must match id from Data_Object
	 * Keys must be in the same order as provided in the do_action hook so we can match them automatically
	 *
	 * @return array
	 * @see Data_Object::get_id()
	 *
	 */
	abstract public static function get_provided_data_objects();

	/**
	 * The number of params the WordPress action has. Will be used when adding implementing do_action
	 *
	 * @return int
	 * @see do_action()
	 */
	abstract public static function get_hook_params_number();

	/**
	 * Array of trigger-field keys, required for the trigger to be setup.
	 * Those fields will be used in the admin UI when setting an automation. e.g. post_id, user_tag, field_value
	 * e.g. if you want to send an email you would require the email field
	 *
	 * @return array
	 * @see Action_Field
	 */
	public static function get_required_trigger_fields() {
		return [];
	}

	/**
	 * Get the hook priority
	 *
	 * @return int
	 */
	public static function get_hook_priority() {
		return 10;
	}

	/**
	 * Get the id of the app to which the hook belongs.
	 * If you have multiple triggers from the same app, make sure to have the same id here
	 *
	 * @return string
	 */
	public static function get_app_id() {
		return 'general';
	}

	/**
	 * Get the trigger name
	 *
	 * @return string
	 */
	abstract public static function get_name();

	/**
	 * Get the trigger description
	 *
	 * @return string
	 */
	abstract public static function get_description();

	/**
	 * Return the image that will be displayed near the trigger - 32 x 32 ideally
	 *
	 * @return string
	 */
	abstract public static function get_image();

	/**
	 * Default function to link data-objects to the provided params.
	 * Process data that comes from the trigger and prepare it as Data_Objects for filters and actions
	 *
	 * @param array $params
	 *
	 * @return Data_Object[]
	 * @see Automation::start()
	 */
	public function process_params( $params = [] ) {

		$data_objects = [];

		if ( ! empty( $params ) ) {
			/* get all registered data objects and see which ones we use for this trigger */
			$data_object_classes = Data_Object::get();
			/* !!! provided params need to be in the same order as params from the trigger */
			$trigger_provided_params = static::get_provided_data_objects();

			foreach ( $trigger_provided_params as $index => $data_object_key ) {
				if ( empty( $data_object_classes[ $data_object_key ] ) ) {
					/* if we don't have a class that parses the current param, we just leave the value as it is */
					$data_objects[ $data_object_key ] = $params[ $index ];
				} else {
					/* when a data object is available for the current parameter key, we create an instance that will handle the data */
					$data_objects[ $data_object_key ] = new $data_object_classes[ $data_object_key ]( $params[ $index ], $this->get_automation_id() );
				}
			}
		}

		return $data_objects;
	}


	/**
	 * Triggers that are scheduled are handled differently using wordpress cron
	 *
	 * @return bool
	 */
	public static function is_single_scheduled_event() {
		return false;
	}

	/**
	 * Before the automation is executed, manage the settings from the editor
	 *
	 * @return array|false|int|string
	 */
	public function prepare_data( $data = [] ) {
		return [];
	}

	/**
	 * Get wp hook that triggers this item in automation context
	 * This is used for dynamic individual hooks of same trigger
	 *
	 * @return string
	 */
	public function get_automation_wp_hook() {
		return static::get_wp_hook();
	}

	/**
	 * Get actions that match their required params to those provided by the trigger
	 *
	 * @return array
	 */
	final public static function get_trigger_matching_actions( $data_objects = [] ): array {
		$actions = [];
		if ( empty( $data_objects ) ) {
			$data_objects = static::get_provided_data_objects();
		}
		foreach ( Action::get() as $action ) {
			if ( $action::is_compatible_with_trigger( $data_objects ) ) {
				$actions[] = $action::get_id();
			}
		}

		return $actions;
	}

	/**
	 * Get trigger information
	 *
	 * @return array
	 * @throws Exception
	 */
	final public static function get_info(): array {
		/* global data is always available for all triggers */
		$provided_data_objects = array_merge( static::get_provided_data_objects(), [ TAP_GLOBAL_DATA_OBJECT ] );
		$fields                = static::get_required_trigger_fields();

		return [
			'id'                => static::get_id(),
			'name'              => static::get_name(),
			'image'             => static::get_image(),
			'description'       => static::get_description(),
			'provided_params'   => $provided_data_objects,
			'hook'              => static::get_wp_hook(),
			'app_id'            => static::get_app_id(),
			'priority'          => static::get_hook_priority(),
			'filterable_fields' => Data_Object::get_all_filterable_fields( $provided_data_objects ),
			'interface_fields'  => empty( $fields ) ? [] : static::localize_trigger_fields(),
			'fields'            => empty( $fields ) ? [] : $fields,
			'search_keywords'   => static::get_search_keywords(),
			'is_top_level'      => static::is_top_level(),
			'has_access'        => static::has_access(),
			'access_url'        => static::get_acccess_url(),
		];
	}

	/**
	 * Whether the trigger is available or not
	 *
	 * @return bool
	 */
	public static function has_access() {
		return true;
	}

	/**
	 * How to access the trigger if not available
	 *
	 * @return string
	 */
	public static function get_acccess_url() {
		return '';
	}

	/**
	 * Whether the trigger should be displayed outside the app or not
	 *
	 * @return false
	 */
	public static function is_top_level() {
		return false;
	}

	/**
	 * Get additional search keywords for this trigger
	 *
	 * @return array
	 */
	public static function get_search_keywords() {
		return [];
	}

	/**
	 * Get trigger information wrapper
	 *
	 * @return array
	 */
	final public function localize_data(): array {
		return [
			'id'   => static::get_id(),
			'info' => static::get_info(),
			'data' => $this->data,
		];
	}

	/**
	 * A list of required properties for the trigger and their data type
	 *
	 * @return string[]
	 */
	final public static function required_properties(): array {
		return [
			'get_id'                    => 'string',
			'get_name'                  => 'string',
			'get_image'                 => 'string',
			'get_description'           => 'string',
			'get_provided_data_objects' => 'array',
			'get_wp_hook'               => 'string',
			'get_app_id'                => 'string',
			'get_hook_priority'         => 'integer',
			'get_hook_params_number'    => 'integer',
		];
	}

	/**
	 * Set trigger conditions (initial filters set at trigger level)
	 */
	final public function set_conditions( $filters ) {
		$this->conditions = $filters;
	}

	/**
	 * Get trigger conditions
	 *
	 * @return array
	 */
	final public function get_conditions(): array {
		return $this->conditions;
	}

	/**
	 * Get action-fields required for setting up the action
	 *
	 * @return array
	 * @throws Exception
	 * @see Trigger::get_required_action_fields()
	 * @see Trigger_Field
	 */
	public static function localize_trigger_fields(): array {
		$fields           = [];
		$available_fields = Trigger_Field::get();

		foreach ( static::get_required_trigger_fields() as $key => $field ) {
			if ( ! is_numeric( $key ) ) {
				$field = $key;
			}

			if ( empty( $available_fields[ $field ] ) ) {
				/* If we require a certain trigger field, but it doesn't exist, or it's not registered */
				throw new Exception( 'Could not find trigger field ' . $field . ' in list' );
			}

			$fields[ $field ] = $available_fields[ $field ]::localize();
		}

		return $fields;
	}

	/**
	 * Default callback for getting action-field subfields, if the value of a certain field influences what other fields are needed for setting up an action
	 * Can be overwritten
	 *
	 * @param $subfields - requested subfields
	 * @param $current_value - field value
	 * @param $action_data - whole action fields data
	 *
	 * @return array
	 */
	public static function get_subfields( $subfields, $current_value, $trigger_data ) {
		$subfields_data = [];

		if ( ! empty( $subfields ) ) {
			$available_fields = Trigger_Field::get();

			foreach ( $subfields as $subfield ) {
				if ( ! empty( $available_fields[ $subfield ] ) ) {
					$state_field = $available_fields[ $subfield ];
					$state_data  = $state_field::localize();

					if ( ! empty( $state_data['is_ajax_field'] ) ) {
						unset( $state_data['is_ajax_field'] );
						$filters              = [
							'trigger_id'   => static::get_id(),
							'trigger_data' => $trigger_data,
							'search'       => '',
							'page'         => 0,
						];
						$state_data['values'] = $state_field::get_field_values( $filters );
					}

					$subfields_data[ $state_data['id'] ] = $state_data;
				}
			}
		}

		return $subfields_data;
	}

	/**
	 * Hide existing trigger
	 */
	public static function hidden() {
		return false;
	}

	/**
	 * While setting up the automation, certain trigger fields may alter the provided data objects and some more actions may be enabled.
	 *
	 * @throws Exception
	 */
	public static function sync_trigger_data( $trigger_data ) {
		return $trigger_data;
	}
}
