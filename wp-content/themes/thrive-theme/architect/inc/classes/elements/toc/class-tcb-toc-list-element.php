<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * General element representing each of the individually stylable typography elements
 *
 * Class TCB_Toc_Heading_Element
 */
class TCB_Toc_List_Element extends TCB_ContentBox_Element {

	public function name() {
		return __( 'Heading List', 'thrive-cb' );
	}

	/**
	 * Element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.tve-toc-list';
	}

	/**
	 * Either to display or not the element in the sidebar menu
	 *
	 * @return bool
	 */
	public function hide() {
		return true;
	}

	public function own_components() {
		$prefix_config  = tcb_selection_root() . ' ';
		$typography_cfg = [ 'css_suffix' => '', 'css_prefix' => '' ];
		$components     = parent::own_components();

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

		$components['typography'] = [
			'disabled_controls' => [
				'p_spacing',
				'h1_spacing',
				'h2_spacing',
				'h3_spacing',
			],
			'config'            => [
				'to'            => '.tve-toc-heading',
				'FontSize'      => $typography_cfg,
				'FontColor'     => $typography_cfg,
				'TextAlign'     => $typography_cfg,
				'TextStyle'     => $typography_cfg,
				'TextTransform' => $typography_cfg,
				'FontFace'      => $typography_cfg,
				'LineHeight'    => $typography_cfg,
				'LetterSpacing' => $typography_cfg,
			],
		];
		$components['scroll']     = [ 'hidden' => true ];
		$components['responsive'] = [ 'hidden' => true ];
		$components['animation']  = [ 'hidden' => true ];

		return $components;
	}
}
