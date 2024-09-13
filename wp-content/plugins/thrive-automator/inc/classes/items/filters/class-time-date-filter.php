<?php

namespace Thrive\Automator\Items;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Time_Date extends Date {

	protected $value;

	protected $um;

	protected $operation;

	/**
	 * Get the filter identifier
	 *
	 * @return string
	 */
	public static function get_id() {
		return 'time_date';
	}

	/**
	 * Get the filter name/label
	 *
	 * @return string
	 */
	public static function get_name() {
		return __( 'Time and date comparison', 'thrive-automator' );
	}

	public function filter( $data ) {
		if ( $this->operation === 'equals' ) {
			$result = strtotime( date( 'Y-m-d', strtotime( $data['value'] ) ) ) === strtotime( $this->value );
		} else {
			$result = parent::filter( $data );
		}

		return $result;
	}
}
