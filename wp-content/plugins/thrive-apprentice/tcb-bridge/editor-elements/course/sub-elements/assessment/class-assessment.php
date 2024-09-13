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
 * Class Course_Lesson
 *
 * @package TVA\Architect\Course\Elements
 */
class Course_Assessment extends \TVA\Architect\Course\Abstract_Course_Structure_Sub_Element {

	/**
	 * @var string
	 */
	protected $_tag = 'course-assessment';

	/**
	 * @return string
	 */
	public function name() {
		return __( 'Assessment', 'thrive-apprentice' );
	}

	/**
	 * @return string
	 */
	public function identifier() {
		return '.tva-course-assessment';
	}

	/**
	 * Lesson Components
	 *
	 * @return array
	 */
	public function own_components() {
		return array_merge( $this->get_course_structure_element_config(), parent::own_components() );
	}
}

return new Course_Assessment();
