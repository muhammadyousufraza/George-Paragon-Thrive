<?php

namespace Thrive\Automator\Items;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class User_Data
 *
 * @property-read string $user_email
 */
class User_Data extends Data_Object {

	/**
	 * Get the data-object identifier
	 *
	 * @return string
	 */
	public static function get_id() {
		return 'user_data';
	}

	public static function get_nice_name() {
		return __( 'Registered user', 'thrive-automator' );
	}

	/**
	 * Array of field object keys that are contained by this data-object
	 *
	 * @return array
	 */
	public static function get_fields() {
		return [
			User_Id_Data_Field::get_id(),
			Last_Login_Data_Field::get_id(),
			User_Registered_Data_Field::get_id(),
			Username_Data_Field::get_id(),
			User_Role_Data_Field::get_id(),
			User_Email_Data_Field::get_id(),
			Firstname_Data_Field::get_id(),
			Lastname_Data_Field::get_id(),
		];
	}

	public static function create_object( $param ) {
		$user = null;
		if ( is_a( $param, 'WP_User' ) ) {
			$user = $param;
		} elseif ( is_numeric( $param ) ) {
			$user = get_userdata( $param );
		} elseif ( ! empty( $param['user_id'] ) && is_numeric( $param['user_id'] ) ) {
			$user = get_userdata( $param['user_id'] );
		} elseif ( ! empty( $param['email'] ) && is_email( $param['email'] ) ) {
			$user = get_user_by( 'email', $param['email'] );
		} elseif ( is_array( $param ) ) {
			$user = get_userdata( $param[0] );
		} elseif ( is_email( $param ) ) {
			$user = get_user_by( 'email', $param );
		}

		if ( $user ) {
			$user_meta = get_user_meta( $user->ID );

			return [
				'user_id'         => $user->ID,
				'last_login'      => ( isset( $user_meta['tve_last_login'] ) && is_array( $user_meta['tve_last_login'] ) ) ? date( 'Y-m-d H:i:s', (int) $user_meta['tve_last_login'][0] ) : '',
				'user_registered' => $user->user_registered,
				'username'        => $user->user_login,
				'user_role'       => $user->roles,
				'user_email'      => $user->user_email,
				'first_name'      => $user->first_name,
				'last_name'       => $user->last_name,
			];
		}

		return $user;
	}

	public function can_provide_email() {
		return true;
	}

	public function get_provided_email() {
		return $this->get_value( 'user_email' );
	}

	public static function get_data_object_options() {
		$users   = get_users();
		$options = [];

		foreach ( $users as $key => $user ) {
			$user_id = $user->ID;

			if ( isset( $key ) & ! empty( $user_id ) ) {
				$options[ $user_id ] = [ 'id' => $user_id, 'label' => $user->user_login ];
			}
		}

		return $options;
	}
}
