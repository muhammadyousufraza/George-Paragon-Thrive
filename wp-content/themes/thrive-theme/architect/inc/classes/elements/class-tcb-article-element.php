<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class TCB_Article_Element
 */
class TCB_Article_Element extends TCB_Post_List_Sub_Element_Abstract {

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Article', 'thrive-cb' );
	}

	/**
	 * WordPress element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.' . TCB_POST_WRAPPER_CLASS;
	}

	/**
	 * Hide this.
	 *
	 * @return bool
	 */
	public function hide() {
		return true;
	}

	/**
	 * This element has a selector
	 *
	 * @return bool
	 */
	public function has_selector() {
		return true;
	}

	/**
	 * Check if this element behaves like a shortcode.
	 *
	 * @return bool
	 */
	public function is_shortcode() {
		return false;
	}

	/**
	 * Whether or not this element can be edited while under :hover state
	 *
	 * @return bool
	 */
	public function has_hover_state() {
		return true;
	}

	/**
	 * @return array
	 */
	public function own_components() {
		$components = parent::own_components();

		$components['responsive']['hidden'] = true;
		$components['typography']['hidden'] = true;

		$components['animation']['disabled_controls'] = [ '.anim-popup', '.anim-link' ];

		$components['layout']['disabled_controls'] =
			[
				'margin-right',
				'margin-bottom',
				'margin-left',
				'.tve-advanced-controls',
				'MaxWidth',
				'Alignment',
				'hr',
				'Display',
			];

		return $components;
	}
}
