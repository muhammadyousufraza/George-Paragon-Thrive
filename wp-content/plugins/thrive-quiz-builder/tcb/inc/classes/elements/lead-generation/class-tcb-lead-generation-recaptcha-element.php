<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package TCB2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

class TCB_Lead_Generation_Recaptcha_Element extends TCB_Element_Abstract {

	public function name() {
		return __( 'Form reCaptcha', 'thrive-cb' );
	}

	public function identifier() {
		return '.tve-captcha-container';
	}

	public function hide() {
		return true;
	}

	public function own_components() {

		$components = array(
			'lead_generation_recaptcha' => array(
				'config' => array(
					'CaptchaTheme' => array(
						'config'  => array(
							'name'    => __( 'Theme', 'thrive-cb' ),
							'options' => array(
								array(
									'value' => 'light',
									'name'  => __( 'Light', 'thrive-cb' ),
								),
								array(
									'value' => 'dark',
									'name'  => __( 'Dark', 'thrive-cb' ),
								),
							),
						),
						'extends' => 'Select',
					),
					'CaptchaType'  => array(
						'config'  => array(
							'name'    => __( 'Type', 'thrive-cb' ),
							'options' => array(
								array(
									'value' => 'image',
									'name'  => __( 'Image', 'thrive-cb' ),
								),
								array(
									'value' => 'audio',
									'name'  => __( 'Audio', 'thrive-cb' ),
								),
							),
						),
						'extends' => 'Select',
					),
					'CaptchaSize'  => array(
						'config'  => array(
							'name'    => __( 'Size', 'thrive-cb' ),
							'options' => array(
								array(
									'value' => 'normal',
									'name'  => __( 'Normal', 'thrive-cb' ),
								),
								array(
									'value' => 'compact',
									'name'  => __( 'Compact', 'thrive-cb' ),
								),
							),
						),
						'extends' => 'Select',
					),
				),
			),
			'layout'                    => [
				'disabled_controls' => [
					'Width',
					'Height',
					'padding',
					'Display',
					'.tve-advanced-controls',
				],
				'config'            => [
					'Alignment' => [
						'important' => true,
					],
				],
			],
			'animation'                 => [
				'hidden' => true,
			],
			'background'                => [
				'hidden' => true,
			],
			'responsive'                => [
				'hidden' => true,
			],
			'styles-templates'          => [ 'hidden' => true ],
			'typography'                => [ 'hidden' => true ],
		);

		return $components;
	}
}
