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
 * Handles backwards-compatibility functionality for landing pages - main content area
 */
class TCB_Landing_Page_Content_Element extends TCB_Element_Abstract {

	/**
	 * Element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.tve_lp_content';
	}

	/**
	 * This is only available on landing pages
	 *
	 * @return false|string
	 */
	public function is_available() {
		return tcb_post()->is_landing_page();
	}

	public function name() {
		return __( 'Landing Page Content', 'thrive-cb' );
	}

	/**
	 * Either to display or not the element in the sidebar menu
	 *
	 * @return bool
	 */
	public function hide() {
		return true;
	}

	/**
	 * The HTML is generated from js
	 *
	 * @return string
	 */
	protected function html() {
		return '';
	}

	/**
	 * Hide all general components.
	 *
	 * @return array
	 */
	public function own_components() {
		return [
			'typography'       => [ 'hidden' => true ],
			'animation'        => [ 'hidden' => true ],
			'responsive'       => [ 'hidden' => true ],
			'styles-templates' => [ 'hidden' => true ],
			'layout'           => [
				'disabled_controls' => [
					'.tve-advanced-controls',
				],
				'config'            => [
					'Width' => [
						'important' => true,
					],
				],
			],
			'borders'          => [
				'config' => [
					'Borders' => [
						'important' => true,
					],
					'Corners' => [
						'important' => true,
					],
				],
			],
			'shadow'           => [
				'config' => [
					'important'      => true,
					'default_shadow' => 'none',
				],
			],
		];
	}
}
