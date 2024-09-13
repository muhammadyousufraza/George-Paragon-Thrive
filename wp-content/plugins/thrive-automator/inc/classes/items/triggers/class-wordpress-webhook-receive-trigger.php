<?php

namespace Thrive\Automator\Items;

use Exception;
use Thrive\Automator\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Wordpress_Webhook_Receive extends Trigger {

	public static function is_top_level() {
		return true;
	}

	/**
	 * @param array $params
	 *
	 * @return array
	 */
	public function process_params( $params = [] ) {

		if ( ! empty( $params ) ) {
			$params = $params[0];
		}
		$data_object_classes = Data_Object::get();

		$primary_keys = Data_Field::get_all_primary_keys();

		$data_objects = [
			'generic_data' => new $data_object_classes['generic_data'](),
		];
		if ( ! empty( $params['webhook_id'] ) ) {

			foreach ( Utils::get_automator_webhook_fields( $params['webhook_id'] ) as $webhook_field ) {

				$field_value = static::process_data_key( $webhook_field['field_key'], $params );
				if ( ! empty( $field_value ) ) {//check if field is populated by webhook
					if ( is_string( $field_value ) ) {
						$field_value = trim( $field_value );
					}

					if ( in_array( $webhook_field['field_type'], array_keys( $primary_keys, true ), true ) ) {                //check if field is primary key for existing data objects
						$data_object_key = $primary_keys[ $webhook_field['field_type'] ]['primary_key'];
						if ( empty( $data_objects[ $data_object_key ] ) ) {                                                         //check if data object not already added to result list
							$data_objects[ $data_object_key ] = new $data_object_classes[ $data_object_key ]( $field_value );
						}

					} else {
						$data_objects['generic_data']->add_field( $webhook_field['id'], [ 'value' => $field_value, 'id' => $webhook_field['field_key'] ] );
					}
				} else {
					/**
					 * If the value doesn't exist we set it as empty string so shortcodes can be replaced
					 */
					$data_objects['generic_data']->add_field( $webhook_field['field_key'], [ 'value' => '', 'id' => $webhook_field['field_key'] ] );
				}
			}
		}

		return $data_objects;
	}

	public static function process_data_key( $original_key, $params ) {
		$original_key = str_replace( ']', '', $original_key );
		$ref          = $params;
		foreach ( explode( '[', $original_key ) as $key ) {
			if ( $key === 'tags' && is_array( $ref[ $key ] ) ) {
				$ref = implode( ',', $ref[ $key ] );
				break;
			}
			$ref = empty( $ref[ $key ] ) ? null : $ref[ $key ];
		}

		return $ref;
	}

	/**
	 * Get the trigger identifier
	 *
	 * @return string
	 */
	public static function get_id() {
		return 'wordpress/webhook_receive';
	}

	/**
	 * Get the trigger hook
	 *
	 * @return string
	 */
	public static function get_wp_hook() {
		return 'tap_receive_webhook_trigger';
	}

	public function get_automation_wp_hook() {
		return empty( $this->data['webhook_hash'] ) ? static::get_wp_hook() : Utils::create_dynamic_trigger( static::get_wp_hook(), $this->data['webhook_hash']['value'] );
	}

	/**
	 * Get the trigger provided params
	 *
	 * @return array
	 */
	public static function get_provided_data_objects() {
		return [];
	}

	/**
	 * Get the number of params
	 *
	 * @return int
	 */
	public static function get_hook_params_number() {
		return 1;
	}

	/**
	 * Get the trigger name
	 *
	 * @return string
	 */
	public static function get_name() {
		return __( 'Incoming webhook', 'thrive-automator' );
	}

	/**
	 * Get the trigger description
	 *
	 * @return string
	 */
	public static function get_description() {
		return __( 'Trigger will be fired when receiving a webhook.', 'thrive-automator' );
	}

	/**
	 * Get the trigger logo
	 *
	 * @return string
	 */
	public static function get_image() {
		return 'tap-send-webhook';
	}

	public static function get_required_trigger_fields() {
		return [];
	}

	/**
	 * For situations where data objects provided by the trigger is altered by a trigger field
	 *
	 * @throws Exception
	 */
	public static function sync_trigger_data( $trigger_data ) {
		if ( ! empty( $trigger_data['extra_data']['webhook_hash']['value'] ) ) {
			$webhook_id   = $trigger_data['extra_data']['webhook_hash']['value'];
			$primary_keys = Data_Field::get_all_primary_keys();
			foreach ( Utils::get_automator_webhook_fields( $webhook_id ) as $webhook_field ) {
				if ( in_array( $webhook_field['field_type'], array_keys( $primary_keys, true ), true ) ) {
					$trigger_data['provided_params'][] = $primary_keys[ $webhook_field['field_type'] ]['primary_key'];
				}
			}

		}
		$trigger_data['provided_params'] = array_unique( $trigger_data['provided_params'] );

		return $trigger_data;
	}
}
