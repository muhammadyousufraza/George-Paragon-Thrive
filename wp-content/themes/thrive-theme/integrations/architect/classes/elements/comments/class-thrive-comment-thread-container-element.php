<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Thrive_Comment_Thread_Container_Element
 */
class Thrive_Comment_Thread_Container_Element extends Thrive_Theme_Element_Abstract {
	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Comment Thread Container', 'thrive-theme' );
	}

	/**
	 * WordPress element identifier
	 * Parent comment will always have depth-1
	 *
	 * @return string
	 */
	public function identifier() {
		return '.comment-list > .comment';
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
			'layout'           => [
				'disabled_controls' => [
					'MaxWidth',
					'Display',
					'Alignment',
					'.tve-advanced-controls',
					'hr',
				],
			],
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

return new Thrive_Comment_Thread_Container_Element( 'thrive_comment_thread_container' );
