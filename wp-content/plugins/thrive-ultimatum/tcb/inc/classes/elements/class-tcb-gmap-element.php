<?php
/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 4/28/2017
 * Time: 4:08 PM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class TCB_Gmaps_Element
 */
class TCB_Gmap_Element extends TCB_Element_Abstract {
	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Google Map', 'thrive-cb' );
	}

	/**
	 * Get element alternate
	 *
	 * @return string
	 */
	public function alternate() {
		return 'address';
	}


	/**
	 * Return icon class needed for display in menu
	 *
	 * @return string
	 */
	public function icon() {
		return 'gmaps';
	}

	/**
	 * WordPress element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.thrv-google-map-embedded-code, .tve-flexible-container';
	}

	/**
	 * Component and control config
	 *
	 * @return array
	 */
	public function own_components() {
		return array(
			'gmap'       => array(
				'config' => array(
					'ExternalFields' => [
						'config'  => [
							'key'               => 'map',
							'shortcode_element' => 'iframe',
						],
						'extends' => 'CustomFields',
					],
					'address'        => array(
						'config'  => array(
							'label'       => __( 'Address', 'thrive-cb' ),
							'placeholder' => __( 'Insert Address', 'thrive-cb' ),
						),
						'extends' => 'LabelInput',
					),
					'zoom'           => array(
						'config'  => array(
							'default' => '10',
							'min'     => '1',
							'max'     => '20',
							'label'   => __( 'Zoom', 'thrive-cb' ),
							'um'      => '',
						),
						'extends' => 'Slider',
					),
					'fullWidth'      => array(
						'config'  => array(
							'name'  => '',
							'label' => __( 'Stretch to fit screen width', 'thrive-cb' ),
						),
						'extends' => 'Switch',
					),
					'width'          => array(
						'config'  => array(
							'default' => '0',
							'min'     => '0',
							'max'     => '2000',
							'label'   => __( 'Width', 'thrive-cb' ),
							'um'      => [ 'px', '%', 'vh', 'vw' ],
							'css'     => 'width',
						),
						'extends' => 'Slider',
					),
					'height'         => array(
						'config'  => array(
							'default' => '0',
							'min'     => '0',
							'max'     => '2000',
							'label'   => __( 'Height', 'thrive-cb' ),
							'um'      => [ 'px', 'vh' ],
							'css'     => 'height',
						),
						'extends' => 'Slider',
					),
				),
			),
			'background' => [
				'hidden' => true,
			],
			'typography' => [
				'hidden' => true,
			],
			'animation'  => [
				'hidden' => true,
			],
			'shadow'     => [
				'config' => [
					'disabled_controls' => [ 'text' ],
				],
			],
			'layout'     => [
				'disabled_controls' => [
					'.tve-advanced-controls',
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

	/**
	 * Element info
	 *
	 * @return string|string[][]
	 */
	public function info() {
		return [
			'instructions' => [
				'type' => 'help',
				'url'  => 'google_map',
				'link' => 'https://help.thrivethemes.com/en/articles/4425799-how-to-use-the-custom-html-and-google-map-elements',
			],
		];
	}
}
