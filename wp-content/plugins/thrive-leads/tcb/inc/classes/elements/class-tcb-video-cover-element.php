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
 * Class TCB_Video_Cover_Element
 */
class TCB_Video_Cover_Element extends TCB_Cloud_Template_Element_Abstract {

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Video cover', 'thrive-cb' );
	}

	/**
	 * @return bool
	 */
	public function hide() {
		return true;
	}

	/**
	 * Element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.tcb-video-cover';
	}

	/**
	 * @return bool
	 */
	public function is_placeholder() {
		return false;
	}

	/**
	 * Component and control config
	 *
	 * @return array
	 */
	public function own_components() {
		return [
			'video_cover'      => [
				'config' => [
					'VideoCoverPalette'  => [
						'config'  => [],
						'extends' => 'PalettesV2',
					],
					'ThumbnailType'      => [
						'config'  => [
							'name'     => __( 'Video thumbnail', 'thrive-cb' ),
							'no-space' => true,
							'options'  => [
								[
									'name'  => __( 'Static image', 'thrive-cb' ),
									'value' => 'static',
								],
								[
									'name'  => __( 'Featured image', 'thrive-cb' ),
									'value' => 'dynamic',
								],
								[
									'name'  => __( 'No image', 'thrive-cb' ),
									'value' => 'none',
								],
							],
							'default'  => 'static',
						],
						'extends' => 'Select',
					],
					'VerticalPosition'   => [
						'config'  => [
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
						],
						'extends' => 'ButtonGroup',
					],
					'PreloadThumbnail'   => [
						'config'  => [
							'name'     => '',
							'label'    => __( 'Preload thumbnail', 'thrive-cb' ),
							'no-space' => true,
							'default'  => true,
						],
						'extends' => 'Switch',
					],
					'ImageOverlaySwitch' => [
						'config'  => [
							'name'     => '',
							'label'    => __( 'Image overlay', 'thrive-cb' ),
							'no-space' => true,
							'default'  => true,
						],
						'extends' => 'Switch',
					],
					'ImageOverlay'       => [
						'config'     => [
							'default'  => '000',
							'no-space' => true,
							'label'    => __( 'Overlay color', 'thrive-cb' ),
						],
						'css_suffix' => ' .tcb-video-cover-overlay',
						'extends'    => 'ColorPicker',
					],
				],
			],
			'typography'       => [ 'hidden' => true ],
			'animation'        => [ 'hidden' => true ],
			'responsive'       => [ 'hidden' => true ],
			'styles-templates' => [ 'hidden' => true ],
			'shadow'           => [
				'config' => [
					'disabled_controls' => [ 'drop' ],
				],
			],
			'borders'          => [
				'disabled_controls' => [ 'Corners' ],
			],
			'layout'           => [
				'disabled_controls' => [
					'Width',
					'Height',
					'Alignment',
					'Display',
					'margin',
					'.tve-advanced-controls',
				],
			],
		];
	}
}
