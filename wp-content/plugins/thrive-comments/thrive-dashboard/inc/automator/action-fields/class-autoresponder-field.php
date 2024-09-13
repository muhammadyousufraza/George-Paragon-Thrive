<?php

namespace TVE\Dashboard\Automator;

use Thrive\Automator\Items\Action_Field;
use Thrive_Dash_List_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Autoresponder_Field
 */
class Autoresponder_Field extends Action_Field {
	/**
	 * Field name
	 */
	public static function get_name() {
		return 'Autoresponder';
	}

	/**
	 * Field description
	 */
	public static function get_description() {
		return static::get_placeholder();
	}

	/**
	 * Field input placeholder
	 */
	public static function get_placeholder() {
		return __( 'Choose service from your list of registered APIs to use', 'thrive-dash' );
	}

	/**
	 * $$value will be replaced by field value
	 * $$length will be replaced by value length
	 *
	 *
	 * @return string
	 */
	public static function get_preview_template() {
		return 'Autoresponder: $$value';
	}

	/**
	 * For multiple option inputs, name of the callback function called through ajax to get the options
	 */
	public static function get_options_callback( $action_id, $action_data ) {
		$is_tag_action = $action_id === Tag_User::get_id();

		$apis   = Thrive_Dash_List_Manager::get_available_apis( true, [
			'exclude_types' => [
				'email',
				'webinar',
				'other',
				'recaptcha',
				'social',
				'sellings',
				'integrations',
				'storage',
				'collaboration',
				'testimonial',
			],
		] );
		$values = array();
		foreach ( $apis as $api ) {
			//email is seen as autoresponder
			$allow_tags = true;
			if ( $is_tag_action ) {
				$allow_tags = $api->has_tags();
			}
			if ( $allow_tags && ! in_array( $api->get_key(), array( 'email', 'wordpress' ) ) ) {
				$values[ $api->get_key() ] = array(
					'id'    => $api->get_key(),
					'label' => $api->get_title(),
				);
			}
		}

		return $values;
	}

	public static function get_id() {
		return 'autoresponder';
	}

	public static function get_type() {
		return 'select';
	}

	public static function is_ajax_field() {
		return true;
	}

	public static function get_validators() {
		return array( 'required' );
	}

}
