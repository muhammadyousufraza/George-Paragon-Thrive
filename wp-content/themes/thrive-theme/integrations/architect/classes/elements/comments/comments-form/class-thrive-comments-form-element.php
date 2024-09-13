<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Thrive_Comments_Form_Element
 */
class Thrive_Comments_Form_Element extends Thrive_Theme_Element_Abstract {

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Comments Form', 'thrive-theme' );
	}

	/**
	 * WordPress element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '#respond';
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
			'comment-form'     => [
				'config' => [
					'FieldsControl'   => [
						'config' => [
							'sortable'      => false,
							'settings_icon' => 'edit',
						],
					],
					'AddRemoveLabels' => [
						'config'  => [
							'name'    => '',
							'label'   => __( 'Labels', 'thrive-theme' ),
							'default' => true,
						],
						'extends' => 'Switch',
					],
				],
			],
			'typography'       => [
				'hidden' => true,
			],
			'animation'        => [
				'hidden' => true,
			],
			'styles-templates' => [
				'hidden' => true,
			],
			'responsive'       => [
				'hidden' => true,
			],

		];
	}

	/**
	 * This element is a shortcode
	 * @return bool
	 */
	public function is_shortcode() {
		return true;
	}

	/**
	 * Return the shortcode tag of the element.
	 *
	 * @return string
	 */
	public static function shortcode() {
		return 'comment-form';
	}

	/**
	 * This element has no icons
	 * @return bool
	 */
	public function has_icons() {
		return false;
	}
}

return new Thrive_Comments_Form_Element( 'comment-form' );

