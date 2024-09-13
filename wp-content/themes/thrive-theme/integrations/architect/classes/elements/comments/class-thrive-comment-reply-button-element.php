<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Thrive_Comment_Reply_Button_Element
 */
class Thrive_Comment_Reply_Button_Element extends Thrive_Theme_Button_Element {
	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Comment Reply Button', 'thrive-theme' );
	}

	/**
	 * WordPress element identifier
	 * A Reply will always be a child.
	 *
	 * @return string
	 */
	public function identifier() {
		return '.comment-list .reply';
	}

	/**
	 * If an element has a selector, the data-css will not be generated.
	 *
	 * @return bool
	 */
	public function has_selector() {
		return true;
	}
}

return new Thrive_Comment_Reply_Button_Element( 'thrive_comment_reply_button' );
