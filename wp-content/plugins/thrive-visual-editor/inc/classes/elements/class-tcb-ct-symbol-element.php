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
 * Class TCB_Ct_Element
 *
 * Content templates - allows inserting saved content templates into the page
 */
class TCB_Ct_Symbol_Element extends TCB_Symbol_Element_Abstract {

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Templates & Symbols', 'thrive-cb' );
	}

	/**
	 * Return icon class needed for display in menu
	 *
	 * @return string
	 */
	public function icon() {
		return 'templatesnsymbols';
	}

	/**
	 * Element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.thrv_ct_symbol';
	}

	/**
	 * HTML layout of the element for when it's dragged in the canvas
	 *
	 * @return string
	 */
	public function html() {
		return tcb_template( 'elements/' . $this->tag() . '.php', $this, true );
	}

	/**
	 * Component and control config
	 *
	 * @return array
	 */
	public function own_components() {
		return [
			'ct_symbol' => [
				'config' => [],
			],
		];
	}

	/**
	 * General components that apply to all elements
	 *
	 * @return array
	 */
	public function general_components() {
		return [
			'layout'     => [
				'order' => 100,
			],
			'responsive' => [
				'order' => 140,
			],
		];
	}

	/**
	 * Element info
	 *
	 * @return string|string[][]
	 */
	public function info() {
		return [
			'instructions' => [
				'type' => 'help',
				'url'  => 'templates_symbols',
				'link' => 'https://help.thrivethemes.com/en/articles/4425777-how-to-use-the-templates-and-symbols-element-formerly-content-template',
			],
		];
	}
}
