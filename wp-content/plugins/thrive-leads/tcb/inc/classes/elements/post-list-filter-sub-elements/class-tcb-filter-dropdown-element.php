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
 * Class TCB_Filter_Dropdown_Element
 */
class TCB_Filter_Dropdown_Element extends TCB_Dynamic_Dropdown_Element {

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Filter Dropdown Option', 'thrive-cb' );
	}

	/**
	 * Filter Button element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.tve-dynamic-dropdown.tcb-filter-dropdown';
	}

	public function own_components() {
		$components = parent::own_components();

		$components['filter_dropdown'] = $components['dynamic_dropdown'];

		$components['filter_dropdown']['config']['DropdownPalettes'] = [
			'config'  => [],
			'extends' => 'PalettesV2',
		];

		unset( $components['dynamic_dropdown'] );

		return $components;
	}
}
