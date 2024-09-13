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
 * Class Data_Object
 *
 * base class for all data objects
 * basic shape of a data objects
 */
abstract class Data_Object {

	use Automation_Item;

	/**
	 * Current automation id
	 *
	 * @var int|mixed
	 */
	protected $aut_id;

	/**
	 * Actual data
	 */
	protected $data = [];

	/**
	 * Magic getter
	 *
	 * @param string $prop
	 *
	 * @return mixed|null
	 */
	public function __get( $prop ) {
		return $this->get_value( $prop );
	}

	/**
	 * Get list of all items to localize
	 *
	 * @return array
	 * @throws Exception
	 */
	public static function localize_all(): array {
		$data_objects = static::get();
		$data         = [];
		$fields       = static::get_all_filterable_fields( array_keys( $data_objects ) );
		foreach ( $fields as $key => $fields_data ) {
			$data[ $key ] = [
				'fields' => $fields_data,
				'name'   => $data_objects[ $key ]::get_nice_name(),
			];
		}

		return $data;
	}

	/**
	 * Data Object name
	 *
	 * @return string
	 */
	public static function get_nice_name() {
		return '';
	}

	/**
	 * Create data-object from the data provided by the trigger and run the filter on the created object
	 *
	 * @param array $raw_data
	 */
	public function __construct( $raw_data = [], $aut_id = 0 ) {
		$this->aut_id = $aut_id;
		if ( empty( $raw_data ) ) {
			tap_logger( $this->aut_id )->register( [
				'key'         => static::get_id(),
				'id'          => 'no-param-on-data-create',
				'message'     => 'No parameter provided for ' . static::class . ' object',
				'class-label' => tap_logger( $this->aut_id )->get_nice_class_name( static::class ),
			] );
		} else {
			$this->data = static::filter_data( static::create_object( $raw_data ), $raw_data, $aut_id );
		}
	}

	/**
	 * Get the data-object identifier
	 *
	 * @return string
	 */
	abstract public static function get_id();

	/**
	 * Array of data field object keys that are contained by this data-object
	 * e.g. user_data could contain id, name, email, phone_number
	 * filters might want to know this in order to provide filtering
	 * actions might want to know those fields so they can know what they can use
	 *
	 * @return array
	 */
	abstract public static function get_fields();

	/**
	 * Must implement constructor for each data-object to create data-object from a parameter provided by trigger
	 * e.g. a trigger would provide user_id and we would create a user object with get_user_by
	 *
	 * @param array $param
	 */
	abstract public static function create_object( $param );

	/**
	 * Run the filter on the created object, if 3rd party items want to insert more fields inside the object
	 *
	 * @param array $data_object
	 * @param array $raw_data
	 * @param int   $aut_id
	 *
	 * @return array
	 */
	private static function filter_data( $data_object, $raw_data, $aut_id = 0 ) {
		$data_object_id = static::get_id();

		if ( empty( $data_object ) ) {
			tap_logger( $aut_id )->register( [
				'key'         => $data_object_id,
				'id'          => 'no-data-object-created',
				'message'     => 'Data object was not created. Required data for ' . static::class . ' was not provided.',
				'class-label' => tap_logger( $aut_id )->get_nice_class_name( static::class ),
			] );
		}

		/**
		 * Filter field vales on the current data object
		 *
		 * @param array  $data_object    array with current values of the data_object
		 * @param array  $raw_data       array of data that is sent from the trigger
		 * @param string $data_object_id the identifier of the data object
		 *
		 * @return array
		 */
		return apply_filters( 'thrive_automator_process_data_object_' . $data_object_id, $data_object, $raw_data, $data_object_id );
	}

	/**
	 * Return data set for the current object
	 *
	 * @return array
	 */
	public function get_data() {
		return $this->data;
	}

	/**
	 * Default function to return values from inside the object based on provided key. Will be overwritten in case it the data-object format doesn't match
	 *
	 * @param string $field
	 *
	 * @return mixed
	 */
	public function get_value( $field ) {

		if ( empty( $field ) ) {
			return null;
		}
		if ( is_subclass_of( $field, Data_Field::class ) ) {
			$key = $field::get_id();
		} else {
			$key = $field;
		}

		return $this->data[ $key ] ?? null;
	}

	/**
	 * Default function to set values inside the object based on provided key. Will be overwritten in case it the data-object format doesn't match
	 *
	 * @param string $field
	 *
	 * @return bool
	 */
	public function set_value( $field, $value ) {

		if ( empty( $field ) ) {
			return null;
		}
		if ( is_subclass_of( $field, Data_Field::class ) ) {
			$key = $field::get_id();
		} else {
			$key = $field;
		}

		$this->data[ $key ] = $value;

		return true;
	}

	final public static function required_properties(): array {
		return [
			'get_id'     => 'string',
			'get_fields' => 'array',
		];
	}

	/**
	 * Get filterable fields from a trigger depending on the provided data-object keys
	 *
	 * @param array $data_object_keys
	 *
	 * @return array
	 * @throws Exception
	 */
	public static function get_all_filterable_fields( $data_object_keys ) {
		$data_objects = static::get();

		$filterable_fields = [];

		if ( is_array( $data_object_keys ) ) {
			foreach ( $data_object_keys as $data_key ) {
				if ( ! empty( $data_objects[ $data_key ] ) ) {
					$filterable_fields[ $data_key ] = $data_objects[ $data_key ]::get_filterable_fields();
				}
			}
		}

		return $filterable_fields;
	}

	/**
	 * Get list of field classes provided by the data-object and run filter on the list
	 *
	 * @return array
	 * @throws Exception
	 */
	public static function get_filterable_fields() {
		$classes = Data_Field::get();

		$filterable_fields = [];

		foreach ( static::get_filterable_fields_keys() as $field ) {
			if ( ! empty( $classes[ $field ] ) ) {
				$filterable_fields[ $field ] = $classes[ $field ]::localize();
			}
		}

		return $filterable_fields;
	}

	/**
	 * Get the email data set on the current data object
	 * e.g $this->get_value( 'user_email' );
	 *
	 * @return string
	 */
	public function get_provided_email() {
		return $this->get_value( 'email' );
	}


	/**
	 * Whether the current data object can provide user email to be used in action
	 *
	 * @return false
	 */
	public function can_provide_email() {
		return false;
	}

	/**
	 * Filter data fields that care available and haven't been declared directly by the current data object
	 *
	 * @param array of data fields
	 *
	 * @return array
	 */
	private static function get_filterable_fields_keys() {
		return apply_filters( 'thrive_automator_data_object_' . static::get_id() . '_fields', static::get_fields() );
	}

	/**
	 * Replace potential shortcodes set as field value
	 *
	 * @param $value
	 *
	 * @return array|mixed|string|string[]|null
	 */
	public function replace_dynamic_data( $value ) {
		$classes = Data_Field::get();
		foreach ( static::get_filterable_fields_keys() as $field ) {
			if ( ! empty( $classes[ $field ] ) && ! empty( $shortcode = $classes[ $field ]::get_shortcode_tag() ) ) {
				$field_value = $this->get_value( $classes[ $field ]::get_id() );
				$value       = Utils::replace_data_shortcode( $value, $shortcode, $field_value );
			}
		}

		return $value;
	}

	/**
	 * Hide existing data-object
	 */
	public static function hidden() {
		return false;
	}

	/**
	 * For dynamic data mapping, we need available options in the editor for the user to choose from
	 */
	public static function get_data_object_options() {
		return [];
	}
}
