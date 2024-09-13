<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package TCB2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

class TCB_Toc_Element extends TCB_Cloud_Template_Element_Abstract {

	/**
	 * @return string
	 */
	public function name() {
		return __( 'Table of Contents', 'thrive-cb' );
	}

	/**
	 * Get element alternate
	 *
	 * @return string
	 */
	public function alternate() {
		return 'index,content,toc';
	}


	/**'
	 * @return string
	 */
	public function icon() {
		return 'table_contents';
	}

	/**
	 * @return string
	 */
	public function identifier() {
		return '.tve-toc';
	}

	/**
	 * This element is not a placeholder
	 *
	 * @return bool|true
	 */
	public function is_placeholder() {
		return false;
	}

	/**
	 * @return array
	 */
	public function own_components() {
		return array_merge(
			array(
				'toc'        => array(
					'config' => array(
						'TOCPalettes'     => [
							'config'  => [],
							'extends' => 'Palettes',
						],
						'Headings'        => array(
							'config'  => array(
								'name'   => __( 'Headings', 'thrive-cb' ),
								'inputs' => [
									[
										'name'  => 'h1',
										'label' => 'H1',
									],
									[
										'name'  => 'h2',
										'label' => 'H2',
									],
									[
										'name'  => 'h3',
										'label' => 'H3',
									],
									[
										'name'  => 'h4',
										'label' => 'H4',
									],
									[
										'name'  => 'h5',
										'label' => 'H5',
									],
									[
										'name'  => 'h6',
										'label' => 'H6',
									],
								],
							),
							'extends' => 'MultipleCheckbox',
						),
						'Columns'         => array(
							'config'  => array(
								'size'    => 'medium',
								'name'    => __( 'Columns', 'thrive-cb' ),
								'options' => [
									[
										'value' => '1',
										'name'  => '1',
									],
									[
										'value' => '2',
										'name'  => '2',
									],
									[
										'value' => '3',
										'name'  => '3',
									],
								],
							),
							'extends' => 'Select',
						),
						'Highlight'       => array(
							'config'  => array(
								'name'    => __( 'Highlight', 'thrive-cb' ),
								'options' => array(
									array(
										'value' => 'none',
										'name'  => __( 'Off', 'thrive-cb' ),
									),
									array(
										'value' => 'heading',
										'name'  => __( 'Current heading', 'thrive-cb' ),
									),
									array(
										'value' => 'section',
										'name'  => __( 'Current section', 'thrive-cb' ),
									),
									array(
										'value' => 'progressive',
										'name'  => __( 'Progressive', 'thrive-cb' ),
									),
								),
							),
							'extends' => 'Select',
						),
						'Numbering'       => array(
							'config'  => array(
								'name'    => __( 'List type', 'thrive-cb' ),
								'options' => array(
									array(
										'value' => 'none',
										'name'  => __( 'None', 'thrive-cb' ),
									),
									array(
										'value' => 'basic',
										'name'  => __( 'Numbered at top level', 'thrive-cb' ),
									),
									array(
										'value' => 'advanced',
										'name'  => __( 'Numbered at all levels', 'thrive-cb' ),
									),
									array(
										'value' => 'bullet',
										'name'  => __( 'Icons at top level', 'thrive-cb' ),
									),
									array(
										'value' => 'bullet_advanced',
										'name'  => __( 'Icons at all levels', 'thrive-cb' ),
									),
								),
							),
							'extends' => 'Select',
						),
						'Evenly'          => array(
							'config'  => array(
								'name'    => '',
								'label'   => __( 'Distribute evenly', 'thrive-cb' ),
								'default' => true,
							),
							'extends' => 'Switch',
						),
						'Indent'          => array(
							'config'  => array(
								'default' => '30',
								'min'     => '0',
								'max'     => '200',
								'label'   => __( 'Indent size', 'thrive-cb' ),
								'um'      => [ 'px' ],
								'size'    => 'medium',
							),
							'extends' => 'Input',
						),
						'TextSize'        => array(
							'to'      => '.tve-toc-heading',
							'config'  => array(
								'default' => '16',
								'min'     => '1',
								'max'     => '100',
								'label'   => __( 'Text size', 'thrive-cb' ),
								'um'      => [ 'px', 'em' ],
								'css'     => 'fontSize',
							),
							'extends' => 'Slider',
						),
						'LineSpacing'     => [
							'to'        => '.tve-toc-heading',
							'important' => true,
						],
						'HeadingList'     => [
							'config' => [
								'sortable'   => false,
								'max_levels' => 2,
								'tpl'        => 'controls/toc/preview-inline-item',
							],
						],
						'ScrollAnimation' => array(
							'config'  => array(
								'name'         => '',
								'label'        => __( 'Disable smooth scroll animation', 'thrive-cb' ),
								'default'      => false,
								'tooltip'      => __( 'Page will scroll to the selected heading without any animation, recommended for pages with lazy-loaded content', 'thrive-cb' ),
								'tooltip_side' => 'top',
							),
							'extends' => 'Switch',
						),
						'Expandable'      => array(
							'config'  => array(
								'name'    => '',
								'label'   => __( 'Allow table to be collapsed', 'thrive-cb' ),
								'default' => true,
							),
							'extends' => 'Switch',
						),
						'DefaultState'    => array(
							'config'  => array(
								'name'    => __( 'Default State', 'thrive-cb' ),
								'buttons' => [
									[
										'icon'    => '',
										'text'    => 'Collapsed',
										'value'   => 'collapsed',
										'default' => true,
									],
									[
										'icon'  => '',
										'text'  => 'Expanded',
										'value' => 'expanded',
									],
								],
							),
							'extends' => 'ButtonGroup',
						),

						'DropdownAnimation' => array(
							'config'  => array(
								'name'    => __( 'Dropdown Animation', 'thrive-cb' ),
								'options' => array(
									array(
										'value' => '',
										'name'  => __( 'None', 'thrive-cb' ),
									),
									array(
										'value' => 'slide',
										'name'  => __( 'Slide', 'thrive-cb' ),
									),
									array(
										'value' => 'fade',
										'name'  => __( 'Fade', 'thrive-cb' ),
									),
								),
							),
							'extends' => 'Select',
						),
						'AnimationSpeed'    => array(
							'config'  => array(
								'name'    => __( 'Animation speed', 'thrive-cb' ),
								'options' => array(
									array(
										'value' => 'slow',
										'name'  => __( 'Slow', 'thrive-cb' ),
									),
									array(
										'value' => 'medium',
										'name'  => __( 'Medium', 'thrive-cb' ),
									),
									array(
										'value' => 'fast',
										'name'  => __( 'Fast', 'thrive-cb' ),
									),
								),
							),
							'extends' => 'Select',
						),
					),
				),
				'background' => [
					'config' => [
						'to' => '>.tve-content-box-background',
					],
				],
				'borders'    => [
					'config' => [
						'Borders' => [
							'important' => true,
							'to'        => '>.tve-content-box-background',
						],
						'Corners' => [
							'important' => true,
							'to'        => '>.tve-content-box-background',
						],
					],
				],
				'layout'     => [
					'disabled_controls' => [ 'Height' ],
				],
				'animation'  => [
					'hidden' => true,
				],
				'typography' => [
					'hidden' => true,
				],
				'shadow'     => [
					'config' => [
						'to' => '>.tve-content-box-background',
					],
				],
				'scroll'     => [
					'hidden' => false,
				],
			),
			$this->group_component()
		);
	}


