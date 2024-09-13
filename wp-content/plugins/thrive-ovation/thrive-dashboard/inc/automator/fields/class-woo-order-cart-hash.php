<?php

namespace TVE\Dashboard\Automator;

use Thrive\Automator\Items\Data_Field;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Woo_Order_Cart_Hash
 */
class Woo_Order_Cart_Hash extends Data_Field {

	/**
	 * Field name
	 */
	public static function get_name() {
		return 'Cart hash';
	}

	/**
	 * Field description
	 */
	public static function get_description() {
		return 'Filter by cart hash';
	}

	/**
	 * Field input placeholder
	 */
	public static function get_placeholder() {
		return '';
	}

	public static function get_dummy_value() {
		return '63cc7e2c30ae121035bbf0115b8b4c72bbdb39b8c708d943bc1961700131243c9a41fa2cb44947d762bf8ff5076782733a446c7a97fb8889515d8559802ea64bb2c4f3fabc43e2549afd12c8acbbedaffd58e69368bbdf9f5b17e83a9f048d54e47a19efc88235e54680bb0f715566b9308c49e67dbc53727273d28f767493cfc13fd8241cd1c7f02cd84b2a358434560f919d5d086aa053be6e96739051a1';
	}

	public static function get_id() {
		return 'cart_hash';
	}

	public static function get_supported_filters() {
		return array( 'string_ec' );
	}

	public static function get_validators() {
		return array( 'required' );
	}

	public static function get_field_value_type() {
		return static::TYPE_STRING;
	}
}
