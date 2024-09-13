<?php
/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 10/15/2018
 * Time: 3:38 PM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

class TCB_Form_Item_Element extends TCB_Element_Abstract {

	public function name() {
		return __( 'Form Item', 'thrive-cb' );
	}

	public function identifier() {
		return '.tve-form-item';
	}

	/**
	 * Hide Element From Sidebar Menu
	 *
	 * @return bool
	 */
	public function hide() {
		return true;
	}

	public function own_components() {

		return [
			'typography'       => [
				'hidden' => true,
			],
			'layout'           => [
				'disabled_controls' => [
					'Width',
					'Height',
					'Alignment',
					'Display',
					'.tve-advanced-controls',
				],
			],
			'animation'        => [
				'hidden' => true,
			],
			'responsive'       => [
				'hidden' => true,
			],
			'styles-templates' => [
				'hidden' => true,
			],
		];
	}
}

