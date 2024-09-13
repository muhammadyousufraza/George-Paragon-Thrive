<?php

namespace Thrive\Automator\Items;

use Exception;
use Thrive\Automator\Traits\Automation_Item;
use Thrive\Automator\Utils;
use function Thrive\Automator\tap_logger;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Action
 *
 * base class for all actions
 * basic shape of a action
 */
abstract class Action {

	use Automation_Item;

	/**
	 * Current automation id
	 *
	 * @var int|mixed
	 */
	protected $aut_id;

	protected $data;

	protected $initial_data;

	/**
	 * Get the action identifier. Must be unique across all the other actions
	 *
	 * @return string
	 */
	abstract public static function get_id();

	/**
	 * Get the action name/label
	 *
	 * @return string
	 */
	abstract public static function get_name();

	/**
	 * Short description of the action that will be displayed in the tooltip
	 *
	 * @return string
	 */
	abstract public static function get_description();

	/**
	 * Return the image that will be displayed near the action - 32 x 32 ideally
	 *
	 * @return string
	 */
	abstract public static function get_image();

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
	 * Array of action-field keys, required for the action to be setup.
	 * Those fields will be used in the admin UI when setting an automation. e.g. post_id, user_tag, field_value
	 * e.g. if you want to send an email you would require the email field
	 *
	 * @return array
	 * @see Action_Field
	 */
	abstract public static function get_required_action_fields();

	/**
	 * Array of action-field keys, required for the action to be setup.
	 * This can be used to change action's fields mapping based on values set by the user
	 *
	 * @param $data - current data set inside the action
	 *
	 * @return array
	 */
	public static function get_action_mapped_fields( $data ) {
		return static::get_required_action_fields();
	}

	/**
	 * Get an array of keys with the required data-objects
	 * Those are the data objects provided by triggers that this action needs in order to run properly.
	 * e.g. if your action does a change to a user => it needs 'user_data' => it's only compatible with triggers that provide 'user_data'.
	 * Leave empty if this action doesn't require any data from the trigger and it can run on any case.
	 *
	 * @return array
	 */
	abstract public static function get_required_data_objects();


	/**
	 * The functionality that will be run when a trigger is executed and all filters are valid
	 *
	 * @param array $data
	 */
	abstract public function do_action( $data );

	/**
	 * Summary description text
	 */
	public static function get_summary_text() {
		return static::get_description();
	}

	/**
	 * If the action adds own data object to $automation_data we need to let the editor know to allow corresponding actions
	 */
	public static function provides_data_objects() {
		return [];
	}

