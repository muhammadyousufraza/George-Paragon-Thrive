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
 * Class Course_Type_Icon
 *
 * @package TVA\Architect\Course\Elements
 */
class Course_Type_Icon extends \TVA\Architect\Course\Abstract_Course_Structure_Sub_Element {
	/**
	 * @var string
	 */
	protected $_tag = 'course-type-icon';

	/**
	 * @return string
	 */
	public function name() {
		return __( 'Lesson type icon', 'thrive-apprentice' );
	}

	/**
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

return new Course_Type_Icon();
