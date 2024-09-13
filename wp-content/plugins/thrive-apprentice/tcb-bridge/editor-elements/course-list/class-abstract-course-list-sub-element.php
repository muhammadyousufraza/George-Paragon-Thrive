<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

namespace TVA\Architect\Course_List;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

if ( ! class_exists( '\TVA\Architect\Abstract_Sub_Element', false ) ) {
	require_once \TVA\Architect\Utils::get_integration_path( 'editor-elements/class-abstract-sub-element.php' );
}

/**
 * Class Abstract_Course_List_Sub_Element
 *
 * @package TVA\Architect\Course
 */
abstract class Abstract_Course_List_Sub_Element extends \TVA\Architect\Abstract_Sub_Element {

	/**
	 * WordPress element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '';
	}

	/**
	 * Check if this should be hidden
	 *
	 * @return string
	 */
	public function hide() {
		return false;
	}

	/**
	 * This is a sub-element and we want to store this in the config
	 *
	 * @return bool
	 */
	public function is_sub_element() {
		return true;
	}

	/**
	 * The element category that will be displayed in the sidebar
	 *
	 * @return string
	 */
	public function category() {
		return tcb_elements()->element_factory( 'course-list' )->elements_group_label();
	}

	/**
	 * @return array
	 */
	public function own_components() {
		return array();
	}
}
