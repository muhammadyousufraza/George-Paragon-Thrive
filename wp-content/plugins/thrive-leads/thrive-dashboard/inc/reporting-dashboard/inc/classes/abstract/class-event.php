<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */

namespace TVE\Reporting;

abstract class Event {

	use Traits\Event;

	abstract public static function key();

	abstract public static function label();

	/**
	 * Register event
	 *
	 * @return void
	 */
	final public static function register() {
		Store::get_instance()->register_event( static::class );
	}

	/**
	 * Store fields values for the event
	 *
	 * @param $fields
	 */
	public function __construct( $fields = [] ) {
		$registered_fields = static::get_registered_fields();

		foreach ( $fields as $key => $value ) {
			$db_col = static::get_field_table_col( $key );

			if ( isset( $registered_fields[ $db_col ] ) ) {
				/* in case we have defined a class for the field, save an instance */
				$this->fields[ $db_col ] = new $registered_fields[ $db_col ]( $value );
			} else {
				$this->fields[ $key ] = $value;
			}
		}
	}
}
