<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Thrive_Rating_Filter_Stars_Element
 */
class Thrive_Rating_Filter_Stars_Element extends Thrive_Theme_Element_Abstract {
	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Rating', 'thrive-theme' );
	}

	/**
	 * WordPress element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.widget_rating_filter .star-rating';
	}

	/**
	 * Hide this.
	 */
	public function hide() {
		return true;
	}

	/**
	 * Default components that most theme elements use
	 *
	 * @return array
	 */
	public function own_components() {
		$components = parent::own_components();

		$components['animation']           = [ 'hidden' => true ];
		$components['typography']          = [ 'hidden' => true ];
		$components['responsive']          = [ 'hidden' => true ];
		$components['background']          = [ 'hidden' => true ];
		$components['layout']              = [ 'hidden' => true ];
		$components['shadow']              = [ 'hidden' => true ];
		$components['borders']             = [ 'hidden' => true ];
		$components['product-star-rating'] = [
			'config' => [
				'color' => [
					'config'  => [
						'default' => '000',
						'label'   => __( 'Color', 'thrive-cb' ),
					],
					'extends' => 'ColorPicker',
				],
				'size'  => [
					'config'  => [
						'min'   => '1',
						'max'   => '100',
						'um'    => [ 'px' ],
						'label' => __( 'Size', 'thrive-cb' ),
					],
					'extends' => 'Slider',
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

return new Thrive_Rating_Filter_Stars_Element( 'wc-rating-filter-stars' );
