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
 * Class TCB_Columns_Element
 */
class TCB_Table_Element extends TCB_Cloud_Template_Element_Abstract {

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Table', 'thrive-cb' );
	}

	/**
	 * Return icon class needed for display in menu
	 *
	 * @return string
	 */
	public function icon() {
		return 'table';
	}

	/**
	 * Element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.thrv_table';
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
	 * Table extra sidebar state - used in MANAGE CELLS mode.
	 *
	 * @return null|string
	 */
	public function get_sidebar_extra_state() {
		return tcb_template( 'sidebars/table-edit-state', null, true );
	}

	/**
	 * Component and control config
	 *
	 * @return array
	 */
	public function own_components() {

		$texts      = [
			' p',
			' li',
			' blockquote',
			' address',
			' .tcb-plain-text',
			' label',
			' h1',
			' h2',
			' h3',
			' h4',
			' h5',
			' h6',
		];
		$css_prefix = tcb_selection_root() . ' ';

		$table_components = array(
			'table'        => array(
				'config' => array(
					'cellpadding'         => array(
						'css_suffix' => [ ' .tve_table td', ' .tve_table th' ],
						'config'     => array(
							'min'     => 0,
							'max'     => 60,
							'default' => '',
							'label'   => __( 'Cell padding', 'thrive-cb' ),
							'um'      => [ 'px' ],
						),
					),
					'sortable'            => array(
						'to'     => '.tve_table',
						'config' => array(
							'label' => __( 'Make table sortable', 'thrive-cb' ),
						),
					),
					'header_bg'           => array(
						'css_suffix' => ' > .tve_table > thead > tr > th',
						'css_prefix' => $css_prefix,
						'config'     => array(
							'label' => __( 'Header color', 'thrive-cb' ),
						),
					),
					'cell_bg'             => array(
						'css_suffix' => ' > .tve_table > tbody > tr > td',
						'css_prefix' => $css_prefix,
						'config'     => array(
							'label' => __( 'Cell color', 'thrive-cb' ),
						),
					),
					'HeaderTextColor'     => array(
						'to'         => 'thead',
						'css_suffix' => $texts,
						'css_prefix' => $css_prefix,
						'config'     => array(
							'label' => __( 'Header text color', 'thrive-cb' ),
						),
					),
					'BodyTextColor'       => array(
						'to'         => 'tbody',
						'css_suffix' => $texts,
						'css_prefix' => $css_prefix,
						'config'     => array(
							'label' => __( 'Cell text color', 'thrive-cb' ),
						),
					),
					'even_rows'           => array(
						'css_suffix' => ' > .tve_table > tbody > tr:nth-child(2n) > td',
						'css_prefix' => $css_prefix,
						'config'     => array(
							'label' => __( 'Even rows', 'thrive-cb' ),
						),
					),
					'odd_rows'            => array(
						'css_suffix' => ' > .tve_table > tbody > tr:nth-child(2n+1) > td',
						'css_prefix' => $css_prefix,
						'config'     => array(
							'label' => __( 'Odd rows', 'thrive-cb' ),
						),
					),
					'valign'              => array(
						'css_suffix' => [ ' .tve_table td', ' .tve_table th' ],
						'css_prefix' => $css_prefix,
						'config'     => array(
							'name'    => __( 'Vertical Align', 'thrive-cb' ),
							'buttons' => [
								[
									'icon'    => 'none',
									'default' => true,
									'value'   => '',
								],
								[
									'icon'  => 'top',
									'value' => 'top',
								],
								[
									'icon'  => 'vertical',
									'value' => 'middle',
								],
								[
									'icon'  => 'bot',
									'value' => 'bottom',
								],
							],
						),
						'extends'    => 'ButtonGroup',
					),
					'TextAlign'           => array(
						'css_suffix' => [ ' .tve_table td', ' .tve_table th' ],
						'config'     => array(
							'name'    => __( 'Text Align', 'thrive-cb' ),
							'buttons' => [
								[
									'icon'    => 'format-align-left',
									'text'    => '',
									'value'   => 'left',
									'default' => true,
								],
								[
									'icon'  => 'format-align-center',
									'text'  => '',
									'value' => 'center',
								],
								[
									'icon'  => 'format-align-right',
									'text'  => '',
									'value' => 'right',
								],
								[
									'icon'  => 'format-align-justify',
									'text'  => '',
									'value' => 'justify',
								],
							],
						),
						'extends'    => 'ButtonGroup',
					),
					'mobile_table'        => array(
						'config' => array(
							'name'  => '',
							'label' => __( 'Create mobile-responsive table', 'thrive-cb' ),
						),
					),
					'mobile_header_width' => array(
						'config' => array(
							'default' => '50',
							'min'     => '10',
							'max'     => '90',
							'label'   => __( 'Mobile header width', 'thrive-cb' ),
							'um'      => [ '%' ],
						),
					),
				),
			),
			'tableborders' => array(
				'config' => array(
					'to'           => '> .tve_table',
					'Borders'      => [
						/**
						 * We've done this to set the css_prefix to the Table Borders - > Simple Borders Control
						 */
						'css_prefix' => $css_prefix,
					],
					'InnerBorders' => array(
						'config'  => array(
							'label' => __( 'Apply inner border', 'thrive-cb' ),
						),
						'extends' => 'Switch',
					),
					'border_th'    => array(
						'css_prefix' => $css_prefix,
						'css_suffix' => ' > thead > tr > th',
						'config'     => array(
							'label' => __( 'Heading border', 'thrive-cb' ),
						),
					),
					'border_td'    => array(
						'css_prefix' => $css_prefix,
						'css_suffix' => ' > tbody > tr > td',
						'config'     => array(
							'label' => __( 'Cell border', 'thrive-cb' ),
						),
					),
				),
				'order'  => 10,
			),
			'borders'      => [
				'hidden' => true,
			],
			'typography'   => [
				'hidden' => true,
			],
			'animation'    => [
				'hidden' => true,
			],
			'shadow'       => [
				'config' => [
					'disabled_controls' => [ 'inner' ],
				],
			],
			'background'   => [ 'hidden' => true ],
			'layout'       => [
				'disabled_controls' => [],
			],
		);

		return array_merge( $table_components, $this->group_component() );
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
					'value'    => 'all_header_items',
					'selector' => 'th > .thrv_text_element',
					'name'     => __( 'Grouped Header Texts', 'thrive-cb' ),
					'singular' => __( '-- Header Text %s', 'thrive-cb' ),
				),
				array(
					'value'    => 'all_body_items',
					'selector' => 'td > .thrv_text_element',
					'name'     => __( 'Grouped Body Texts', 'thrive-cb' ),
					'singular' => __( '-- Body Item %s', 'thrive-cb' ),
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
				'url'  => 'table',
				'link' => 'https://help.thrivethemes.com/en/articles/4425798-how-to-use-the-table-element',
			],
		];
	}
}
