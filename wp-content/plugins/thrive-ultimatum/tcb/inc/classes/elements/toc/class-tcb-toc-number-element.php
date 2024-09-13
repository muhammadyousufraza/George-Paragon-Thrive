<?php


class TCB_Toc_Number_Element extends TCB_Label_Disabled_Element {
	public function name() {
		return __( 'Number', 'thrive-cb' );
	}

	/**
	 * Element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.tve-toc-number';
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
		return [
			'toc_number' => [
				'config' => [
					'NumberSuffix' => [
						'config'  => [
							'label' => 'Suffix',
						],
						'extends' => 'LabelInput',
					],
				],
			],
			'typography' => [
				'disabled_controls' => [
					'TextTransform',
					'typography-text-transform-hr',
					'.tve-advanced-controls',
				],
				'config'            => [
					'FontColor'  => [
						'css_suffix' => ' .tve-toc-disabled',
					],
					'FontSize'   => [
						'css_suffix' => ' .tve-toc-disabled',
					],
					'FontFace'   => [
						'css_suffix' => ' .tve-toc-disabled',
					],
					'TextStyle'  => [
						'css_suffix' => ' .tve-toc-disabled',
					],
					'LineHeight' => [
						'css_suffix' => ' .tve-toc-disabled',
					],
				],
			],
			'layout'     => [
				'disabled_controls' => [
					'.tve-advanced-controls',
					'Width',
					'Height',
					'Alignment',
				],
			],
		];
	}
}
