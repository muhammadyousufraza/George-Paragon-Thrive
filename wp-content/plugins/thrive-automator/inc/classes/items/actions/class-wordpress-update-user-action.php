<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-automator
 */

namespace Thrive\Automator\Items;

use WP_Error;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Wordpress_Update_User extends Wordpress_New_Register {

	protected $update_profile_fields;

	public static function get_id(): string {
		return 'wordpress/update_user';
	}

	public static function get_name(): string {
		return __( 'Update user', 'thrive-automator' );
	}

	public static function get_description(): string {
		return __( 'This action will find a user with the provided email address and update their account details. If there is no email match, the action will be skipped. If the user’s role does not have custom fields, their data will be ignored.', 'thrive-automator' );
	}

	public static function get_required_action_fields(): array {
		return [ Update_Profile_Fields::get_id() ];
	}

	public static function get_required_data_objects(): array {
		return [ User_Data::get_id() ];
	}

	public function prepare_data( $data = [] ) {
		foreach ( static::get_required_action_fields() as $field ) {
			if ( ! empty( $data[ $field ]['value'] ) && $field === 'update_profile_fields' ) {
				$this->$field = $this->construct_additional_fields( $data[ $field ]['value'] );
			}
		}
	}

	public function do_action( $data ) {
		global $automation_data;

		if ( ! empty( $automation_data->get( User_Data::get_id() ) ) ) {
			$email = $automation_data->get( User_Data::get_id() )->get_provided_email();
		}
		if ( ! empty( $email ) ) {
			$email = sanitize_email( $email );
		}

		if ( ! empty( $email ) ) {
			$user = get_user_by( 'email', $email );

			if ( $user ) {
				$user_id = $user->ID;

				if ( ! empty( $this->update_profile_fields ) ) {
					$user_role     = $user->roles;
					$social_fields = get_user_meta( $user_id, 'thrive_social_urls' );
					$userdata      = $this->construct_userdata( $this->update_profile_fields, $user_role, $social_fields );

					foreach ( $userdata as $key => $val ) {
						if ( $key === 'user_url' ) {
							wp_update_user( array( 'ID' => $user_id, $key => $val ) );
						} else {
							update_user_meta( $user_id, $key, $val );
						}
					}
				}

				if ( ! $user_id instanceof WP_Error ) {
					$automation_data->set( 'user_data', new User_Data( $user_id, $this->get_automation_id() ) );
				}
			}
		}
	}
}
