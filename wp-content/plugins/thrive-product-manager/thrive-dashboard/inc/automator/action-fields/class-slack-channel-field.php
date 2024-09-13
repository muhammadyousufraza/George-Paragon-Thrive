<?php

namespace TVE\Dashboard\Automator;

use Thrive\Automator\Items\Action_Field;
use Thrive_Dash_List_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Form_List_Field
 */
class Slack_Channel_Field extends Action_Field {
	/**
	 * Field name
	 */
	public static function get_name() {
		return __( 'Slack channel', 'thrive-dash' );
	}

	/**
	 * Field description
	 */
	public static function get_description() {
		return __( 'Choose the slack channel you want to use', 'thrive-dash' );
	}

	/**
	 * Field input placeholder
	 */
	public static function get_placeholder() {
		return __( 'Select a channel', 'thrive-dash' );
	}

	/**
	 * $$value will be replaced by field value
	 * $$length will be replaced by value length
	 *
	 *
	 * @return string
	 */
	public static function get_preview_template() {
		return '';
	}

	public static function get_id() {
		return 'slack_channel';
	}

	public static function get_type() {
		return 'select';
	}

	public static function get_options_callback( $action_id, $action_data ) {
		$values = array();

		$api_instance = Thrive_Dash_List_Manager::connection_instance( 'slack' );
		if ( $api_instance && $api_instance->is_connected() ) {
			$channels = $api_instance->get_channel_list();

			foreach ( $channels as $channel ) {
				$values[ $channel->id ] = [ 'name' => $channel->name, 'id' => $channel->id ];
			}
		}


		return $values;
	}

	public static function get_validators() {
		return [ 'required' ];
	}
}