	/**
	 * Enable group editing on text elements from table cells
	 *
	 * @return array|bool
	 */
	public function has_group_editing() {
		return array(
			'select_values' => array(
				array(
					'value'     => 'all_lvl0',
					'selector'  => ' .tve-toc-heading-level0',
					'name'      => __( 'Grouped Heading Level 1', 'thrive-cb' ),
					'singular'  => __( '-- Heading Item Level 1 %s', 'thrive-cb' ),
					'no_unlock' => true,
				),
				array(
					'value'     => 'all_lvl1',
					'selector'  => ' .tve-toc-heading-level1',
					'name'      => __( 'Grouped Heading Level 2', 'thrive-cb' ),
					'singular'  => __( '-- Heading Item Level 2 %s', 'thrive-cb' ),
					'no_unlock' => true,
				),
				array(
					'value'     => 'all_lvl2',
					'selector'  => ' .tve-toc-heading-level2',
					'name'      => __( 'Grouped Heading Level 3', 'thrive-cb' ),
					'singular'  => __( '-- Heading Item Level 3 %s', 'thrive-cb' ),
					'no_unlock' => true,
				),
				array(
					'value'     => 'all_bullet_lvl0',
					'selector'  => ' .tve-toc-bullet0',
					'name'      => __( 'Grouped Icon Level 1', 'thrive-cb' ),
					'singular'  => __( '-- Icon Item Level 1 %s', 'thrive-cb' ),
					'no_unlock' => true,
				),
				array(
					'value'     => 'all_bullet_lvl1',
					'selector'  => ' .tve-toc-bullet1',
					'name'      => __( 'Grouped Icon Level 2', 'thrive-cb' ),
					'singular'  => __( '-- Icon Item Level 2 %s', 'thrive-cb' ),
					'no_unlock' => true,
				),
				array(
					'value'     => 'all_bullet_lvl2',
					'selector'  => ' .tve-toc-bullet2',
					'name'      => __( 'Grouped Icon Level 3', 'thrive-cb' ),
					'singular'  => __( '-- Icon Item Level 3 %s', 'thrive-cb' ),
					'no_unlock' => true,
				),
				array(
					'value'     => 'all_number_lvl0',
					'selector'  => ' .tve-toc-number0',
					'name'      => __( 'Grouped Number Level 1', 'thrive-cb' ),
					'singular'  => __( '-- Number Item Level 1 %s', 'thrive-cb' ),
					'no_unlock' => true,
				),
				array(
					'value'     => 'all_number_lvl1',
					'selector'  => ' .tve-toc-number1',
					'name'      => __( 'Grouped Number Level 2', 'thrive-cb' ),
					'singular'  => __( '-- Number Item Level 2 %s', 'thrive-cb' ),
					'no_unlock' => true,
				),
				array(
					'value'     => 'all_number_lvl2',
					'selector'  => ' .tve-toc-number2',
					'name'      => __( 'Grouped Number Level 3', 'thrive-cb' ),
					'singular'  => __( '-- Number Item Level 3 %s', 'thrive-cb' ),
					'no_unlock' => true,
				),
				array(
					'value'     => 'all_dividers',
					'selector'  => ' .thrv-divider.tve-vert-divider',
					'name'      => __( 'Grouped Dividers', 'thrive-cb' ),
					'singular'  => __( '-- Divider %s', 'thrive-cb' ),
					'no_unlock' => true,
				),
			),
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

	/**
	 * Element info
	 *
	 * @return string|string[][]
	 */
	public function info() {
		return [
			'instructions' => [
				'type' => 'help',
				'url'  => 'table_of_contents',
				'link' => 'https://help.thrivethemes.com/en/articles/4425803-how-to-set-up-the-table-of-contents-element',
			],
		];
	}
}
