<?php

namespace Thrive\Automator\Items;

use TCB\inc\helpers\FormSettings;
use Thrive\Automator\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Form_Data
 */
class Form_Data extends Data_Object {

	/**
	 * Get the data-object identifier
	 *
	 * @return string
	 */
	public static function get_id() {
		return 'form_data';
	}

	public static function get_nice_name() {
		return __( 'Form data', 'thrive-automator' );
	}

	/**
	 * Array of field object keys that are contained by this data-object
	 *
	 * @return array
	 */
	public static function get_fields() {
		return [
			Form_Consent_Data_Field::get_id(),
			Form_Email_Data_Field::get_id(),
			Form_Name_Data_Field::get_id(),
			Form_Phone_Data_Field::get_id(),
			'post_id',
			'form_identifier',
		];
	}

	public static function create_object( $param ) {
		$post_data = [];

		if ( is_array( $param ) ) {
			$allowed_keys = [
				'name',
				'email',
				'phone',
				'message',
				'url',
				'post_id',
			];

			/**
			 * Fields that can be set for WP connection and Ovation connection
			 */
			$extra_mapped_keys = [
				'nickname',
				'description',
				'user_url',
				'url',
				'role',
				'website_url',
				'question',
				'title',
			];
			$extra_keys_regex  = implode( '|', $extra_mapped_keys );

			foreach ( $param as $key => $value ) {
				if ( in_array( $key, $allowed_keys, true ) || strpos( $key, 'field_' ) !== false || strpos( $key, 'mapping_' ) !== false || preg_match( "/^($extra_keys_regex)/", $key ) ) {
					$post_data[ $key ] = $value;
				}
			}

			$post_data['user_consent'] = isset( $param['user_consent'] ) && $param['user_consent'];

			if ( isset( $param['_tcb_id'] ) && class_exists( '\TCB\inc\helpers\FormSettings', false ) ) {
				$form = FormSettings::get_one( $param['_tcb_id'] );

				$post_data['form_identifier'] = $form->form_identifier;
			}
		} elseif ( is_email( $param ) ) {
			$post_data['email'] = $param;
		}

		return $post_data;
	}

	public function replace_dynamic_data( $value ) {
		$value = parent::replace_dynamic_data( $value );

		return Utils::replace_additional_data_shortcodes( $value, $this->data );
	}

	public function can_provide_email() {
		return true;
	}

	public function get_provided_email() {
		return $this->get_value( 'email' );
	}

}
