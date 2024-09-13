<?php

namespace TVE\Dashboard\Automator;

use Thrive\Automator\Items\Action_Field;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Mailing_List_Field
 */
class Mailing_List_Field extends Action_Field {
	/**
	 * Field name
	 */
	public static function get_name() {
		return 'Add the user to the following list';
	}

	/**
	 * Field description
	 */
	public static function get_description() {
		return static::get_placeholder();
	}

	/**
	 * Field input placeholder
	 */
	public static function get_placeholder() {
		return __( 'Select an autoresponder mailing list to add the user to', 'thrive-dash' );
	}

	/**
	 * $$value will be replaced by field value
	 * $$length will be replaced by value length
	 *
	 *
	 * @return string
	 */
	public static function get_preview_template() {
		return 'List: $$value';
	}

	/**
	 * For multiple option inputs, name of the callback function called through ajax to get the options
	 */
	public static function get_options_callback( $action_id, $action_data ) {
		$lists = [];

		if ( ! empty( $action_data ) ) {
			if ( is_string( $action_data ) ) {
				$api = $action_data;
			} else if ( property_exists( $action_data, 'autoresponder' ) ) {
				$api = $action_data->autoresponder->value;
			}
		}
		if ( ! empty( $api ) ) {
			$api_instance = \Thrive_Dash_List_Manager::connection_instance( $api );

			if ( $api_instance && $api_instance->is_connected() ) {
				$lists = $api_instance->get_lists( false );
				if ( $api_instance->has_forms() ) {
					$lists = static::add_form_data( $lists, $api_instance->get_forms() );
				}
			}
		}

		return $lists;
	}

	public static function get_id() {
		return 'mailing_list';
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

	/**
	 * @param array $lists
	 * @param array $forms
	 *
	 * @return array
	 */
	public static function add_form_data( $lists, $forms ) {
		foreach ( $lists as $key => $list ) {
			if ( is_array( $list ) ) {
				$lists[ $key ]['values'] = $forms[ $list['id'] ];
			} else {
				$lists[ $key ]->values = $forms[ $list->id ];
			}
		}

		return $lists;
	}
}
