<?php

namespace TVA\Assessments\Grading;

class PassFail extends Base {

	const PASSING_GRADE = 'pass';
	const FAILING_GRADE = 'fail';

	/**
	 * Checks if the $value is passing the grading
	 *
	 * @param mixed $value
	 *
	 * @return bool
	 */
	public function passed( $value ) {
		return $value === static::PASSING_GRADE;
	}

	/**
	 * Returns the passing grade
	 *
	 * @return string
	 */
	public function get_passing_grade() {
		return static::PASSING_GRADE;
	}
}
