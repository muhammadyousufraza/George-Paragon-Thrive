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
 * Class Course_List_Item_Author_Image
 *
 * @package  TVA\Architect\Course_List\Elements
 * @project  : thrive-apprentice
 */
class Course_List_Item_Author_Image extends \TVA\Architect\Course_List\Abstract_Course_List_Sub_Element {
	/**
	 * @var string
	 */
	protected $_tag = 'course_list_item_author_image';

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Author Image', 'thrive-apprentice' );
	}

	/**
	 * Return the icon class needed for display in menu
	 *
	 * @return string
	 */
	public function icon() {
		return 'author-picture';
	}
}

return new Course_List_Item_Author_Image();
