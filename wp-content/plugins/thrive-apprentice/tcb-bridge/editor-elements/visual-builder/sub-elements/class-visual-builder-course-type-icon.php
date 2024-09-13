<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

namespace TVA\Architect\Visual_Builder\Elements;

use TVA\Architect\Visual_Builder\Abstract_Visual_Builder_Element;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Visual_Builder_Course_Type_Icon
 *
 * @package TVA\Architect\Visual_Builder\Elements
 * @project : thrive-apprentice
 */
class Visual_Builder_Course_Type_Icon extends Abstract_Visual_Builder_Element {
	/**
	 * @var string
	 */
	protected $_tag = 'visual_builder_course_type_icon';

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Course Type Icon', 'thrive-apprentice' );
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
	 * Section element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.tva-course-type-icon';
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

return new Visual_Builder_Course_Type_Icon();
