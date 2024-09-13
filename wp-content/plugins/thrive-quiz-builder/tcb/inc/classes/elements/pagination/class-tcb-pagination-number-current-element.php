<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class TCB_Pagination_Number_Current_Element extends TCB_Element_Abstract {
	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Active Page', 'thrive-cb' );
	}

	/**
	 * Element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.tcb-pagination-current.tcb-pagination-number';
	}

	/**
	 * Component and control config
	 *
	 * @return array
	 */
	public function own_components() {
		$components = parent::general_components();

		$components['typography']['config']['css_suffix'] = '';
		$components['typography']['disabled_controls']    = [ 'TextTransform', 'TextAlign', '.tve-advanced-controls', '[data-value="tcb-typography-line-height"]' ];

		foreach ( $components['typography']['config'] as $control => $config ) {
			if ( is_array( $config ) ) {
				$components['typography']['config'][ $control ]['css_suffix'] = '';
			}
		}

		$components['typography']['config']['FontColor']['important'] = true;
		$components['typography']['config']['FontSize']['important']  = true;

		$components['layout']['disabled_controls'] = [ 'Display', 'Alignment', '.tve-advanced-controls' ];

		$components['animation']        = [ 'hidden' => true ];
		$components['responsive']       = [ 'hidden' => true ];
		$components['styles-templates'] = [ 'hidden' => true ];

		return $components;
	}

	/**
	 * Hide this element in the sidebar.
	 *
	 * @return string
	 */
	public function hide() {
		return true;
	}
}
