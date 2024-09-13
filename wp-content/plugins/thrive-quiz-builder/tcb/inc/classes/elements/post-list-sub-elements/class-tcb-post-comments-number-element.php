<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class TCB_Post_Comments_Number_Element
 *
 * This element is permanently hidden from the sidebar, and is only kept for compatibility reasons.
 */
class TCB_Post_Comments_Number_Element extends TCB_Post_List_Sub_Element_Abstract {

	/**
	 * Hide this.
	 *
	 * @return string
	 */
	public function hide() {
		return true;
	}

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Comments Counter', 'thrive-cb' );
	}

	/**
	 * WordPress element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.' . TCB_POST_COMMENTS_NUMBER_IDENTIFIER;
	}

	/**
	 * Return the shortcode tag of the element.
	 *
	 * @return string
	 */
	public function shortcode() {
		return 'tcb_post_comments_number';
	}

	/**
	 * Add/disable controls.
	 *
	 * @return array
	 */
	public function own_components() {
		$components = parent::own_components();

		$components['animation']['hidden'] = true;

		return $components;
	}

	/**
	 * The post title should have hover state
	 *
	 * @return bool
	 */
	public function has_hover_state() {
		return true;
	}
}
