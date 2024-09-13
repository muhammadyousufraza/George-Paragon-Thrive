<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package TCB2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

class TCB_Contact_Form_Item_Element extends TCB_Element_Abstract {

	public function name() {
		return __( 'Contact Form Item', 'thrive-cb' );
	}

	public function identifier() {
		return '.tve-cf-item';
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

