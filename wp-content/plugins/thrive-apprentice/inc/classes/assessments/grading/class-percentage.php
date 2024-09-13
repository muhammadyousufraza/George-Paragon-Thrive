<?php

namespace TVA\Assessments\Grading;

class Percentage extends Base {

	protected $passing_percentage;

	public function __construct( $data ) {
		parent::__construct( $data );

		if ( isset( $data['passing_percentage'] ) ) {
			$this->passing_percentage = (int) $data['passing_percentage'];
		}
	}

	public function get_additional_meta_keys() {
		return [
			'passing_percentage',
		];
	}

	/**
	 * Checks if the $value is passing the grading
	 *
	 * @param mixed $value
	 *
	 * @return bool
	 */
	public function passed( $value ) {
		return $value >= $this->passing_percentage;
	}

	/**
	 * Returns the set passing percentage
	 *
	 * @return int
	 */
	public function get_passing_grade() {
		return isset( $this->passing_percentage ) ? $this->passing_percentage : 0;
	}
}
