<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

namespace TCB\ConditionalDisplay\Conditions;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Date extends Date_And_Time_Picker {
	/**
	 * @return string
	 */
	public static function get_key() {
		return 'date';
	}

	/**
	 * @return string
	 */
	public static function get_label() {
		return esc_html__( 'Date comparison', 'thrive-cb' );
	}

	public function apply( $data ) {
		$result = false;

		$field_value = $data['field_value'];

		if ( ! empty( $field_value ) ) {
			$formatted_field_value    = strtotime( $data['field_value'] );
			$formatted_compared_value = strtotime( $this->get_value() );

			switch ( $this->get_operator() ) {
				case 'equals':
					$result = strtotime( date( 'Y/m/d', $formatted_field_value ) )
					          ===
					          strtotime( date( 'Y/m/d', $formatted_compared_value ) );
					break;
				case 'between':
					/* reduce the format to date-only */
					$formatted_start_interval = strtotime( date( 'Y/m/d', $formatted_compared_value ) );
					$formatted_end_interval   = strtotime( date( 'Y/m/d', strtotime( $this->get_extra() ) ) );
					$formatted_field_value    = strtotime( date( 'Y/m/d', $formatted_field_value ) );

					$result = $formatted_field_value >= $formatted_start_interval &&
					          $formatted_field_value <= $formatted_end_interval;
					break;
				default:
					$result = parent::apply( $data );
			}
		}

		return $result;
	}

	public static function get_operators() {
		return array_merge( parent::get_operators(), [
			'between' => [
				'label' => 'between',
			],
		] );
	}

	public static function get_control_type() {
		return static::get_key();
	}

	/**
	 * @return array
	 */
	public static function get_validation_data() {
		return [];
	}
}
