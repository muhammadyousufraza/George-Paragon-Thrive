<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Thrive_Comment_Author_Name_Element
 */
class Thrive_Comment_Author_Name_Element extends Thrive_Theme_Element_Abstract {
	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Comment Author Name', 'thrive-theme' );
	}

	/**
	 * WordPress element identifier
	 * Parent comment will always have depth-1
	 *
	 * @return string
	 */
	public function identifier() {
		return '.comment-author .fn';
	}

	/**
	 * Component and control config
	 *
	 * @return array
	 */
	public function own_components() {
		$components                                = parent::own_components();
		$components['layout']['disabled_controls'] = [ 'MaxWidth' ];
		$components['animation']                   = [ 'hidden' => true ];
		$components['responsive']                  = [ 'hidden' => true ];

		return $components;
	}

	/**
	 * Hide this.
	 */
	public function hide() {
		return true;
	}

	/**
	 * This element has no icons
	 * @return bool
	 */
	public function has_icons() {
		return false;
	}

	/**
	 * This element has a selector
	 * @return bool
	 */
	public function has_selector() {
		return true;
	}
}

return new Thrive_Comment_Author_Name_Element( 'thrive_comment_author_name' );
