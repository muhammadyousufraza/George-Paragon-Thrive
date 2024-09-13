<?php
/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 7/3/2018
 * Time: 2:25 PM
 */


if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class TCB_Pricing_Table_Box_Container_Element
 */
class TCB_Pricing_Table_Box_Container_Element extends TCB_Element_Abstract {

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Box Container', 'thrive-cb' );
	}

	/**
	 * Hide the element in the sidebar menu
	 *
	 * @return bool
	 */
	public function hide() {
		return true;
	}

	/**
	 * Element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.tcb-pricing-table-box-container';
	}

	/**
	 * This is only a placeholder element
	 *
	 * @return bool
	 */
	public function is_placeholder() {
		return true;
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
	 * Components that apply only to this
	 *
	 * @return array
	 */
	public function own_components() {
		$pricing_table_box_container = [
			'pricing_table_box_container' => [
				'config' => [],
			],
			'layout'                      => [ 'disabled_controls' => [ 'Height', 'Width', 'Alignment', 'Overflow', 'ScrollStyle', 'Display', '.tve-advanced-controls', ] ],
		];

		return array_merge( $pricing_table_box_container, $this->group_component() );
	}

	/**
	 * Group Edit Properties
	 *
	 * @return array|bool
	 */
	public function has_group_editing() {

		return array(
			'select_values' => array(
				array(
					'value'    => 'all_pricing_boxes',
					'selector' => '.tcb-col > .thrv-content-box',
					'name'     => __( 'Grouped Pricing Boxes', 'thrive-cb' ),
					'singular' => __( '-- Box %s', 'thrive-cb' ),
				),
				array(
					'value'    => 'all_pricing_boxes_buttons',
					'selector' => '.tcb-col > .thrv-button, .tcb-col > .thrv-content-box > .tve-cb > .thrv-button',
					'name'     => __( 'Grouped Pricing Boxes Buttons', 'thrive-cb' ),
					'singular' => __( '-- Button %s', 'thrive-cb' ),
				),
			),
		);
	}
}
