<?php
/**
 * Created by PhpStorm.
 * User: ovidi
 * Date: 7/22/2017
 * Time: 6:40 PM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class TCB_Ultimatum_Widget_Element extends TCB_Cloud_Template_Element_Abstract {
	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Ultimatum Widget', 'thrive-ult');
	}

	/**
	 * Return icon class needed for display in menu
	 *
	 * @return string
	 */
	public function icon() {
		return '';
	}

	/**
	 * Element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.thrv_ult_widget';
	}

	/**
	 * Hidden element
	 *
	 * @return string
	 */
	public function hide() {
		return true;
	}

	public function is_placeholder() {
		return false;
	}

	/**
	 * Component and control config
	 *
	 * @return array
	 */
	public function own_components() {

		return array(
			'ultimatum_widget' => array(
				'config' => array(),
			),
			'typography'       => array( 'hidden' => true ),
			'animation'        => array( 'hidden' => true ),
			'styles-templates' => array( 'hidden' => true ),
			'responsive'       => array( 'hidden' => true ),
		);
	}
}
