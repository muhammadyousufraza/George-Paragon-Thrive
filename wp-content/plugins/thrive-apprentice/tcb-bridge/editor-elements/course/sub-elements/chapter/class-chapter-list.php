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
 * Class Course_Chapter_List
 *
 * @package TVA\Architect\Course\Elements
 */
class Course_Chapter_List extends \TVA\Architect\Course\Abstract_Course_Structure_Sub_Element {
	/**
	 * @var string
	 */
	protected $_tag = 'course-chapter-list';

	/**
	 * @return string
	 */
	public function name() {
		return __( 'Chapter List', 'thrive-apprentice' );
	}

	/**
	 * @return string
	 */
	public function identifier() {
		return '.tva-course-chapter-list';
	}
}

return new Course_Chapter_List();
