<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Thrive_Comments_Form_Item_Element
 */
class Thrive_Comments_Form_Item_Element extends Thrive_Theme_Element_Abstract {
	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Comments Form Item', 'thrive-theme' );
	}

	/**
	 * WordPress element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.comment-form-item';
	}

	/**
	 * Hide this.
	 */
	public function hide() {
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
		];
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

return new Thrive_Comments_Form_Item_Element( 'thrive_comment_form_item' );
