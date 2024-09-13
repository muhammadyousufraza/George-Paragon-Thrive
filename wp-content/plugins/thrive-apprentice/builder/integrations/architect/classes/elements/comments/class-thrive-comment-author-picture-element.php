<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Thrive_Comment_Author_Picture_Element
 */
class Thrive_Comment_Author_Picture_Element extends Thrive_Theme_Element_Abstract {
	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Comment Author Picture', 'thrive-theme' );
	}

	/**
	 * WordPress element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '#comments .avatar';
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

	/**
	 * Component and control config
	 *
	 * @return array
	 */
	public function own_components() {
		return [
			'animation'        => [ 'hidden' => true ],
			'styles-templates' => [ 'hidden' => true ],
			'shadow'           => [ 'hidden' => true ],
			'typography'       => [ 'hidden' => true ],
			'background'       => [ 'hidden' => true ],
			'responsive'       => [ 'hidden' => true ],
			'layout'           => [ 'disabled_controls' => [ 'Alignment' ] ],
		];
	}
}

return new Thrive_Comment_Author_Picture_Element( 'thrive_comment_author_picture' );
