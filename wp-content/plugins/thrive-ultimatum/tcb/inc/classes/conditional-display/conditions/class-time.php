<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

namespace TCB\ConditionalDisplay\Conditions;

use TCB\ConditionalDisplay\Condition;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Time extends Date_And_Time_Picker {
	/**
	 * @return string
	 */
	public static function get_key() {
		return 'time';
	}

	/**
	 * @return string
	 */
	public static function get_label() {
		return esc_html__( 'Time comparison', 'thrive-cb' );
	}

	public function apply( $data ) {
		$compared_value        = $this->get_value();
		$formatted_field_value = strtotime( $data['field_value'] );

		$formatted_compared_value = strtotime( $compared_value['hours'] . ':' . $compared_value['minutes'] );

		switch ( $this->get_operator() ) {
			case 'before':
				$result = $formatted_field_value <= $formatted_compared_value;
				break;
			case 'after':
				$result = $formatted_field_value >= $formatted_compared_value;
				break;
			case 'between':
				$formatted_start_interval = $formatted_compared_value;
				$formatted_end_interval   = strtotime( $this->get_extra()['hours'] . ':' . $this->get_extra()['minutes'] );

				$result = $formatted_field_value >= $formatted_start_interval &&
				          $formatted_field_value <= $formatted_end_interval;
				break;
			default:
				$result = false;
		}

		return $result;
	}

	public static function get_control_type() {
		return static::get_key();
	}

	public static function get_operators() {
		return [
			'before'  => [
				'label' => 'before',
			],
			'after'   => [
				'label' => 'after',
			],
			'between' => [
				'label' => 'between',
			],
		];
	}
}
