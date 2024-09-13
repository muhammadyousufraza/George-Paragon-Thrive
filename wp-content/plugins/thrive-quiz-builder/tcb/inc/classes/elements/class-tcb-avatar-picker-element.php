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
 * Class TCB_Avatar_Picker_Element
 */
class TCB_Avatar_Picker_Element extends TCB_Element_Abstract {

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Avatar Picker', 'thrive-cb' );
	}

	/**
	 * Return icon class needed for display in menu
	 *
	 * @return string
	 */
	public function icon() {
		return 'text';
	}

	public function hide() {
		return true;
	}

	/**
	 * Element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.tve-avatar-picker-element';
	}

	/**
	 * Element category that will be displayed in the sidebar
	 *
	 * @return string
	 */
	public function category() {
		return static::get_thrive_integrations_label();
	}

	public function has_hover_state() {
		return true;
	}

	/**
	 * Component and control config
	 *
	 * @return array
	 */
	public function own_components() {
		$avatar_image_selector = ' .tve-avatar-picker-image';

		$has_google_connection   = ! empty( tvd_get_google_api_key() );
		$has_facebook_connection = ! empty( tvd_get_facebook_app_id() );

		return [
			'avatar_picker'    => [
				'config' => [
					'GoogleApi'      => [
						'config'  => [
							'name'       => '',
							'label'      => __( 'Enable Google', 'thrive-cb' ),
							'default'    => false,
							'info'       => ! $has_google_connection,
							'info_hover' => ! $has_google_connection,
						],
						'extends' => 'Switch',
					],
					'FacebookApi'    => [
						'config'  => [
							'name'       => '',
							'label'      => __( 'Enable Facebook', 'thrive-cb' ),
							'default'    => false,
							'info'       => ! $has_facebook_connection,
							'info_hover' => ! $has_facebook_connection,
						],
						'extends' => 'Switch',
					],
					'Gravatar'       => [
						'config'  => [
							'name'    => '',
							'label'   => __( 'Enable Gravatar', 'thrive-cb' ),
							'default' => true,
						],
						'extends' => 'Switch',
					],
					'CustomUrl'      => [
						'config'  => [
							'name'    => '',
							'label'   => __( 'Custom url', 'thrive-cb' ),
							'default' => false,
						],
						'extends' => 'Switch',
					],
					'ImagePicker'    => [
						'config' => [
							'label' => __( 'Default avatar', 'thrive-cb' ),
						],
					],
					'ImageSize'      => [
						'config' => [
							'default' => '240',
							'min'     => '50',
							'max'     => '1024',
							'label'   => __( 'Image size', 'thrive-cb' ),
							'um'      => [ 'px' ],
						],
					],
					'ButtonType'     => [
						'config'  => [
							'name'    => __( 'Edit button type', 'thrive-cb' ),
							'options' => [
//								'button'  => __( 'Button', 'thrive-cb' ),
								'icon'    => __( 'Icon', 'thrive-cb' ),
								'overlay' => __( 'Overlay', 'thrive-cb' ),
							],
						],
						'extends' => 'Select',
					],
					'ButtonPosition' => [
						'config'  => [
							'name'    => __( 'Button position', 'thrive-cb' ),
							'options' => [
								'top'    => __( 'Top', 'thrive-cb' ),
								'bottom' => __( 'Bottom', 'thrive-cb' ),
							],
						],
						'extends' => 'Select',
					],
					'IconPosition'   => [
						'config'  => [
							'name'    => __( 'Icon position', 'thrive-cb' ),
							'options' => [
								'top-left'  => __( 'Left', 'thrive-cb' ),
								'top-right' => __( 'Right', 'thrive-cb' ),
							],
						],
						'extends' => 'Select',
					],
				],
			],
			'typography'       => [
				'hidden' => true,
			],
			'shadow'           => [
				'config' => [
					'disabled_controls' => [ 'text' ],
					'default_shadow'    => 'none',
					'css_suffix'        => $avatar_image_selector,
				],
			],
			'background'       => [
				'hidden' => true,
			],
			'styles-templates' => [
				'hidden' => true,
			],
			'responsive'       => [
				'hidden' => true,
			],
			'animation'        => [
				'hidden' => true,
			],
			'layout'           => [
				'disabled_controls' => [ '[data-label="Width"]', '[data-label="Height"]', '[data-view="Display"]', '.tve-advanced-controls' ],
			],
			'borders'          => [
				'config' => [
					'Borders' => [
						'css_suffix' => $avatar_image_selector,
					],
					'Corners' => [
						'css_suffix' => $avatar_image_selector,
					],
				],
			],
		];
	}
}
