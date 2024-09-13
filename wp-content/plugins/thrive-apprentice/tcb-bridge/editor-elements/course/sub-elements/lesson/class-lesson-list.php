<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

namespace TVA\Architect\Course\Elements;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Course_Lesson_List
 *
 * @package TVA\Architect\Course\Elements
 */
class Course_Lesson_List extends \TVA\Architect\Course\Abstract_Course_Structure_Sub_Element {
	/**
	 * @var string
	 */
	protected $_tag = 'course-lesson-list';

	/**
	 * @return string
	 */
	public function name() {
		return __( 'Lesson List', 'thrive-apprentice' );
	}

	/**
	 * @return string
	 */
	public function identifier() {
		return '.tva-course-lesson-list';
	}
}

return new Course_Lesson_List();
