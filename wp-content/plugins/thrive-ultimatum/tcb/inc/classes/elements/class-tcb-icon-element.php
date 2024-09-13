<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package TCB2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

/**
 * Class TCB_Icon_Element
 */
class TCB_Icon_Element extends TCB_Element_Abstract {

	/**
	 * @return string
	 */
	public function icon() {
		return 'icon';
	}

	/**
	 * @return string
	 */
	public function name() {
		return __( 'Icon', 'thrive-cb' );
	}

	/**
	 * Get element alternate
	 *
	 * @return string
	 */
	public function alternate() {
		return 'media,icon,icons';
	}


	/**
	 * @return string
	 */
	public function identifier() {
		/**
		 * These elements all inherit the icon element class (this one)
		 * Since they all have the '.thrv_icon' class, we must ensure that they are matched by their own identifiers, not by this, so we add them inside :not()
		 */
		$not_icon_elements = implode( ', ', [
			'.tve_lg_input_container .thrv_icon',
			'.tve-login-form-input .thrv_icon',
			'.tcb-carousel-arrow',
			'.tcb-icon-open',
			'.tcb-icon-close',
			'.tcb-icon-close-offscreen',
		] );

		return ".tve_lg_file .thrv_icon, .thrv_icon:not($not_icon_elements)";
	}

	/**
	 * @return array
	 */
	public function own_components() {
		return array(
			'icon'       => array(
				'config' => array(
					'ToggleColorControls'        => [
						'config'  => [
							'name'    => __( 'Color type', 'thrive-cb' ),
							'buttons' => [
								[ 'value' => 'tcb-icon-solid-color', 'text' => __( 'Solid', 'thrive-cb' ) ],
								[ 'value' => 'tcb-icon-gradient-color', 'text' => __( 'Gradient', 'thrive-cb' ) ],
							],
						],
						'extends' => 'ButtonGroup',
					],
					'ColorPicker' => array(
						'css_prefix' => tcb_selection_root() . ' ',
						'css_suffix' => ' > :first-child',
						'config'     => array(
							'label'   => __( 'Color', 'thrive-cb' ),
							'options' => [ 'noBeforeInit' => false ],
						),
					),
					'GradientPicker'               => [
						'config'  => [
							'default' => '000',
							'label'   => __( 'Gradient', 'thrive-cb' ),
							'options' => [
								'output'   => 'object',
								'hasInput' => true,
							],
						],
						'extends' => 'GradientPicker',
					],
					'StyleColor'              => [
						'css_prefix' => tcb_selection_root() . ' ',
						'css_suffix' => ' > :first-child',
						'config'     => [
							'default' => '000',
							'label'   => __( 'Style color', 'thrive-cb' ),
							'info'    => true,
							'options' => [
								'output' => 'object',
							],
						],
						'extends'    => 'ColorPicker',
					],
					'Slider'      => array(
						'config' => array(
							'default' => '30',
							'min'     => '12',
							'max'     => '200',
							'label'   => __( 'Size', 'thrive-cb' ),
							'um'      => [ 'px' ],
							'css'     => 'fontSize',
						),
					),
					'RotateIcon'  => array(
						'config'  => array(
							'step'    => '1',
							'label'   => __( 'Rotate Icon', 'thrive-cb' ),
							'default' => '0',
							'min'     => '-180',
							'max'     => '180',
							'um'      => [ ' Deg' ],
						),
						'extends' => 'Slider',
					),
					'link'        => array(
						'config' => array(
							'label' => __( 'Icon link', 'thrive-cb' ),
							'class' => 'thrv_icon',
						),
					),
					'StylePicker' => array(
						'config' => array(
							'label' => __( 'Choose icon style', 'thrive-cb' ),
							'items' => [
								'circle_outlined'  => 'Circle Outlined',
								'circle_shaded'    => 'Circle Shaded',
								'circle_inverted'  => 'Circle Inverted',
								'rounded_outlined' => 'Rounded Outlined',
								'rounded_shaded'   => 'Rounded Shaded',
								'rounded_inverted' => 'Rounded Inverted',
								'square_outlined'  => 'Square Outlined',
								'square_shaded'    => 'Square Shaded',
								'square_inverted'  => 'Square Inverted',
							],
						),
					),
					'IconPicker'  => array(
						'config'  => array(
							'label_style' => __( 'Change style', 'thrive-cb' ),
							'label_modal' => __( 'Change icon', 'thrive-cb' ),
							'label'       => __( 'Icon and style', 'thrive-cb' ),
						),
						'extends' => 'ModalStylePicker',
					),
				),
			),
			'typography' => [ 'hidden' => true ],
			'shadow'     => [
				'config' => [
					'disabled_controls' => [ 'text' ],
				],
			],
			'layout'     => [
				'config'            => [
					'Position' => [
						'important' => true,
					],
				],
				'disabled_controls' => [
					'Width',
					'Height',
					'Display',
					'Overflow',
					'ScrollStyle',
				],
			],
			'scroll'     => [
				'hidden'            => false,
				'disabled_controls' => [ '[data-value="sticky"]' ],
			],
		);
	}

	/**
	 * @return bool
	 */
	public function has_hover_state() {
		return true;
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
				'url'  => 'icon',
				'link' => 'https://help.thrivethemes.com/en/articles/4425785-how-to-use-the-icon-element',
			],
		];
	}
}
