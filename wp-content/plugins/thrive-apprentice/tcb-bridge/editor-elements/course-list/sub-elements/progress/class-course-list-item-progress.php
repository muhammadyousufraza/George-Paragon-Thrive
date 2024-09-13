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
 * Class Course_List_Item_Progress
 *
 * @package TVA\Architect\Course\Elements
 */
class Course_List_Item_Progress extends \TVA\Architect\Course_List\Abstract_Course_List_Sub_Element {

	/**
	 * @var string
	 */
	protected $_tag = 'course_list_item_progress';

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Progress', 'thrive-apprentice' );
	}

	/**
	 * Return the icon class needed for display in menu
	 * //todo
	 *
	 * @return string
	 */
	public function icon() {
		return 'course-progress';
	}
}

return new Course_List_Item_Progress();
