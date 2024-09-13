<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

if ( ! class_exists( 'Thrive_Theme_Cloud_Element_Abstract' ) ) {
	require_once ARCHITECT_INTEGRATION_PATH . '/classes/class-thrive-theme-cloud-element-abstract.php';
}

/**
 * Class Thrive_Section_Element
 */
class Thrive_Theme_Section_Element extends Thrive_Theme_Cloud_Element_Abstract {
	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Section', 'thrive-theme' );
	}

	/**
	 * WordPress element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.theme-section > div';
	}

	/**
	 * Temporary hide this
	 */
	public function hide() {
		return true;
	}

	/**
	 * Add the theme section component
	 *
	 * @return array
	 */
	public function own_components() {
		$components = parent::own_components();
		$prefix     = tcb_selection_root( false ) . ' ';

		$suffix = [ ' p', ' li', ' label', ' .tcb-plain-text' ];

		$background_selector = '.section-background';
		$content_selector    = '.section-content';

		$components['layout']['config']['MarginAndPadding']['padding_to'] = $content_selector;

		$components['borders']['config']['to'] = $background_selector;
		$components['shadow']['config']['to']  = $background_selector;

		$components['typography']['config']['to']                      = $content_selector;
		$components['typography']['config']['TextStyle']['css_suffix'] = $suffix;

		$components['typography']['config']['FontSize']['css_suffix']   = $suffix;
		$components['typography']['config']['FontSize']['css_prefix']   = $prefix;
		$components['typography']['config']['LineHeight']['css_suffix'] = $suffix;
		$components['typography']['config']['LineHeight']['css_prefix'] = $prefix;
		$components['typography']['config']['FontColor']['css_suffix']  = $suffix;
		$components['typography']['config']['FontColor']['css_prefix']  = $prefix;
		$components['typography']['config']['FontFace']['css_suffix']   = array_merge( $suffix, [ ' h1', ' h2', ' h3', ' h4', ' h5', ' h6' ] );
		$components['typography']['config']['FontFace']['css_prefix']   = $prefix;

		$components['background'] = [
			'config'            => [ 'to' => $background_selector ],
			'disabled_controls' => [],
		];

		$components['animation']  = [ 'hidden' => true ];
		$components['decoration'] = [
			'config' => [ 'to' => $background_selector ],
			'order'  => 50,
		];

		$components['theme_section'] = [
			'config' => [
				'SectionTemplates'   => [
					'config'  => [
						'label' => __( 'Template', 'thrive-theme' ),
					],
					'extends' => 'ModalPicker',
				],
				'StretchBackground'  => [
					'config'  => [
						'name'    => '',
						'label'   => __( 'Stretch background to full width', 'thrive-theme' ),
						'default' => true,
					],
					'extends' => 'Switch',
				],
				'InheritContentSize' => [
					'config'  => [
						'name'    => '',
						'label'   => __( 'Inherit content size from layout', 'thrive-theme' ),
						'default' => true,
					],
					'extends' => 'Switch',
				],
				'MinWidth'           => [
					'config'  => [
						'default' => '1080',
						'min'     => '1',
						'max'     => '1980',
						'label'   => __( 'Section minimum width', 'thrive-theme' ),
						'um'      => [ 'px', '%' ],
						'css'     => 'min-width',
					],
					'extends' => 'Slider',
				],
				'ContentWidth'       => [
					'config'     => [
						'default' => '1080',
						'min'     => '1',
						'max'     => '1980',
						'label'   => __( 'Content width', 'thrive-theme' ),
						'um'      => [ 'px' ],
						'css'     => 'max-width',
					],
					'css_suffix' => " {$content_selector}",
					'extends'    => 'Slider',
				],
				'StretchContent'     => [
					'config'  => [
						'name'    => '',
						'label'   => __( 'Stretch content to fullwidth', 'thrive-theme' ),
						'default' => true,
					],
					'extends' => 'Switch',
				],
				'SectionHeight'      => [
					'config'     => [
						'default' => '80',
						'min'     => '1',
						'max'     => '1000',
						'label'   => __( 'Section minimum height', 'thrive-theme' ),
						'um'      => [ 'px', 'vh' ],
						'css'     => 'min-height',
					],
					'css_suffix' => " {$content_selector}",
					'extends'    => 'Slider',
				],
				'VerticalPosition'   => [
					'config'     => [
						'name'    => __( 'Vertical position', 'thrive-theme' ),
						'buttons' => [
							[
								'icon'    => 'top',
								'default' => true,
								'value'   => '',
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
					],
					'css_suffix' => " {$content_selector}",
					'extends'    => 'ButtonGroup',
				],
				'Visibility'         => [
					'config'  => [
						'name'    => '',
						'label'   => __( 'Visibility', 'thrive-theme' ),
						'default' => true,
					],
					'extends' => 'Switch',
				],
				'Position'           => [
					'config'  => [
						'name'    => __( 'Position', 'thrive-theme' ),
						'options' => [
							[
								'name'  => __( 'Right', 'thrive-theme' ),
								'value' => 'right',
							],
							[
								'name'  => __( 'Left', 'thrive-theme' ),
								'value' => 'left',
							],
						],
						'default' => 'left',
					],
					'extends' => 'Select',
				],
			],
		];

		$components['sidebar-settings'] = [
			'order'  => 2,
			'config' => [
				'SidebarDisplay'              => [
					'config'  => [
						'name'    => __( 'Display sidebar', 'thrive-theme' ),
						'buttons' => [
							[
								'text'    => __( 'Normal', 'thrive-theme' ),
								'default' => true,
								'value'   => 'normal',
							],
							[
								'text'  => __( 'Off screen', 'thrive-theme' ),
								'value' => 'off-screen',
							],
						],
					],
					'extends' => 'ButtonGroup',
				],
				/* Sticky sidebar settings */
				'Sticky'                      => [
					'config'  => [
						'name'    => '',
						'label'   => __( 'Enable sticky sidebar', 'thrive-theme' ),
						'default' => false,
					],
					'extends' => 'Switch',
				],
				'StickyDelta'                 => [
					'config'  => [
						'default' => '0',
						'min'     => '0',
						'max'     => '240',
						'label'   => __( 'Distance from top or bottom', 'thrive-theme' ),
						'um'      => [ 'px' ],
					],
					'extends' => 'Slider',
				],
				'StickyUntil'                 => [
					'config'  => [
						'name'    => __( 'Sticky until', 'thrive-theme' ),
						'options' => [
							[
								'name'  => __( 'End of main container', 'thrive-theme' ),
								'value' => 'main',
							],
							[
								'name'  => __( 'End of page', 'thrive-theme' ),
								'value' => 'end',
							],
							[
								'name'  => __( 'It reaches another element', 'thrive-theme' ),
								'value' => 'element',
							],
						],
						'default' => 'main',
					],
					'extends' => 'Select',
				],
				'StickyElementId'             => [
					'config'  => [
						'label'   => __( 'Element ID', 'thrive-theme' ),
						'default' => '',
					],
					'extends' => 'LabelInput',
				],

				/* Off screen controls */
				'OffscreenDisplay'            => [
					'config'  => [
						'name'       => __( 'Show sidebar', 'thrive-theme' ),
						'full-width' => true,
						'buttons'    => [
							[
								'text'    => __( 'Over content', 'thrive-theme' ),
								'default' => true,
								'value'   => 'slide',
							],
							[
								'text'  => __( 'Push content', 'thrive-theme' ),
								'value' => 'push',
							],
						],
					],
					'extends' => 'ButtonGroup',
				],
				'OffscreenDefaultState'       => [
					'config'  => [
						'name'    => __( 'Default state', 'thrive-theme' ),
						'buttons' => [
							[
								'text'    => __( 'Collapsed', 'thrive-theme' ),
								'default' => true,
								'value'   => 'collapsed',
							],
							[
								'text'  => __( 'Expanded', 'thrive-theme' ),
								'value' => 'expanded',
							],
						],
					],
					'extends' => 'ButtonGroup',
				],
				'ShowOffscreenInEditor'       => [
					'config'  => [
						'name'    => '',
						'label'   => __( 'View expanded sidebar in editor', 'thrive-theme' ),
						'default' => true,
					],
					'extends' => 'Switch',
				],
				'OffscreenOverlayColorSwitch' => [
					'config'  => [
						'name'    => '',
						'label'   => __( 'Overlay', 'thrive-theme' ),
						'default' => false,
					],
					'extends' => 'Switch',
				],
				'OffscreenOverlayColor'       => [
					'config'  => [
						'default' => '000',
						'label'   => __( 'Color', 'thrive-theme' ),
						'options' => [
							'output' => 'object',
						],
					],
					'extends' => 'ColorPicker',
				],
				'OffscreenDefaultTrigger'     => [
					'config'  => [
						'name'    => '',
						'label'   => __( 'Quick toggle icon', 'thrive-theme' ),
						'default' => true,
						'info'    => true,
					],
					'extends' => 'Switch',
				],
				'OffscreenTriggerPosition'    => [
					'config'  => [
						'name'    => __( 'Position', 'thrive-theme' ),
						'options' => [
							[
								'name'  => __( 'Top', 'thrive-theme' ),
								'value' => 'top',
							],
							[
								'name'  => __( 'Center', 'thrive-theme' ),
								'value' => 'center',
							],
							[
								'name'  => __( 'Bottom', 'thrive-theme' ),
								'value' => 'bottom',
							],
						],
						'default' => 'left',
					],
					'extends' => 'Select',
				],
				'OffscreenCloseIcon'          => [
					'config'  => [
						'name'    => '',
						'label'   => __( 'Close icon', 'thrive-theme' ),
						'default' => true,
					],
					'extends' => 'Switch',
				],
			],
		];

		return $components;
	}
}

return new Thrive_Theme_Section_Element( 'theme_section' );
