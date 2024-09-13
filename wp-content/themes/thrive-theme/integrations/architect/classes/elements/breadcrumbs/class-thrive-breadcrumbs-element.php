<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Thrive_Breadcrumbs_Element
 */
class Thrive_Breadcrumbs_Element extends Thrive_Theme_Element_Abstract {
	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Breadcrumbs', 'thrive-theme' );
	}

	/**
	 * Return icon class needed for display in menu
	 *
	 * @return string
	 */
	public function icon() {
		return 'breadcrumbs';
	}

	/**
	 * WordPress element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.thrive-breadcrumbs';
	}

	/**
	 * Component and control config
	 *
	 * @return array
	 */
	public function own_components() {
		$breadcrumbs_main_config = [
			'SeparatorType'        => [
				'config'            => [
					'name'    => __( 'Separator Type', 'thrive-theme' ),
					'buttons' => [
						[
							'icon'    => '',
							'text'    => 'Character',
							'value'   => 'character',
							'default' => true,
						],
						[
							'icon'  => '',
							'text'  => 'Icon',
							'value' => 'icon',
						],
						[
							'icon'  => '',
							'text'  => 'None',
							'value' => 'none',
						],
					],
				],
				'disabled_controls' => [ 'hr' ],
				'extends'           => 'ButtonGroup',

			],
			'ItemSpacing'          => [
				'css_prefix' => tcb_selection_root() . ' ',
				'config'     => [
					'default' => '5',
					'min'     => '1',
					'max'     => '100',
					'label'   => __( 'Item spacing', 'thrive-theme' ),
					'um'      => [ 'px' ],
					'css'     => 'margin-right',
				],
				'extends'    => 'Slider',
			],
			'Alignment'            => [
				'config'  => [
					'default' => 'flex-start',
					'name'    => __( 'Alignment', 'thrive-theme' ),
					'options' => [
						[
							'name'  => __( 'Centered', 'thrive-theme' ),
							'value' => 'center',
						],
						[
							'name'  => __( 'Left', 'thrive-theme' ),
							'value' => 'flex-start',
						],
						[
							'name'  => __( 'Right', 'thrive-theme' ),
							'value' => 'flex-end',
						],
						[
							'name'  => __( 'Space Between', 'thrive-theme' ),
							'value' => 'space-between',
						],
					],
				],
				'extends' => 'Select',
			],
			'ShowCategoriesInPath' => [
				'config'  => [
					'label' => __( 'Display categories', 'thrive-theme' ),
					'info'  => true,
				],
				'extends' => 'Switch',
			],
		];

		foreach ( Thrive_Breadcrumbs::get_default_labels() as $key => $label ) {
			$breadcrumbs_main_config[ $key . 'Label' ] = [
				'config'  => [
					'label'       => $label,
					'extra_attrs' => '',
					'placeholder' => $label,
					'default'     => $label,
				],
				'extends' => 'LabelInput',
			];
		}

		/* get the config for the separator */
		$breadcrumbs_config = array_merge( $breadcrumbs_main_config, Thrive_Breadcrumbs_Separator_Element::get_separator_config() );

		return [
			'thrive_breadcrumbs' => [
				'config' => $breadcrumbs_config,
			],
			'layout'             => [
				'disabled_controls' => [
					'Alignment',
					'Display',
				],
			],
			'animation'          => [ 'hidden' => true ],
			'styles-templates'   => [ 'hidden' => true ],
			'shadow'             => [ 'hidden' => true ],
			'typography'         => [ 'hidden' => true ],
		];
	}

	/**
	 * Element HTML
	 *
	 * @return string
	 */
	public function html() {
		return TCB_Utils::wrap_content( '', '', '', 'thrive-breadcrumbs', [ 'icon-name' => 'icon-angle-right-light' ] );
	}

	/**
	 * Hide on 404 pages
	 *
	 * @return bool
	 */
	public function hide() {
		$hide = false;

		if ( thrive_template()->meta( THRIVE_PRIMARY_TEMPLATE ) === THRIVE_ERROR404_TEMPLATE ) {
			$hide = true;
		}

		return $hide;
	}

	/**
	 * This element is a shortcode
	 *
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
		return 'thrive_breadcrumbs';
	}
}

return new Thrive_Breadcrumbs_Element( 'thrive_breadcrumbs' );
