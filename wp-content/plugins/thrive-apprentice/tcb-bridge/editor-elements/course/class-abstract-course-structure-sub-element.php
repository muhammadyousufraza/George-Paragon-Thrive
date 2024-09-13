<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

namespace TVA\Architect\Course;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

if ( ! class_exists( '\TVA\Architect\Abstract_Sub_Element', false ) ) {
	require_once \TVA\Architect\Utils::get_integration_path( 'editor-elements/class-abstract-sub-element.php' );
}

/**
 * Class Abstract_Course_Structure_Sub_Element
 *
 * @package TVA\Architect\Course
 */
abstract class Abstract_Course_Structure_Sub_Element extends \TVA\Architect\Abstract_Sub_Element {

	/**
	 * @return array
	 */
	public function own_components() {
		$components = $this->general_components();

		$components['layout'] = array( 'disabled_controls' => array( 'Display', 'Alignment', 'Width', 'Height', 'Float', '[data-value="absolute"]', 'Overflow' ) );

		return $components;
	}

	/**
	 * Returns the course structure element config
	 *
	 * @return array
	 */
	protected function get_course_structure_element_config() {
		return array(
			'course-structure-item' => array(
				'config' => array(
					'ToggleIcon'           => array(
						'config'  => array(
							'name'  => '',
							'label' => __( 'Show icon', 'thrive-apprentice' ),
						),
						'extends' => 'Switch',
					),
					'ToggleTypeIcon'       => array(
						'config'  => array(
							'name'  => '',
							'label' => __( 'Show lesson type icon', 'thrive-apprentice' ),
						),
						'extends' => 'Switch',
					),
					'ToggleExpandCollapse' => array(
						'config'  => array(
							'name'  => '',
							'label' => __( 'Show expand / collapse icon', 'thrive-apprentice' ),
						),
						'extends' => 'Switch',
					),
					'Height'               => array(
						'config'     => array(
							'default' => '150',
							'min'     => '1',
							'max'     => '500',
							'label'   => __( 'Height', 'thrive-apprentice' ),
							'um'      => array( 'px' ),
							'css'     => 'min-height',
						),
						'css_suffix' => ' .tva-course-state-content',
						'extends'    => 'Slider',
					),
					'VerticalPosition'     => array(
						'config'  => array(
							'name'      => __( 'Icon position', 'thrive-apprentice' ),
							'important' => true,
							'buttons'   => array(
								array(
									'icon'    => 'none',
									'default' => true,
									'value'   => '',
								),
								array(
									'icon'  => 'top',
									'value' => 'flex-start',
								),
								array(
									'icon'  => 'vertical',
									'value' => 'center',
								),
								array(
									'icon'  => 'bot',
									'value' => 'flex-end',
								),
							),
						),
						'extends' => 'ButtonGroup',
					),
				),
			),
		);
	}
}
