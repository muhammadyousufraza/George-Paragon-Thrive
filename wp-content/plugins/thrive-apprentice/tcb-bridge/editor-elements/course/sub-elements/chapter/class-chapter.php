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
 * Class Course_Chapter
 *
 * @package TVA\Architect\Course\Elements
 */
class Course_Chapter extends \TVA\Architect\Course\Abstract_Course_Structure_Sub_Element {
	/**
	 * @var string
	 */
	protected $_tag = 'course-chapter';

	/**
	 * @return string
	 */
	public function name() {
		return __( 'Chapter', 'thrive-apprentice' );
	}

	/**
	 * @return string
	 */
	public function identifier() {
		return '.tva-course-chapter';
	}
}

return new Course_Chapter();
