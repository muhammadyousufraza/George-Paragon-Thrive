<?php
require_once plugin_dir_path( __FILE__ ) . 'class-tcb-section-element.php';

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class TCB_Section_Element
 */
class TCB_Block_Element extends TCB_Section_Element {
	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Block', 'thrive-cb' );
	}

	/**
	 * Section element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.thrv-lp-block.thrv-page-section';
	}

	/**
	 * @return bool
	 */
	public function promoted() {
		return true;
	}

	/**
	 * @return bool
	 */
	public function is_placeholder() {
		return false;
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
	 * HTML layout of the element for when it's dragged in the canvas
	 *
	 * @return string
	 */
	protected function html() {
		return tcb_template( 'elements/' . $this->tag() . '.php', $this, true );
	}

	/**
	 * Component and control config
	 *
	 * @return array
	 */
	public function own_components() {
		$components = parent::own_components();
		$components = array_merge( [ 'block' => $components['section'] ], $components );

		unset( $components['section'] );
		unset( $components['shared-styles'] );

		return array_merge( $components, $this->group_component() );
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
				'url'  => 'block',
				'link' => 'https://help.thrivethemes.com/en/articles/4425843-how-to-use-the-block-element',
			],
		];
	}
}
