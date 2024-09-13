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
 * Class Course_Module_List
 *
 * @package TVA\Architect\Course\Elements
 */
class Course_Module_List extends \TVA\Architect\Course\Abstract_Course_Structure_Sub_Element {

	/**
	 * @var string
	 */
	protected $_tag = 'course-module-list';

	/**
	 * @return string
	 */
	public function name() {
		return __( 'Module List', 'thrive-apprentice' );
	}

	/**
	 * @return string
	 */
	public function identifier() {
		return '.tva-course-module-list';
	}
}

return new Course_Module_List();