	/**
	 * Get action information mostly to be localized in the admin dashboard
	 */
	final public static function get_info(): array {
		return [
			'id'               => static::get_id(),
			'name'             => static::get_name(),
			'image'            => static::get_image(),
			'description'      => static::get_description(),
			'app_id'           => static::get_app_id(),
			'requires_params'  => static::get_required_data_objects(),
			'summary_text'     => static::get_summary_text(),
			'interface_fields' => static::localize_action_fields(),
			'fields'           => static::get_required_action_fields(),
			'provided_params'  => static::provides_data_objects(),
			'search_keywords'  => static::get_search_keywords(),
			'is_top_level'     => static::is_top_level(),
			'has_access'       => static::has_access(),
			'access_url'       => static::get_acccess_url(),
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
	 * Get additional search keywords for this trigger
	 *
	 * @return array
	 */
	public static function get_search_keywords() {
		return [];
	}

	/**
	 * Validate extended class data
	 *
	 * @throws Exception
	 */
	final protected static function validate( $action ) {
		if ( empty( $action['requires_params'] ) || ! is_array( $action['requires_params'] ) ) {
			throw new Exception( 'Missing  ' . $action['id'] . ' requires_params or mismatch filter compatible type' );
		}

		return $action;
	}

	/**
	 * Get action-fields required for setting up the action
	 *
	 * @return array
	 * @throws Exception
	 * @see Action::get_required_action_fields()
	 * @see Action_Field
	 */
	public static function localize_action_fields(): array {
		$fields           = [];
		$available_fields = Action_Field::get();

		foreach ( static::get_required_action_fields() as $key => $field ) {
			if ( ! is_numeric( $key ) ) {
				$field = $key;
			}

			if ( empty( $available_fields[ $field ] ) ) {
				/* If we require a certain action field, but it doesn't exist, or it's not registered */
				throw new Exception( 'Could not find action field ' . $field . ' in list' );
			}

			$fields[ $field ] = $available_fields[ $field ]::localize();
		}

		return $fields;
	}

	/**
	 * Default callback for getting action-field subfields, if the value of a certain field influences what other fields are needed for setting up an action
	 * Can be overwritten
	 *
	 * @param $subfields     - requested subfields
	 * @param $current_value - field value
	 * @param $action_data   - whole action fields data
	 *
	 * @return array
	 * @throws Exception
	 */
	public static function get_subfields( $subfields, $current_value, $action_data ) {
		$subfields_data = [];

		if ( ! empty( $subfields ) ) {
			$available_fields = Action_Field::get();

			foreach ( $subfields as $subfield ) {
				if ( ! empty( $available_fields[ $subfield ] ) ) {
					$state_field = $available_fields[ $subfield ];
					$state_data  = $state_field::localize();

					if ( ! empty( $state_data['is_ajax_field'] ) ) {
						unset( $state_data['is_ajax_field'] );
						$filters              = [
							'action_id'   => static::get_id(),
							'action_data' => $action_data,
							'search'      => '',
							'page'        => 0,
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
	 * Check if this action is compatible with a specific data objects
	 *
	 * @param $provided_data_objects
	 *
	 * @return bool
	 */
	public static function is_compatible_with_trigger( $provided_data_objects ) {
		$action_data_objects = static::get_required_data_objects() ?: [];

		return count( array_intersect( $action_data_objects, $provided_data_objects ) ) === count( $action_data_objects );
	}

	/**
	 * Required properties for action implementation
	 *
	 * @return string[]
	 */
	final public static function required_properties(): array {
		return [
			'get_id'                     => 'string',
			'get_name'                   => 'string',
			'get_image'                  => 'string',
			'get_description'            => 'string',
			'get_app_id'                 => 'string',
			'get_summary_text'           => 'string',
			'get_required_action_fields' => 'array',
			'get_required_data_objects'  => 'array',
		];
	}

	/**
	 * @param array $data
	 */
	public function __construct( array $data = [], $aut_id = 0 ) {
		if ( ! empty( $data ) ) {
			$this->data         = $data['extra_data'] ?? [];
			$this->initial_data = $this->data;
		}
		$this->aut_id = $aut_id;
	}

	/**
	 * Search & replace potential shortcodes set as field value
	 *
	 * @param $action_data
	 */
	final public function replace_shortcodes( &$action_data ) {
		global $automation_data;
		$automation_data_array = $automation_data->get_all();
		if ( ! empty( $action_data ) && ! empty( $automation_data_array ) ) {
			$action_fields = Action_Field::get();

			foreach ( $action_data as $key => &$item ) {
				/**
				 * Replace only fields that allow dynamic data as their value
				 */
				if ( ! empty( $action_fields[ $key ] ) && $action_fields[ $key ]::allow_dynamic_data() ) {

					foreach ( $automation_data_array as $data_object ) {

						/**
						 * Support data that are not object while replacing dynamic shortcodes
						 */
						if ( is_array( $data_object ) ) {
							foreach ( $data_object as $field_id => $field_value ) {
								$data_field = Data_Field::get_by_id( $field_id );
								if ( $data_field && $data_field::get_shortcode_tag() ) {
									$item['value'] = Utils::replace_data_shortcode( $item['value'], $data_field::get_shortcode_tag(), $field_value );
								}
							}
						} else if ( $data_object instanceof Data_Object && ! empty( $item['value'] ) ) {
							$item['value'] = $data_object->replace_dynamic_data( $item['value'] );
						}
					}
				}
				/**
				 * Replace potential shortcodes of subfields
				 */
				if ( ! empty( $item['subfield'] ) ) {
					$this->replace_shortcodes( $item['subfield'] );
				}
			}
		}
	}

	/**
	 * Extend constructor functionality
	 */
	public function prepare_data( $data = [] ) {
	}

	/**
	 * Getter for data fields or data array
	 *
	 * @param string $field if empty, return the whole data array
	 * @param mixed  $default
	 *
	 * @return mixed|null
	 */
	final public function get_automation_data( string $field = null, $default = null ) {
		if ( $field === null ) {
			return $this->data;
		}

		return $this->data[ $field ] ?? $default;
	}

	/**
	 * Get value of input set in action while automation setup
	 *
	 * @param      $setting_name
	 * @param null $default
	 *
	 * @return mixed|null
	 */
	final public function get_automation_data_value( $setting_name, $default = null ) {
		return $this->get_automation_data( $setting_name, [] )['value'] ?? $default;
	}

	/**
	 * Get action information wrapper
	 *
	 * @return array
	 */
	final public function localize_data(): array {
		return [
			'id'   => static::get_id(),
			'info' => static::get_info(),
			'data' => $this->get_automation_data(),
		];
	}

	/**
	 * Check to see if the action has all the required data, so it can run.
	 * Can be overridden in order to implement custom functionality
	 *
	 * @return bool
	 */
	public function can_run( $data ) {
		$valid = true;
		global $automation_data;
		foreach ( static::get_required_data_objects() as $key ) {
			/**
			 * log if data is not defined or if the data object doesnt have any data set
			 */
			$data_object = $automation_data->get( $key );
			if ( empty( $data_object ) || ( is_subclass_of( $data_object, Data_Object::class ) && empty( $data_object->get_data() ) ) ) {
				tap_logger( $this->aut_id )->register( [
					'key'         => static::get_id(),
					'id'          => 'data-not-provided-to-action',
					'message'     => 'Data object required by ' . static::class . ' action is not provided by trigger',
					'class-label' => tap_logger( $this->aut_id )->get_nice_class_name( static::class ),
				] );
				$valid = false;
			}
		}

		return $valid;
	}

	/**
	 * Wrapper function for the actual action operation
	 */
	final public function run() {
		global $automation_data;
		if ( $this->can_run( $automation_data ) ) {
			if ( ! empty( $this->data ) ) {
				/**
				 * Replace potential shortcodes from Data_Object are re-prepare_data so the action has is fields properly filled
				 * make sure that every time the action will run the shortcodes will be replaced with the newest values
				 */
				$this->data = $this->initial_data;
				$this->replace_shortcodes( $this->data );
				$this->prepare_data( $this->data );
			}

			Utils::update_meta_counter( $this->get_automation_id(), 'action_run_' . static::get_id() );

			$this->do_action( $automation_data );

			tap_logger( $this->aut_id )->log_success( [
				'key'         => static::get_id(),
				'id'          => 'action-success',
				'message'     => 'Action ' . static::class . ' successfully executed',
				'class-label' => tap_logger( $this->aut_id )->get_nice_class_name( static::class ),
			] );
		}
	}

	/**
	 * Whether the action should be displayed outside the app or not
	 *
	 * @return false
	 */
	public static function is_top_level() {
		return false;
	}

	/**
	 * Hide existing action
	 */
	public static function hidden() {
		return false;
	}

	/**
	 * While setting up the automation, certain trigger fields/actions may alter the provided data objects and some more actions may be enabled.
	 *
	 * @throws Exception
	 */
	public static function sync_action_data( $data ) {

		return $data;
	}
}
