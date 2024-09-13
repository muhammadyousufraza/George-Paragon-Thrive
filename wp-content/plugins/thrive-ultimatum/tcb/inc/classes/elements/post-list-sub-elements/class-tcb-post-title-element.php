<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class TCB_Post_Title_Element
 *
 * This element is permanently hidden from the sidebar, and is only kept for compatibility reasons.
 */
class TCB_Post_Title_Element extends TCB_Post_List_Sub_Element_Abstract {

	/**
	 * Hide this.
	 *
	 * @return string
	 */
	public function hide() {
		return false;
	}

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Post Title', 'thrive-cb' );
	}

	/**
	 * Return icon class needed for display in menu
	 *
	 * @return string
	 */
	public function icon() {
		return 'post-title';
	}

	/**
	 * WordPress element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.' . TCB_POST_TITLE_IDENTIFIER;
	}

	/**
	 * Return the shortcode tag of the element.
	 *
	 * @return string
	 */
	public function shortcode() {
		return 'tcb_post_title';
	}

	/**
	 * Add/disable controls.
	 *
	 * @return array
	 */
	public function own_components() {
		$components = parent::own_components();

		foreach ( $components['typography']['config'] as $control => $config ) {
			if ( in_array( $control, [ 'css_suffix', 'css_prefix' ] ) ) {
				continue;
			}
			/* make sure typography elements apply also on the link inside the title */
			$components['typography']['config'][ $control ]['css_suffix'] = [ ' a', '' ];
			$components['typography']['config'][ $control ]['css_prefix'] = '.' . TCB_POST_TITLE_IDENTIFIER;
		}

		/* add the prefix in order to make this a bit more specific, so it's stronger than the content-box Heading spacing stuff */
		$components['layout'] ['config'] ['MarginAndPadding'] ['css_prefix'] = tcb_selection_root();

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
