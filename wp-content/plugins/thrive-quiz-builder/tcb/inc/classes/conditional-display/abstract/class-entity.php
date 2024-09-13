<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

namespace TCB\ConditionalDisplay;

use TCB\ConditionalDisplay\Traits\Item;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

abstract class Entity {
	use Item;

	protected $data;

	protected static $fields = [];

	/**
	 * @return array
	 */
	public static function get_fields() {
		$entity_key = static::get_key();

		if ( empty( static::$fields[ $entity_key ] ) ) {
			foreach ( Field::get( true ) as $field ) {
				if ( $field::get_entity() === $entity_key ) {
					static::$fields[ $entity_key ][] = $field::get_key();
				}
			}
		}

		return static::$fields[ $entity_key ];
	}

	/**
	 * @return string
	 */
	abstract public static function get_key();

	/**
	 * Create an entity from the data provided
	 *
	 * @param array $extra_data
	 */
	public function __construct( $extra_data = [] ) {
		$this->data = $this->create_object( $extra_data );
	}

	/**
	 * @param $param
	 *
	 * @return mixed
	 */
	abstract public function create_object( $param );

	/**
	 * @param $field_key
	 *
	 * @return string
	 */
	public function get_field_value( $field_key ) {
		$value = '';

		$field_object = $this->get_field( $field_key );

		if ( ! empty( $field_object ) ) {
			$value = $field_object->get_value( $this->data );
		}

		return $value;
	}

	/**
	 * @param $field_key
	 *
	 * @return Field
	 */
	public function get_field( $field_key ) {
		$field_object = null;

		if ( static::validate_field( $field_key ) ) {
			$field_object = Field::get_instance( $field_key );
		} else {
			trigger_error( 'Field ' . $field_key . ' must be an allowed field of ' . static::class );
		}

		return $field_object;
	}

	/**
	 * @param $field
	 *
	 * @return bool
	 */
	public static function validate_field( $field ) {
		return in_array( $field, static::get_fields() );
	}

	/**
	 * @return array
	 */
	public static function get_data_to_localize() {
		$data = [];

		$entities = static::get( true );

		foreach ( $entities as $key => $class ) {
			$data[ $key ] = [
				'label' => $class::get_label(),
			];
		}

		return $data;
	}

	/**
	 * Determines the display order in the modal entity select
	 *
	 * @return int
	 */
	public static function get_display_order() {
		/* 'User' is 0, other entities are multipliers of 5, everything else that doesn't overwrite this is 100 */
		return 100;
	}
}
