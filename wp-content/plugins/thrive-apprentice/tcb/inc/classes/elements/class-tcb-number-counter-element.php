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
 * Class TCB_NumberCounter_Element
 */
class TCB_Number_Counter_Element extends TCB_Element_Abstract {

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Number Counter', 'thrive-cb' );
	}

	/**
	 * Get element alternate
	 *
	 * @return string
	 */
	public function alternate() {
		return 'number, counter, fill';
	}

	/**
	 * Return icon class needed for display in menu
	 *
	 * @return string
	 */
	public function icon() {
		return 'number_counter_icon';
	}

	/**
	 * Section element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.tve-number-counter';
	}

	/**
	 * Component and control config
	 *
	 * @return array
	 */
	public function own_components() {
		$components['number_counter'] = [
			'config' => [
				'StartingValue'    => [
					'config'  => [
						'name'    => __( 'Starting value', 'thrive-cb' ),
						'default' => 0,
					],
					'extends' => 'Input',
				],
				'EndValue'         => [
					'config'  => [
						'name'    => __( 'End value', 'thrive-cb' ),
						'default' => 1,
					],
					'extends' => 'Input',
				],
				'DecimalPrecision' => [
					'config'  => [
						'label'   => __( 'Decimal precision', 'thrive-cb' ),
						'default' => '0',
						'min'     => '0',
						'max'     => '3',
						'um'      => [ '' ],
					],
					'extends' => 'Slider',
				],
				'DecimalCharacter' => [
					'config'  => [
						'name'    => __( 'Decimal character', 'thrive-cb' ),
						'options' => [
							'.' => 'Point',
							',' => 'Comma',
						],
					],
					'extends' => 'Select',
				],
				'ThousandsDivider' => [
					'config'  => [
						'name'    => __( 'Thousands divider', 'thrive-cb' ),
						'options' => [
							''  => 'None',
							',' => 'Comma',
							'.' => 'Point',
							' ' => 'Space',
						],
					],
					'extends' => 'Select',
				],
				'Prefix'           => [
					'extends' => 'LabelInput',
				],
				'Suffix'           => [
					'extends' => 'LabelInput',
				],
				'ShowLabel'        => [
					'config'  => [
						'name'    => '',
						'label'   => __( 'Show label', 'thrive-cb' ),
						'default' => false,
					],
					'extends' => 'Switch',
				],
				'LabelPosition'    => [
					'config'  => [
						'name'    => __( 'Position', 'thrive-cb' ),
						'options' => [
							[
								'value' => 'tcb-label-top',
								'name'  => __( 'Top', 'thrive-cb' ),
							],
							[
								'value' => 'tcb-label-bottom',
								'name'  => __( 'Bottom', 'thrive-cb' ),
							],
							[
								'value' => 'tcb-label-both',
								'name'  => __( 'Both', 'thrive-cb' ),
							],
						],
					],
					'extends' => 'Select',
				],
				'Size'             => [
					'config'  => [
						'default' => '25',
						'min'     => '10',
						'max'     => '100',
						'um'      => [ 'px' ],
						'label'   => __( 'Size', 'thrive-cb' ),
					],
					'extends' => 'Slider',
				],
				'Speed'            => [
					'config'  => [
						'name'    => __( 'Speed', 'thrive-cb' ),
						'options' => [
							[
								'value' => '4007',
								'name'  => __( 'Slow', 'thrive-cb' ),
							],
							[
								'value' => '2507',
								'name'  => __( 'Default', 'thrive-cb' ),
							],
							[
								'value' => '1007',
								'name'  => __( 'Fast', 'thrive-cb' ),
							],
							[
								'value' => '2000',
								'name'  => __( 'Custom', 'thrive-cb' ),
							],
						],
					],
					'extends' => 'Select',
				],
				'CustomSpeed'      => [
					'config'  => [
						'name'  => __( 'Custom speed(ms)', 'thrive-cb' ),
						'value' => '2000',
					],
					'extends' => 'Input',
				],
			],
		];

		$general_components = $this->general_components();

		$components['typography'] = $general_components['typography'];

		/* disable this, since we already have a Size control in the Main Options */
		$components['typography']['disabled_controls'] = [ 'FontSize', '[data-value="tcb-typography-font-size"]' ];

		foreach ( $components['typography']['config'] as $control => $config ) {
			if ( in_array( $control, [ 'css_suffix', 'css_prefix' ] ) ) {
				continue;
			}

			$components['typography']['config'][ $control ]['css_suffix'] = [ ' .thrv-inline-text' ];
		}

		$components['typography']['config']['FontSize']['config'] ['min'] = '10';

		/* the text align should be flex-based */
		$components['typography']['config']['TextAlign'] = array_merge(
			$components['typography']['config']['TextAlign'],
			[
				'property'     => 'text-align',
				'property_val' => [
					'left'    => 'left',
					'center'  => 'center',
					'right'   => 'right',
					'justify' => 'justify',
				],
			] );

		$components['typography']['config']['TextAlign']['css_suffix'] = [ ' .thrv-inline-text', ' .tve-number-wrapper' ];

		return $components;
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
				'url'  => 'number_counter',
				'link' => 'https://help.thrivethemes.com/en/articles/5579404-how-to-use-the-number-counter-element',
			],
		];
	}
}
