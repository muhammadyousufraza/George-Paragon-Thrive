<?php
/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 7/31/2017
 * Time: 11:51 AM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class TCB_TQB_Page_Element extends TCB_Element_Abstract {
	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Thrive Quiz Builder Page', 'thrive-quiz-builder' );
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
		return '.tve-tqb-page-type';
	}

	/**
	 * Hidden element
	 *
	 * @return string
	 */
	public function hide() {
		return true;
	}

	/**
	 * Component and control config
	 *
	 * @return array
	 */
	public function own_components() {
		return array(
			'tqb_page'         => array(
				'config' => array(),
			),
			'layout'           => array(
				'disabled_controls' => array( 'MaxWidth', 'Alignment', '.tve-advanced-controls', 'hr' ),
			),
			'borders'          => array(
				'config' => array(
					'Corners' => array(
						'important' => true,
					),
				),
			),
			'typography'       => array( 'hidden' => true ),
			'animation'        => array( 'hidden' => true ),
			'styles-templates' => array( 'hidden' => true ),
			'responsive'       => array( 'hidden' => true ),
		);
	}
}
