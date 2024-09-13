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

if ( ! class_exists( '\TCB_Progressbar_Element' ) ) {
	require_once TVE_TCB_ROOT_PATH . 'inc/classes/elements/class-tcb-progressbar-element.php';
}

/**
 * Class Course_List_Item_Progress_Bar
 *
 * @package TVA\Architect\Course\Elements
 */
class Course_List_Item_Progress_Bar extends \TCB_Progressbar_Element {

	/**
	 * Course_List_Item_Progress_Bar constructor.
	 *
	 * @param string $tag
	 */
	public function __construct( $tag = '' ) {
		parent::__construct( $tag );

		add_filter( 'tcb_element_' . $this->tag() . '_config', array( $this, 'add_config' ) );
	}

	public function add_config( $config ) {
		$config['is_sub_element'] = $this->is_sub_element();

		return $config;
	}

	/**
	 * @var string
	 */
	protected $_tag = 'course_list_item_progress_bar';

	/**
	 * Check if this is a sub-element
	 *
	 * @return bool
	 */
	public function is_sub_element() {
		return true;
	}

	/**
	 * All sub-elements are hidden
	 *
	 * @return bool
	 */
	public function hide() {
		return false;
	}

	/**
	 * The element category that will be displayed in the sidebar
	 *
	 * @return string
	 */
	public function category() {
		return tcb_elements()->element_factory( 'course-list' )->elements_group_label();
	}
}

return new Course_List_Item_Progress_Bar();
