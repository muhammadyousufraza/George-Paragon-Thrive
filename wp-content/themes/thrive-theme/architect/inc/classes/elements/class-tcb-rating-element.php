<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class TCB_Rating_Element
 */
class TCB_Rating_Element extends TCB_Element_Abstract {

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Star Rating', 'thrive-cb' );
	}

	/**
	 * Get element alternate
	 *
	 * @return string
	 */
	public function alternate() {
		return 'review';
	}

	/**
	 * Return icon class needed for display in menu
	 *
	 * @return string
	 */
	public function icon() {
		return 'rating';
	}

	/**
	 * Element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.thrv-rating';
	}

	/**
	 * The HTML is generated from js
	 *
	 * @return string
	 */
	protected function html() {
		return '';
	}

	/**
	 * Component and control config
	 *
	 * @return array
	 */
	public function own_components() {
		return array(
			'rating'           => array(
				'config' => array(
					'ratingValue'    => [
						'config' => [
							'default_value' => 2.5,
							'default_max'   => 5,
							'max_size'      => 10,
						],
					],
					'ExternalFields' => [
						'config'  => [
							'key'               => 'number',
							'shortcode_element' => '.thrv-rating',
						],
						'extends' => 'CustomFields',
					],
					'style'          => array(
						'config' => array(
							'label' => __( 'Style', 'thrive-cb' ),
						),
					),
					'stylePicker'    => array(
						'config' => array(
							'label' => __( 'Change style', 'thrive-cb' ),
						),
					),
					'size'           => array(
						'config' => array(
							'default' => '25',
							'min'     => '10',
							'max'     => '150',
							'label'   => __( 'Size', 'thrive-cb' ),
							'um'      => [ 'px' ],
						),
					),
					'background'     => array(
						'config' => array(
							'label'   => __( 'Background', 'thrive-cb' ),
							'options' => [ 'noBeforeInit' => false ],
						),
					),
					'fill'           => array(
						'config' => array(
							'label'   => __( 'Fill', 'thrive-cb' ),
							'options' => [ 'noBeforeInit' => false ],
						),
					),
					'outline'        => array(
						'config' => array(
							'label'   => __( 'Outline', 'thrive-cb' ),
							'options' => [ 'noBeforeInit' => false ],
						),
					),
				),
				'order'  => 1,
			),
			'typography'       => [
				'hidden' => true,
			],
			'animation'        => [
				'hidden' => true,
			],
			'styles-templates' => [
				'hidden' => true,
			],
			'layout'           => [
				'disabled_controls' => [
					'Width',
					'Height',
					'Overflow',
					'ScrollStyle',
				],
			],
			'shadow'           => [
				'config' => [
					'disabled_controls' => [ 'text' ],
				],
			],
		);
	}

	/**
	 * @return bool
	 */
	public function has_hover_state() {
		return true;
	}

	/**
	 * Element category that will be displayed in the sidebar
	 *
	 * @return string
	 */
	public function category() {
		return static::get_thrive_advanced_label();
	}

	/**
	 * Element info
	 *
	 * @return string|string[][]
	 */
	public function info() {
		return [
			'instructions' => [
				'type' => 'help',
				'url'  => 'star_rating',
				'link' => 'https://help.thrivethemes.com/en/articles/4425791-how-to-use-the-divider-and-star-rating-elements',
			],
		];
	}
}
