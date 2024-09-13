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
 * Class TCB_Lightbox_Element
 *
 * Thrive Lightbox general settings
 */
class TCB_Lightbox_Element extends TCB_Element_Abstract {

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Thrive Lightbox', 'thrive-cb' );
	}

	/**
	 * Return icon class needed for display in menu
	 *
	 * @return string
	 */
	public function icon() {
		return 'none';
	}

	/**
	 * Element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.tve_p_lb_content';
	}

	/**
	 * Not visible in the side menu ( elements )
	 *
	 * @return bool
	 */
	public function hide() {
		return true;
	}

	/**
	 * @return bool
	 */
	public function is_available() {
		return tcb_post()->is_lightbox();
	}

	/**
	 * Component and control config
	 *
	 * @return array
	 */
	public function own_components() {

		return array(
			'lightbox'         => array(
				'config' => array(
					'Switch'       => array(
						'to'     => '> .tve_p_lb_close',
						'config' => array(
							'label' => __( 'Show "close" icon', 'thrive-cb' ),
						),
					),
					'CloseColor'   => array(
						'to'      => '> .tve_p_lb_close',
						'config'  => array(
							'label' => __( 'Icon color', 'thrive-cb' ),
						),
						'extends' => 'ColorPicker',
					),
					'BorderColor'  => array(
						'to'      => '> .tve_p_lb_close',
						'config'  => array(
							'label' => __( 'Icon border', 'thrive-cb' ),
						),
						'extends' => 'ColorPicker',
					),
					'IconBg'       => array(
						'to'      => '> .tve_p_lb_close',
						'config'  => array(
							'label' => __( 'Icon background', 'thrive-cb' ),
						),
						'extends' => 'ColorPicker',
					),
					'OverlayColor' => array(
						'to'      => 'main::.tve_p_lb_overlay',
						'config'  => array(
							'label' => __( 'Overlay color', 'thrive-cb' ),
						),
						'extends' => 'ColorPicker',
					),
				),
			),
			'borders'          => [
				'config' => [
					'Borders' => [
						'important' => true,
					],
					'Corners' => [
						'important' => true,
					],
				],
			],
			'shadow'           => [
				'config' => [
					'important' => true,
				],
			],
			'animation'        => [
				'hidden' => true,
			],
			'typography'       => [
				'hidden' => true,
			],
			'responsive'       => [
				'hidden' => true,
			],
			'styles-templates' => [
				'hidden' => true,
			],
			'layout'           => [
				'config'            => [
					'Width' => [
						'important' => true,
					],
				],
				'disabled_controls' => [
					'Alignment',
					'.tve-advanced-controls',
				],
			],
		);
	}
}
