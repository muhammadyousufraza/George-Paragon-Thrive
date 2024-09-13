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
class Tag_User extends Action {

	private $autoresponder;

	private $tags;

	private $additional = array();

	/**
	 * Get the action identifier
	 *
	 * @return string
	 */
	public static function get_id() {
		return 'thrive/taguser';
	}

	/**
	 * Get the action name/label
	 *
	 * @return string
	 */
	public static function get_name() {
		return 'Tag user in autoresponder';
	}

	/**
	 * Get the action description
	 *
	 * @return string
	 */
	public static function get_description() {
		return 'Apply {num_items} tags to user in autoresponder';
	}

	/**
	 * Get the action logo
	 *
	 * @return string
	 */
	public static function get_image() {
		return 'tap-tag-user';
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
		return array( 'autoresponder' => 'tag_input' );
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
				$fields = $api_instance->get_automator_tag_autoresponder_mapping_fields();
			}
		}

		return $fields;
	}

	public function prepare_data( $data = array() ) {
		if ( ! empty( $data['extra_data'] ) ) {
			$data = $data['extra_data'];
		}

		foreach ( $data['autoresponder']['subfield'] as $key => $subfield ) {
			$this->additional[ $key ] = $subfield['value'];
		}

		$this->autoresponder = $data['autoresponder']['value'];
		if ( ! empty( $data['tag_input']['value'] ) ) {
			$this->tags = $data['tag_input']['value'];
		}//tags were moved as subfields of autoresponder
		elseif ( ! empty( $this->additional['tag_input'] ) ) {
			$this->tags = $this->additional['tag_input'];
		} elseif ( ! empty( $this->additional['tag_select'] ) ) {
			$this->tags = $this->additional['tag_select'];
		}
	}

	public function do_action( $data ) {

		$email = '';

		$data_sets = Main::get_email_data_sets();

		global $automation_data;
		while ( ! empty( $data_sets ) && empty( $email ) ) {
			$set = array_shift( $data_sets );

			$data_object = $automation_data->get( $set );
			if ( ! empty( $data_object ) && $data_object->can_provide_email() ) {
				$email = $data_object->get_provided_email();
			}
		}

		if ( empty( $email ) ) {
			return false;
		}

		$apis = Thrive_Dash_List_Manager::get_available_apis( true );
		$api  = $apis[ $this->autoresponder ];

		if ( empty( $api ) ) {
			return false;
		}

		$extra      = [];
		$tags_value = $this->tags;
		if ( is_array( $tags_value ) ) {
			$tags_value = implode( ', ', $tags_value );
		}

		if ( ! empty( $this->additional['mailing_list'] ) ) {
			$extra['list_identifier'] = $this->additional['mailing_list'];
		}

		$api->update_tags( $email, $tags_value, $extra );
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
			if ( ! empty( $automation_data->get( $key ) ) ) {
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
