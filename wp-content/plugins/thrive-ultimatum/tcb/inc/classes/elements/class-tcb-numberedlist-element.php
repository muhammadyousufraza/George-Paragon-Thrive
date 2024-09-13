<?php
/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 11/3/2017
 * Time: 9:20 AM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class TCB_Nunberlist_Element
 */
class TCB_Numberedlist_Element extends TCB_Element_Abstract {

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Numbered List', 'thrive-cb' );
	}

	/**
	 * Get element alternate
	 *
	 * @return string
	 */
	public function alternate() {
		return 'list';
	}

	/**
	 * Return icon class needed for display in menu
	 *
	 * @return string
	 */
	public function icon() {
		return 'numbered-list';
	}

	/**
	 * Number List element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return ' .thrv-numbered_list';
	}

	/**
	 * The HTML is generated from js
	 *
	 * @return string
	 */
	protected function html() {
		return '';
	}

	/**
	 * Component and control config
	 *
	 * @return array
	 */
	public function own_components() {
		$prefix_config  = tcb_selection_root();
		$default_config = [
			'css_suffix' => ' li',
			'css_prefix' => $prefix_config . ' ',
			'important'  => true,
		];

		$numberedlist = array(
			'numberedlist' => array(
				'config' => array(
					'FontFace'         => array(
						'css_suffix' => ' .tcb-numbered-list-number',
						'config'     => array(
							'template' => 'controls/font-manager',
							'label'    => __( 'Number Font', 'thrive-cb' ),
							'inline'   => false,
						),
					),
					'numbers_color'    => array(
						'css_suffix' => ' .tcb-numbered-list-number',
						'config'     => array(
							'label' => __( 'Numbers color', 'thrive-cb' ),
						),
						'extends'    => 'ColorPicker',
					),
					'item_spacing'     => array(
						'css_suffix' => ' li',
						'css_prefix' => $prefix_config . ' ',
						'config'     => array(
							'default' => '20',
							'min'     => '1',
							'max'     => '100',
							'label'   => __( 'List Item Spacing', 'thrive-cb' ),
							'um'      => [ 'px', 'em' ],
							'css'     => 'margin-bottom',
						),
						'extends'    => 'Slider',
					),
					'starting_number'  => array(
						'config'  => array(
							'name'      => __( 'Starting Number', 'thrive-cb' ),
							'default'   => 10,
							'min'       => 0,
							'max'       => 366,
							'maxlength' => 3,
						),
						'extends' => 'Input',
					),
					'increment_number' => array(
						'config'  => array(
							'name'      => __( 'Increment', 'thrive-cb' ),
							'default'   => 10,
							'min'       => 1,
							'max'       => 366,
							'maxlength' => 3,
						),
						'extends' => 'Input',
					),
					'numbers_size'     => array(
						'css_suffix' => ' .tcb-numbered-list-number',
						'config'     => array(
							'default' => '18',
							'min'     => '8',
							'max'     => '200',
							'label'   => __( 'Numbers size', 'thrive-cb' ),
							'um'      => [ 'px' ],
							'css'     => 'fontSize',
						),
						'extends'    => 'Slider',
					),
					'preview'          => [
						'config' => [
							'sortable' => true,
						],
					],
				),
			),
			'typography'   => [
				'disabled_controls' => [
					'.tve-advanced-controls',
					'[data-value="tcb-typography-line-height"] ',
					'p_spacing',
					'h1_spacing',
					'h2_spacing',
					'h3_spacing',
				],
				'config'            => [
					'TextAlign'     => [
						'css_suffix'   => ' .thrv-styled-list-item',
						'property'     => 'justify-content',
						'property_val' => [
							'left'    => 'flex-start',
							'center'  => 'center',
							'right'   => 'flex-end',
							'justify' => 'space-evenly',
						],
					],
					'FontColor'     => $default_config,
					'FontSize'      => $default_config,
					'TextTransform' => $default_config,
				],
			],
			'layout'       => [
				'disabled_controls' => [],
			],
		);

		return array_merge( $numberedlist, $this->group_component() );
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
	 * Group Edit Properties
	 *
	 * @return array|bool
	 */
	public function has_group_editing() {
		return array(
			'select_values' => array(
				array(
					'value'    => 'all_list_items',
					'selector' => '.thrv-styled-list-item',
					'name'     => __( 'Grouped List Items', 'thrive-cb' ),
					'singular' => __( '-- List Item %s', 'thrive-cb' ),
				),
				array(
					'value'    => 'all_numbers',
					'selector' => '.thrv-disabled-label',
					'name'     => __( 'Grouped Figures', 'thrive-cb' ),
					'singular' => __( '-- Figure %s', 'thrive-cb' ),
				),
				array(
					'value'    => 'all_texts',
					'selector' => '.thrv-advanced-inline-text',
					'name'     => __( 'Grouped Texts', 'thrive-cb' ),
					'singular' => __( '-- Text %s', 'thrive-cb' ),
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
				'url'  => 'numbered_list',
				'link' => 'https://help.thrivethemes.com/en/articles/4425821-how-to-use-the-numbered-list-element',
			],
		];
	}
}
