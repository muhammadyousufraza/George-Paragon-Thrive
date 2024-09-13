<?php

namespace TVE\Dashboard\Automator;

use Thrive\Automator\Items\Action_Field;
use function wc_get_order_statuses;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Woo_Order_Status_Field
 */
class Woo_Order_Status_Field extends Action_Field {
	/**
	 * Field name
	 */
	public static function get_name() {
		return 'Change order to the following status';
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
		return __( 'Select a status to set on the order', 'thrive-dash' );
	}

	/**
	 * $$value will be replaced by field value
	 * $$length will be replaced by value length
	 *
	 *
	 * @return string
	 */
	public static function get_preview_template() {
		return 'Status: $$value';
	}

	/**
	 * For multiple option inputs, name of the callback function called through ajax to get the options
	 */
	public static function get_options_callback( $action_id, $action_data ) {
		$statuses = array();

		foreach ( wc_get_order_statuses() as $key => $status ) {
			$statuses[ $key ] = array(
				'label' => $status,
				'id'    => $key,
			);
		}

		return $statuses;
	}

	public static function get_id() {
		return 'woo_order_status';
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
