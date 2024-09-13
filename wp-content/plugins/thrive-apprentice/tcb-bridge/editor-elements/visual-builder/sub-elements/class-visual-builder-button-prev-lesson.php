<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

namespace TVA\Architect\Visual_Builder\Elements;

use TVA\Architect\Visual_Builder\Abstract_Visual_Builder_Element;
use function TVA\TTB\thrive_apprentice_template;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Visual_Builder_Button_Prev_Lesson
 *
 * @package  TVA\Architect\Visual_Builder\Elements
 * @project  : thrive-apprentice
 */
class Visual_Builder_Button_Prev_Lesson extends Abstract_Visual_Builder_Element {
	/**
	 * @var string
	 */
	protected $_tag = 'visual_builder_button_prev_lesson';

	/**
	 * This should only be available for the lesson template & module template
	 *
	 * @return bool
	 */
	public function hide() {
		return ! ( thrive_apprentice_template()->is_lesson() || thrive_apprentice_template()->is_assessment() || thrive_apprentice_template()->is_module() );
	}

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Previous Lesson', 'thrive-apprentice' );
	}

	/**
	 * Return the icon class needed for display in menu
	 *
	 * @return string
	 */
	public function icon() {
		return 'previous-lesson';
	}
}

return new Visual_Builder_Button_Prev_Lesson();
