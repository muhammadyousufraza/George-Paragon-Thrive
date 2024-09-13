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
 * Class TCB_Filter_Checkbox_Element
 */
class TCB_Filter_Checkbox_Element extends TCB_Lead_Generation_Checkbox_Option_Element {

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Filter Checkbox Option', 'thrive-cb' );
	}

	/**
	 * Filter Button element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.tcb-filter-checkbox';
	}

	public function own_components() {
		$components = parent::own_components();

		$components['lead_generation_checkbox_option']['disabled_controls'] = [
			'LabelAsValue',
			'InputValue',
			'SetAsDefault',
			'CustomAnswerInput',
		];

		$components['filter_checkbox'] = $components['lead_generation_checkbox_option'];

		$components['filter_checkbox']['config']['CheckboxPalettes'] = [
			'config'  => [],
			'extends' => 'PalettesV2',
		];

		unset( $components['lead_generation_checkbox_option'] );

		return $components;
	}
}
