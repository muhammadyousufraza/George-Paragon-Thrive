<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Thrive_Comments_Form_Label_Element
 */
class Thrive_Comments_Form_Label_Element extends Thrive_Theme_Element_Abstract {

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Comments Form Label', 'thrive-theme' );
	}

	/**
	 * WordPress element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.comment-form-label';
	}

	/**
	 * Hide this.
	 */
	public function hide() {
		return true;
	}

	public function own_components() {
		$components = parent::own_components();

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
		$components['animation']['hidden']        = true;
		$components['responsive']['hidden']       = true;
		$components['styles-templates']['hidden'] = true;

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

return new Thrive_Comments_Form_Label_Element( 'thrive_comments_form_label' );

