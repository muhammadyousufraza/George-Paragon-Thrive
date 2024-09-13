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
 * Class TCB_Toggle_Title_Element
 */
class TCB_Toggle_Title_Element extends TCB_Element_Abstract {

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Toggle Title', 'thrive-cb' );
	}

	/**
	 * Return icon class needed for display in menu
	 *
	 * @return string
	 */
	public function icon() {
		return 'toggle';
	}

	/**
	 * Section element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.thrv_toggle_title';
	}

	/**
	 * Component and control config
	 *
	 * @return array
	 */
	public function own_components() {
		$prefix_config = tcb_selection_root() . ' ';

		return array(
			'toggle_title' => array(
				'config' => array(
					'TextTypeDropdown' => array(
						'config'  => array(
							'default'     => 'none',
							'name'        => __( 'Change text type', 'thrive-cb' ),
							'label_col_x' => 6,
							'options'     => array(
								array(
									'name'  => __( 'Heading 1', 'thrive-cb' ),
									'value' => 'h1',
								),
								array(
									'name'  => __( 'Heading 2', 'thrive-cb' ),
									'value' => 'h2',
								),
								array(
									'name'  => __( 'Heading 3', 'thrive-cb' ),
									'value' => 'h3',
								),
								array(
									'name'  => __( 'Heading 4', 'thrive-cb' ),
									'value' => 'h4',
								),
								array(
									'name'  => __( 'Heading 5', 'thrive-cb' ),
									'value' => 'h5',
								),
								array(
									'name'  => __( 'Heading 6', 'thrive-cb' ),
									'value' => 'h6',
								),
								array(
									'name'  => __( 'Paragraph', 'thrive-cb' ),
									'value' => 'p',
								),
								array(
									'name'  => __( 'Plain text', 'thrive-cb' ),
									'value' => 'span',
								),
							),
						),
						'extends' => 'Select',
					),
					'DefaultState'     => array(
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
					'ShowIcon'         => array(
						'config'  => array(
							'label' => __( 'Show Icon' ),
						),
						'extends' => 'Switch',
					),
					'IconColor'        => array(
						'css_suffix' => ' .tve_toggle',
						'config'     => array(
							'label'   => __( 'Icon color', 'thrive-cb' ),
							'options' => [ 'noBeforeInit' => false ],
						),
						'important'  => true,
						'extends'    => 'ColorPicker',
					),
					'IconPlacement'    => array(
						'config'  => array(
							'name'    => __( 'Placement', 'thrive-cb' ),
							'buttons' => [
								[
									'icon'    => '',
									'text'    => 'Left',
									'value'   => 'left',
									'default' => true,
								],
								[
									'icon'  => '',
									'text'  => 'Right',
									'value' => 'right',
								],
							],
						),
						'extends' => 'ButtonGroup',
					),
					'IconSize'         => array(
						'config'     => array(
							'default' => '15',
							'min'     => '0',
							'max'     => '100',
							'label'   => __( 'Icon Size', 'thrive-cb' ),
							'um'      => [ 'px', '%' ],
							'css'     => 'font-size',

						),
						'css_suffix' => ' .tve_toggle',
						'important'  => true,
						'extends'    => 'Slider',
					),
					'RotateIcon'       => array(
						'config'  => array(
							'step'    => '45',
							'label'   => __( 'Rotate Icon', 'thrive-cb' ),
							'default' => '0',
							'min'     => '-180',
							'max'     => '180',
							'um'      => [ ' Deg' ],
						),
						'extends' => 'Slider',
					),
				),
			),
			'typography'   => [
				'config' => [
					'FontColor'     => [
						'css_suffix' => ' .tve-toggle-text',
						'important'  => true,
					],
					'TextAlign'     => [
						'css_suffix' => ' .tve-toggle-text',
						'css_prefix' => $prefix_config,
						'important'  => true,
					],
					'FontSize'      => [
						'css_suffix' => ' .tve-toggle-text',
						'important'  => true,
					],
					'TextStyle'     => [
						'css_suffix' => ' .tve-toggle-text',
						'css_prefix' => $prefix_config,
						'important'  => true,
					],
					'LineHeight'    => [
						'css_suffix' => ' .tve-toggle-text',
						'important'  => true,
					],
					'FontFace'      => [
						'css_suffix' => ' .tve-toggle-text',
						'important'  => true,
					],
					'LetterSpacing' => [
						'css_suffix' => ' .tve-toggle-text',
						'css_prefix' => $prefix_config,
						'important'  => true,
					],
					'TextTransform' => [
						'css_suffix' => ' .tve-toggle-text',
						'css_prefix' => $prefix_config,
						'important'  => true,
					],
				],
			],
			'animation'    => [ 'hidden' => true ],
			'layout'       => [
				'disabled_controls' => [
					'margin',
					'Width',
					'Height',
					'Alignment',
					'Display',
					'.tve-advanced-controls',
				],
			],
			'shadow'       => [
				'config' => [
					'disabled_controls' => [ 'drop' ],
				],
			],
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

	public function hide() {
		return true;
	}

	/**
	 * @return bool
	 */
	public function has_hover_state() {
		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function expanded_state_config() {
		return true;
	}
}
