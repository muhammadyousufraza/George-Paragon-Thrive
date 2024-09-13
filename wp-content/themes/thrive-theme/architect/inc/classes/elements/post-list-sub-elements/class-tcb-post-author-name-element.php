<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class TCB_Post_Author_Name_Element
 *
 * This element is permanently hidden from the sidebar, and is only kept for compatibility reasons.
 */
class TCB_Post_Author_Name_Element extends TCB_Post_List_Sub_Element_Abstract {

	/**
	 * Hide this.
	 *
	 * @return string
	 */
	public function hide() {
		return false;
	}

	/**
	 * Return icon class needed for display in menu
	 *
	 * @return string
	 */
	public function icon() {
		return 'author-name';
	}

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Author Name', 'thrive-cb' );
	}

	/**
	 * WordPress element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.tcb-post-author';
	}

	/**
	 * Return the shortcode tag of the element.
	 *
	 * @return string
	 */
	public function shortcode() {
		return 'tcb_post_author_name';
	}

	/**
	 * Component and control config.
	 *
	 * @return array
	 */
	public function own_components() {
		$prefix_config = tcb_selection_root();
		$components    = parent::own_components();

		foreach ( $components['typography']['config'] as $control => $config ) {
			if ( in_array( $control, [ 'css_suffix', 'css_prefix' ] ) ) {
				continue;
			}
			/* make sure typography elements also apply on the link inside the tag */
			$components['typography']['config'][ $control ]['css_suffix'] = [ ' a', '' ];
		}

		$components['typography']['config']['FontColor']['css_prefix'] = $prefix_config . ' ';
		$components['typography']['config']['FontSize']['css_prefix']  = $prefix_config . ' ';

		return $components;
	}
}
