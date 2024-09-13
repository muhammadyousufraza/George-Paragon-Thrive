<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}


/**
 * Class Thrive_Breadcrumbs_Separator_Element
 */
class Thrive_Breadcrumbs_Separator_Element extends Thrive_Theme_Element_Abstract {
	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Breadcrumbs Separator', 'thrive-theme' );
	}

	/**
	 * WordPress element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.thrive-breadcrumb-separator';
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

		$components['breadcrumbs_separator']       = [
			'config' => static::get_separator_config(),
		];
		$components['animation']                   = [ 'hidden' => true ];
		$components['styles-templates']            = [ 'hidden' => true ];
		$components['shadow']                      = [ 'hidden' => true ];
		$components['decoration']                  = [ 'hidden' => true ];
		$components['typography']                  = [ 'hidden' => true ];
		$components['layout']['disabled_controls'] = [
			'Alignment',
			'.tve-advanced-controls',
			'hr',
			'MaxWidth',
		];

		return $components;
	}

	/**
	 * Returns the config for the separator controls.
	 *
	 * @return array
	 */
	public static function get_separator_config() {
		return [
			'IconPicker'     => [
				'config'  => [
					'label' => __( 'Choose Icon', 'thrive-theme' ),
				],
				'extends' => 'ModalPicker',
			],
			'SeparatorColor' => [
				'css_prefix' => tcb_selection_root() . ' ',
				'config'     => [
					'label'   => __( 'Color', 'thrive-theme' ),
					'options' => [ 'noBeforeInit' => false ],
				],
				'extends'    => 'ColorPicker',
			],
			'CharacterInput' => [
				'config'  => [
					'label'       => __( 'Character', 'thrive-theme' ),
					'extra_attrs' => '',
					'placeholder' => 'Desired Character',
					'default'     => ' / ',
				],
				'extends' => 'LabelInput',
			],
			'SeparatorSize'  => [
				'css_prefix' => tcb_selection_root() . ' ',
				'config'     => [
					'default' => '25',
					'min'     => '10',
					'max'     => '100',
					'label'   => __( 'Size', 'thrive-theme' ),
					'um'      => [ 'px' ],
					'css'     => 'fontSize',
				],
				'extends'    => 'Slider',
			],
		];
	}

	/**
	 * Enables Hover State on Breadcrumbs Separator.
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

return new Thrive_Breadcrumbs_Separator_Element( 'breadcrumbs_icon' );
