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
class TCB_Columns_Element extends TCB_Element_Abstract {

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Columns', 'thrive-cb' );
	}

	/**
	 * Get element alternate
	 *
	 * @return string
	 */
	public function alternate() {
		return 'container,box,content';
	}


	/**
	 * Return icon class needed for display in menu
	 *
	 * @return string
	 */
	public function icon() {
		return 'column';
	}

	/**
	 * Text element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.thrv-columns:not(.thrv-testimonial), .thrv_columns';
	}

	protected function html() {
		return '';
	}

	/**
	 * Component and control config
	 *
	 * @return array
	 */
	public function own_components() {
		return array(
			'columns'    => array(
				'config' => array(
					'GutterWidth'      => array(
						'config'     => array(
							'default' => '15',
							'min'     => '0',
							'max'     => '200',
							'label'   => __( 'Gutter width', 'thrive-cb' ),
							'um'      => [ 'px' ],
						),
						'to'         => '> .tcb-flex-row',
						'css_suffix' => ' > .tcb-flex-col',
						'extends'    => 'Slider',
					),
					'ColumnsOrder'     => array(
						'config'  => array(
							'name'    => '',
							'label'   => __( 'Reverse column order', 'thrive-cb' ),
							'default' => false,
						),
						'to'      => ' > .tcb-flex-row',
						'extends' => 'Switch',
					),
					'VerticalPosition' => array(
						'config'  => array(
							'name'    => __( 'Vertical position', 'thrive-cb' ),
							'buttons' => [
								[
									'icon'    => 'top',
									'default' => true,
									'value'   => 'flex-start',
								],
								[
									'icon'  => 'vertical',
									'value' => 'center',
								],
								[
									'icon'  => 'bot',
									'value' => 'flex-end',
								],
							],
							'info'    => true,
						),
						'to'      => ' > .tcb-flex-row',
						'extends' => 'ButtonGroup',
					),
					'Wrap'             => array(
						'config'  => array(
							'name'    => '',
							'label'   => __( 'Wrap columns', 'thrive-cb' ),
							'default' => true,
						),
						'to'      => ' > .tcb-flex-row',
						'extends' => 'Switch',
					),
					'ColumnWidth'      => array(
						'config'  => array(
							'default'    => '250',
							'min'        => '40',
							'max'        => '700',
							'label'      => __( 'Column breakpoint', 'thrive-cb' ),
							'info'       => true,
							'info_hover' => true,
							'um'         => [ 'PX' ],
						),
						'to'      => ' > .tcb-flex-row',
						'extends' => 'Slider',
					),
					'FullWidth'        => array(
						'config'  => array(
							'name'    => '',
							'label'   => __( 'Stretch to fit screen width', 'thrive-cb' ),
							'default' => true,
						),
						'extends' => 'Switch',
					),
					'MinHeight'        => array(
						'config'     => array(
							'default' => '1024',
							'min'     => '1',
							'max'     => '1000',
							'label'   => __( 'Minimum height', 'thrive-cb' ),
							'um'      => [ 'px', 'vh' ],
							'css'     => 'min-height',
						),
						'to'         => ' > .tcb-flex-row',
						'css_suffix' => ' > .tcb-flex-col > .tcb-col',
						'extends'    => 'Slider',
					),
				),
			),
			'layout'     => [
				'config'            => [
					'MarginAndPadding' => [
						'margin_to'  => '',
						'padding_to' => ' > .tcb-flex-row',
					],
					'Height'           => [
						'to'         => ' > .tcb-flex-row',
						'css_suffix' => ' > .tcb-flex-col > .tcb-col',
					],
				],
				'disabled_controls' => [],
			],
			'typography' => [
				'disabled_controls' => [],
				'config'            => [
					'ParagraphStyle' => [ 'hidden' => false ],
				],
			],
			'animation'  => [ 'hidden' => true ],
			'scroll'     => [
				'hidden' => false,
			],
		);
	}

	/**
	 * Element category that will be displayed in the sidebar
	 *
	 * @return string
	 */
	public function category() {
		return static::get_thrive_basic_label();
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
				'url'  => 'columns',
				'link' => 'https://help.thrivethemes.com/en/articles/4425769-how-to-use-the-columns-element',
			],
		];
	}
}
