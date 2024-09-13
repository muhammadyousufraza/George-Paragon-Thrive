<?php
/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 5/12/2017
 * Time: 8:28 AM //countdown
 */


if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class TCB_Countdown_Element
 */
class TCB_Countdown_Old_Element extends TCB_Element_Abstract {

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Countdown', 'thrive-cb' );
	}

	public function hide() {
		return true;
	}

	/**
	 * Get element alternate
	 *
	 * @return string
	 */
	public function alternate() {
		return 'scarcity ';
	}


	/**
	 * Return icon class needed for display in menu
	 *
	 * @return string
	 */
	public function icon() {
		return 'countdown';
	}

	/**
	 * Element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.thrv-countdown_timer_plain, .tve_cd_timer_plain:not(.tve_countdown_timer_evergreen)';
	}

	/**
	 * Component and control config
	 *
	 * @return array
	 */
	public function own_components() {
		$components = array(
			'countdown_old' => array(
				'config' => array(
					'CompleteText'   => array(
						'config'  => array(
							'label' => __( 'Text to show on complete', 'thrive-cb' ),
						),
						'extends' => 'Textarea',
					),
					'style'          => array(
						'config' => array(
							'label'   => __( 'Style', 'thrive-cb' ),
							'items'   => array(
								'tve_countdown_1' => array(
									'label'   => __( 'Simple', 'thrive-cb' ),
									'preview' => 'countdown_s1',
								),
								'tve_countdown_2' => array(
									'label'   => __( 'Rounded', 'thrive-cb' ),
									'preview' => 'countdown_s2',
								),
								'tve_countdown_3' => array(
									'label'   => __( 'Squared', 'thrive-cb' ),
									'preview' => 'countdown_s3',
								),
							),
							'default' => 'tve_countdown_1',
						),
					),
					'EndDate'        => array(
						'config'  => array(
							'label' => __( 'End Date', 'thrive-cb' ),
						),
						'extends' => 'DatePicker',
					),
					'ExternalFields' => [
						'config'  => [
							'key'               => 'countdown',
							'shortcode_element' => '.thrv_countdown_timer',
						],
						'extends' => 'CustomFields',
					],
					'Color'          => array(
						'css_suffix' => ' .t-caption',
						'config'     => array(
							'default'             => 'f00',
							'label'               => __( 'Color', 'thrive-cb' ),
							'important'           => true,
							'options'             => [ 'noBeforeInit' => false ],
							'style_default_color' => [
								'.tve_countdown_2 .t-digits [class*="part-"]' => [ 'color' => '' ],
								'.tve_countdown_2 .tve_t_part'                => [ 'border-color' => '' ],
								'.tve_countdown_3 .t-digits'                  => [ 'background' => '' ],
							],
						),
						'extends'    => 'ColorPicker',
					),
					'Hour'           => array(
						'config'  => array(
							'inline'    => true,
							'name'      => __( 'H', 'thrive-cb' ),
							'default'   => 10,
							'min'       => 0,
							'max'       => 23,
							'maxlength' => 2,
						),
						'extends' => 'Input',
					),
					'Minute'         => array(
						'config'  => array(
							'inline'    => true,
							'name'      => __( 'M', 'thrive-cb' ),
							'default'   => 10,
							'min'       => 0,
							'max'       => 59,
							'maxlength' => 2,
						),
						'extends' => 'Input',
					),
				),
			),
			'typography'    => [
				'hidden' => true,
			],
			'shadow'        => [
				'config' => [
					'disabled_controls' => [ 'text' ],
				],
			],
			'layout'        => [
				'disabled_controls' => [
					'Overflow',
					'ScrollStyle',
				],
			],
		);

		return apply_filters( 'tcb_countdown_controls', $components );
	}

	/**
	 * Element category that will be displayed in the sidebar
	 *
	 * @return string
	 */
	public function category() {
		return static::get_thrive_advanced_label();
	}
}
