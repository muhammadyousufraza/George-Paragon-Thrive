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
 * Class TCB_Symbol_Element
 */
class TCB_Symbol_Element extends TCB_Symbol_Element_Abstract {

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Thrive Symbol', 'thrive-cb' );
	}

	/**
	 * The symbol doesn't have cloud templates, only the headers and footers for the moment
	 *
	 * @return bool
	 */
	public function has_cloud_templates() {
		return false;
	}

	/**
	 * Return icon class needed for display in menu
	 *
	 * @return string
	 */
	public function icon() {
		return 'post_grid';
	}

	/**
	 * Element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.thrv_symbol ';
	}

	/**
	 * Whether or not this element is only a placeholder ( it has no menu, it's not selectable etc )
	 * e.g. Content Templates
	 *
	 * @return bool
	 */
	public function is_placeholder() {
		return false;
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
			'symbol' => [
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
