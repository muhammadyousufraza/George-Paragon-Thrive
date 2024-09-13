<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-automator
 */

namespace Thrive\Automator\Items;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Dropdown extends Filter {

	protected $value;

	/**
	 * Get the filter identifier
	 *
	 * @return string
	 */
	public static function get_id() {
		return 'dropdown';
	}

	/**
	 * Get the filter name/label
	 *
	 * @return string
	 */
	public static function get_name() {
		return __( 'Dropdown', 'thrive-automator' );
	}

	public function filter( $data ) {
		return $this->value == $data['value'];
	}

	public static function get_operators() {
		return [
			'equals' => [
				'label' => '=',
			],
		];
	}
}
