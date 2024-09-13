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
 * Class TCB_Progressbar_Element
 */
class TCB_Progressbar_Element extends TCB_Cloud_Template_Element_Abstract {

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Progress Bar', 'thrive-cb' );
	}

	/**
	 * Get element alternate
	 *
	 * @return string
	 */
	public function alternate() {
		return 'progress, fill';
	}

	/**
	 * Return icon class needed for display in menu
	 *
	 * @return string
	 */
	public function icon() {
		return 'progress_bar';
	}

	/**
	 * Section element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.tve-progress-bar-wrapper';
	}

	public function is_placeholder() {
		return false;
	}

	/**
	 * HTML layout of the element for when it's dragged in the canvas
	 *
	 * @return string
	 */
	public function html_placeholder( $title = null ) {
		if ( empty( $title ) ) {
			$title = $this->name();
		}

		return tcb_template( 'elements/element-placeholder', array(
			'icon'       => $this->icon(),
			'class'      => 'tcb-ct-placeholder',
			'title'      => $title,
			'extra_attr' => 'data-ct="progressbar-0" data-tcb-elem-type="progressbar" data-specific-modal="progressbar"',
		), true );
	}

	/**
	 * Component and control config
	 *
	 * @return array
	 */
	public function own_components() {
		$components = array(
			'progressbar' => array(
				'config' => array(
					'ProgressPalette'  => [
						'config'  => [],
						'extends' => 'PalettesV2',
					],
					'Type'             => array(
						'config'  => array(
							'name'    => __( 'Type', 'thrive-cb' ),
							'buttons' => array(
								array(
									'icon'    => '',
									'text'    => __( 'Simple', 'thrive-cb' ),
									'value'   => 'simple',
									'default' => true,
								),
								array(
									'icon'  => '',
									'text'  => __( 'Nodes', 'thrive-cb' ),
									'value' => 'nodes',
								),
							),
						),
						'extends' => 'ButtonGroup',
					),
					'NodesControl'     => [
						'config' => [
							'sortable' => true,
							'tpl'      => 'controls/preview-list-inline-item',
						],
					],
					'LabelPosition'    => array(
						'config'  => array(
							'name'    => __( 'Progress label position', 'thrive-cb' ),
							'options' => array(
								array(
									'value' => 'inside',
									'class' => 'simple',
									'name'  => __( 'Inside bar', 'thrive-cb' ),
								),
								array(
									'value' => 'above',
									'class' => 'simple nodes',
									'name'  => __( 'Above bar', 'thrive-cb' ),
								),
								array(
									'value' => 'below',
									'class' => 'simple nodes',
									'name'  => __( 'Below bar', 'thrive-cb' ),
								),
							),
						),
						'extends' => 'Select',
					),
					'DisplayLabels'    => array(
						'config'  => array(
							'name'    => __( 'Display progress labels', 'thrive-cb' ),
							'options' => array(
								array(
									'value' => 'all',
									'class' => 'nodes',
									'name'  => __( 'All', 'thrive-cb' ),
								),
								array(
									'value' => 'current',
									'class' => 'nodes',
									'name'  => __( 'Current', 'thrive-cb' ),
								),
								array(
									'class' => 'nodes',
									'value' => 'sides',
									'name'  => __( 'First & last', 'thrive-cb' ),
								),
							),
						),
						'extends' => 'Select',
					),
					'BarHeight'        => array(
						'config'  => array(
							'default' => '16',
							'min'     => '1',
							'max'     => '100',
							'label'   => __( 'Bar height', 'thrive-cb' ),
							'um'      => [ 'px' ],
						),
						'extends' => 'Slider',
					),
					'HasLabels'        => array(
						'config'  => array(
							'name'    => '',
							'label'   => __( 'Show label', 'thrive-cb' ),
							'default' => false,
						),
						'extends' => 'Switch',
					),
					'WithDualProgress' => array(
						'config'  => array(
							'name'    => '',
							'label'   => __( 'Enable dual progress', 'thrive-cb' ),
							'default' => false,
						),
						'extends' => 'Switch',
					),
					'WithAnimation'    => array(
						'config'  => array(
							'name'    => '',
							'label'   => __( 'Fill animation on view', 'thrive-cb' ),
							'default' => false,
						),
						'extends' => 'Switch',
					),
					'HideEmpty'        => array(
						'config'  => array(
							'name'    => '',
							'label'   => __( 'Current progress is 0%', 'thrive-cb' ),
							'default' => false,
						),
						'extends' => 'Switch',
					),
					'HideFull'         => array(
						'config'  => array(
							'name'    => '',
							'label'   => __( 'Current progress is 100%', 'thrive-cb' ),
							'default' => false,
						),
						'extends' => 'Switch',
					),
					'AnimSpeed'        => array(
						'config'  => array(
							'name'    => __( 'Speed', 'thrive-cb' ),
							'buttons' => array(
								array(
									'icon'  => '',
									'text'  => __( 'Slow', 'thrive-cb' ),
									'value' => '5s',
								),
								array(
									'icon'    => '',
									'text'    => __( 'Default', 'thrive-cb' ),
									'value'   => '3s',
									'default' => true,
								),
								array(
									'icon'  => '',
									'text'  => __( 'Fast', 'thrive-cb' ),
									'value' => '1s',
								),
							),
						),
						'extends' => 'ButtonGroup',
					),
					'AnimStart'        => array(
						'config'  => array(
							'default' => '50',
							'min'     => '0',
							'max'     => '100',
							'label'   => __( 'Start at', 'thrive-cb' ),
							'um'      => [ '%' ],
						),
						'extends' => 'Slider',
					),
				),
			),
			'borders'     => [
				'config' => [
					'Borders' => [],
					'Corners' => [],
				],
			],
			'typography'  => [ 'hidden' => true ],
			'animation'   => [ 'hidden' => true ],
			'layout'      => [
				'disabled_controls' => [
					'Overflow',
					'ScrollStyle',
					'Height',
				],
			],
		);

		return array_merge(
			$components,
			$this->group_component()
		);
	}

	public function has_group_editing() {
		return array(
			'select_values' => array(
				array(
					'value'    => 'all_labels',
					'selector' => ' .tve-progress-label-wrapper--nodes .tve-progress-label',
					'name'     => __( 'Progress labels', 'thrive-cb' ),
					'singular' => __( '-- Progress label item %s', 'thrive-cb' ),
				),
				array(
					'value'     => 'all_labels_simple',
					'selector'  => ' .tve-progress-label:not(.tve-progress-label-wrapper--nodes *)',
					'name'      => __( 'Progress labels', 'thrive-cb' ),
					'singular'  => __( '-- Progress label item %s', 'thrive-cb' ),
					'no_unlock' => true,
				),

				array(
					'value'    => 'all_icons',
					'selector' => ' .tve-steps-wrapper .tve-progress-icon',
					'name'     => __( 'Progress icons', 'thrive-cb' ),
					'singular' => __( '-- Progress icon item %s', 'thrive-cb' ),
				),
				array(
					'value'    => 'all_nodes',
					'selector' => ' .tve-steps-wrapper .tve-progress-node',
					'name'     => __( 'Progress nodes', 'thrive-cb' ),
					'singular' => __( '-- Progress node item %s', 'thrive-cb' ),
				),
				array(
					'value'     => 'all_line_background',
					'selector'  => ' .tve-progress-line--background',
					'name'      => __( 'Grouped lines background', 'thrive-cb' ),
					'singular'  => __( '-- Line background item %s', 'thrive-cb' ),
					'no_unlock' => true,
				),
				array(
					'value'     => 'all_lines',
					'selector'  => ' .tve-progress-line--fill',
					'name'      => __( 'Grouped completed lines', 'thrive-cb' ),
					'singular'  => __( '-- Completed line item %s', 'thrive-cb' ),
					'no_unlock' => true,
				),
				array(
					'value'     => 'all_wrapper_lines',
					'selector'  => ' .tve-line-wrapper',
					'name'      => __( 'Grouped wrapper lines', 'thrive-cb' ),
					'singular'  => __( '-- Wrapper line item %s', 'thrive-cb' ),
					'no_unlock' => true,
				),
				array(
					'value'     => 'all_dual_lines',
					'selector'  => ' .tve-progress-line--fill-dual',
					'name'      => __( 'Grouped secondary completed lines', 'thrive-cb' ),
					'singular'  => __( '-- Secondary completed line item %s', 'thrive-cb' ),
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
				'url'  => 'progress_bar',
				'link' => 'https://help.thrivethemes.com/en/articles/4790886-how-to-use-the-progress-bar-element',
			],
		];
	}
}
