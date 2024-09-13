<?php

namespace Thrive\Automator\Items;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Delay {
	/**
	 * Delay object key
	 */
	static protected $key = 'delay';

	/**
	 * Actual instance value
	 */
	private $value;

	/**
	 * Actual instance time unit
	 */
	private $unit;

	/**
	 * Create delay instance with provided values
	 *
	 * @param array $settings
	 */
	public function __construct( $settings ) {
		$this->value = $settings['value'];
		$this->unit  = $settings['unit'];
	}

	/**
	 * Get calculated timestamp depending on instance
	 *
	 * @return int
	 */
	public function calculate() {
		return static::dropdown_options()[ $this->unit ]['multiplier'] * $this->value + time();
	}

	/**
	 * Get delay unit options
	 *
	 * @return array
	 */
	public static function dropdown_options() {
		return [
			'minutes' => [
				'key'        => 'minutes',
				'label'      => __( 'Minute(s)', 'thrive-automator' ),
				'multiplier' => MINUTE_IN_SECONDS,
			],
			'hours'   => [
				'key'        => 'hours',
				'label'      => __( 'Hour(s)', 'thrive-automator' ),
				'multiplier' => HOUR_IN_SECONDS,
			],
			'days'    => [
				'key'        => 'days',
				'label'      => __( 'Day(s)', 'thrive-automator' ),
				'multiplier' => DAY_IN_SECONDS,
			],
			'weeks'   => [
				'key'        => 'weeks',
				'label'      => __( 'Week(s)', 'thrive-automator' ),
				'multiplier' => WEEK_IN_SECONDS,
			],
			'months'  => [
				'key'        => 'months',
				'label'      => __( 'Month(s)', 'thrive-automator' ),
				'multiplier' => MONTH_IN_SECONDS,
			],
			'years'   => [
				'key'        => 'years',
				'label'      => __( 'Year(s)', 'thrive-automator' ),
				'multiplier' => YEAR_IN_SECONDS,
			],
		];
	}

	/**
	 * Get object information wrapper
	 *
	 * @return array
	 */
	final public function localize_data() {
		return $this->get_info();
	}

	/**
	 * Get object information
	 *
	 * @return array
	 */
	public function get_info() {
		return [
			'id'    => static::$key,
			'value' => $this->value,
			'unit'  => $this->unit,
		];
	}


}
