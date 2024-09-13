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
class TCB_Countdown_Element extends TCB_Cloud_Template_Element_Abstract {

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Countdown', 'thrive-cb' );
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
		return '.tve-countdown';
	}

	public function is_placeholder() {
		return false;
	}

	/**
	 * HTML layout of the element for when it's dragged in the canvas
	 *
	 * @return string
	 */
	public function html_placeholder( $title = null ) {
		if ( empty( $title ) ) {
			$title = $this->name();
		}

		return tcb_template( 'elements/element-placeholder', array(
			'icon'       => $this->icon(),
			'class'      => 'tcb-ct-placeholder',
			'title'      => $title,
			'extra_attr' => 'data-ct="countdown-0" data-tcb-elem-type="countdown" data-specific-modal="countdown"',
		), true );
	}

	/**
	 * Component and control config
	 *
	 * @return array
	 */
	public function own_components() {
		$components = array(
			'countdown'  => array(
				'config' => array(
					'CountdownPalette' => [
						'config'  => [],
						'extends' => 'PalettesV2',
					],
					'EndDate'          => array(
						'config'  => array(
							'label' => __( 'End date', 'thrive-cb' ),
						),
						'extends' => 'DatePicker',
					),
					'ExternalFields'   => [
						'config'  => [
							'key'               => 'countdown',
							'shortcode_element' => '.thrv_countdown_timer',
						],
						'extends' => 'CustomFields',
					],
					'Day'              => array(
						'config'  => array(
							'inline'    => true,
							'name'      => __( 'Days', 'thrive-cb' ),
							'default'   => 10,
							'maxlength' => 3,
						),
						'extends' => 'Input',
					),
					'Hour'             => array(
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
					'Minute'           => array(
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
					'ShowSep'          => array(
						'config'  => array(
							'name'    => '',
							'label'   => __( 'Enable tile separators', 'thrive-cb' ),
							'default' => false,
						),
						'extends' => 'Switch',
					),
					'ShowElement'      => array(
						'config'  => array(
							'name'    => '',
							'label'   => __( 'Display expired countdown', 'thrive-cb' ),
							'default' => false,
						),
						'extends' => 'Switch',
					),
					'Second'           => array(
						'config'  => array(
							'inline'    => true,
							'name'      => __( 'Seconds', 'thrive-cb' ),
							'default'   => 10,
							'maxlength' => 2,
						),
						'extends' => 'Input',
					),
					'ExpDay'           => array(
						'config'  => array(
							'inline'    => true,
							'name'      => __( 'Days', 'thrive-cb' ),
							'default'   => 10,
							'maxlength' => 3,
						),
						'extends' => 'Input',
					),
					'ExpHour'          => array(
						'config'  => array(
							'inline'    => true,
							'name'      => __( 'Hours', 'thrive-cb' ),
							'default'   => 10,
							'maxlength' => 2,
						),
						'extends' => 'Input',
					),
					'StartAgain'       => array(
						'config'  => array(
							'name'    => '',
							'label'   => __( 'Starts again after', 'thrive-cb' ),
							'default' => false,
						),
						'extends' => 'Switch',
					),
					'Size'             => array(
						'config'  => array(
							'default' => '100',
							'min'     => '20',
							'max'     => '350',
							'label'   => __( 'Tile Size', 'thrive-cb' ),
							'um'      => [ 'px' ],
							'css'     => 'max-width',
						),
						'extends' => 'Slider',
					),
				),
			),
			'typography' => [
				'hidden' => true,
			],
			'shadow'     => [
				'config' => [
					'disabled_controls' => [ 'text' ],
				],
			],
			'layout'     => [
				'disabled_controls' => [
					'Overflow',
					'ScrollStyle',
					'Display',
					'Width',
					'Height',
					'Float',
				],
			],
		);

		return array_merge(
			apply_filters( 'tcb_countdown_controls', $components ),
			$this->group_component()
		);
	}


	public function has_group_editing() {
		return array(
			'select_values' => array(
				array(
					'value'    => 'all_labels',
					'selector' => ' .tve-countdown-label',
					'name'     => __( 'Grouped countdown labels', 'thrive-cb' ),
					'singular' => __( '-- Countdown label item %s', 'thrive-cb' ),
				),
				array(
					'value'    => 'all_tiles',
					'selector' => ' .tve-countdown-tile',
					'name'     => __( 'Grouped countdown tiles', 'thrive-cb' ),
					'singular' => __( '-- Countdown tile item %s', 'thrive-cb' ),
				),
				array(
					'value'    => 'all_digits',
					'selector' => ' .tve-countdown-digit',
					'name'     => __( 'Grouped countdown digits', 'thrive-cb' ),
					'singular' => __( '-- Countdown digit item %s', 'thrive-cb' ),
				),
				array(
					'value'     => 'all_separators',
					'selector'  => ' .tve-countdown-separator',
					'name'      => __( 'Grouped countdown separators', 'thrive-cb' ),
					'singular'  => __( '-- Countdown separator item %s', 'thrive-cb' ),
					'no_unlock' => true,
				),
			),
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
				'url'  => 'countdown',
				'link' => 'https://help.thrivethemes.com/en/articles/4425793-how-to-use-the-countdown-elements',
			],
		];
	}
}
