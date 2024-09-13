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
 * Class TCB_Tabs_Element
 */
class TCB_Tabs_Element extends TCB_Cloud_Template_Element_Abstract {

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
		return '.thrv-tabbed-content.tve-tab-upgraded';
	}

	/**
	 * This element is not a placeholder
	 *
	 * @return bool|true
	 */
	public function is_placeholder() {
		return false;
	}

	public function hide() {
		return false;
	}

	/**
	 * Component and control config
	 *
	 * @return array
	 */
	public function own_components() {
		return array_merge( array(
			'tabs'       => array(
				'config' => array(
					'TabPalettes'      => [
						'config'  => [],
						'extends' => 'Palettes',
					],
					'ContentAnimation' => array(
						'config'  => array(
							'name'    => __( 'Content Switch Animation', 'thrive-cb' ),
							'options' => array(
								array(
									'value' => 'appear',
									'name'  => __( 'Appear', 'thrive-cb' ),
								),
								array(
									'value' => 'slide-right',
									'name'  => __( 'From Right', 'thrive-cb' ),
								),
								array(
									'value' => 'slide-left',
									'name'  => __( 'From Left', 'thrive-cb' ),
								),
								array(
									'value' => 'slide-up',
									'name'  => __( 'From Top', 'thrive-cb' ),
								),
								array(
									'value' => 'slide-down',
									'name'  => __( 'From Bottom', 'thrive-cb' ),
								),
								array(
									'value' => 'carousel',
									'name'  => __( 'Carousel', 'thrive-cb' ),
								),
								array(
									'value' => 'smooth-resize',
									'name'  => __( 'Smooth Resize', 'thrive-cb' ),
								),
								array(
									'value' => 'swing-up',
									'name'  => __( 'Swing Up', 'thrive-cb' ),
								),
							),
						),
						'extends' => 'Select',
					),
					'HoverEffect'      => array(
						'config'  => array(
							'name'    => __( 'Hover Effect', 'thrive-cb' ),
							'options' => [
								''            => 'None',
								'c-underline' => 'Underline',
								'c-double'    => 'Double line',
								'c-brackets'  => 'Brackets',
								'c-thick'     => 'Thick Underline',
							],
						),
						'extends' => 'Select',
					),
					'TabType'            => [
						'config'  => [
							'css_suffix' => ' a',
							'name'       => __( 'Default Tab', 'thrive-cb' ),
							'buttons'    => [
								[ 
									'value' => 'static', 
									'icon' => 'link', 
									'default' => true,
									'tooltip' => __( 'Static', 'thrive-cb' ),
									'tooltip_side' => 'top'
								],
								[ 
									'value' => 'dynamic', 
									'icon' => 'database-regular', 
									'tooltip' => __( 'Dynamic', 'thrive-cb' ),
									'tooltip_side' => 'top'
								],
							],
						],
						'extends' => 'Tabs',
					],
					'DefaultTab'       => array(
						'config'  => array(
							'name'    => __( '', 'thrive-cb' ),
							'options' => [],
						),
						'extends' => 'Select',
					),
					'DynamicTabType'       => array(
						'config'  => array(
							'name'    => __( '', 'thrive-cb' ),
							'options' => [
								array(
									'value' => 'url-query-string',
									'name'  => __( 'URL Query String', 'thrive-cb' ),
								),
								array(
									'value' => 'post-variable',
									'name'  => __( 'Post variable', 'thrive-cb' ),
								),
								array(
									'value' => 'cookie',
									'name'  => __( 'Cookie', 'thrive-cb' ),
								),
							],
						),
						'extends' => 'Select',
					),
					'VariableName'     => [
						'config'  => [
							'label'   => __( 'Variable name', 'thrive-cb' ),
							'extra_attrs' => 'value="tab"'
						],
						'extends' => 'LabelInput',
					],
					'FallbackValue' =>   array(
						'config'  => array(
							'name'    => __( 'Fallback value', 'thrive-cb' ),
							'options' => [],
						),
						'extends' => 'Select',
					),
				),
			),
			'typography' => [ 'hidden' => true ],
			'animation'  => [ 'hidden' => true ],
			'scroll'     => [
				'hidden' => false,
			],
			'layout'     => [
				'disabled_controls' => [],
			],
		), $this->group_component() );
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
	 * Enable group editing on text elements from table cells
	 *
	 * @return array|bool
	 */
	public function has_group_editing() {
		return array(
			'select_values' => array(
				array(
					'value'    => 'all_items',
					'selector' => ' .tve_scT > ul > .tve_tab_title_item',
					'name'     => __( 'Grouped Tab Items', 'thrive-cb' ),
					'singular' => __( '-- Tab Item %s', 'thrive-cb' ),
				),
				array(
					'value'    => 'all_content',
					'selector' => ' .tve_scT > .tve_tab_content',
					'name'     => __( 'Grouped Tab Contents', 'thrive-cb' ),
					'singular' => __( '-- Tab Content %s', 'thrive-cb' ),
				),
			),
		);
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
				'url'  => 'tabs',
				'link' => 'https://help.thrivethemes.com/en/articles/4425806-how-to-use-the-tabs-element',
			],
		];
	}
}
