<?php

namespace Thrive\Automator\Items;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Boolean extends Filter {

	protected $value;

	/**
	 * Get the filter identifier
	 *
	 * @return string
	 */
	public static function get_id() {
		return 'boolean';
	}

	/**
	 * Get the filter name/label
	 *
	 * @return string
	 */
	public static function get_name() {
		return __( 'Boolean', 'thrive-automator' );
	}

	public function filter( $data ) {
		$result = $data['value'] === 1 || $data['value'] === true || $data['value'] === 'true' || $data['value'] === '1';

		return $this->value === 'true' ? $result : ! $result;
	}

	public static function get_operators() {
		return [
			'true'  => [
				'label' => __( 'True', 'thrive-automator' ),
			],
			'false' => [
				'label' => __( 'False', 'thrive-automator' ),
			],
		];
	}

}
