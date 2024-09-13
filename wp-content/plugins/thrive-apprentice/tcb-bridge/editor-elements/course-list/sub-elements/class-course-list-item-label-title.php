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
 * Class Course_List_Item_Label_Title
 *
 * @package TVA\Architect\Course\Elements
 */
class Course_List_Item_Label_Title extends \TVA\Architect\Course_List\Abstract_Course_List_Sub_Element {

	/**
	 * WordPress element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.tva-course-list-item-label';
	}

	/**
	 * @var string
	 */
	protected $_tag = 'course_list_item_label_title';

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Course label', 'thrive-apprentice' );
	}

	/**
	 * Return the icon class needed for display in menu
	 *
	 * @return string
	 */
	public function icon() {
		return 'course-label';
	}

	/**
	 * @return array
	 */
	public function own_components() {
		$components = parent::own_components();

		$components['animation']['hidden']        = true;
		$components['responsive']['hidden']       = true;
		$components['styles-templates']['hidden'] = true;

		return $components;
	}
}

return new Course_List_Item_Label_Title();
