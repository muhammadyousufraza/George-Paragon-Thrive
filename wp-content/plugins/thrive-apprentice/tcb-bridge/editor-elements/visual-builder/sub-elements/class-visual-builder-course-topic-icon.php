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
 * Class Visual_Builder_Course_Topic_Icon
 *
 * @package TVA\Architect\Visual_Builder\Elements
 * @project : thrive-apprentice
 */
class Visual_Builder_Course_Topic_Icon extends Abstract_Visual_Builder_Element {
	/**
	 * @var string
	 */
	protected $_tag = 'visual_builder_course_topic_icon';

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Course Topic Icon', 'thrive-apprentice' );
	}

	/**
	 * Return the icon class needed for display in menu
	 *
	 * @return string
	 */
	public function icon() {
		return 'topic-i';
	}

	/**
	 * Section element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.tva-course-topic-icon';
	}

	/**
	 * We inherit the icon components and hide some controls
	 *
	 * @return array
	 */
	public function own_components() {
		return array(
			'course_list_item_topic_icon' => array(//We are using the course list item topic icon control to control also the size here
				'config' => array(
					'Slider' => array(
						'config' => array(
							'default' => '30',
							'min'     => '12',
							'max'     => '200',
							'label'   => __( 'Size', 'thrive-apprentice' ),
							'um'      => array( 'px' ),
							'css'     => 'fontSize',
						),
					),
				),
			),
			'animation'                   => array( 'hidden' => true ),
			'styles-templates'            => array( 'hidden' => true ),
			'typography'                  => array( 'hidden' => true ),
			'responsive'                  => array( 'hidden' => true ),
			'layout'                      => array(
				'disabled_controls' => array( 'Display', 'Width', 'Height', '.tve-advanced-controls', 'Overflow', 'padding' ),
			),
		);
	}
}

return new Visual_Builder_Course_Topic_Icon();
