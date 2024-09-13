<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

namespace TVA\Architect\Course_List\Elements;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Course_List_Item_Type
 *
 * @package TVA\Architect\Course\Elements
 */
class Course_List_Item_Type extends \TVA\Architect\Course_List\Abstract_Course_List_Sub_Element {

	/**
	 * @var string
	 */
	protected $_tag = 'course_list_item_type';

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Course type', 'thrive-apprentice' );
	}

	/**
	 * Return the icon class needed for display in menu
	 *
	 * @return string
	 */
	public function icon() {
		return 'course-type-i';
	}
}

return new Course_List_Item_Type();
