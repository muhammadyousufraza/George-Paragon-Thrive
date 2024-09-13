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
 * Class Course_Module
 *
 * @package TVA\Architect\Course\Elements
 */
class Course_Module_Dropzone extends \TVA\Architect\Course\Abstract_Course_Structure_Sub_Element {
	/**
	 * @var string
	 */
	protected $_tag = 'course-module-dropzone';

	/**
	 * @return string
	 */
	public function name() {
		return __( 'Module Item', 'thrive-apprentice' );
	}

	/**
	 * @return string
	 */
	public function identifier() {
		return '.tva-course-module-dropzone';
	}

	/**
	 * Module Components
	 *
	 * @return array
	 */
	public function own_components() {
		return array_merge( $this->get_course_structure_element_config(), parent::own_components() );
	}

	/**
	 * @return bool
	 */
	public function expanded_state_config() {
		return true;
	}

	/**
	 * @return bool
	 */
	public function expanded_state_apply_inline() {
		return true;
	}

	/**
	 * For TOC expanded is collapsed because we can
	 *
	 * @return string
	 */
	public function expanded_state_label() {
		return __( 'Collapsed', 'thrive-apprentice' );
	}
}

return new Course_Module_Dropzone();
