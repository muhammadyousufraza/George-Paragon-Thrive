<?php

namespace Thrive\Automator\Items;

use Exception;
use Thrive\Automator\Traits\Automation_Item;
use Thrive\Automator\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Field
 */
abstract class Data_Field {

	const TYPE_STRING   = 'string';
	const TYPE_NUMBER   = 'number';
	const TYPE_DATE     = 'date';
	const TYPE_DATETIME = 'timedate';
	const TYPE_BOOLEAN  = 'boolean';
	const TYPE_CLASS    = 'class';
	const TYPE_ARRAY    = 'array';

	use Automation_Item {
		register as register_data_field;
	}

	/**
	 * Unique identifier for the field
	 */
	abstract public static function get_id();

	/**
	 * Array of filters that are supported by the field
	 *
	 * @return array
	 */
	abstract public static function get_supported_filters();

	/**
	 * Field name
	 */
	abstract public static function get_name();

	/**
	 * Field description
	 */
	abstract public static function get_description();

	/**
	 * Field input placeholder
	 */
	abstract public static function get_placeholder();

	/**
	 * Field tooltip
	 */
	public static function get_tooltip() {
		return static::get_description();
	}

	/**
	 * Is used as primary identifier inside its belonging data-object
	 */
	public static function primary_key() {
		return false;
	}

	/**
	 * Editor validators to run in frontend
	 */
	public static function get_validators() {
		return [];
	}

	/**
	 * Return an array of data object ids that can use the current data field
	 *
	 * @return array
	 */
	public static function get_compatible_data_objects() {
		return [];
	}

	/**
	 * Modify data object values to add value for the current data field
	 *
	 * @param $data_object
	 * @param $raw_data
	 * @param $data_object_id
	 *
	 * @return mixed
	 */
	public static function process_data_value( $data_object, $raw_data, $data_object_id ) {
		return $data_object;
	}

	/**
	 * Whether the current field values should be fetched
	 *
	 * @return false
	 */
	public static function is_ajax_field() {
		return false;
	}

	/**
	 *
	 * @deprecated since version 1.3.0
	 *
	 * @see        Data_Field::get_field_values - which support the same functionality but with additional filters
	 */
	public static function get_options_callback() {
		return [
			[
				'id'    => 1,
				'label' => 'Label 1',
			],
		];
	}


	/**
	 * For multiple option inputs, name of the callback function called through ajax to get the options
	 * FIELD_TYPE_SELECT, FIELD_TYPE_AUTOCOMPLETE, FIELD_TYPE_CHECKBOX should have their values fetched
	 * Data format should be like array{ array{id: String|int, label: String} , ...}
	 * FIELD_TYPE_DOUBLE_DROPDOWN -> for each option there should a values param which will include available data for the secondary dropdown
	 *
	 *
	 * @param $filters       array of filters to apply to the options
	 *                       e.g:
	 *                       - 'search' => string - search term to filter the options by
	 *                       - 'limit'  => int - limit the number of options to return (-1 could be used to return all)
	 *                       - 'page'   => int - page number to return (0 based)
	 */
	public static function get_field_values( $filters = [] ) {
		return static::get_options_callback();
	}

	/**
	 * In case the current data field wants to register to compatible data objects, add specific callbacks and filters
	 */
	final public static function register_compatible_data_object() {
		foreach ( static::get_compatible_data_objects() as $data_object_key ) {
			add_filter( 'thrive_automator_data_object_' . $data_object_key . '_fields', function ( $fields ) {
				$fields[] = static::get_id();

				return $fields;
			} );

			add_filter( 'thrive_automator_process_data_object_' . $data_object_key, [
				static::class,
				'process_data_value',
			], 10, 3 );
		}
	}

	/**
	 * Override register function so we can register current field to data objects also
	 *
	 * @param Data_Field $field
	 */
	final public static function register( $field ) {
		if ( is_subclass_of( $field, __CLASS__ ) ) {
			static::register_data_field( $field );

			$field::register_compatible_data_object();
		} else {
			Utils::trigger_error( 'Argument ' . $field . ' must be a subclass of Data_Field.' );
		}
	}

	/**
	 * Validate extended class data
	 */
	final protected static function validate( $field ) {
		foreach ( $field['filters'] as $filter ) {
			if ( ! empty( $field['values_callback'] ) && Utils::is_multiple( $filter ) && ! method_exists( static::class, $field['values_callback'] ) ) {
				throw new Exception( 'Missing  ' . $field['id'] . '  field options callback' );
			}
		}

		return $field;
	}

	/**
	 * Get the type of the field value
	 *
	 * @return string
	 * @see Data_Field::TYPE_STRING
	 */
	public static function get_field_value_type() {
		return '';
	}

	/**
	 * Return a dummy data that can be used to simulate an automation
	 *
	 * @return string
	 */
	public static function get_dummy_value() {
		return '';
	}

	/**
	 * Get potential shortcode string for the current field
	 *
	 * @return string
	 */
	final public static function get_shortcode_tag() {
		$id   = static::get_id();
		$type = static::get_field_value_type();


		return in_array( $type, [
			static::TYPE_NUMBER,
			static::TYPE_STRING,
			static::TYPE_DATE,
			static::TYPE_BOOLEAN,
			static::TYPE_DATETIME,
		] ) || ( $type === static::TYPE_ARRAY && count( static::get_supported_filters() ) === 0 ) ? "%$id%" : '';
	}

	/**
	 * Get class properties types
	 *
	 * @return string[]
	 */
	final public static function required_properties(): array {
		return [
			'get_id'                => 'string',
			'get_supported_filters' => 'array',
			'get_name'              => 'string',
			'get_description'       => 'string',
			'get_tooltip'           => 'string',
			'get_placeholder'       => 'string',
		];
	}

	/**
	 * Get field information that will be used in admin UI
	 *
	 * @return array
	 * @throws Exception
	 */
	final public static function localize(): array {
		return static::validate( [
			'id'            => static::get_id(),
			'filters'       => static::get_supported_filters(),
			'validators'    => static::get_validators(),
			'name'          => static::get_name(),
			'description'   => static::get_description(),
			'tooltip'       => static::get_tooltip(),
			'placeholder'   => static::get_placeholder(),
			'is_ajax_field' => static::is_ajax_field(),
			'value_type'    => static::get_field_value_type(),
			'shortcode_tag' => static::get_shortcode_tag(),
			'dummy_value'   => static::get_dummy_value(),
			'primary_key'   => static::primary_key(),
			'extra_options' => static::get_extra_options(),
		] );
	}

	/**
	 * An array of extra options to be passed to the field which can affect the display of the field
	 *
	 * @return array
	 */
	public static function get_extra_options() {
		return [];
	}


	/**
	 * Get filterable fields from a trigger depending on the provided data-object keys
	 *
	 * @return array
	 * @throws Exception
	 */
	final public static function get_all_primary_keys() {
		$fields       = static::get();
		$data_sets    = Data_Object::get();
		$primary_keys = [];

		foreach ( $fields as $field ) {
			$primary_key = $field::primary_key();
			if ( $primary_key && isset( $data_sets[ $primary_key ] ) ) {
				$field_data                       = $field::localize();
				$primary_keys[ $field::get_id() ] = [
					'id'          => $field::get_id(),
					'name'        => $data_sets[ $primary_key ]::get_nice_name() ?: $field_data['name'],
					'primary_key' => $primary_key,
				];
			}
		}

		return $primary_keys;
	}

	/**
	 * Hide existing data-field
	 */
	public static function hidden() {
		return false;
	}
}
