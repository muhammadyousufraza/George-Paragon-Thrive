<?php
/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 6/27/2018
 * Time: 1:57 PM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class TCB_Pricing_Table_Element
 *
 * Element Class
 */
class TCB_Pricing_Table_Element extends TCB_Cloud_Template_Element_Abstract {

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Pricing Table', 'thrive-cb' );
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
	 * Element Icon
	 *
	 * @return string
	 */
	public function icon() {
		return 'pricing_box';
	}

	/**
	 * When element is selected in editor this identifier
	 * establishes element _type
	 *
	 * @return string
	 * @see TVE.main.element_selected() TVE._type()
	 *
	 */
	public function identifier() {

		return '.thrv-pricing-table';
	}

	/**
	 * This element is not a placeholder
	 *
	 * @return bool|true
	 */
	public function is_placeholder() {
		return false;
	}

	/**
	 * Components that apply only to this
	 *
	 * @return array
	 */
	public function own_components() {
		return array(
			'pricing_table' => [
				'config' => [
					'PricingPalette' => [
						'config'  => [],
						'extends' => 'PalettesV2',
					],
					'PriceInstances' => [
						'config' => [
							'sortable' => true,
						],
					],
				],
			],
			'typography'    => [ 'hidden' => true ],
			'animation'     => array(
				'disabled_controls' => array(
					'.btn-inline:not(.anim-animation)',
				),
			),
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
				'url'  => 'pricing_table',
				'link' => 'https://help.thrivethemes.com/en/articles/4425836-how-to-use-the-pricing-table-element',
			],
		];
	}
}
