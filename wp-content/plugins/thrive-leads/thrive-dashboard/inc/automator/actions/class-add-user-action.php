<?php

namespace TVE\Dashboard\Automator;

use Thrive\Automator\Items\Action;
use Thrive_Dash_List_Manager;
use function Thrive\Automator\tap_logger;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Tag_User
 */
class Add_User extends Action {
	private $autoresponder;

	private $additional = array();

	/**
	 * Get the action identifier
	 *
	 * @return string
	 */
	public static function get_id() {
		return 'thrive/adduser';
	}

	/**
	 * Get the action name/label
	 *
	 * @return string
	 */
	public static function get_name() {
		return 'Add user in autoresponder';
	}

	/**
	 * Get the action description
	 *
	 * @return string
	 */
	public static function get_description() {
		return 'Add user to autoresponder';
	}

	/**
	 * Get the action logo
	 *
	 * @return string
	 */
	public static function get_image() {
		return 'tap-add-user';
	}

	public static function get_app_id() {
		return 'email';
	}

	public static function get_required_data_objects() {
		return array( 'user_data', 'form_data', 'email_data' );
	}

	/**
	 * Array of action-field keys, required for the action to be setup
	 *
	 * @return array
	 */
	public static function get_required_action_fields() {
		return array( 'autoresponder' => array( 'mailing_list' ) );
	}

	public function prepare_data( $data = array() ) {
		if ( ! empty( $data['extra_data'] ) ) {
			$data = $data['extra_data'];
		}

		$this->autoresponder = $data['autoresponder']['value'];

		$this->build_subfield( $data['autoresponder']['subfield'] );
	}

	/**
	 * Init all subfields
	 *
	 * @param $data
	 */
	public function build_subfield( $data ) {
		foreach ( $data as $key => $subfield ) {
			if ( ! empty( $subfield['value'] ) ) {
				$this->additional[ $key ] = $subfield['value'];
			}
			if ( ! empty( $subfield['subfield'] ) ) {
				$this->build_subfield( $subfield['subfield'] );
			}
		}
	}


	public function do_action( $data ) {
		$email = '';


		global $automation_data;
		$data_sets = Main::get_email_data_sets();
		/**
		 * Try to get email for available data objects
		 */
		while ( ! empty( $data_sets ) && empty( $email ) ) {
			$set         = array_shift( $data_sets );
			$data_object = $automation_data->get( $set );
			if ( ! empty( $data_object ) && $data_object->can_provide_email() ) {
				$email = $data_object->get_provided_email();
			}
		}

		if ( empty( $email ) ) {
			return false;
		}
		$api_load = array( 'email' => $email );

		$apis = Thrive_Dash_List_Manager::get_available_apis( true );

		if ( empty( $apis[ $this->autoresponder ] ) ) {
			return false;
		}

		$api = $apis[ $this->autoresponder ];

		if ( ! empty( $this->additional['tag_input'] ) && $api->has_tags() ) {
			$tags = $this->additional['tag_input'];
			if ( is_array( $tags ) ) {
				$tags = implode( ', ', $tags );
			}
			$api_load[ $api->get_tags_key() ] = $tags;
		}

		if ( ! empty( $this->additional['tag_select'] ) && $api->has_tags() ) {
			$tags                             = $this->additional['tag_select'];
			$api_load[ $api->get_tags_key() ] = $tags;
		}

		if ( ! empty( $this->additional['optin'] ) && $api->has_optin() ) {
			$api_load[ $api->get_optin_key() ] = $this->additional['optin'];
		}

		if ( ! empty( $this->additional['form_list'] ) && $api->has_forms() ) {
			$api_load[ $api->get_forms_key() ] = $this->additional['form_list'];
		}

		$list_identifier = ! empty( $this->additional['mailing_list'] ) ? $this->additional['mailing_list'] : null;

		if ( ! empty( $this->additional['api_fields'] ) ) {
			$name = $this->get_specific_field_value( 'name' );
			if ( ! empty( $name ) ) {
				$api_load['name'] = $name;
			}
			$phone = $this->get_specific_field_value( 'phone' );
			if ( ! empty( $phone ) ) {
				$api_load['phone'] = $phone;
			}
		}

		if ( ! empty( $this->additional['api_fields'] ) && $api->has_custom_fields() ) {
			$api_load['automator_custom_fields'] = $api->build_automation_custom_fields( $this->additional );
		}

		return $api->add_subscriber( $list_identifier, $api_load );
	}

	public function get_specific_field_value( $field, $unset = true ) {
		$key   = array_search( $field, array_column( $this->additional['api_fields'], 'key' ) );
		$value = false;
		if ( $key !== false ) {
			$value = $this->additional['api_fields'][ $key ]['value'];
			if ( $unset ) {
				array_splice( $this->additional['api_fields'], $key, 1 );
			}
		}

		return $value;
	}

	/**
	 * For APIs with forms add it as required field
	 *
	 * @param $data
	 *
	 * @return array|string[][]|string[][][]
	 */
	public static function get_action_mapped_fields( $data ) {
		$fields = static::get_required_action_fields();
		if ( property_exists( $data, 'autoresponder' ) ) {
			$api_instance = \Thrive_Dash_List_Manager::connection_instance( $data->autoresponder->value );

			if ( $api_instance !== null && $api_instance->is_connected() ) {
				$fields = $api_instance->get_automator_add_autoresponder_mapping_fields();
			}
		}

		return $fields;
	}

	public static function get_subfields( $subfields, $current_value, $action_data ) {
		$fields = parent::get_subfields( $subfields, $current_value, $action_data );
		/**
		 * Remove required validation for tags
		 */
		if ( isset( $fields[ Tag_Input_Field::get_id() ] ) ) {
			$fields[ Tag_Input_Field::get_id() ]['validators'] = array();
		}

		return $fields;
	}

	/**
	 * Match all trigger that provice user/form data
	 *
	 * @param $trigger
	 *
	 * @return bool
	 */
	public static function is_compatible_with_trigger( $provided_data_objects ) {
		$action_data_keys = static::get_required_data_objects() ?: array();

		return count( array_intersect( $action_data_keys, $provided_data_objects ) ) > 0;
	}

	public function can_run( $data ) {
		$valid          = true;
		$available_data = array();
		global $automation_data;
		foreach ( Main::get_email_data_sets() as $key ) {
			$data_set = $automation_data->get( $key );
			if ( ! empty( $data_set ) && $data_set->can_provide_email() && ! empty( $data_set->get_provided_email() ) ) {
				$available_data[] = $key;
			}
		}

		if ( empty( $available_data ) ) {
			$valid = false;
			tap_logger( $this->aut_id )->register( [
				'key'         => static::get_id(),
				'id'          => 'data-not-provided-to-action',
				'message'     => 'Data object required by ' . static::class . ' action is not provided by trigger',
				'class-label' => tap_logger( $this->aut_id )->get_nice_class_name( static::class ),
			] );
		}

		return $valid;
	}

}
