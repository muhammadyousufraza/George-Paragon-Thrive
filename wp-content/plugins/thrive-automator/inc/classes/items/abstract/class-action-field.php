<?php

namespace Thrive\Automator\Items;

use Exception;
use Thrive\Automator\Traits\Automation_Item;
use Thrive\Automator\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Action_Field
 */
abstract class Action_Field {

	use Automation_Item;

	const REQUIRED_VALIDATION = 'required';
	const EMAIL_VALIDATION    = 'email';
	/**
	 * Used for Utils::FIELD_TYPE_KEY_PAIR
	 */
	const KEY_PAIR_VALIDATION = 'key_value_pair';
	/**
	 * For key that would be used as http header
	 */
	const HTTP_HEADERS_VALIDATION = 'http_headers';

	/**
	 * $$value will be replaced by field value
	 * $$length will be replaced by value length
	 *
	 *
	 * @return string
	 */
	public static function get_preview_template() {
		return '$$value';
	}

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
	 * Whether the current field values should be fetched
	 *
	 * @return false
	 */
	public static function is_ajax_field() {
		return Utils::is_multiple( static::get_type() ?? '' );
	}

	/**
	 *
	 * @deprecated since version 1.3.0
	 *
	 * @see        Action_Field::get_field_values - which support the same functionality but with additional filters
	 */
	public static function get_options_callback( $action_id, $action_data ) {
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
	 *
	 * @param $filters       array of filters to apply to the options
	 *                       e.g:
	 *                       - 'action_id' => string - current action id
	 *                       - 'action_data' => array - current action data
	 *                       - 'search' => string - search term to filter the options by
	 *                       - 'limit'  => int - limit the number of options to return (-1 could be used to return all)
	 *                       - 'page'   => int - page number to return (0 based)
	 */
	public static function get_field_values( $filters = [] ) {
		$action_id   = $filters['action_id'] ?? '';
		$action_data = $filters['action_data'] ?? [];

		return static::get_options_callback( $action_id, $action_data );
	}

	/**
	 * Field tooltip
	 */
	public static function get_tooltip() {
		return static::get_description();
	}

	/**
	 * Get field information
	 *
	 * @throws Exception
	 */
	final public static function localize(): array {
		return static::validate( [
			'id'                      => static::get_id(),
			'type'                    => static::get_type(),
			'validators'              => static::get_validators(),
			'name'                    => static::get_name(),
			'description'             => static::get_description(),
			'tooltip'                 => static::get_tooltip(),
			'placeholder'             => static::get_placeholder(),
			'is_ajax_field'           => static::is_ajax_field(),
			'preview'                 => static::get_preview_template(),
			'allow_dynamic_data'      => static::allow_dynamic_data(),
			'default_value'           => static::get_default_value(),
			'allowed_data_set_values' => static::allowed_data_set_values(),
			'extra_options'           => static::get_extra_options(),
		] );
	}

	/**
	 * Can be used to set default values for a field
	 * At the moment only used for select fields
	 *
	 * @return string
	 */
	public static function get_default_value() {
		return '';
	}

	/**
	 * Validate extended class data
	 *
	 * @param array $field
	 *
	 * @return array
	 * @throws Exception
	 */
	final protected static function validate( array $field ): array {
		if ( ! empty( $field['values_callback'] ) && Utils::is_multiple( $field['type'] ) && ! method_exists( static::class, $field['values_callback'] ) ) {
			throw new Exception( `Missing {$field['id']} field options callback` );
		}

		return $field;
	}

	/**
	 * Unique identifier for the action field
	 */
	abstract public static function get_id();

	/**
	 * return type of input field, required to render in automation editor
	 *
	 * @see Utils::FIELD_TYPE_TEXT
	 * @see Utils::FIELD_TYPE_TAGS
	 * @see Utils::FIELD_TYPE_SELECT
	 * @see Utils::FIELD_TYPE_CHECKBOX
	 * @see Utils::FIELD_TYPE_AUTOCOMPLETE
	 * @see Utils::FIELD_TYPE_DOUBLE_DROPDOWN
	 * @see Utils::FIELD_TYPE_BUTTON
	 */
	abstract public static function get_type();

	/**
	 * Editor validators to run in frontend
	 *
	 * @return array
	 *
	 * @see Action_Field::REQUIRED_VALIDATION
	 * @see Action_Field::EMAIL_VALIDATION
	 */
	public static function get_validators() {
		return [];
	}

	/**
	 * Whether users should be allowed to add dynamic data from Data_Field as value for the current Action_Field
	 *
	 * @return false
	 */
	public static function allow_dynamic_data() {
		return false;
	}


	final public static function required_properties(): array {
		return [
			'get_id'               => 'string',
			'get_type'             => 'string',
			'get_name'             => 'string',
			'get_description'      => 'string',
			'get_tooltip'          => 'string',
			'get_placeholder'      => 'string',
			'get_preview_template' => 'string',
		];
	}

	/**
	 * Hide existing action-field
	 */
	public static function hidden() {
		return false;
	}

	/**
	 * If the current field value can be set based on a dynamic data object
	 *
	 * e.g if the field is a product field, the value can be set based on the dynamic product object
	 *
	 * @return array
	 */
	public static function allowed_data_set_values() {
		return [];
	}

	/**
	 * An array of extra options to be passed to the field which can affect the display of the field
	 *
	 * @return array
	 */
	public static function get_extra_options() {
		return [];
	}
}
