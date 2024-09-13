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
class TCB_Login_Form_Element extends TCB_Element_Abstract {

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return esc_html__( 'Login Form', 'thrive-cb' );
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
		return '.tcb-login-form';
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
		return array(
			'login_form' => array(
				'config' => array(
					'FieldsControl'   => [
						'config' => [
							'sortable'      => false,
							'settings_icon' => 'edit',
						],
					],
					'AddRemoveLabels' => array(
						'config'  => array(
							'name'    => '',
							'label'   => __( 'Show labels', 'thrive-cb' ),
							'default' => true,
						),
						'extends' => 'Switch',
					),
					'RememberMe'      => array(
						'config'     => array(
							'name'    => '',
							'label'   => __( 'Remember me', 'thrive-cb' ),
							'default' => true,
						),
						'css_suffix' => ' .tcb-remember-me-item',
						'css_prefix' => '',
						'extends'    => 'Switch',
					),
				),
			),
		);
	}
}
