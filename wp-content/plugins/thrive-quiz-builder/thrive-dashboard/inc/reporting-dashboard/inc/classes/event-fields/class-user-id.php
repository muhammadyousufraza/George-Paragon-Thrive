<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */

namespace TVE\Reporting\EventFields;

class User_Id extends Event_Field {

	public static function key(): string {
		return 'user_id';
	}

	public static function can_group_by(): bool {
		return true;
	}

	public static function get_label( $singular = true ): string {
		return $singular ? 'User' : 'Users';
	}

	public static function format_value( $value ) {
		return (int) $value;
	}

	public function get_title(): string {
		if ( $this->value === null ) {
			$user_name = 'Users';
		} elseif ( (int) $this->value === 0 ) {
			$user_name = 'Unknown user';
		} else {
			$user = get_user_by( 'ID', $this->value );

			$user_name = $user instanceof \WP_User ? $user->display_name : "User $this->value";
		}

		return $user_name;
	}

	/**
	 * @return string
	 */
	public function get_image(): string {
		return get_avatar_url( $this->value );
	}

	public static function get_filter_options(): array {
		return array_map( static function ( $user ) {
			return [
				'id'    => $user->ID,
				'label' => $user->data->display_name,
			];
		}, get_users() );
	}

	/**
	 * Return true/false if this field contains an attached image.
	 *
	 * @return bool
	 */
	public static function has_image(): bool {
		return true;
	}
}
