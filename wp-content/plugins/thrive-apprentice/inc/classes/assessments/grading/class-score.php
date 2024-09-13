<?php

namespace TVA\Assessments\Grading;

class Score extends Base {
	protected $passing_score;

	public function __construct( $data ) {
		parent::__construct( $data );

		if ( isset( $data['passing_score'] ) ) {
			$this->passing_score = (int) $data['passing_score'];
		}
	}

	public function get_additional_meta_keys() {
		return [
			'passing_score',
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
		return $value >= $this->passing_score;
	}

	/**
	 * Returns the set passing score
	 *
	 * @return int
	 */
	public function get_passing_grade() {
		return isset( $this->passing_score ) ? $this->passing_score : 0;
	}
}
