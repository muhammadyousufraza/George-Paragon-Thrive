<?php

namespace TVD\Dashboard\Access_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}


abstract class Functionality {

	public static function init() {
		static::hooks();
	}

	abstract public static function hooks();

	abstract public static function get_name();

	abstract public static function get_tag();

	abstract public static function get_default();

	public static function update_option_value( $user_role, $updated_value ) {
		update_option( static::get_option_name( $user_role ), $updated_value );
	}

	public static function get_option_name( $user_role ) {
		return '_' . $user_role . '_' . static::get_tag();
	}

	public static function get_properties( $user_role ) {
		return array(
			'name'                  => static::get_name(),
			'tag'                   => static::get_tag(),
			'functionality_options' => static::get_options(),
			'get_options_type'      => static::get_options_type(),
			'icon'                  => static::get_icon(),
			'default_value'         => static::get_default(),
			'value'                 => static::get_option_value( $user_role ),
		);
	}
}