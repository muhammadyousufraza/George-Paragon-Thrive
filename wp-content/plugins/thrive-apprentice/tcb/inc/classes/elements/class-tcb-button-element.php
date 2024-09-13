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
 * Class TCB_Button_Element
 */
class TCB_Button_Element extends TCB_Cloud_Template_Element_Abstract {

	/**
	 * Get element alternate
	 *
	 * @return string
	 */
	public function alternate() {
		return 'button';
	}

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Button', 'thrive-cb' );
	}

	/**
	 * Return icon class needed for display in menu
	 *
	 * @return string
	 */
	public function icon() {
		return 'button';
	}

	/**
	 * Button element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.thrv-button, .thrv_button_shortcode';
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
	 * Allow this element to be also styled for active state
	 *
	 * The active state class is .tcb-active-state
	 *
	 * @return string
	 */
	public function active_state_config() {
		return apply_filters( 'tcb_button_active_state', false );
	}

	/**
	 * HTML layout of the element for when it's dragged in the canvas
	 *
	 * @return string
	 */
	protected function html() {
		return tcb_template( 'elements/' . $this->tag() . '.php', $this, true );
	}

	/**
	 * Component and control config
	 *
	 * @return array
	 */
	public function own_components() {
		$button = array(
			'button'     => array(
				'config' => array(
					'ButtonPalettes' => [
						'config'  => [],
						'extends' => 'Palettes',
					],
					'icon_side'      => array(
						'config' => array(
							'name'    => __( 'Icon side', 'thrive-cb' ),
							'buttons' => array(
								array( 'value' => 'left', 'text' => __( 'Left', 'thrive-cb' ), 'default' => true ),
								array( 'value' => 'right', 'text' => __( 'Right', 'thrive-cb' ) ),
							),
						),
					),
					'ButtonIcon'     => array(
						'config'  => array(
							'name'    => '',
							'label'   => __( 'Add icon', 'thrive-cb' ),
							'default' => false,
						),
						'extends' => 'Switch',
					),
					'MasterColor'    => array(
						'config' => array(
							'default'             => '000',
							'label'               => __( 'Master Color', 'thrive-cb' ),
							'important'           => true,
							'affected_components' => [ 'shadow', 'background', 'borders' ],
							'options'             => [
								'showGlobals' => false,
							],
						),
					),
					'SecondaryText'  => array(
						'config'  => array(
							'name'    => '',
							'label'   => __( 'Secondary button text', 'thrive-cb' ),
							'default' => false,
						),
						'to'      => '.tcb-button-texts',
						'extends' => 'Switch',
					),
					'ButtonSize'     => array(
						'css_prefix' => tcb_selection_root() . ' ',
						'config'     => array(
							'name'       => __( 'Size and alignment', 'thrive-cb' ),
							'full-width' => true,
							'buttons'    => array(
								array(
									'value'      => 's',
									'properties' => [
										'padding'     => '12px 15px',
										'font-size'   => '18px',
										'line-height' => '1.2em',
									],
									'text'       => 'S',
									'default'    => true,
								),
								array(
									'value'      => 'm',
									'properties' => [
										'padding'     => '14px 22px',
										'font-size'   => '24px',
										'line-height' => '1.2em',
									],
									'text'       => 'M',
								),
								array(
									'value'      => 'l',
									'properties' => [
										'padding'     => '18px 30px',
										'font-size'   => '32px',
										'line-height' => '1.2em',
									],
									'text'       => 'L',
								),
								array(
									'value'      => 'xl',
									'properties' => [
										'padding'     => '22px 40px',
										'font-size'   => '38px',
										'line-height' => '1.1em',
									],
									'text'       => 'XL',
								),
								array(
									'value'      => 'xxl',
									'properties' => [
										'padding'     => '32px 50px',
										'font-size'   => '52px',
										'line-height' => '1.1em',
									],
									'text'       => 'XXL',
								),
							),
						),
					),
					'Align'          => array(
						'config' => array(
							'buttons' => array(
								array(
									'icon'    => 'a_left',
									'value'   => 'left',
									'tooltip' => __( 'Align Left', 'thrive-cb' ),
								),
								array(
									'icon'    => 'a_center',
									'value'   => 'center',
									'default' => true,
									'tooltip' => __( 'Align Center', 'thrive-cb' ),
								),
								array(
									'icon'    => 'a_right',
									'value'   => 'right',
									'tooltip' => __( 'Align Right', 'thrive-cb' ),
								),
								array(
									'text'    => 'FULL',
									'value'   => 'full',
									'tooltip' => __( 'Full Width', 'thrive-cb' ),
								),
							),
						),
					),
					'ButtonWidth'    => array(
						'config'  => array(
							'default' => '0',
							'min'     => '10',
							'max'     => '1080',
							'label'   => __( 'Button width', 'thrive-cb' ),
							'um'      => [ '%', 'px' ],
							'css'     => 'max-width',
						),
						'extends' => 'Slider',
					),
					'style'          => array(
						'css_suffix' => ' .tcb-button-link',
						'config'     => array(
							'label'         => __( 'Button Styles', 'thrive-cb' ),
							'items'         => [],
							'default_label' => __( 'Template Button', 'thrive-cb' ),
							'default'       => 'default',
						),
					),
				),
			),
			'animation'  => [
				'config' => [
					'to' => '.tcb-button-link',
				],
			],
			'background' => [
				'config' => [
					'css_suffix' => ' .tcb-button-link',
				],
			],
			'borders'    => [
				'config' => [
					'css_suffix' => ' .tcb-button-link',
				],
			],
			'typography' => [
				'config' => [
					'css_suffix'    => ' .tcb-button-link',
					'FontColor'     => [
						'css_suffix' => ' .tcb-button-link span',
					],
					'FontSize'      => [
						'css_suffix' => ' .tcb-button-link',
						'important'  => true,
					],
					'TextStyle'     => [
						'css_suffix' => ' .tcb-button-link span',
					],
					'LineHeight'    => [
						'css_suffix' => ' .tcb-button-link',
					],
					'FontFace'      => [
						'css_suffix' => ' .tcb-button-link',
					],
					'TextTransform' => [
						'css_suffix' => ' .tcb-button-link span',
					],
					'LetterSpacing' => [
						'css_suffix' => ' .tcb-button-link',
					],
				],
			],
			'shadow'     => [
				'config' => [
					'css_suffix'     => ' .tcb-button-link',
					'default_shadow' => 'none',
				],
			],
			'layout'     => [
				'config'            => [
					'MarginAndPadding' => [
						'padding_suffix' => ' .tcb-button-link',
					],
					'Height'           => [
						'css_suffix' => ' .tcb-button-link',
					],
				],
				'disabled_controls' => [
					'Display',
					'Alignment',
					'Overflow',
					'ScrollStyle',
				],
			],
			'scroll'     => [
				'hidden' => false,
			],
		);

		return array_merge( $button, $this->shared_styles_component() );
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
		return static::get_thrive_basic_label();
	}

	/**
	 * Get default button templates from the cloud
	 *
	 * @return array|mixed|WP_Error
	 */
	public function get_default_templates() {
		return tve_get_cloud_content_templates( $this->get_template_tag() );
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
				'url'  => 'button',
				'link' => 'https://help.thrivethemes.com/en/articles/4425768-how-to-use-the-button-element',
			],
		];
	}
}
