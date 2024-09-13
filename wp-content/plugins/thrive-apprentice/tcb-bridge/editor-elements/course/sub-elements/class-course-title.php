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
 * Class Course_Title
 *
 * @package TVA\Architect\Course\Elements
 */
class Course_Title extends \TVA\Architect\Course\Abstract_Course_Structure_Sub_Element {

	/**
	 * @var string
	 */
	protected $_tag = 'course_title';

	/**
	 * Hide this.
	 *
	 * @return string
	 */
	public function hide() {
		return false;
	}

	/**
	 * This is a sub-element for course-structure and we want to store this in the config
	 *
	 * @return bool
	 */
	public function is_sub_element() {
		return true;
	}

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Title', 'thrive-apprentice' );
	}

	/**
	 * Return icon class needed for display in menu
	 *
	 * @return string
	 */
	public function icon() {
		return 'post-title';
	}

	/**
	 * WordPress element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '';
	}

	/**
	 * Element category that will be displayed in the sidebar
	 *
	 * @return string
	 */
	public function category() {
		return tcb_elements()->element_factory( 'course' )->elements_group_label();
	}

	/**
	 * @return array
	 */
	public function own_components() {
		return array();
	}
}

return new Course_Title();
