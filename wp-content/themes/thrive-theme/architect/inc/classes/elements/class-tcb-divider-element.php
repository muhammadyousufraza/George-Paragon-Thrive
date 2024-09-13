<?php
/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 4/20/2017
 * Time: 3:49 PM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class TCB_Divider_Element
 */
class TCB_Divider_Element extends TCB_Element_Abstract {

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Divider', 'thrive-cb' );
	}

	/**
	 * Get element alternate
	 *
	 * @return string
	 */
	public function alternate() {
		return 'line';
	}


	/**
	 * Return icon class needed for display in menu
	 *
	 * @return string
	 */
	public function icon() {
		return 'divider';
	}

	/**
	 * Element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.thrv_wrapper:has(>.tve_sep), .thrv-divider';//'.thrv-divider';
	}

	/**
	 * Component and control config
	 *
	 * @return array
	 */
	public function own_components() {
		return array(
			'divider'    => array(
				'config' => array(
					'divider_color' => array(
						'config'  => array(
							'label'   => __( 'Color', 'thrive-cb' ),
							'options' => [
								'showGlobals' => false,
							],
						),
						'extends' => 'ColorPicker',
					),
					'ToggleColorControls'        => [
						'config'  => [
							'name'    => __( 'Fill type', 'thrive-cb' ),
							'buttons' => [
								[ 'value' => 'tcb-divider-solid-color', 'text' => __( 'Solid', 'thrive-cb' ) ],
								[ 'value' => 'tcb-divider-gradient-color', 'text' => __( 'Gradient', 'thrive-cb' ) ],
							],
						],
						'extends' => 'ButtonGroup',
					],
					'DividerGradient'               => [
						'config'  => [
							'default' => '000',
							'label'   => __( 'Gradient', 'thrive-cb' ),
							'options' => [
								'output'   => 'object',
								'hasInput' => true,
							],
						],
						'extends' => 'GradientPicker',
					],
					'thickness'     => array(
						'css_prefix' => apply_filters( 'tcb_divider_prefix', '' ),
						'config'     => array(
							'default' => '5',
							'min'     => '1',
							'max'     => '100',
							'label'   => __( 'Thickness', 'thrive-cb' ),
							'um'      => [ 'px' ],
						),
						'extends'    => 'Slider',
					),
					'style'         => array(
						'config' => array(
							'label'   => __( 'Choose Divider Style', 'thrive-cb' ),
							'items'   => array(
								'tve_sep-1' => array(
									'label'            => __( 'Solid', 'thrive-cb' ),
									'background_image' => '',
								),
								'tve_sep-2' => array(
									'label'            => __( 'Dotted', 'thrive-cb' ),
									'background_image' => "data:image/svg+xml;charset=utf8,%3Csvg version='1.1' xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' x='0px' y='0px' viewBox='0 0 9 3' style='enable-background:new 0 0 9 3;' xml:space='preserve'%3E%3Ccircle fill='%thrive%' cx='4.5' cy='1.5' r='1.5'/%3E%3C/svg%3E",
								),
								'tve_sep-3' => array(
									'label'            => __( 'Dashed Slim', 'thrive-cb' ),
									'background_image' => "data:image/svg+xml;charset=utf8,%3Csvg version='1.1' xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' x='0px' y='0px' viewBox='0 0 9 3' style='enable-background:new 0 0 9 3;' xml:space='preserve'%3E%3Crect x='2' y='1' fill='%thrive%' width='5' height='1'/%3E%3C/svg%3E",
								),
								'tve_sep-4' => array(
									'label'            => __( 'Dashed Thick', 'thrive-cb' ),
									'background_image' => "data:image/svg+xml;charset=utf8,%3Csvg version='1.1' xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' x='0px' y='0px' viewBox='0 0 9 3' style='enable-background:new 0 0 9 3;' xml:space='preserve'%3E%3Crect x='1' y='0.4' fill='%thrive%' width='7' height='2.3'/%3E%3C/svg%3E",
								),
								'tve_sep-5' => array(
									'label'            => __( 'Starred', 'thrive-cb' ),
									'background_image' => "data:image/svg+xml;charset=utf8,%3Csvg version='1.1' xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' x='0px' y='0px' viewBox='0 0 50 50' enable-background='new 0 0 50 50' xml:space='preserve' preserveAspectRatio='none slice'%3E%3Cpath fill='%thrive%' d='M44.4 39.2l-15-3.4-11.3 10.4-1.4-15.3-13.3-7.5 14.1-6 3-15.1 10.1 11.6 15.2-1.8-7.8 13.2z'/%3E%3C/svg%3E",
								),
							),
							'default' => 'tve_sep1',
						),
					),
				),
			),
			'typography' => [ 'hidden' => true ],
			'borders'    => [ 'hidden' => true ],
			'animation'  => [ 'hidden' => true ],
			'background' => [ 'hidden' => true ],
			'shadow'     => [ 'hidden' => true ],
			'layout'     => [
				'disabled_controls' => [
					'.tve-advanced-controls',
					'hr',
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
				'url'  => 'divider',
				'link' => 'https://help.thrivethemes.com/en/articles/4425791-how-to-use-the-divider-and-star-rating-elements',
			],
		];
	}
}
