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
 * Class Course_List_Item
 *
 * @package  TVA\Architect\Course_List\Elements
 * @project  : thrive-apprentice
 */
class Course_List_Item extends \TVA\Architect\Abstract_Sub_Element {
	/**
	 * @var string
	 */
	protected $_tag = 'course_list_item';

	/**
	 * @return string
	 */
	public function name() {
		return __( 'Course Item', 'thrive-apprentice' );
	}

	/**
	 * @return string
	 */
	public function identifier() {
		return '.tva-course-list-item';
	}

	/**
	 * Whether or not this element can be edited while under :hover state
	 *
	 * @return bool
	 */
	public function has_hover_state() {
		return true;
	}

	/**
	 * @return array
	 */
	public function own_components() {
		$components = parent::own_components();

		$components['typography']['hidden']       = true;
		$components['responsive']['hidden']       = true;
		$components['styles-templates']['hidden'] = true;

		$components['animation']['disabled_controls'] = array( '.anim-popup', '.anim-link' );

		$components['layout']['disabled_controls'] =
			array(
				'margin-right',
				'margin-bottom',
				'margin-left',
				'margin-top',
				'.tve-advanced-controls',
				'MaxWidth',
				'Alignment',
				'hr',
				'Display',
			);

		return $components;
	}
}

return new Course_List_Item();
