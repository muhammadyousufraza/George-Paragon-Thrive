<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */

namespace TVE\Reporting\Traits;

use TVE\Reporting\EventFields\Event_Field;
use TVE\Reporting\EventFields\Created;
use TVE\Reporting\EventFields\Event_Type;
use TVE\Reporting\EventFields\Item_Id;
use TVE\Reporting\EventFields\Post_Id;
use TVE\Reporting\EventFields\User_Id;
use TVE\Reporting\Logs;

trait Event {

	protected static $hook_name = '';

	protected static $hook_priority = 10;

	protected static $hook_params_number = 1;

	/**
	 * @var Event_Field[]|mixed
	 */
	protected $fields = [];


	/**
	 * Things to do after an event has been registered
	 *
	 * @return void
	 */
	public static function after_register() {
		static::register_action();
	}

	/**
	 * Register action that will provide the data for log
	 *
	 * @return void
	 */
	public static function register_action() {
		if ( empty( static::$hook_name ) ) {
			throw new \RuntimeException( __CLASS__ . 'Please define a hook name or register an action!' );
		} else {
			add_action( static::$hook_name, static function ( $fields ) {
				$event = new static( $fields );

				$event->log();
			}, static::$hook_priority, static::$hook_params_number );
		}
	}

	public static function get_event_type_field(): string {
		return Event_Type::class;
	}

	public static function get_created_field(): string {
		return Created::class;
	}

	public static function get_post_id_field(): string {
		return Post_Id::class;
	}

	public static function get_user_id_field(): string {
		return User_Id::class;
	}

	public static function get_item_id_field(): string {
		return Item_Id::class;
	}

	public static function get_extra_int_field_1() {
		return null;
	}

	public static function get_extra_int_field_2() {
		return null;
	}

	public static function get_extra_float_field() {
		return null;
	}

	public static function get_extra_varchar_field_1() {
		return null;
	}

	public static function get_extra_varchar_field_2() {
		return null;
	}

	public static function get_extra_text_field_1() {
		return null;
	}

	/**
	 * Return only the registered (non null) fields
	 *
	 * @return Event_Field[]
	 */
	final public static function get_registered_fields(): array {
		$fields = [
			'event_type'      => static::get_event_type_field(),
			'created'         => static::get_created_field(),
			'item_id'         => static::get_item_id_field(),
			'post_id'         => static::get_post_id_field(),
			'user_id'         => static::get_user_id_field(),
			'int_field_1'     => static::get_extra_int_field_1(),
			'int_field_2'     => static::get_extra_int_field_2(),
			'float_field'     => static::get_extra_float_field(),
			'varchar_field_1' => static::get_extra_varchar_field_1(),
			'varchar_field_2' => static::get_extra_varchar_field_2(),
			'text_field_1'    => static::get_extra_text_field_1(),
		];

		return array_filter( $fields, static function ( $field ) {
			return $field !== null;
		} );
	}

	/**
	 * Get db table column for a specific field
	 *
	 * @param string $key
	 *
	 * @return string
	 */
	final public static function get_field_table_col( string $key ): string {
		$db_col = $key;

		foreach ( static::get_registered_fields() as $col => $field_class ) {
			if ( $field_class::key() === $key ) {
				$db_col = $col;
			}
		}

		return $db_col;
	}

	/**
	 * @param $key
	 *
	 * @return Event_Field|null
	 */
	final public static function get_registered_field( $key ) {
		$field = null;

		foreach ( static::get_registered_fields() as $col => $field_class ) {
			if ( $col === $key || $field_class::key() === $key ) {
				$field = $field_class;
			}
		}

		return $field;
	}

	/**
	 * @param      $field_key
	 * @param bool $format
	 *
	 * @return mixed|null
	 */
	public function get_field_value( $field_key, $format = true ) {
		$field_key = static::get_field_table_col( $field_key );

		if ( isset( $this->fields[ $field_key ] ) ) {
			if ( is_subclass_of( $this->fields[ $field_key ], Event_Field::class ) ) {
				$value = $this->fields[ $field_key ]->get_value( $format );
			} else {
				$value = $this->fields[ $field_key ];
			}
		} else {
			$value = null;
		}

		return $value;
	}

	/**
	 * @param $field_key
	 *
	 * @return mixed|Event_Field|null
	 */
	public function get_field( $field_key ) {
		$field_key = static::get_field_table_col( $field_key );

		return $this->fields[ $field_key ] ?? null;
	}

	/**
	 * @return mixed|Event_Field[]
	 */
	public function get_fields() {
		return $this->fields;
	}

	/**
	 * Store event data in the database
	 *
	 * @return bool|int|\mysqli_result|resource|null
	 */
	final public function log() {
		return Logs::get_instance()->insert( $this );
	}

	/**
	 * Update event data in the database
	 *
	 * @param $id
	 * @param $fields_to_update
	 *
	 * @return bool|int|\mysqli_result|resource|null
	 */
	final public function update_log( $id, $fields_to_update ) {
		return Logs::get_instance()->update( $this, $id, $fields_to_update );
	}

	/**
	 * @param $query_fields
	 * @param $fields_to_update
	 *
	 * @return void
	 */
	public function upsert( $query_fields, $fields_to_update = [] ) {
		$id = $this->get_entry_row( $query_fields );

		if ( is_null( $id ) ) {
			$this->log();
		} else {
			if ( empty( $fields_to_update ) ) {
				$fields_to_update = $query_fields;
			}

			$this->update_log( $id, $fields_to_update );
		}
	}

	/**
	 * @param $filter_keys
	 *
	 * @return string|null
	 */
	final public function get_entry_row( $filter_keys ) {
		$filters = [];

		foreach ( array_keys( $this::get_registered_fields() ) as $field_key ) {
			if ( in_array( $field_key, $filter_keys, true ) ) {
				$filters[ $field_key ] = $this->get_field_value( $field_key );
			}
		}

		return Logs::get_instance()->set_query( [
			'event_type' => static::key(),
			'filters'    => $filters,
		] )->get_row();
	}

	/**
	 * @param $allowed_fields
	 *
	 * @return array
	 */
	public function get_log_data( $allowed_fields = [] ) {
		$log_data = [];

		if ( empty( $allowed_fields ) ) {
			$allowed_fields = array_keys( $this::get_registered_fields() );
		}

		foreach ( array_keys( $this::get_registered_fields() ) as $field_key ) {
			if ( in_array( $field_key, $allowed_fields, true ) ) {
				$log_data[ $field_key ] = $this->get_field_value( $field_key );
			}
		}

		$log_data['event_type'] = $this::key();
		$log_data['created']    = gmdate( 'Y-m-d H:i:s' );

		return $log_data;
	}
}
