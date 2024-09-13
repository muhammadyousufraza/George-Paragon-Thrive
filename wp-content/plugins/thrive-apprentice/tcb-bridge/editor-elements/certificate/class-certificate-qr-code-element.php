<?php

namespace TVA\Architect\Certificate;

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Certificate QR Code Element class
 */
class Certificate_Qr_Code_Element extends \TCB_Element_Abstract {
	protected $_tag = 'certificate_qr_code';

	/**
	 * The name of the element
	 *
	 * @return string|null
	 */
	public function name() {
		return __( 'QR Code', 'thrive-apprentice' );
	}

	/**
	 * The icon of the element
	 *
	 * @return string
	 */
	public function icon() {
		return 'certificates-qr';
	}

	/**
	 * Category of the element that will be displayed in the sidebar
	 *
	 * @return string
	 */
	public function category() {
		return $this->get_thrive_basic_label();
	}

	/**
	 * Whether to display the element in the sidebar menu
	 *
	 * @return false
	 */
	public function hide() {
		return false;
	}

	/**
	 * Element identifier that will help us understand what we click on
	 *
	 * @return string
	 */
	public function identifier() {
		return '.tva-certificate-qr-code';
	}

	/**
	 * Components that apply only to the element
	 *
	 * @return array
	 */
	public function own_components() {
		return array(
			'certificate_qr_code' => array(
				'config' => array(
					'StaticSource'    => array(
						'config'  => array(
							'label'      => __( 'Source text', 'thrive-apprentice' ),
							'full-width' => true,
						),
						'extends' => 'LabelInput',
					),
					'ExternalFields'  => array(
						'config'  => array(
							'label'             => __( 'Content', 'thrive-apprentice' ),
							'main_dropdown'     => array(
								''             => __( 'Select a source', 'thrive-apprentice' ),
								'verification' => __( 'Certificate verification link', 'thrive-apprentice' ),
								'course'       => __( 'Course homepage', 'thrive-apprentice' ),
								'site'         => __( 'Site homepage', 'thrive-apprentice' ),
							),
							'key'               => 'image',
							'shortcode_element' => '.tve-qr-code',
						),
						'extends' => 'CustomFields',
					),
					'ForegroundColor' => array(
						'config'  => array(
							'label' => __( 'Foreground color', 'thrive-apprentice' ),
						),
						'extends' => 'ColorPicker',
					),
					'BackgroundColor' => array(
						'config'  => array(
							'label' => __( 'Background color', 'thrive-apprentice' ),
						),
						'extends' => 'ColorPicker',
					),
					'Size'            => array(
						'config'  => array(
							'default' => '200',
							'min'     => '50',
							'max'     => '400',
							'label'   => __( 'Size', 'thrive-apprentice' ),
							'um'      => array( 'px' ),
						),
						'extends' => 'Slider',
					),
				),
			),
			'typography'          => array(
				'hidden' => true,
			),
			'layout'              => array(
				'hidden' => true,
			),
			'background'          => array(
				'hidden' => true,
			),
			'borders'             => array(
				'hidden' => true,
			),
			'shadow'              => array(
				'hidden' => true,
			),
		);
	}

	/**
	 * Element info
	 *
	 * @return string|string[][]
	 */
	public function info() {
		return array(
			'instructions' => array(
				'type' => 'help',
				'url'  => 'certificate_qr',
				'link' => 'https://help.thrivethemes.com/en/articles/6685758-how-to-use-the-verification-qr-code-element',
			),
		);
	}
}
