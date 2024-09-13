<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Thrive_Comment_Text_Element
 */
class Thrive_Comment_Text_Element extends Thrive_Theme_Element_Abstract {
	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Comment Text', 'thrive-theme' );
	}

	/**
	 * WordPress element identifier
	 * A Reply will always be a child.
	 *
	 * @return string
	 */
	public function identifier() {
		return '.comment-content';
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
	 * Hide this.
	 */
	public function hide() {
		return true;
	}

	/**
	 * Defines the configuration for the Comment Text Element Components
	 *
	 * @return array
	 */
	public function own_components() {
		$components = parent::own_components();

		$components['styles-templates'] = [ 'hidden' => true ];
		$components['responsive']       = [ 'hidden' => true ];
		$components['animation']        = [ 'hidden' => true ];
		$components['layout']           = [
			'disabled_controls' => [
				'Display',
				'Alignment',
				'Height',
				'Width',
				'.tve-advanced-controls',
				'margins',
			],
		];

		return $components;

	}
}

return new Thrive_Comment_Text_Element( 'thrive_comment_text' );
