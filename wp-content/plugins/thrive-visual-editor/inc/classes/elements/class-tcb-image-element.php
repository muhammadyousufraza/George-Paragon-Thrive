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
 * Class TCB_Image_Element
 */
class TCB_Image_Element extends TCB_Element_Abstract {

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Image', 'thrive-cb' );
	}

	/**
	 * Get element alternate
	 *
	 * @return string
	 */
	public function alternate() {
		return 'media';
	}


	/**
	 * Return icon class needed for display in menu
	 *
	 * @return string
	 */
	public function icon() {
		return 'image';
	}

	/**
	 * Text element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return 'div.tve_image_caption';
	}

	/**
	 * Component and control config
	 *
	 * @return array
	 */
	public function own_components() {
		return array(
			'image'         => array(
				'config' => array(
					'StyleChange'    => array(
						'config' => array(
							'label' => __( 'Image style', 'thrive-cb' ),
						),
					),
					'ImagePicker'    => array(
						'config' => array(
							'label' => __( 'Replace image', 'thrive-cb' ),
						),
					),
					'ExternalFields' => array(
						'config'  => array(
							'main_dropdown'     => array(
								''         => __( 'Select a source', 'thrive-cb' ),
								'featured' => __( 'Featured image', 'thrive-cb' ),
								'author'   => __( 'Author image', 'thrive-cb' ),
								'user'     => __( 'User image', 'thrive-cb' ),
								'custom'   => __( 'Custom fields', 'thrive-cb' ),
							),
							'key'               => 'image',
							'shortcode_element' => 'img.tve_image',
						),
						'extends' => 'CustomFields',
					),
					'ImageSize'      => array(
						'config'  => array(
							'default'  => 'auto',
							'min'      => '30',
							'forceMin' => '5',
							'max'      => '1024',
							'label'    => __( 'Size', 'thrive-cb' ),
							'um'       => [ 'px', '%' ],
							'css'      => 'width',
						),
						'extends' => 'Slider',
					),
					'ImageHeight'    => array(
						'config'  => array(
							'default' => 'auto',
							'min'     => '5',
							'max'     => '300',
							'label'   => __( 'Height', 'thrive-cb' ),
							'um'      => [ '' ],
							'css'     => 'height',
						),
						'extends' => 'Slider',
					),
					'StylePicker'    => array(
						'config' => array(
							'label'   => __( 'Choose image style', 'thrive-cb' ),
							'items'   => array(
								'no_style'                  => __( 'No style', 'thrive-cb' ),
								'img_style_dark_frame'      => __( 'Dark frame', 'thrive-cb' ),
								'img_style_framed'          => __( 'Framed', 'thrive-cb' ),
								'img_style_lifted_style1'   => __( 'Lifted style 1', 'thrive-cb' ),
								'img_style_lifted_style2'   => __( 'Lifted style 2', 'thrive-cb' ),
								'img_style_polaroid'        => __( 'Polaroid', 'thrive-cb' ),
								'img_style_rounded_corners' => __( 'Rounded corners', 'thrive-cb' ),
								'img_style_circle'          => __( 'Circle', 'thrive-cb' ),
								'img_style_caption_overlay' => __( 'Caption overlay', 'thrive-cb' ),
							),
							'default' => 'no_style',
						),
					),
					'ImageTitle'     => array(
						'config'  => array(
							'label' => __( 'Title', 'thrive-cb' ),
						),
						'extends' => 'LabelInput',
					),
					'ImageAltText'   => array(
						'config'  => array(
							'label' => __( 'Alt text', 'thrive-cb' ),
						),
						'extends' => 'LabelInput',
					),
					'ImageCaption'   => array(
						'config'  => array(
							'name'    => '',
							'label'   => __( 'Add caption text', 'thrive-cb' ),
							'default' => false,
						),
						'extends' => 'Switch',
					),
					'ImageLoading'   => array(
						'config'  => array(
							'name'    => '',
							'label'   => __( 'Lazy-load image', 'thrive-cb' ),
							'default' => true,
						),
						'extends' => 'Switch',
					),
					'ImageFullSize'  => array(
						'config'  => array(
							'name'    => '',
							'label'   => __( 'Open full size image on click', 'thrive-cb' ),
							'default' => false,
						),
						'extends' => 'Checkbox',
					),
				),
			),
			'background'    => [ 'hidden' => true ],
			'image-effects' => array(
				'config' => array(
					'css_suffix'         => ' img',
					'ImageGreyscale'     => array(
						'config'  => array(
							'default' => '0',
							'min'     => '0',
							'max'     => '100',
							'label'   => __( 'Greyscale', 'thrive-cb' ),
							'um'      => [ '' ],
							'css'     => 'filter',
						),
						'extends' => 'Slider',
					),
					'ImageOpacity'       => array(
						'config'  => array(
							'default' => '100',
							'min'     => '1',
							'max'     => '100',
							'label'   => __( 'Opacity', 'thrive-cb' ),
							'um'      => [ '' ],
							'css'     => 'opacity',
						),
						'extends' => 'Slider',
					),
					'ImageBlur'          => array(
						'config'  => array(
							'default' => '0',
							'min'     => '0',
							'max'     => '20',
							'label'   => __( 'Blur', 'thrive-cb' ),
							'um'      => [ 'px' ],
							'css'     => 'filter',
						),
						'extends' => 'Slider',
					),
					'ImageBrightness'    => array(
						'config'  => array(
							'default' => '100',
							'min'     => '0',
							'max'     => '300',
							'label'   => __( 'Brightness', 'thrive-cb' ),
							'um'      => [ '' ],
							'css'     => 'filter',
						),
						'extends' => 'Slider',
					),
					'ImageContrast'      => array(
						'config'  => array(
							'default' => '100',
							'min'     => '0',
							'max'     => '300',
							'label'   => __( 'Contrast', 'thrive-cb' ),
							'um'      => [ '' ],
							'css'     => 'filter',
						),
						'extends' => 'Slider',
					),
					'ImageSepia'         => array(
						'config'  => array(
							'default' => '0',
							'min'     => '0',
							'max'     => '100',
							'label'   => __( 'Sepia', 'thrive-cb' ),
							'um'      => [ '' ],
							'css'     => 'filter',
						),
						'extends' => 'Slider',
					),
					'ImageInvert'        => array(
						'config'  => array(
							'default' => '0',
							'min'     => '0',
							'max'     => '100',
							'label'   => __( 'Invert', 'thrive-cb' ),
							'um'      => [ '' ],
							'css'     => 'filter',
						),
						'extends' => 'Slider',
					),
					'ImageSaturate'      => array(
						'config'  => array(
							'default' => '100',
							'min'     => '0',
							'max'     => '300',
							'label'   => __( 'Saturate', 'thrive-cb' ),
							'um'      => [ '' ],
							'css'     => 'filter',
						),
						'extends' => 'Slider',
					),
					'ImageHueRotate'     => array(
						'config'  => array(
							'default' => '0',
							'min'     => '0',
							'max'     => '359',
							'label'   => __( 'Hue rotate', 'thrive-cb' ),
							'um'      => [ 'deg' ],
							'css'     => 'filter',
						),
						'extends' => 'Knob',
					),
					'ImageOverlaySwitch' => array(
						'strategy' => 'element',
						'config'   => array(
							'name'    => '',
							'label'   => __( 'Image overlay', 'thrive-cb' ),
							'default' => true,
						),
						'extends'  => 'Switch',
					),
					'ImageOverlay'       => array(
						'config'     => array(
							'default' => '000',
							'label'   => __( 'Overlay color', 'thrive-cb' ),
						),
						'css_suffix' => ' .tve-image-overlay',
						'extends'    => 'ColorPicker',
					),
				),
			),
			'typography'    => [
				'hidden' => true,
			],
			'animation'     => [
				'config' => [
					'to' => 'img',
				],
			],
			'layout'        => [
				'disabled_controls' => [
					'Width',
					'Height',
					'Overflow',
					'ScrollStyle',
				],
			],
			'shadow'        => [
				'config' => [
					'disabled_controls' => [ 'inner', 'text' ],
				],
			],
			'scroll'        => [
				'hidden' => false,
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
				'url'  => 'image_element',
				'link' => 'https://help.thrivethemes.com/en/articles/4425765-how-to-use-the-image-element',
			],
		];
	}
}
