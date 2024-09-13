<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */

namespace TVE\Dashboard\Automator;

use Thrive\Automator\Items\Action_Field;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}


class Api_Fields extends Action_Field {

	public static function get_name() {
		return 'Field mapping';
	}

	public static function get_description() {
		return 'Field mapping lets you add more contact information to your email service. If the ‘name’ field receives two names separated by a space, they will automatically be split and saved as a first and last name.';
	}

	public static function get_placeholder() {
		return 'Field mapping';
	}

	public static function get_id() {
		return 'api_fields';
	}

	public static function get_type() {
		return 'mapping_pair';
	}

	public static function get_preview_template() {
		return '';
	}

	/**
	 * For multiple option inputs, name of the callback function called through ajax to get the options
	 */
	public static function get_options_callback( $action_id, $action_data ) {
		$values = array(
			0 => array(
				'id'    => 'name',
				'label' => __( 'Name', 'thrive-dash' ),
			),
			1 => array(
				'id'    => 'phone',
				'label' => __( 'Phone', 'thrive-dash' ),
			),
		);

		if ( ! empty( $action_data ) ) {
			if ( is_string( $action_data ) ) {
				$api = $action_data;
			} else if ( property_exists( $action_data, 'autoresponder' ) ) {
				$api = $action_data->autoresponder->value;
			}
		}
		if ( ! empty( $api ) ) {
			$api_instance = \Thrive_Dash_List_Manager::connection_instance( $api );

			if ( $api_instance && $api_instance->is_connected() && $api_instance->has_custom_fields() ) {
				$mailing_list  = empty( $action_data->autoresponder->subfield->mailing_list->value ) ? '' : $action_data->autoresponder->subfield->mailing_list->value;
				$custom_fields = $api_instance->get_custom_fields_by_list( $mailing_list );
				foreach ( $custom_fields as $key => $field ) {
					array_push( $values, array(
						'id'    => $field['id'],
						'label' => $field['label'] ?: $field['name'],
					) );
				}
			}
		}

		return array_reverse( $values, true );
	}

	public static function get_validators() {
		return array( 'key_value_pair' );
	}

	public static function allow_dynamic_data() {
		return true;
	}
}
