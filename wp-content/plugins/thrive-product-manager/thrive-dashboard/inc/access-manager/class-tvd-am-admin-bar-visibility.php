<?php

namespace TVD\Dashboard\Access_Manager;

use TVD\Dashboard\Access_Manager\Functionality;


if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Admin_Bar_Visibility extends Functionality {
	public static function hooks() {
		add_filter( 'show_admin_bar', array( __CLASS__, 'show_admin_bar' ) );
	}

	public static function get_name() {
		return __( 'Admin Bar Visibility', 'thrive-dash' );
	}

	public static function get_tag() {
		return 'admin_bar_visibility';
	}

	public static function get_default() {
		return 'inherit';
	}

	public static function get_options() {
		return array(
			[
				'name' => 'Inherit',
				'tag'  => 'inherit',
			],
			[
				'name' => 'Hidden',
				'tag'  => 'hidden',
			],
			[
				'name' => 'Displayed',
				'tag'  => 'displayed',
			],
		);
	}

	public static function get_icon() {
		return 'wordpress';
	}

	public static function get_options_type() {
		return 'dropdown';
	}

	public static function get_option_value( $user_role ) {
		return get_option( static::get_option_name( $user_role ) );
	}

	public static function show_admin_bar( $show_admin_bar ) {
		if ( is_user_logged_in() ) {
			$user_role  = reset( wp_get_current_user()->roles );
			$visibility = static::get_option_value( $user_role );

			switch ( $visibility ) {
				case 'displayed':
					$show_admin_bar = true;
					break;
				case 'hidden':
					$show_admin_bar = false;
					break;
				default:
					break;
			}
		}

		return $show_admin_bar;
	}
}
