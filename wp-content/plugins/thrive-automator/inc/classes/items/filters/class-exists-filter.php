<?php

namespace Thrive\Automator\Items;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Exists extends Filter {

	protected $operation;

	/**
	 * Get the filter identifier
	 *
	 * @return string
	 */
	public static function get_id() {
		return 'exists';
	}

	/**
	 * Get the filter name/label
	 *
	 * @return string
	 */
	public static function get_name() {
		return __( 'Exists', 'thrive-automator' );
	}

	public function prepare_data( $data = [] ) {
		$this->operation = $data['value'];
	}

	public function get_value_data() {
		return empty( $this->operation ) ? null : $this->operation;
	}

	public function filter( $data ) {
		$result = empty( $data['value'] );

		return $this->operation === 'empty' ? $result : ! $result;
	}

	public static function get_operators() {
		return [
			'empty'     => [
				'label' => __( 'is empty', 'thrive-automator' ),
			],
			'not_empty' => [
				'label' => __( 'is not empty', 'thrive-automator' ),
			],
		];
	}

}
