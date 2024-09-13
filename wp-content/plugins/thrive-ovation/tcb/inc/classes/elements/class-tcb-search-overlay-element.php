<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class TCB_Search_Overlay_Element extends TCB_ContentBox_Element {

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Search Overlay', 'thrive-cb' );
	}


	/**
	 * Section element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.tve-sf-overlay-container';
	}

	public function own_components() {
		$prefix_config = tcb_selection_root() . ' ';
		$bg_selector   = '>.tve-content-box-background';
		$components    = parent::own_components();

		unset( $components['contentbox'] );
		unset( $components['shared-styles'] );
		$components['layout'] = [
			'disabled_controls' => [
				'Display',
				'Float',
				'Position',
				'Width',
				'Alignment',
				'.tve-advanced-controls',
			],
			'config'            => [
				'Height' => [
					'important' => true,
				],
			],
		];

		$components['background'] = [
			'config' => [
				'ColorPicker' => [ 'css_prefix' => $prefix_config ],
				'PreviewList' => [ 'css_prefix' => $prefix_config ],
				'to'          => $bg_selector,
			],
		];

		$components['borders'] = [
			'config' => [
				'Borders' => [
					'important' => true,
					'to'        => $bg_selector,
				],
				'Corners' => [
					'important' => true,
					'to'        => $bg_selector,
				],
			],
		];

		$components['typography']       = [ 'hidden' => true ];
		$components['scroll']           = [ 'hidden' => true ];
		$components['responsive']       = [ 'hidden' => true ];
		$components['animation']        = [ 'hidden' => true ];
		$components['decoration']       = [ 'hidden' => true ];
		$components['styles-templates'] = [ 'hidden' => true ];

		return $components;
	}


	public function hide() {
		return true;
	}
}
