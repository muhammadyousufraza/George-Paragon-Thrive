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
 * Class Course_List_Item_Type_Icon
 *
 * @package TVA\Architect\Course\Elements
 */
class Course_List_Item_Type_Icon extends \TVA\Architect\Course_List\Abstract_Course_List_Sub_Element {

	/**
	 * @var string
	 */
	protected $_tag = 'course_list_item_type_icon';

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Course type icon', 'thrive-apprentice' );
	}

	/**
	 * Section element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.tva-course-list-item-type-icon';
	}

	/**
	 * Return the icon class needed for display in menu
	 *
	 * @return string
	 */
	public function icon() {
		return 'course-type';
	}

	/**
	 * We inherit the icon components and hide some controls
	 *
	 * @return array
	 */
	public function own_components() {
		$components = tcb_elements()->element_factory( 'icon' )->own_components();

		$components['icon']['disabled_controls'] = array( '.click[data-fn="openModal"]', 'ToggleURL', 'link' );

		$components['animation']        = array( 'hidden' => true );
		$components['responsive']       = array( 'hidden' => true );
		$components['scroll']           = array( 'hidden' => true );
		$components['styles-templates'] = array( 'hidden' => true );

		return $components;
	}
}

return new Course_List_Item_Type_Icon();
