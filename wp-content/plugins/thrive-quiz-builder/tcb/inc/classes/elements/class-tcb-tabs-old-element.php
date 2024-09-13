<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class TCB_Tabs_Old_Element
 */
class TCB_Tabs_Old_Element extends TCB_Element_Abstract {

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Tabs', 'thrive-cb' );
	}

	/**
	 * Return icon class needed for display in menu
	 *
	 * @return string
	 */
	public function icon() {
		return 'tabs';
	}

	/**
	 * Tabs element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.thrv_tabs_shortcode, .thrv-tabbed-content';
	}

	public function hide() {
		return true;
	}

	/**
	 * Component and control config
	 *
	 * @return array
	 */
	public function own_components() {

		return array(
			'tabs_old'   => array(
				'config' => array(
					'TabLayout'     => array(
						'config'  => array(
							'name'    => __( 'Tabs layout', 'thrive-cb' ),
							'options' => array(
								array(
									'value' => 'horizontal',
									'name'  => __( 'Horizontal', 'thrive-cb' ),
								),
								array(
									'value' => 'vertical',
									'name'  => __( 'Vertical', 'thrive-cb' ),
								),
							),
						),
						'extends' => 'Select',
					),
					'TabsWidth'     => array(
						'config'  => array(
							'default' => '200',
							'min'     => '100',
							'max'     => '1000',
							'label'   => __( 'Tabs Width', 'thrive-cb' ),
							'um'      => [ 'px', '%' ],
							'css'     => 'width',
						),
						'extends' => 'Slider',
					),
					'DefaultTab'    => array(
						'config'  => array(
							'name'    => __( 'Default Tab', 'thrive-cb' ),
							'options' => [],
						),
						'extends' => 'Select',
					),
					'EditTabs'      => array(
						'config'  => array(
							'name'       => __( 'Select which tabs to edit', 'thrive-cb' ),
							'full-width' => true,
							'options'    => array(
								array(
									'value'   => 'active',
									'name'    => __( 'Active Tabs', 'thrive-cb' ),
									'default' => true,
								),
								array(
									'value' => 'inactive',
									'name'  => __( 'Inactive Tabs', 'thrive-cb' ),
								),
							),
						),
						'extends' => 'Select',
					),
					'TabBackground' => array(
						'config'  => array(
							'default' => '000',
							'label'   => __( 'Background', 'thrive-cb' ),
							'options' => [
								'output' => 'object',
							],
						),
						'extends' => 'ColorPicker',
					),
					'TabBorder'     => array(
						'config'  => array(
							'default' => '000',
							'label'   => __( 'Border', 'thrive-cb' ),
							'options' => [
								'output' => 'object',
							],
						),
						'extends' => 'ColorPicker',
					),
					'ContentColor'  => array(
						'config'  => array(
							'default' => '000',
							'label'   => __( 'Background', 'thrive-cb' ),
							'options' => [
								'output' => 'object',
							],
						),
						'extends' => 'ColorPicker',
					),
					'ContentBorder' => array(
						'config'  => array(
							'default' => '000',
							'label'   => __( 'Border', 'thrive-cb' ),
							'options' => [
								'output' => 'object',
							],
						),
						'extends' => 'ColorPicker',
					),
				),
			),
			'typography' => [
				'config' => [
					'TextStyle' => [
						'css_suffix' => ' .tve_scT .thrv-inline-text, .tve_scT p',
					],
				],
			],
			'animation'  => [ 'hidden' => true ],
			'background' => [ 'hidden' => true ],
		);
	}

	/**
	 * Element category that will be displayed in the sidebar
	 *
	 * @return string
	 */
	public function category() {
		return static::get_thrive_advanced_label();
	}
}
