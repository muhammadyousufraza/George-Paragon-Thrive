<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class TCB_Tab_Content_Element extends TCB_ContentBox_Element {

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Tab Content', 'thrive-cb' );
	}


	/**
	 * Section element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.tve_tab_content';
	}

	public function own_components() {
		$prefix_config = tcb_selection_root() . ' ';

		$components = parent::own_components();

		unset( $components['contentbox'] );
		unset( $components['shared-styles'] );
		$components['layout'] = [
			'disabled_controls' => [
				'Display',
				'Float',
				'Position',
			],
			'config'            => [
				'Width'  => [
					'important' => true,
				],
				'Height' => [
					'important' => true,
				],
			],
		];

		$components['background'] = [
			'config' => [
				'ColorPicker' => [ 'css_prefix' => $prefix_config ],
				'PreviewList' => [ 'css_prefix' => $prefix_config ],
				'to'          => '>.tve-content-box-background',
			],
		];

		$components['borders'] = [
			'config' => [
				'Borders' => [
					'important' => true,
					'to'        => '>.tve-content-box-background',
				],
				'Corners' => [
					'important' => true,
					'to'        => '>.tve-content-box-background',
				],
			],
		];

		$prefix_config_text       = [ 'css_prefix' => $prefix_config . '.tve_tab_content ' ];
		$components['typography'] = [
			'disabled_controls' => [],
			'config'            => [
				'to'         => '.tve-cb',
				'FontSize'   => $prefix_config_text,
				'FontColor'  => $prefix_config_text,
				'LineHeight' => $prefix_config_text,
				'FontFace'   => $prefix_config_text,
			],
		];
		$components['scroll']     = [ 'hidden' => true ];
		$components['responsive'] = [ 'hidden' => true ];
		$components['animation']  = [ 'hidden' => true ];

		return $components;
	}


	public function hide() {
		return true;
	}
}
