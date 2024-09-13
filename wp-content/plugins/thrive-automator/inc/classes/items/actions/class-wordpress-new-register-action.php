<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-automator
 */

namespace Thrive\Automator\Items;

use Thrive\Automator\Utils;
use WP_Error;
use function get_editable_roles;
use function Thrive\Automator\tap_logger;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Wordpress_New_Register extends Action {

	protected $register_role;
	protected $first_name;
	protected $last_name;
	protected $additional_profile_fields;

	public static function get_id(): string {
		return 'wordpress/register_new_user';
	}

	public static function get_name(): string {
		return __( 'Find or create user', 'thrive-automator' );
	}

	public static function get_description(): string {
		return __( 'This action will check if the provided email address matches a user on your site. If not, a user account will be created and will be given the selected user role.', 'thrive-automator' );
	}

	public static function get_image(): string {
		return 'tap-wordpress-logo';
	}

	public static function get_app_id(): string {
		return Wordpress_App::get_id();
	}

	public static function get_required_action_fields(): array {
		return [ First_Name::get_id() => [], Last_Name::get_id() => [], Register_Role_Field::get_id() => [ Additional_Profile_Fields::get_id() ] ];
	}

	public static function get_all_action_fields(): array {
		return [ First_Name::get_id(), Last_Name::get_id(), Register_Role_Field::get_id() ];
	}

	public static function get_required_data_objects(): array {
		return [ Form_Data::get_id(), Email_Data::get_id() ];
	}

	public static function provides_data_objects(): array {
		return [ User_Data::get_id() ];
	}

	public function prepare_data( $data = [] ) {
		foreach ( static::get_all_action_fields() as $field ) {
			if ( ! empty( $data[ $field ]['value'] ) ) {
				$this->$field = $data[ $field ]['value'];

				/* Append additional fields */
				if ( ! empty( $data[ $field ]['subfield'] ) ) {
					foreach ( $data[ $field ]['subfield'] as $key => $subfield ) {
						if ( $key === 'additional_profile_fields' ) {
							$this->$key = $this->construct_additional_fields( $subfield['value'] );
						}
					}
				}
			}
		}
	}

	public function do_action( $data ) {
		global $automation_data;

		if ( ! empty( $automation_data->get( Form_Data::get_id() ) ) ) {
			$email = $automation_data->get( Form_Data::get_id() )->get_provided_email();
		}
		if ( ! empty( $automation_data->get( Email_Data::get_id() ) ) ) {
			$email = $automation_data->get( Email_Data::get_id() )->get_provided_email();
		}
		if ( ! empty( $email ) ) {
			$email = sanitize_email( $email );
		}

		if ( ! empty( $email ) ) {
			$user = get_user_by( 'email', $email );
			if ( $user ) {
				$user_id = $user->ID;
			} else {
				$sanitized_user_login = trim( sanitize_user( $email ) );
				if ( ! function_exists( 'get_editable_roles' ) || ! function_exists( 'wp_insert_user' ) ) {
					require_once( ABSPATH . '/wp-admin/includes/user.php' );
				}

				$user_roles = get_editable_roles();
				$user_role  = '';

				unset( $user_roles['administrator'], $user_roles['editor'] );

				if ( array_key_exists( $this->register_role, $user_roles ) ) {
					$user_role = $this->register_role;
				}

				$userdata = [
					'user_email' => $email,
					'user_login' => $sanitized_user_login,
					'first_name' => $this->first_name ?: '',
					'last_name'  => $this->last_name ?: '',
					'role'       => $user_role ?: '',
					'user_pass'  => wp_generate_password( 12, false ),
				];

				$user_id = wp_insert_user( $userdata );
				/**
				 * Trigger register_new_user so other plugins can hook into it
				 * wp_insert_user doesn't trigger email notification so we make sure that email can contain all the user's details
				 */
				if ( ! $user_id instanceof WP_Error ) {
					do_action( 'register_new_user', $user_id );
				}

				if ( ! empty( $this->additional_profile_fields ) ) {
					$userdata = $this->construct_userdata( $this->additional_profile_fields, $user_role );

					foreach ( $userdata as $key => $val ) {
						if ( $key === 'user_url' ) {
							wp_update_user( array( 'ID' => $user_id, $key => $val ) );
						} else {
							update_user_meta( $user_id, $key, $val );
						}
					}
				}
			}
			if ( ! $user_id instanceof WP_Error ) {
				$automation_data->set( 'user_data', new User_Data( $user_id, $this->get_automation_id() ) );
			}
		}
	}

	/**
	 * Construct additional fields that will be sent to the prepare_data function
	 *
	 * @param $fields
	 *
	 * @return array|array[]
	 */
	public function construct_additional_fields( $fields ): array {
		$additional_fields = array();

		if ( is_array( $fields ) ) {
			foreach ( $fields as $field ) {
				$subfield_item_name  = $field['key'];
				$subfield_item_value = $field['value'];
				$category            = 'default';
				$social_identifier   = TAP_USER_FIELD_SOCIAL_IDENTIFIER;
				$acf_identifier      = TAP_USER_FIELD_ACF_IDENTIFIER;

				if ( strpos( $subfield_item_name, $social_identifier ) !== false ) {
					$category           = 'social';
					$subfield_item_name = str_replace( $social_identifier, '', $subfield_item_name );
				} else if ( strpos( $subfield_item_name, $acf_identifier ) !== false ) {
					$category           = 'acf';
					$subfield_item_name = str_replace( $acf_identifier, '', $subfield_item_name );
				}

				$additional_fields[ $category ][ $subfield_item_name ] = $subfield_item_value;
			}
		}

		return $additional_fields;
	}

	/**
	 * Construct user data that will be used for the update/create action
	 *
	 * @param       $additional_fields_categories
	 * @param       $user_role
	 * @param array $social_fields
	 *
	 * @return array
	 */
	public function construct_userdata( $additional_fields_categories, $user_role, array $social_fields = array() ): array {
		$userdata = array();

		/* Social fields are only available when TTB is active*/
		if ( Utils::is_ttb_active() ) {
			if ( empty( $social_fields ) ) {
				$social_fields = array(
					'fb'   => '',
					't'    => '',
					'pin'  => '',
					'in'   => '',
					'xing' => '',
					'yt'   => '',
					'ig'   => '',
				);
			} else {
				$social_fields = $social_fields[0];
			}
		}

		foreach ( $additional_fields_categories as $additional_fields_category_name => $additional_fields_category_value ) {
			if ( ! empty( $additional_fields_category_value ) ) {
				foreach ( $additional_fields_category_value as $additional_field_name => $additional_field_value ) {
					if ( ! empty( $additional_field_value ) ) {
						if ( $additional_fields_category_name === 'social' && Utils::is_ttb_active() ) {
							$social_fields[ $additional_field_name ] = $additional_field_value;
						} else {
							/* Check if the user has access to the required ACF */
							if ( ( $additional_fields_category_name === 'acf' ) && ! Utils::user_has_access_to_field( $user_role, $additional_field_name ) ) {
								continue;
							}

							$userdata[ $additional_field_name ] = $additional_field_value;
						}
					}
				}
			}
		}

		if ( Utils::is_ttb_active() ) {
			$userdata['thrive_social_urls'] = $social_fields;
		}

		return $userdata;
	}

	/**
	 * Check if this action is compatible with a specific trigger
	 *
	 * @param $provided_data_objects
	 *
	 * @return bool
	 */
	public static function is_compatible_with_trigger( $provided_data_objects ): bool {
		$action_data_objects = static::get_required_data_objects() ?: [];

		return count( array_intersect( $action_data_objects, $provided_data_objects ) ) > 0;
	}

	/**
	 * We can run the register if either of data objects is available
	 *
	 * @param $data
	 *
	 * @return bool
	 */
	public function can_run( $data ): bool {
		$valid          = true;
		$available_data = array();
		global $automation_data;
		foreach ( static::get_required_data_objects() as $key ) {
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
