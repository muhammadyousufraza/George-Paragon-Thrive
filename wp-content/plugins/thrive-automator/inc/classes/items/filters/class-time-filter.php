<?php

namespace Thrive\Automator\Items;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Time extends Date {

	/**
	 * Get the filter identifier
	 *
	 * @return string
	 */
	public static function get_id() {
		return 'time';
	}

	/**
	 * Get the filter name/label
	 *
	 * @return string
	 */
	public static function get_name() {
		return __( 'Time comparison', 'thrive-automator' );
	}

	public function time_to_seconds( $time ) {
		$str_time = preg_replace( "/^([\d]{1,2})\:([\d]{2})$/", "00:$1:$2", $time );

		sscanf( $str_time, "%d:%d:%d", $hours, $minutes, $seconds );

		return $hours * 3600 + $minutes * 60 + $seconds;
	}

	public function filter( $data ) {
		$now = current_time( 'timestamp' ) - strtotime( current_time( 'Y-m-d' ) );

		switch ( $this->operation ) {
			case 'more':
				$result = $now > $this->time_to_seconds( $data['value'] ) + $this->get_added_time();
				break;
			case 'less':
				$result = $this->time_to_seconds( $data['value'] ) > $now - $this->get_added_time();
				break;
			case 'before':
				$result = $this->time_to_seconds( $data['value'] ) < $this->time_to_seconds( $this->value );
				break;
			case 'after':
				$result = $this->time_to_seconds( $data['value'] ) > $this->time_to_seconds( $this->value );
				break;
			case 'equals':
				$result = $this->time_to_seconds( $data['value'] ) === $this->value;
				break;
			default:
				$result = false;
		}

		return $result;
	}

	private function get_added_time() {
		switch ( $this->um ) {
			case 'hours':
				$result = $this->value * HOUR_IN_SECONDS;
				break;
			case 'minutes':
				$result = $this->value * MINUTE_IN_SECONDS;
				break;
			default:
				$result = $this->value;
		}

		return $result;
	}

}
