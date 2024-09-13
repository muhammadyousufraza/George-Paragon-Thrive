<?php

namespace TVE\Dashboard\Automator;

use Thrive\Automator\Items\Action_Field;
use Thrive_Dash_List_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Tag_Select_Field
 */
class Tag_Select_Field extends Action_Field {
	/**
	 * Field name
	 */
	public static function get_name() {
		return 'Add the following tags to the user';
	}

	/**
	 * Field description
	 */
	public static function get_description() {
		return 'Which tag(s) would you like to apply?  Use a comma to add multiple tags. Example:- tag1, tag2';
	}

	/**
	 * Field input placeholder
	 */
	public static function get_placeholder() {
		return 'Choose tag';
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

	/**
	 * For multiple option inputs, name of the callback function called through ajax to get the options
	 */
	public static function get_options_callback( $action_id, $action_data ) {
		$values = array();

		if ( ! empty( $action_data ) ) {
			if ( is_string( $action_data ) ) {
				$api = $action_data;
			} else if ( property_exists( $action_data, 'autoresponder' ) ) {
				$api = $action_data->autoresponder->value;
			}
		}
		if ( ! empty( $api ) ) {
			$api_instance = Thrive_Dash_List_Manager::connection_instance( $api );
			if ( $api_instance && $api_instance->is_connected() ) {

				$tags = $api_instance->getTags();
				foreach ( $tags as $key => $tag ) {
					$values[ $key ] = [ 'name' => $tag, 'id' => $key ];
				}
			}
		}

		return $values;
	}

	public static function get_id() {
		return 'tag_select';
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
