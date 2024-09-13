<?php

namespace TVE\Dashboard\Automator;

use Thrive\Automator\Items\Data_Object;
use Thrive\Automator\Items\Form_Email_Data_Field;
use Thrive\Automator\Items\Form_Name_Data_Field;
use Thrive\Automator\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Elementor_Form_Data
 */
class Elementor_Form_Data extends Data_Object {

	/**
	 * Get the data-object identifier
	 *
	 * @return string
	 */
	public static function get_id() {
		return 'elementor_form_data';
	}

	public static function get_nice_name() {
		return __( 'Elementor form data', 'thrive-dash' );
	}

	/**
	 * Array of field object keys that are contained by this data-object
	 *
	 * @return array
	 */
	public static function get_fields() {
		return [
			Form_Email_Data_Field::get_id(),
			Form_Name_Data_Field::get_id(),
		];
	}

	public static function create_object( $param ) {
		$post_data = [];

		if ( is_array( $param ) ) {
			$allowed_keys = [
				'name',
				'email',
				'message',
				'url',
			];

			/**
			 * Fields that can be set for WP connection
			 */
			$extra_mapped_keys = [ 'nickname', 'description', 'user_url', 'url' ];
			$extra_keys_regex  = implode( '|', $extra_mapped_keys );

			foreach ( $param as $key => $value ) {
				if ( in_array( $key, $allowed_keys, true ) || strpos( $key, 'field_' ) !== false || preg_match( "/^($extra_keys_regex)/", $key ) ) {
					$post_data[ $key ] = $value;
				}
			}

		} elseif ( is_email( $param ) ) {
			$post_data['email'] = $param;
		}

		return $post_data;
	}

	public function replace_dynamic_data( $value ) {
		$value = parent::replace_dynamic_data( $value );
		$value = Utils::replace_additional_data_shortcodes( $value, $this->data );

		// replace shortcodes if values are not provided by the form
		if ( is_array( $value ) ) {
			foreach ( $value as &$field ) {
				if ( $field['value'] ) {

					preg_match_all( '/%+field_[a-zA-Z0-9]{7}%/', $field['value'], $matches );
					foreach ( $matches as $shortcode ) {
						$field['value'] = str_replace( $shortcode, '', $field['value'] );
					}
				}

			}
		}

		return $value;
	}

	public function can_provide_email() {
		return true;
	}

	public function get_provided_email() {
		return $this->get_value( 'email' );
	}

}
