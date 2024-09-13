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
 * Class TCB_Menu_Element
 */
class TCB_Menu_Element extends TCB_Cloud_Template_Element_Abstract {

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Custom Menu', 'thrive-cb' );
	}

	/**
	 * All these elements act as placeholders
	 *
	 * @return boolean
	 */
	public function is_placeholder() {
		return false;
	}

	/**
	 * Get element alternate
	 *
	 * @return string
	 */
	public function alternate() {
		return 'navigation menu, nav, nav menu';
	}


	/**
	 * Return icon class needed for display in menu
	 *
	 * @return string
	 */
	public function icon() {
		return 'menu';
	}

	/**
	 * Section element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.thrv_widget_menu:not(.tcb-mega-std)';
	}

	/**
	 * Component and control config
	 *
	 * @return array
	 */
	public function own_components() {
		$dropdown_svg   = $this->get_icon_styles();
		$dropdown_icons = [];
		foreach ( $dropdown_svg as $key => & $dropdown_icon ) {
			$dropdown_icons[ $key ] = $dropdown_icon['label'];
			unset( $dropdown_icon['label'] );
		}
		unset( $dropdown_icon );

		$menus = array(
			array(
				'value' => 'custom',
				'name'  => __( 'Custom', 'thrive-cb' ),
			),
		);
		foreach ( tve_get_custom_menus() as $menu ) {
			$menu['value'] = $menu['id'];
			$menus []      = $menu;
		}

		$menu = array(
			'menu'                => array(
				'config' => array(
					'MenuPalettes'       => [
						'config'  => [],
						'extends' => 'Palettes',
					],
					'MenuSource'         => array(
						'config'  => array(
							'label'   => __( 'Menu source', 'thrive-cb' ),
							'options' => $menus,
						),
						'extends' => 'Select',
					),
					'OrderList'          => [
						'config' => [
							'sortable'   => true,
							'max_levels' => 2,
							'tpl'        => 'controls/menu/preview-list-item',
						],
					],
					'SelectMenu'         => [],
					'HoverEffect'        => array(
						'config'  => array(
							'name'    => __( 'Hover effect', 'thrive-cb' ),
							'options' => [
								'none'    => 'None',
								'style_1' => 'Underline',
								'style_2' => 'Double line',
								'style_3' => 'Brackets',
							],
						),
						'extends' => 'Select',
					),
					'ContentAlign'       => array(
						'to'      => '.menu-item',
						'config'  => array(
							'name'    => __( 'Content alignment', 'thrive-cb' ),
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
							],
						),
						'extends' => 'ButtonGroup',
					),
					'Align'              => array(
						'config'     => array(
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
						'css_suffix' => '.tve-vertical-menu',
					),
					'HamburgerMenuAlign' => [
						'config' => [
							'buttons' => [
								[
									'icon'    => 'a_left',
									'value'   => 'flex-start',
									'default' => true,
									'tooltip' => __( 'Align Left', 'thrive-cb' ),
								],
								[
									'icon'    => 'a_center',
									'value'   => 'center',
									'tooltip' => __( 'Align Center', 'thrive-cb' ),
								],
								[
									'icon'    => 'a_right',
									'value'   => 'flex-end',
									'tooltip' => __( 'Align Right', 'thrive-cb' ),
								],
							],
						],
					],
					'MenuWidth'          => array(
						'config'     => array(
							'default' => '0',
							'min'     => '50',
							'max'     => '1080',
							'label'   => __( 'Menu width', 'thrive-cb' ),
							'um'      => [ 'px', '%' ],
							'css'     => 'max-width',
						),
						'css_suffix' => '.tve-vertical-menu',
						'extends'    => 'Slider',
					),
					'DropdownDirection'  => array(
						'config'  => array(
							'name'    => __( 'Expand style', 'thrive-cb' ),
							'options' => array(
								'vm-toggle' => __( 'Toggle', 'thrive-cb' ),
								'vmd-right' => __( 'Drop right', 'thrive-cb' ),
								'vmd-left'  => __( 'Drop left', 'thrive-cb' ),
							),
						),
						'extends' => 'Select',
					),
					'DropdownIcon'       => array(
						'config' => array(
							'name'    => __( 'Dropdown icon', 'thrive-cb' ),
							'options' => $dropdown_icons,
						),
						'paths'  => $dropdown_svg,
					),
					'DropdownAnimation'  => array(
						'config'  => array(
							'name'    => __( 'Dropdown animation', 'thrive-cb' ),
							'options' => array(
								''          => __( 'None (instant)', 'thrive-cb' ),
								'da-fade'   => __( 'Fade In and Out ', 'thrive-cb' ),
								'da-slide1' => __( 'Slide Down #1', 'thrive-cb' ),
								'da-slide2' => __( 'Slide Down #2', 'thrive-cb' ),
								'da-fold'   => __( 'Fold Out', 'thrive-cb' ),
							),
						),
						'extends' => 'Select',
					),
					'MenuType'           => array(
						'config'  => array(
							'options' => [
								'horizontal' => __( 'Horizontal', 'thrive-cb' ),
								'vertical'   => __( 'Vertical', 'thrive-cb' ),
								'hamburger'  => __( 'Hamburger', 'thrive-cb' ),
							],
							'name'    => __( 'Type', 'thrive-cb' ),
						),
						'extends' => 'Select',
					),
					'MenuSpacing'        => array(
						'config'  => array(
							'name'       => __( 'Spacing', 'thrive-cb' ),
							'full-width' => true,
							'buttons'    => array(
								array(
									'value' => 'horizontal',
									'text'  => __( 'Horizontal', 'thrive-cb' ),
								),
								array(
									'value' => 'vertical',
									'text'  => __( 'Vertical', 'thrive-cb' ),
								),
								array(
									'value' => 'between',
									'text'  => __( 'Between', 'thrive-cb' ),
								),
							),
						),
						'extends' => 'Tabs',
					),
					'LogoSplit'          => array(
						'config'  => array(
							'label'   => __( 'Split menu with logo', 'thrive-cb' ),
							'default' => false,
						),
						'extends' => 'Switch',
					),
					'MenuState'          => array(
						'config'  => array(
							'label'   => __( 'View expanded menu in editor', 'thrive-cb' ),
							'default' => false,
						),
						'extends' => 'Switch',
					),
					'MenuOverlay'        => array(
						'config'  => array(
							'label'   => __( 'Overlay', 'thrive-cb' ),
							'default' => false,
						),
						'extends' => 'Switch',
					),
					'MenuOverlayColor'   => array(
						'config'  => array(
							'default' => 'transparent',
							'label'   => __( 'Color ', 'thrive-cb' ),
							'options' => [
								'output' => 'object',
							],
						),
						'extends' => 'ColorPicker',
					),
					'HorizontalSpacing'  => [
						'to'      => '.tve_w_menu > .menu-item',
						'config'  => [
							'default' => '16',
							'min'     => '0',
							'max'     => '100',
							'label'   => '',
							'um'      => [ 'px' ],
						],
						'extends' => 'Slider',
					],
					'VerticalSpacing'    => [
						'to'      => '.tve_w_menu > .menu-item',
						'config'  => [
							'default' => '1',
							'min'     => '0',
							'max'     => '100',
							'label'   => '',
							'um'      => [ 'px' ],
						],
						'extends' => 'Slider',
					],
					'BetweenSpacing'     => [
						'to'      => '.tve_w_menu > .menu-item',
						'config'  => [
							'default' => 'auto',
							'min'     => '0',
							'max'     => '300',
							'label'   => '',
							'um'      => [ 'px' ],
						],
						'extends' => 'Slider',
					],
					'MobileSide'         => array(
						'config'  => array(
							'name'    => __( 'Menu display', 'thrive-cb' ),
							'options' => [
								'tve-mobile-side-right'      => 'Off screen, right',
								'tve-mobile-side-left'       => 'Off screen, left',
								'tve-mobile-dropdown'        => 'Drop down',
								'tve-mobile-side-fullscreen' => 'Full screen overlay',
							],
						),
						'extends' => 'Select',
					),
					'DisableActiveLinks' => array(
						'config'  => array(
							'label' => __( 'Disable links to current page', 'thrive-cb' ),
						),
						'extends' => 'Switch',
					),
				),
				'icons'  => $this->get_trigger_icons(),
			),
			'background'          => [
				'config' => [
					'css_suffix' => ' .tve_w_menu',
				],
			],
			'typography'          => [
				'hidden' => true,
			],
			'animation'           => [ 'hidden' => true ],
			'shadow'              => [
				'config' => [
					'css_suffix' => ' .tve_w_menu',
				],
			],
			'borders'             => [
				'config' => [
					'css_suffix' => ' .tve_w_menu',
					'Corners'    => [
						'overflow' => false,
					],
				],
			],
			'layout'              => array(
				'disabled_controls' => [
					'Display',
					'Width',
					'Height',
				],
				'config'            => array(
					'MarginAndPadding' => [
						'padding_suffix' => ' .tve_w_menu',
					],
					'Alignment'        => array(
						'override_buttons' => array(
							array( 'icon' => 'a_left', 'value' => 'left', 'data' => [ 'tooltip' => 'Align Left' ] ),
							array( 'icon' => 'a_center', 'value' => 'center', 'default' => true, 'data' => [ 'tooltip' => 'Align Center' ] ),
							array( 'icon' => 'a_right', 'value' => 'right', 'data' => [ 'tooltip' => 'Align Right' ] ),
							array( 'text' => 'FULL', 'value' => 'full', 'tooltip' => __( 'Full Width', 'thrive-cb' ), ),
						),
					),
				),
			),
			'conditional-display' => [
				'hidden' => false,
			],
		);

		return array_merge( $menu, $this->group_component() );
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
			'elem_selector' => '.thrv_widget_menu',
			'select_values' => array(
				array(
					'value'    => 'top_level',
					'selector' => '.thrive-shortcode-html ul.tve_w_menu > li:not(.tcb-menu-logo-wrap)',
					'element'  => '.thrive-shortcode-html li:not(.tcb-menu-logo-wrap)',
					'name'     => __( 'Top Level Items', 'thrive-cb' ),
					/* Translators: %s represents index of the unlocked item */
					'singular' => __( '-- Item %s', 'thrive-cb' ),
				),
				array(
					'value'    => 'dropdowns',
					'selector' => '.tve_w_menu .sub-menu',
					'element'  => '.tve_w_menu .sub-menu',
					'name'     => __( 'All Dropdowns', 'thrive-cb' ),
					/* Translators: %s represents index of the unlocked item */
					'singular' => __( '-- Dropdown %s', 'thrive-cb' ),
				),
				array(
					'value'    => 'items',
					'selector' => '.thrive-shortcode-html li li',
					'element'  => '.thrive-shortcode-html li',
					'name'     => __( 'All Dropdown Items', 'thrive-cb' ),
					/* Translators: %s represents index of the unlocked item */
					'singular' => __( '-- Item %s', 'thrive-cb' ),
				),
				array(
					'value'    => 'menu-images',
					'selector' => '.tcb-menu-item-image:not(.tcb-elem-placeholder)',
					'element'  => '.tcb-menu-item-image',
					'name'     => __( 'All Menu Images', 'thrive-cb' ),
					/* Translators: %s represents index of the unlocked item */
					'singular' => __( '-- Menu Image %s', 'thrive-cb' ),
				),
				array(
					'value'    => 'menu-images-hamburger',
					'selector' => '.tcb-menu-img-hamburger .tcb-mm-image:not(.tcb-elem-placeholder)',
					'element'  => '.tcb-menu-img-hamburger .tcb-mm-image',
					'name'     => __( 'All Menu Images', 'thrive-cb' ),
					/* Translators: %s represents index of the unlocked item */
					'singular' => __( '-- Menu Image %s', 'thrive-cb' ),
				),
			),
		);
	}

	public function get_icon_styles() {
		return [
			'none'    => [
				'label' => 'None',
				'up'    => '',
				'box'   => '',
			],
			'style_1' => [
				'label' => 'Angle',
				'up'    => '<path d="M151.5 347.8L3.5 201c-4.7-4.7-4.7-12.3 0-17l19.8-19.8c4.7-4.7 12.3-4.7 17 0L160 282.7l119.7-118.5c4.7-4.7 12.3-4.7 17 0l19.8 19.8c4.7 4.7 4.7 12.3 0 17l-148 146.8c-4.7 4.7-12.3 4.7-17 0z"/>',
				'box'   => '0 0 320 512',
			],
			'style_2' => [
				'label' => 'Chevron',
				'up'    => '<path d="M443.5 162.6l-7.1-7.1c-4.7-4.7-12.3-4.7-17 0L224 351 28.5 155.5c-4.7-4.7-12.3-4.7-17 0l-7.1 7.1c-4.7 4.7-4.7 12.3 0 17l211 211.1c4.7 4.7 12.3 4.7 17 0l211-211.1c4.8-4.7 4.8-12.3.1-17z"/>',
				'box'   => '0 0 448 512',
			],
			'style_3' => [
				'label' => 'Caret',
				'up'    => '<path d="M31.3 192h257.3c17.8 0 26.7 21.5 14.1 34.1L174.1 354.8c-7.8 7.8-20.5 7.8-28.3 0L17.2 226.1C4.6 213.5 13.5 192 31.3 192z"/>',
				'box'   => '0 0 320 512',
			],
			'style_4' => [
				'label' => 'Triangle',
				'up'    => '<path d="M272 160H48.1c-42.6 0-64.2 51.7-33.9 81.9l111.9 112c18.7 18.7 49.1 18.7 67.9 0l112-112c30-30.1 8.7-81.9-34-81.9zM160 320L48 208h224L160 320z"/>',
				'box'   => '0 0 320 512',
			],
		];
	}

	public function get_trigger_icons() {
		return [
			'style_1' => [
				'open'  => '<svg class="tcb-icon" viewBox="0 0 24 24" data-name="align-justify"><g><g><path class="st0" d="M23,13H1c-0.6,0-1-0.4-1-1s0.4-1,1-1h22c0.6,0,1,0.4,1,1S23.6,13,23,13z"/></g><g><path class="st0" d="M23,6.7H1c-0.6,0-1-0.4-1-1s0.4-1,1-1h22c0.6,0,1,0.4,1,1S23.6,6.7,23,6.7z"/></g><g><path class="st0" d="M23,19.3H1c-0.6,0-1-0.4-1-1s0.4-1,1-1h22c0.6,0,1,0.4,1,1S23.6,19.3,23,19.3z"/></g></g></svg>',
				'close' => '<svg class="tcb-icon" viewBox="0 0 24 24" data-name="close"><path class="st0" d="M13.4,12l7.1-7.1c0.4-0.4,0.4-1,0-1.4s-1-0.4-1.4,0L12,10.6L4.9,3.5c-0.4-0.4-1-0.4-1.4,0s-0.4,1,0,1.4l7.1,7.1 l-7.1,7.1c-0.4,0.4-0.4,1,0,1.4c0.4,0.4,1,0.4,1.4,0l7.1-7.1l7.1,7.1c0.4,0.4,1,0.4,1.4,0c0.4-0.4,0.4-1,0-1.4L13.4,12z"/></svg>',
			],
			'style_2' => [
				'open'  => '<svg class="tcb-icon" viewBox="0 0 32 32" data-name="align-justify"><path class="st0" d="M0,0v32h32V0H0z M23.5,23.1h-15c-0.7,0-1.3-0.6-1.3-1.3s0.6-1.3,1.3-1.3h15c0.7,0,1.3,0.6,1.3,1.3 S24.2,23.1,23.5,23.1z M23.5,17.3h-15c-0.7,0-1.3-0.6-1.3-1.3s0.6-1.3,1.3-1.3h15c0.7,0,1.3,0.6,1.3,1.3S24.2,17.3,23.5,17.3z M23.5,11.4h-15c-0.7,0-1.3-0.6-1.3-1.3s0.6-1.3,1.3-1.3h15c0.7,0,1.3,0.6,1.3,1.3S24.2,11.4,23.5,11.4z"/></svg>',
				'close' => '<svg class="tcb-icon" viewBox="0 0 32 32" data-name="close"><path class="st0" d="M0,0v32h32V0H0z M22.2,20.4c0.5,0.5,0.5,1.3,0,1.8c-0.2,0.2-0.6,0.4-0.9,0.4s-0.6-0.1-0.9-0.4L16,17.8l-4.4,4.4 c-0.2,0.2-0.6,0.4-0.9,0.4s-0.6-0.1-0.9-0.4c-0.5-0.5-0.5-1.3,0-1.8l4.4-4.4l-4.4-4.4c-0.5-0.5-0.5-1.3,0-1.8s1.3-0.5,1.8,0l4.4,4.4 l4.4-4.4c0.5-0.5,1.3-0.5,1.8,0s0.5,1.3,0,1.8L17.8,16L22.2,20.4z"/></svg>',
			],
			'style_3' => [
				'open'  => '<svg class="tcb-icon" viewBox="0 0 32 32" data-name="align-justify"><g><rect x="8" y="10.1" class="st0" width="16" height="2"/><rect x="8" y="15" class="st0" width="16" height="2"/><rect x="8" y="19.9" class="st0" width="16" height="2"/><path class="st0" d="M0,0v32h32V0H0z M30,30H2V2h28V30z"/></g></svg>',
				'close' => '<svg class="tcb-icon" viewBox="0 0 32 32" data-name="close"><g><path class="st0" d="M0,0v32h32V0H0z M30,30H2V2h28V30z"/><polygon class="st0" points="11.1,22.4 16,17.4 21,22.4 22.4,21 17.4,16 22.4,11.1 21,9.6 16,14.6 11.1,9.6 9.6,11.1 14.6,16 9.6,21 "/></g></svg>',
			],
			'style_4' => [
				'open'  => '<svg class="tcb-icon" viewBox="0 0 32 32" data-name="align-justify"><g><path class="st0" d="M23.5,11.4h-15c-0.4,0-0.8,0.3-0.8,0.8s0.3,0.8,0.8,0.8h15c0.4,0,0.8-0.3,0.8-0.8S23.9,11.4,23.5,11.4z"/><path class="st0" d="M23.5,15.3h-15c-0.4,0-0.8,0.3-0.8,0.8s0.3,0.8,0.8,0.8h15c0.4,0,0.8-0.3,0.8-0.8S23.9,15.3,23.5,15.3z"/><path class="st0" d="M23.5,19.1h-15c-0.4,0-0.8,0.3-0.8,0.8s0.3,0.8,0.8,0.8h15c0.4,0,0.8-0.3,0.8-0.8S23.9,19.1,23.5,19.1z"/><path class="st0" d="M16,0C7.2,0,0,7.2,0,16v0c0,8.8,7.2,16,16,16s16-7.2,16-16v0C32,7.2,24.8,0,16,0z M16,30.5 C8,30.5,1.5,24,1.5,16C1.5,8,8,1.5,16,1.5C24,1.5,30.5,8,30.5,16C30.5,24,24,30.5,16,30.5z"/></g></svg>',
				'close' => '<svg class="tcb-icon" viewBox="0 0 32 32" data-name="close"><g><path class="st0" d="M16,0C7.2,0,0,7.2,0,16v0c0,8.8,7.2,16,16,16s16-7.2,16-16v0C32,7.2,24.8,0,16,0z M16,30.5 C8,30.5,1.5,24,1.5,16C1.5,8,8,1.5,16,1.5C24,1.5,30.5,8,30.5,16C30.5,24,24,30.5,16,30.5z"/><path class="st0" d="M21.8,10.2c-0.3-0.3-0.8-0.3-1.1,0L16,14.9l-4.8-4.8c-0.3-0.3-0.8-0.3-1.1,0s-0.3,0.8,0,1.1l4.8,4.8l-4.8,4.8 c-0.3,0.3-0.3,0.8,0,1.1c0.1,0.1,0.3,0.2,0.5,0.2s0.4-0.1,0.5-0.2l4.8-4.8l4.8,4.8c0.1,0.1,0.3,0.2,0.5,0.2s0.4-0.1,0.5-0.2 c0.3-0.3,0.3-0.8,0-1.1L17.1,16l4.8-4.8C22.1,10.9,22.1,10.5,21.8,10.2z"/></g></svg>',
			],
			'style_5' => [
				'open'  => '<svg class="tcb-icon" viewBox="0 0 64.4 14.7" data-name="align-justify"><g><g><path class="st0" d="M63.4,8.3h-22c-0.6,0-1-0.4-1-1s0.4-1,1-1h22c0.6,0,1,0.4,1,1S63.9,8.3,63.4,8.3z"/></g><g><path class="st0" d="M63.4,2h-22c-0.6,0-1-0.4-1-1s0.4-1,1-1h22c0.6,0,1,0.4,1,1S63.9,2,63.4,2z"/></g><g><path class="st0" d="M63.4,14.7h-22c-0.6,0-1-0.4-1-1s0.4-1,1-1h22c0.6,0,1,0.4,1,1S63.9,14.7,63.4,14.7z"/></g><g><g><path class="st0" d="M5.6,7.3l0.1-1.4H5.5L5,7.2l-1.4,3l-1.4-3L1.7,5.9H1.5l0.1,1.4v4.4H0V3.3h1.8l1.1,2.5l0.6,1.8h0.1l0.6-1.8 l1.1-2.5h1.8v8.4H5.6V7.3z"/></g><g><path class="st0" d="M9,11.6V3.3h5.2v1.5h-3.5v1.9h2.9v1.5h-2.9v2h3.5v1.5H9z"/></g><g><path class="st0" d="M17.9,7.1l-0.5-1.3h-0.2l0.1,1.4v4.4h-1.6V3.3h1.9l2.1,4.5l0.5,1.3h0.2l-0.1-1.4V3.3h1.6v8.4H20L17.9,7.1z" /></g><g><path class="st0" d="M25.3,3.3v5.4c0,0.6,0.1,1,0.3,1.2c0.2,0.2,0.5,0.4,1,0.4c0.5,0,0.8-0.1,1-0.4c0.2-0.2,0.3-0.6,0.3-1.2V3.3 h1.7v5.2c0,0.6-0.1,1.1-0.2,1.5c-0.1,0.4-0.3,0.8-0.5,1s-0.5,0.5-0.9,0.6c-0.4,0.1-0.9,0.2-1.4,0.2c-0.6,0-1-0.1-1.4-0.2 s-0.7-0.3-0.9-0.6c-0.2-0.3-0.4-0.6-0.5-1c-0.1-0.4-0.2-0.9-0.2-1.5V3.3H25.3z"/></g></g></g></svg>',
				'close' => '<svg class="tcb-icon" viewBox="0 0 60.9 17.6" data-name="close"><g><path class="st0" d="M3.4,13.1c-0.5,0-1-0.1-1.4-0.2c-0.4-0.2-0.8-0.4-1.1-0.7s-0.5-0.8-0.7-1.3S0,9.6,0,8.8s0.1-1.5,0.2-2 c0.2-0.6,0.4-1,0.7-1.4C1.2,5.1,1.6,4.9,2,4.7c0.4-0.2,0.9-0.2,1.4-0.2c0.8,0,1.3,0.2,1.8,0.5s0.8,0.8,1.1,1.5L4.7,7.2 c0-0.2-0.1-0.3-0.2-0.5C4.5,6.5,4.4,6.4,4.3,6.3C4.2,6.2,4.1,6.1,3.9,6C3.8,6,3.6,5.9,3.4,5.9c-0.5,0-0.9,0.1-1.1,0.4 S1.8,7.1,1.8,7.7v2.2c0,0.6,0.1,1,0.4,1.3s0.6,0.4,1.1,0.4c0.2,0,0.4,0,0.6-0.1c0.2-0.1,0.3-0.2,0.4-0.3c0.1-0.1,0.2-0.3,0.3-0.4 c0.1-0.2,0.1-0.3,0.2-0.5l1.4,0.8c-0.1,0.3-0.3,0.6-0.5,0.8c-0.2,0.2-0.4,0.5-0.6,0.6S4.7,12.9,4.4,13C4.1,13.1,3.7,13.1,3.4,13.1z"/><path class="st0" d="M7.5,13V4.6h1.7v6.9H12V13H7.5z"/><path class="st0" d="M15.9,13.1c-0.5,0-1-0.1-1.4-0.2c-0.4-0.2-0.8-0.4-1-0.8c-0.3-0.3-0.5-0.8-0.7-1.3c-0.2-0.5-0.2-1.2-0.2-2 c0-0.8,0.1-1.4,0.2-2s0.4-1,0.7-1.3c0.3-0.3,0.6-0.6,1-0.8c0.4-0.2,0.9-0.2,1.4-0.2c0.5,0,1,0.1,1.4,0.2s0.8,0.4,1,0.8 c0.3,0.3,0.5,0.8,0.7,1.3s0.2,1.2,0.2,2c0,0.8-0.1,1.5-0.2,2c-0.2,0.5-0.4,1-0.7,1.3c-0.3,0.3-0.6,0.6-1,0.8S16.4,13.1,15.9,13.1z M15.9,11.6c0.5,0,0.9-0.1,1.1-0.4s0.4-0.7,0.4-1.3V7.7c0-0.6-0.1-1-0.4-1.3s-0.6-0.4-1.1-0.4c-0.5,0-0.8,0.1-1.1,0.4 s-0.4,0.7-0.4,1.3v2.2c0,0.6,0.1,1,0.4,1.3S15.4,11.6,15.9,11.6z"/><path class="st0" d="M23.1,13.1c-1.3,0-2.2-0.4-3-1.3l1.1-1.1c0.5,0.6,1.1,0.9,1.9,0.9c0.8,0,1.2-0.4,1.2-1.1 c0-0.3-0.1-0.5-0.2-0.7c-0.1-0.2-0.4-0.3-0.7-0.3l-0.8-0.1C21.9,9.3,21.3,9,21,8.6c-0.4-0.4-0.5-0.9-0.5-1.7c0-0.8,0.2-1.4,0.7-1.9 c0.5-0.4,1.2-0.6,2.1-0.6c1.2,0,2.1,0.4,2.7,1.2l-1.1,1.1c-0.2-0.3-0.4-0.4-0.7-0.6c-0.3-0.1-0.6-0.2-0.9-0.2 c-0.4,0-0.7,0.1-0.8,0.2c-0.2,0.1-0.3,0.4-0.3,0.7c0,0.3,0.1,0.5,0.2,0.6c0.1,0.1,0.3,0.2,0.6,0.3L23.7,8c0.4,0.1,0.8,0.2,1,0.3 s0.5,0.3,0.7,0.5c0.2,0.2,0.3,0.4,0.4,0.7c0.1,0.3,0.1,0.6,0.1,0.9c0,0.9-0.3,1.6-0.8,2C24.8,12.9,24.1,13.1,23.1,13.1z"/><path class="st0" d="M27.4,13V4.6h5.2v1.5h-3.5V8h2.9v1.5h-2.9v2h3.5V13H27.4z"/></g><path class="st0" d="M53.6,8.8l7.1-7.1c0.4-0.4,0.4-1,0-1.4s-1-0.4-1.4,0l-7.1,7.1l-7.1-7.1c-0.4-0.4-1-0.4-1.4,0s-0.4,1,0,1.4 l7.1,7.1l-7.1,7.1c-0.4,0.4-0.4,1,0,1.4c0.2,0.2,0.5,0.3,0.7,0.3s0.5-0.1,0.7-0.3l7.1-7.1l7.1,7.1c0.2,0.2,0.5,0.3,0.7,0.3 s0.5-0.1,0.7-0.3c0.4-0.4,0.4-1,0-1.4L53.6,8.8z"/></svg>',
			],
		];
	}

	/**
	 * Build html for the hamburger trigger icons
	 *
	 * @param array $attributes
	 *
	 * @return string
	 */
	public function get_hamburger_trigger_html( $attributes = [] ) {
		$sets          = $this->get_trigger_icons();
		$trigger_icons = empty( $attributes['mobile_icon'] ) ? $sets['style_1'] : $sets[ $attributes['mobile_icon'] ];

		$trigger_classes = [ 'tve-m-trigger' ];
		if ( ! empty( $attributes['dir'] ) ) {
			$trigger_classes[] = 't_' . $attributes['dir'];
		}
		if ( ! empty( $attributes['color'] ) ) {
			$trigger_classes[] = $attributes['color'];
		}

		return sprintf(
			'<a%s class="%s" href="javascript:void(0)">' .
			'<div class="thrv_wrapper thrv_icon tcb-icon-open">%s</div>' .
			'<div class="thrv_wrapper thrv_icon tcb-icon-close">%s</div>' .
			'</a>',
			empty( $attributes['trigger_attr'] ) ? '' : sprintf( " data-tve-custom-colour='%s'", $attributes['trigger_attr'] ),
			implode( ' ', $trigger_classes ),
			$trigger_icons['open'],
			$trigger_icons['close']
		);
	}

	/**
	 * Use another set of cloud templates - since the revamp
	 *
	 * @return string
	 */
	public function get_template_tag() {
		return 'menu_v2';
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
				'url'  => 'menu',
				'link' => 'https://help.thrivethemes.com/en/articles/4425832-how-to-use-the-custom-menu-element',
			],
		];
	}
}
