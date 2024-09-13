<?php
/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 10/16/2018
 * Time: 9:06 AM
 */


if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class TCB_Login_Form_Element
 */
class TCB_Registration_Form_Element extends TCB_Lead_Generation_Element {

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return esc_html__( 'Registration Form', 'thrive-cb' );
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
		return '.tcb-registration-form';
	}

	/**
	 * Components that apply only to this
	 *
	 * @return array
	 */
	public function own_components() {
		$components                      = parent::own_components();
		$components['registration_form'] = $components['lead_generation'];
		unset( $components['lead_generation'] );

		return $components;
	}

	/**
	 * Group Edit Properties
	 *
	 * @return array|bool
	 */
	public function has_group_editing() {
		return false;
	}
}
