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
 * Class Course_List_Item_Topic_Icon
 *
 * @package TVA\Architect\Course\Elements
 */
class Course_List_Item_Topic_Icon extends \TVA\Architect\Course_List\Abstract_Course_List_Sub_Element {

	/**
	 * WordPress element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.tva-course-list-item-topic-icon';
	}

	/**
	 * @var string
	 */
	protected $_tag = 'course_list_item_topic_icon';

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Topic Icon', 'thrive-apprentice' );
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
	 * Class Components
	 *
	 * @return array
	 */
	public function own_components() {
		return array(
			'course_list_item_topic_icon' => array(
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

return new Course_List_Item_Topic_Icon();
