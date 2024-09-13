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
 * Class TCB_Filter_Radio_Element
 */
class TCB_Filter_Radio_Element extends TCB_Lead_Generation_Radio_Option_Element {

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Filter Radio Option', 'thrive-cb' );
	}

	/**
	 * Filter Radio element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.tcb-filter-radio';
	}

	public function own_components() {
		$components = parent::own_components();

		$components['lead_generation_radio_option']['disabled_controls'] = [
			'InputValue',
			'SetAsDefault',
			'CustomAnswerInput',
		];

		$components['filter_radio'] = $components['lead_generation_radio_option'];

		$components['filter_radio']['config']['RadioPalettes'] = [
			'config'  => [],
			'extends' => 'PalettesV2',
		];

		unset( $components['lead_generation_radio_option'] );

		return $components;
	}
}
