<?php

namespace Thrive\Automator\Items;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Date extends Filter {

	protected $value;

	protected $um;

	protected $operation;

	/**
	 * Get the filter identifier
	 *
	 * @return string
	 */
	public static function get_id() {
		return 'date';
	}

	/**
	 * Get the filter name/label
	 *
	 * @return string
	 */
	public static function get_name() {
		return __( 'Date comparison', 'thrive-automator' );
	}

	public function prepare_data( $data = [] ) {
		$this->operation = $data['operator'];
		$this->value     = empty( $data['value'] ) ? null : $data['value'];
		$this->um        = empty( $data['unit'] ) ? null : $data['unit'];
	}

	public function get_value_data() {
		return [
			'value'     => empty( $this->value ) ? null : $this->value,
			'operation' => empty( $this->operation ) ? null : $this->operation,
			'um'        => empty( $this->um ) ? null : $this->um,
		];
	}

	public function filter( $data ) {
		$now = current_time( 'timestamp' );
		switch ( $this->operation ) {
			case 'more':
				$result = $now > strtotime( $data['value'] ) + $this->get_added_time();
				break;
			case 'less':
				$result = strtotime( $data['value'] ) > $now - $this->get_added_time();
				break;
			case 'before':
				$result = strtotime( $data['value'] ) < strtotime( $this->value );
				break;
			case 'after':
				$result = strtotime( $data['value'] ) > strtotime( $this->value );
				break;
			case 'equals':
				$result = strtotime( date( 'Y-m-d', strtotime( $data['value'] ) ) ) === strtotime( $this->value );
				break;
			default:
				$result = false;
		}

		return $result;
	}

	private function get_added_time() {
		switch ( $this->um ) {

			case 'days':
				$result = $this->value * DAY_IN_SECONDS;
				break;
			case 'hours':
				$result = $this->value * HOUR_IN_SECONDS;
				break;
			case 'minutes':
				$result = $this->value * MINUTE_IN_SECONDS;
				break;
			case 'months':
				$result = $this->value * MONTH_IN_SECONDS;
				break;
			case 'years':
				$result = $this->value * YEAR_IN_SECONDS;
				break;
			default:
				$result = 0;

		}

		return $result;
	}

	public static function get_operators() {
		return [
			'more'   => [
				'label' => __( 'more than', 'thrive-automator' ),
			],
			'less'   => [
				'label' => __( 'less than', 'thrive-automator' ),
			],
			'equals' => [
				'label' => __( 'equals', 'thrive-automator' ),
			],
			'before' => [
				'label' => __( 'before', 'thrive-automator' ),
			],
			'after'  => [
				'label' => __( 'after', 'thrive-automator' ),
			],
		];
	}

}
