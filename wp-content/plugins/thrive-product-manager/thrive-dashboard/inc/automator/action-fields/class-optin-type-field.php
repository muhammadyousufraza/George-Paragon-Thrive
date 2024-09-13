<?php

namespace TVE\Dashboard\Automator;

use Thrive\Automator\Items\Action_Field;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Autoresponder_Field
 */
class Optin_Type_Field extends Action_Field {

	/**
	 * Field name
	 */
	public static function get_name() {
		return 'Optin';
	}

	/**
	 * Field description
	 */
	public static function get_description() {
		return 'Choose the type of optin you would like. Double optin means your subscribers will need to confirm their email address before being added to your list';
	}

	/**
	 * Field input placeholder
	 */
	public static function get_placeholder() {
		return 'Choose autoresponder';
	}

	/**
	 * For multiple option inputs, name of the callback function called through ajax to get the options
	 */
	public static function get_options_callback( $action_id, $action_data ) {
		return array(
			's' => array(
				'id'   => 's',
				'name' => 'Single',
			),
			'd' => array(
				'id'   => 'd',
				'name' => 'Double',
			),
		);
	}

	public static function get_id() {
		return 'optin';
	}

	public static function get_type() {
		return 'select';
	}

	public static function is_ajax_field() {
		return true;
	}

	public static function get_validators() {
		return array( 'required' );
	}
}
