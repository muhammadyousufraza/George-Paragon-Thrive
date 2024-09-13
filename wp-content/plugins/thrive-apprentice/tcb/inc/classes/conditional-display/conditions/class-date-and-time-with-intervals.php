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

class Date_And_Time_With_Intervals extends Date_And_Time_Picker {
	/**
	 * @return string
	 */
	public static function get_key() {
		return 'date_and_time_with_intervals';
	}

	public function apply( $data ) {
		$result = false;

		if ( ! empty( $data['field_value'] ) ) {
			$now = current_time( 'timestamp' );

			switch ( $this->get_operator() ) {
				case 'more':
					$result = $now > strtotime( $data['field_value'] ) + $this->get_added_time();
					break;
				case 'less':
					$result = strtotime( $data['field_value'] ) > $now - $this->get_added_time();
					break;
				default:
					$result = parent::apply( $data );
			}
		}

		return $result;
	}

	public static function get_operators() {
		return array_merge(
			[
				'more' => [
					'label' => 'more than',
				],
				'less' => [
					'label' => 'less than',
				],
			],
			parent::get_operators()
		);
	}

	private function get_added_time() {
		$um    = $this->get_um();
		$value = (int) $this->get_value();

		switch ( $um ) {
			case 'days':
				$result = $value * DAY_IN_SECONDS;
				break;
			case 'hours':
				$result = $value * HOUR_IN_SECONDS;
				break;
			case 'minutes':
				$result = $value * MINUTE_IN_SECONDS;
				break;
			case 'months':
				$result = $value * MONTH_IN_SECONDS;
				break;
			case 'years':
				$result = $value * YEAR_IN_SECONDS;
				break;
			default:
				$result = 0;
		}

		return $result;
	}

	/**
	 * @return array
	 */
	public static function get_validation_data() {
		return array_merge( parent::get_validation_data(), [
			'min_interval' => 1,
			'max_interval' => 1000,
		] );
	}
}
