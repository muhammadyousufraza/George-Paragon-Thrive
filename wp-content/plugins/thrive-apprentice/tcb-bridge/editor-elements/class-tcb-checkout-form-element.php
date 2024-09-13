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
 * Class TCB_Pricing_Table_Box_Container_Element
 */
class TCB_Checkout_Form_Element extends TCB_Element_Abstract {

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Checkout Form', 'thrive-apprentice' );
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
		return '.tcb-checkout-form';
	}

	/**
	 * Element category that will be displayed in the sidebar
	 *
	 * @return string
	 */
	public function category() {
		return self::get_thrive_advanced_label();
	}

	/**
	 * Components that apply only to this
	 *
	 * @return array
	 */
	public function own_components() {
		return array(
			'checkout_form' => array(
				'config' => array(
					'FieldsControl' => array(
						'config' => array(
							'sortable'      => true,
							'settings_icon' => 'edit',
						),
					),
				),
			),
		);
	}
}
