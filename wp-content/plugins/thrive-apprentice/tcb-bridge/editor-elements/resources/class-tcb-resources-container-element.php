<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class TCB_Resources_Container_Element extends TCB_Element_Abstract {

	protected $_tag = 'resources_container';

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Lesson resources container', 'thrive-apprentice' );
	}

	/**
	 * Section element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.tva-resource-container';
	}

	public function own_components() {

		return [
			'typography'       => [
				'hidden' => true,
			],
			'styles-templates' => [
				'hidden' => true,
			],
			'scroll'           => [
				'hidden' => true,
			],
			'responsive'       => [
				'hidden' => true,
			],
			'animation'        => [
				'hidden' => true,
			],
			'layout'           => [
				'disabled_controls' => [ 'Alignment', 'Display', '.tve-advanced-controls' ],
			],
		];
	}

	public function hide() {
		return true;
	}
}

return new TCB_Resources_Container_Element();
