<?php

namespace TVE\Dashboard\Automator;

use Thrive\Automator\Items\Action_Field;
use Thrive_Dash_List_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Form_List_Field
 */
class Form_List_Field extends Action_Field {
	/**
	 * Field name
	 */
	public static function get_name() {
		return __( 'Select the form', 'thrive-dash' );
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
		return __( 'Choose the form you want to use', 'thrive-dash' );
	}

	/**
	 * $$value will be replaced by field value
	 * $$length will be replaced by value length
	 *
	 *
	 * @return string
	 */
	public static function get_preview_template() {
		return '';
	}

	public static function get_id() {
		return 'form_list';
	}

	public static function get_type() {
		return 'select';
	}

	public static function get_options_callback( $action_id, $action_data ) {
		$values = array();

		if ( ! empty( $action_data ) ) {
			if ( is_string( $action_data ) ) {
				$api = $action_data;
			} else if ( property_exists( $action_data, 'autoresponder' ) ) {
				$api = $action_data->autoresponder->value;
			}
		}
		if ( ! empty( $api ) ) {
			$api_instance = Thrive_Dash_List_Manager::connection_instance( $api );
			if ( $api_instance && $api_instance->is_connected() && $api_instance->has_forms() ) {
				$forms = $api_instance->get_forms();
				if ( ! empty( $forms[ $action_data->autoresponder->subfield->mailing_list->value ] ) ) {
					$values = $forms[ $action_data->autoresponder->subfield->mailing_list->value ];
				}
			}
		}

		return $values;
	}
}
