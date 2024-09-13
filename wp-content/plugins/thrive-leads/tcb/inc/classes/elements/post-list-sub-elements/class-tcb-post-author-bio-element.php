<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class TCB_Post_Author_Bio_Element
 *
 * This element is permanently hidden from the sidebar, and is only kept for compatibility reasons.
 */
class TCB_Post_Author_Bio_Element extends TCB_Post_List_Sub_Element_Abstract {

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
		return __( 'Author Bio', 'thrive-cb' );
	}

	/**
	 * WordPress element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.' . TCB_POST_AUTHOR_BIO_IDENTIFIER;
	}

	/**
	 * Return the shortcode tag of the element.
	 *
	 * @return string
	 */
	public function shortcode() {
		return 'tcb_post_author_bio';
	}

	/**
	 * Component and control config.
	 *
	 * @return array
	 */
	public function own_components() {
		$components = parent::own_components();

		$components['shadow'] = [ 'hidden' => true ];

		return $components;
	}
}
