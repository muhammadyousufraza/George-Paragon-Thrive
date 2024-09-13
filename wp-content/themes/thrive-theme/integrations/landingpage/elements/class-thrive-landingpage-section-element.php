<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

if ( ! class_exists( 'Thrive_Theme_Cloud_Element_Abstract' ) ) {
	require_once ARCHITECT_INTEGRATION_PATH . '/classes/class-thrive-theme-cloud-element-abstract.php';
}

/**
 * Class Thrive_Landingpage_Section_Element
 */
class Thrive_Landingpage_Section_Element extends Thrive_Theme_Cloud_Element_Abstract {

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Section', 'thrive-theme' );
	}

	/**
	 * WordPress element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.landingpage-section > div';
	}

	/**
	 * Hide Section Element
	 *
	 * @return bool
	 */
	public function hide() {
		return true;
	}

	/**
	 * Add the theme section component
	 *
	 * @return array
	 */
	public function own_components() {
		$components = parent::own_components();
		$prefix     = tcb_selection_root( false ) . ' ';

		$suffix = [ ' p', ' li', ' label', ' .tcb-plain-text' ];

		$background_selector = '.section-background';
		$content_selector    = '.section-content';

		$components['layout']['config']['MarginAndPadding']['padding_to'] = $content_selector;

		$components['borders']['config']['to'] = $background_selector;
		$components['shadow']['config']['to']  = $background_selector;

		$components['typography']['config']['to']                      = $content_selector;
		$components['typography']['config']['TextStyle']['css_suffix'] = $suffix;

		$components['typography']['config']['FontSize']['css_suffix']   = $suffix;
		$components['typography']['config']['FontSize']['css_prefix']   = $prefix;
		$components['typography']['config']['LineHeight']['css_suffix'] = $suffix;
		$components['typography']['config']['LineHeight']['css_prefix'] = $prefix;
		$components['typography']['config']['FontColor']['css_suffix']  = $suffix;
		$components['typography']['config']['FontColor']['css_prefix']  = $prefix;
		$components['typography']['config']['FontFace']['css_suffix']   = array_merge( $suffix, [ ' h1', ' h2', ' h3', ' h4', ' h5', ' h6' ] );
		$components['typography']['config']['FontFace']['css_prefix']   = $prefix;

		$components['background'] = [
			'config'            => [ 'to' => $background_selector ],
			'disabled_controls' => [],
		];

		$components['animation']  = [ 'hidden' => true ];
		$components['decoration'] = [
			'config' => [ 'to' => $background_selector ],
			'order'  => 50,
		];

		$components['landingpage_section'] = [
			'config' => [
				'SectionTemplates'   => [
					'config'  => [
						'label' => __( 'Template', 'thrive-theme' ),
					],
					'extends' => 'ModalPicker',
				],
				'StretchBackground'  => [
					'config'  => [
						'name'    => '',
						'label'   => __( 'Stretch background to full width', 'thrive-theme' ),
						'default' => true,
					],
					'extends' => 'Switch',
				],
				'InheritContentSize' => [
					'config'  => [
						'name'    => '',
						'label'   => __( 'Inherit content size from layout', 'thrive-theme' ),
						'default' => true,
					],
					'extends' => 'Switch',
				],
				'ContentWidth'       => [
					'config'     => [
						'default' => '1080',
						'min'     => '1',
						'max'     => '1980',
						'label'   => __( 'Content Width', 'thrive-theme' ),
						'um'      => [ 'px' ],
						'css'     => 'max-width',
					],
					'css_suffix' => " {$content_selector}",
					'extends'    => 'Slider',
				],
				'StretchContent'     => [
					'config'  => [
						'name'    => '',
						'label'   => __( 'Stretch content to fullwidth', 'thrive-theme' ),
						'default' => true,
					],
					'extends' => 'Switch',
				],
				'SectionHeight'      => [
					'config'     => [
						'default' => '80',
						'min'     => '1',
						'max'     => '1000',
						'label'   => __( 'Section Minimum Height', 'thrive-theme' ),
						'um'      => [ 'px', 'vh' ],
						'css'     => 'min-height',
					],
					'css_suffix' => " {$content_selector}",
					'extends'    => 'Slider',
				],
				'VerticalPosition'   => [
					'config'  => [
						'name'    => __( 'Vertical Position', 'thrive-theme' ),
						'buttons' => [
							[
								'icon'    => 'top',
								'default' => true,
								'value'   => '',
							],
							[
								'icon'  => 'vertical',
								'value' => 'center',
							],
							[
								'icon'  => 'bot',
								'value' => 'flex-end',
							],
						],
					],
					'to'      => $content_selector,
					'extends' => 'ButtonGroup',
				],
				'Visibility'         => [
					'config'  => [
						'name'    => '',
						'label'   => __( 'Visibility', 'thrive-theme' ),
						'default' => true,
					],
					'extends' => 'Switch',
				],
			],
		];

		return $components;
	}
}

return new Thrive_Landingpage_Section_Element( 'landingpage_section' );
