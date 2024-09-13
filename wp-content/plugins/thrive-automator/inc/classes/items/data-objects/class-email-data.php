<?php

namespace Thrive\Automator\Items;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Email_Data
 */
class Email_Data extends Data_Object {

	/**
	 * Get the data-object identifier
	 *
	 * @return string
	 */
	public static function get_id() {
		return 'email_data';
	}

	public static function get_nice_name() {
		return __( 'Email', 'thrive-automator' );
	}

	/**
	 * Array of field object keys that are contained by this data-object
	 *
	 * @return array
	 */
	public static function get_fields() {
		return [ Form_Email_Data_Field::get_id() ];
	}

	public static function create_object( $param ) {

		$data = [];
		if ( is_array( $param ) ) {
			$email = $param['email'];
		} else {
			$email = $param;
		}

		if ( is_email( $email ) ) {
			$data['email'] = $email;
		}

		return $data;
	}

	public function can_provide_email() {
		return true;
	}

	public function get_provided_email() {
		return $this->get_value( 'email' );
	}

}
