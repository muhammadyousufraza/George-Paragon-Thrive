<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Thrive_Template_Content_Element
 */
class Thrive_Template_Content_Element extends Thrive_Theme_Element_Abstract {
	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Layout Container', 'thrive-theme' );
	}

	/**
	 * WordPress element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '#content .main-content-background';
	}

	/**
	 * Hide this.
	 */
	public function hide() {
		return true;
	}

	/**
	 * This element has a selector
	 * @return bool
	 */
	public function has_selector() {
		return true;
	}

	/**
	 * No icons for the wrapper
	 * @return bool
	 */
	public function has_icons() {
		return false;
	}

	/**
	 * @return array
	 */
	public function own_components() {
		$components = parent::own_components();

		$components['animation']  = [ 'hidden' => true ];
		$components['responsive'] = [ 'hidden' => true ];
		$components['typography'] = [ 'hidden' => true ];

		$background_selector = '.main-content-background';

		$components['layout']['disabled_controls'] = [
			'Width',
			'Height',
			'.tve-advanced-controls',
			'Alignment',
			'Display',
		];

		$components['borders']['config']['to'] = $background_selector;
		$components['shadow']['config']['to']  = $background_selector;

		$components['background'] = [
			'config'            => [ 'to' => $background_selector ],
			'disabled_controls' => [],
		];

		$components['template-content'] = [
			'config' => [
				'ContentWidth' => [
					'config'  => [
						'default' => '1080',
						'min'     => '420',
						'max'     => '1980',
						'label'   => __( 'Content Width', 'thrive-theme' ),
						'um'      => [ 'px', '%' ],
						'css'     => 'max-width',
					],
					'extends' => 'Slider',
				],
				'LayoutWidth'  => [
					'config'  => [
						'default' => '1080',
						'min'     => '420',
						'max'     => '1980',
						'label'   => __( 'Layout Width', 'thrive-theme' ),
						'um'      => [ 'px', '%' ],
						'css'     => 'max-width',
					],
					'extends' => 'Slider',
				],
				'PageMap'      => [ 'config' => [] ],
			],
		];

		return $components;
	}
}

return new Thrive_Template_Content_Element( 'template-content' );
