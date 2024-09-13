<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

if ( ! class_exists( 'TCB_Dynamic_Dropdown_Element' ) ) {
	require_once TVE_TCB_ROOT_PATH . 'inc/classes/elements/class-tcb-dynamic-dropdown-element.php';
}

/**
 * Class TCB_Course_List_Dropdown_Element
 *
 * @project  : thrive-apprentice
 */
class TCB_Course_List_Dropdown_Element extends TCB_Dynamic_Dropdown_Element {

	/**
	 * @var string
	 */
	protected $_tag = 'course_list_dropdown';

	/**
	 * Element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return \TVA\Architect\Course_List\Dropdown::IDENTIFIER;
	}

	/**
	 * @return string
	 */
	public function html() {
		return \TVA\Architect\Course_List\tcb_course_list_dropdown_shortcode()->render();
	}

	/**
	 * Own Components
	 *
	 * @return array
	 */
	public function own_components() {
		$components = parent::own_components();

		/**
		 * Reuse dynamic_dropdown components
		 */
		$components['course_list_dropdown'] = $components['dynamic_dropdown'];

		/**
		 * Hide some components
		 */
		$components['styles-templates'] = array( 'hidden' => true );

		/**
		 * Add additional controls
		 */
		$components['course_list_dropdown']['config'] = array_merge( $components['course_list_dropdown']['config'], array(
			'RowsWhenOpen'                 => array(
				'config'  => array(
					'min'   => 1,
					'max'   => 15,
					'label' => __( 'Rows when open', 'thrive-apprentice' ),
					'um'    => array(),
				),
				'extends' => 'Slider',
			),
			'FilterTopics'                 => array(
				'config'  => array(
					'name'  => '',
					'label' => __( 'Topics', 'thrive-apprentice' ),
				),
				'extends' => 'Switch',
			),
			'FilterTopicsSubheading'       => array(
				'config'  => array(
					'label' => __( 'Topics', 'thrive-apprentice' ),
				),
				'extends' => 'LabelInput',
			),
			'FilterRestrictions'           => array(
				'config'  => array(
					'name'  => '',
					'label' => __( 'Access restrictions', 'thrive-apprentice' ),
				),
				'extends' => 'Switch',
			),
			'FilterRestrictionsSubheading' => array(
				'config'  => array(
					'label' => __( 'Access restrictions', 'thrive-apprentice' ),
				),
				'extends' => 'LabelInput',
			),
			'FilterProgress'               => array(
				'config'  => array(
					'name'        => '',
					'label'       => __( 'My courses (progress)', 'thrive-apprentice' ),
					'info'        => true,
					'icontooltip' => __( 'These options will only be available to users who are logged in', 'thrive-apprentice' ),
					'iconside'    => 'top',
				),
				'extends' => 'Switch',
			),
			'FilterProgressSubheading'     => array(
				'config'  => array(
					'label' => __( 'My courses', 'thrive-apprentice' ),
				),
				'extends' => 'LabelInput',
			),
		) );

		unset(
			$components['dynamic_dropdown'],
			$components['group']
		);

		return $components;
	}
}

return new TCB_Course_List_Dropdown_Element();
