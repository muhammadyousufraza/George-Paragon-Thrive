<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Thrive_Comment_Container_Element
 */
class Thrive_Comment_Container_Element extends Thrive_Theme_Element_Abstract {
	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Comment Container', 'thrive-theme' );
	}

	/**
	 * WordPress element identifier
	 * A Reply will always be a child.
	 *
	 * @return string
	 */
	public function identifier() {
		return '.comment-body';
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
		$components = parent::own_components();

		$components['layout']['disabled_controls'] = [
			'MaxWidth',
			'Display',
			'Alignment',
			'.tve-advanced-controls',
			'hr',
		];

		return $components;
	}
}

return new Thrive_Comment_Container_Element( 'thrive_comment_container' );
