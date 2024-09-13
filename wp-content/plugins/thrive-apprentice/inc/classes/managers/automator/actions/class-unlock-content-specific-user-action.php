<?php

namespace TVA\Automator;

use Thrive\Automator\Items\Action;
use Thrive\Automator\Items\User_Data;
use Thrive\Automator\Items\User_Id_Data_Field;
use Thrive\Automator\Utils;
use TVA_Const;
use function TVA\Drip\unlock_content_for_specific_user;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Unlock_Content_Specific_User extends Action {

	protected $content_type;

	protected $content;

	/**
	 * Get the action identifier
	 *
	 * @return string
	 */
	public static function get_id() {
		return 'thrive/unlockcontentforspecificuser';
	}

	/**
	 * Get the action name/label
	 *
	 * @return string
	 */
	public static function get_name() {
		return 'Unlock content for specific user';
	}

	/**
	 * Get the action description
	 *
	 * @return string
	 */
	public static function get_description() {
		return static::get_name();
	}

	/**
	 * Get the action logo
	 *
	 * @return string
	 */
	public static function get_image() {
		return 'tap-unlock-content-user';
	}

	/**
	 * Get the name of app to which action belongs
	 *
	 * @return string
	 */
	public static function get_app_id() {
		return Apprentice_App::get_id();
	}

	/**
	 * Array of action-field keys, required for the action to be setup
	 *
	 * @return array
	 */
	public static function get_required_action_fields() {
		return array( 'content_type' => array( 'content' ) );
	}

	/**
	 * Get an array of keys with the required data-objects
	 *
	 * @return array
	 */
	public static function get_required_data_objects() {
		return [ 'user_data' ];
	}

	public function prepare_data( $data = array() ) {
		$content_type = $data['content_type'];
		/* it's ok to take the data like this because both fields are required */
		if ( ! empty( $content_type ) && isset( $content_type['subfield']['content']['value'] ) ) {
			$this->content_type = $content_type['value'];
			$this->content      = $content_type['subfield']['content']['value'];
		}
	}

	public function do_action( $data ) {

		global $automation_data;
		if ( empty( $automation_data->get( User_Data::get_id() ) ) ) {
			return false;
		}

		$mapped_products = [];

		foreach ( $this->content as $content ) {

			$potential_value = Utils::get_dynamic_data_object_from_automation( $content, [ Module_Id_Data_Field::get_id(), Lesson_Id_Data_Field::get_id() ] );

			if ( ! empty( $potential_value ) ) {
				$mapped_products[] = (int) $potential_value;
			}
		}
		$mapped_products = array_unique( $mapped_products );

		foreach ( $mapped_products as $post_id ) {
			unlock_content_for_specific_user( (int) $post_id, $automation_data->get( User_Data::get_id() )->get_value( User_Id_Data_Field::get_id() ) );
		}
	}

	/**
	 * Set the allowed dynamic fields for this action based on the content type
	 *
	 * @param $subfields
	 * @param $current_value
	 * @param $action_data
	 *
	 * @return array
	 * @throws \Exception
	 */
	public static function get_subfields( $subfields, $current_value, $action_data ) {
		$subfields_data = parent::get_subfields( $subfields, $current_value, $action_data );

		if ( ! empty( $subfields[0] ) && $subfields[0] === 'content' ) {
			$subfields_data['content']['allowed_data_set_values'] = [ $current_value === TVA_Const::MODULE_POST_TYPE ? Module_Data::get_id() : Lesson_Data::get_id() ];
		}

		return $subfields_data;
	}
}
