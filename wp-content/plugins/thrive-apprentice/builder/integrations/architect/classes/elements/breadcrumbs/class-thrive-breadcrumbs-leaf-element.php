<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Thrive_Breadcrumbs_Leaf_Element
 */
class Thrive_Breadcrumbs_Leaf_Element extends Thrive_Theme_Element_Abstract {
	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Breadcrumbs Leaf', 'thrive-theme' );
	}

	/**
	 * WordPress element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.thrive-breadcrumb-leaf';
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
		$components = parent::own_components();

		$components['breadcrumbs_leaf'] = [
			'order'  => 1,
			'config' => [
				'EnableTruncateChars' => [
					'config'  => [
						'label' => __( 'Enable truncate characters', 'thrive-theme' ),
					],
					'extends' => 'Switch',
				],
				'CharactersTruncate'  => [
					'config'  => [
						'name'      => __( 'Truncate Characters', 'thrive-theme' ),
						'default'   => Thrive_Breadcrumbs::LEAF_CHAR_NR,
						'maxlength' => 5,
						'min'       => 1,
					],
					'extends' => 'Input',
				],
			],
		];

		$components['animation'] ['hidden']        = true;
		$components['styles-templates'] ['hidden'] = true;
		$components['shadow'] ['hidden']           = true;
		$components['decoration'] ['hidden']       = true;
		$components['layout']['disabled_controls'] = [
			'Alignment',
			'.tve-advanced-controls',
			'hr',
			'MaxWidth',
		];

		foreach ( $components['typography']['config'] as $control => $config ) {
			if ( is_array( $config ) ) {
				$components['typography']['config'][ $control ]['css_suffix'] = ' span';
				$components['typography']['config'][ $control ]['important']  = true;
			}
		}

		$components['typography']['config']['css_suffix'] = '';
		$components['typography']['config']['css_prefix'] = '';

		$components['typography']['disabled_controls'] = [ 'TextAlign', '.tve-advanced-controls' ];

		return $components;
	}

	/**
	 * Enables Hover States on Breadcrumbs Leaf
	 *
	 * @return bool
	 */
	public function has_hover_state() {
		return true;
	}

	/**
	 * This element has no icons
	 *
	 * @return bool
	 */
	public function has_icons() {
		return false;
	}

	/**
	 * This element has a selector
	 *
	 * @return bool
	 */
	public function has_selector() {
		return true;
	}
}

return new Thrive_Breadcrumbs_Leaf_Element( 'breadcrumbs_leaf' );
