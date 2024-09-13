<?php

namespace Thrive\Automator\Items;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Number_Comparison extends Filter {

	protected $value;

	protected $operation;

	/**
	 * Get the filter identifier
	 *
	 * @return string
	 */
	public static function get_id() {
		return 'number_comparison';
	}

	/**
	 * Get the filter name/label
	 *
	 * @return string
	 */
	public static function get_name() {
		return __( 'Number comparison', 'thrive-automator' );
	}

	public function prepare_data( $data = [] ) {
		$this->operation = $data['operator'];
		$this->value     = $data['value'];
	}

	public function get_value_data() {
		return [
			'value'     => empty( $this->value ) ? null : $this->value,
			'operation' => empty( $this->operation ) ? null : $this->operation,
		];
	}

	public function filter( $data ) {
		switch ( $this->operation ) {

			case 'more':
				$result = $data['value'] > $this->value;
				break;

			case 'less':
				$result = $data['value'] < $this->value;
				break;

			case 'equal':
				$result = $data['value'] == $this->value;
				break;
			default:
				$result = false;

		}

		return $result;
	}

	public static function get_operators() {
		return [
			'more'  => [
				'label' => __( 'more than', 'thrive-automator' ),
			],
			'less'  => [
				'label' => __( 'less than', 'thrive-automator' ),
			],
			'equal' => [
				'label' => __( 'equal to', 'thrive-automator' ),
			],
		];
	}

}
