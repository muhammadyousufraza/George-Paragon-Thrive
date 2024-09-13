<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Thrive_Comments_Form_Reply_Text_Element
 */
class Thrive_Comments_Form_Reply_Text_Element extends Thrive_Theme_Element_Abstract {

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Leave Reply Text', 'thrive-theme' );
	}

	/**
	 * WordPress element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.comment-reply-title';
	}

	/**
	 * Hide this.
	 */
	public function hide() {
		return true;
	}

	/**
	 * @return array
	 */
	public function own_components() {
		$components = parent::own_components();

		$components['animation']['disabled_controls'] = [ '.btn-inline.anim-link' ];
		$components['layout']['disabled_controls']    = [
			'Alignment',
			'.tve-advanced-controls',
			'hr',
			'MaxWidth',
		];

		$components['thrive_comments_form_label'] = [
			'config' => [
				'Label' => [
					'config'  => [
						'label'   => 'Label',
						'default' => 'Label',
					],
					'extends' => 'LabelInput',
				],
			],
		];

		return $components;
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

return new Thrive_Comments_Form_Reply_Text_Element( 'thrive_comments_reply_text' );

