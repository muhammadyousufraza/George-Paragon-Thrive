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
 * Class TCB_Button_Element
 */
class TCB_Submit_Button_Element extends TCB_Button_Element {

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Submit Button', 'thrive-cb' );
	}

	/**
	 * Button element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.tve-form-button';
	}

	public function hide() {
		return true;
	}

	/**
	 * Component and control config
	 *
	 * @return array
	 */
	public function own_components() {

		$parent_components = parent::own_components();

		$parent_components['button']['disabled_controls']                     = [ '.tcb-button-link-container' ];
		$parent_components['button']['config']['ButtonPalettes']['important'] = apply_filters( 'tcb_lg_color_inputs_important', true );

		$parent_components['submit_button'] = $parent_components['button'];
		unset( $parent_components['button'] );
		unset( $parent_components['scroll'] );

		$parent_components['animation']  = [
			'hidden' => true,
		];
		$parent_components['responsive'] = [
			'hidden' => true,
		];

		return array_merge( $parent_components, $this->shared_styles_component() );
	}
}
