<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * General element representing each of the individually stylable typography elements
 *
 * Class TCB_Toc_Heading_Element
 */
class TCB_Toc_Heading_Element extends TCB_Element_Abstract {

	public function name() {
		return __( 'Heading Element', 'thrive-cb' );
	}

	/**
	 * Element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.tve-toc-heading';
	}

	/**
	 * Either to display or not the element in the sidebar menu
	 *
	 * @return bool
	 */
	public function hide() {
		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function expanded_state_config() {
		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function expanded_state_apply_inline() {
		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function expanded_state_label() {
		return __( 'Highlight', 'thrive-cb' );
	}

	/**
	 * @return bool
	 */
	public function has_hover_state() {
		return true;
	}

	protected function general_components() {
		$components = parent::general_components();
		foreach ( $components['typography']['config'] as $control => &$config ) {
			$config['css_suffix'] = $config['css_prefix'] = '';
		}

		$components['typography']['disabled_controls'] = [
			'p_spacing',
			'h1_spacing',
			'h2_spacing',
			'h3_spacing',
		];

		$components['typography']['config']['TextAlign'] = array_merge( $components['typography']['config']['TextAlign'], [
			'property'     => 'justify-content',
			'property_val' => [
				'left'    => 'flex-start',
				'center'  => 'center',
				'right'   => 'flex-end',
				'justify' => 'space-evenly',
			],
		] );

		return array(
			'toc_heading' => $components['typography'],
			'layout'      => array_merge(
				$components['layout'],
				array(
					'disabled_controls' => [ 'Width', 'Height', 'hr', 'Alignment', 'Display', '.tve-advanced-controls' ],
					'config'            => [
						'MarginAndPadding' => [
							'important' => true,
						],
					],
				)
			),
			'background'  => $components['background'],
			'borders'     => $components['borders'],
			'shadow'      => array_merge(
				$components['shadow'],
				array(
					'config' => [
						'disabled_controls' => [ 'inner' ],
					],
				)
			),
		);
	}
}
