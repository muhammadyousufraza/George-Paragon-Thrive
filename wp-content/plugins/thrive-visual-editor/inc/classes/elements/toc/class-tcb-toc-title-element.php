<?php


class TCB_Toc_Title_Element extends TCB_ContentBox_Element {

	public function name() {
		return __( 'Table of Contents Title', 'thrive-cb' );
	}

	/**
	 * @inheritDoc
	 */
	public function expanded_state_config() {
		return true;
	}

	/**
	 * @return bool
	 */
	public function has_hover_state() {
		return true;
	}

	/**
	 * Element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.tve-toc-title';
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
	public function expanded_state_apply_inline() {
		return true;
	}

	/**
	 * For TOC expanded is collapsed because we can
	 *
	 * @inheritDoc
	 */
	public function expanded_state_label() {
		return __( 'Collapsed', 'thrive-cb' );
	}

	public function own_components() {
		$prefix_config = tcb_selection_root() . ' .tve-toc-title';

		$components = parent::own_components();


		$components['toc_title'] = array(
			'order'  => 0,
			'config' => array(
				'State'         => array(
					'config'  => array(
						'name'    => __( 'State', 'thrive-cb' ),
						'buttons' => [
							[
								'icon'    => '',
								'text'    => 'Expanded',
								'value'   => 'expanded',
								'default' => true,
							],
							[
								'icon'  => '',
								'text'  => 'Collapsed',
								'value' => 'collapsed',

							],
						],
					),
					'extends' => 'ButtonGroup',
				),
				'ShowIcon'      => array(
					'config'  => array(
						'label' => __( 'Show Icon' ),
					),
					'extends' => 'Switch',
				),
				'IconColor'     => array(
					'css_suffix' => ' .tve-toc-title-icon',
					'config'     => array(
						'label'   => __( 'Icon color', 'thrive-cb' ),
						'options' => [ 'noBeforeInit' => false ],
					),
					'important'  => true,
					'extends'    => 'ColorPicker',
				),
				'IconPlacement' => array(
					'config'  => array(
						'name'    => __( 'Placement', 'thrive-cb' ),
						'buttons' => [
							[
								'icon'    => '',
								'text'    => 'Left',
								'value'   => 'left',
								'default' => true,
							],
							[
								'icon'  => '',
								'text'  => 'Right',
								'value' => 'right',
							],
						],
					),
					'extends' => 'ButtonGroup',
				),
				'IconSize'      => array(
					'config'     => array(
						'default' => '15',
						'min'     => '0',
						'max'     => '100',
						'label'   => __( 'Icon Size', 'thrive-cb' ),
						'um'      => [ 'px', '%' ],
						'css'     => 'font-size',

					),
					'css_suffix' => ' .tve-toc-title-icon',
					'important'  => true,
					'extends'    => 'Slider',
				),
				'RotateIcon'    => array(
					'config'  => array(
						'step'    => '45',
						'label'   => __( 'Rotate Icon', 'thrive-cb' ),
						'default' => '0',
						'min'     => '-180',
						'max'     => '180',
						'um'      => [ ' Deg' ],
					),
					'extends' => 'Slider',
				),
			),
		);

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
				'css_suffix'  => ' > .tve-content-box-background',
			],
		];

		$components['borders']                         = [
			'config' => [
				'Borders'    => [
					'important' => true,
				],
				'Corners'    => [
					'important' => true,
				],
				'css_suffix' => ' > .tve-content-box-background',
			],
		];
		$components['typography']['config']            = [
			'FontSize'       => [ 'css_prefix' => $prefix_config ],
			'FontColor'      => [ 'css_prefix' => $prefix_config ],
			'LineHeight'     => [ 'css_prefix' => $prefix_config ],
			'FontFace'       => [ 'css_prefix' => $prefix_config ],
			'ParagraphStyle' => [ 'hidden' => false ],
		];
		$components['typography']['disabled_controls'] = [
			'.tve-advanced-controls',
			'p_spacing',
			'h1_spacing',
			'h2_spacing',
			'h3_spacing',

		];

		$components['scroll']     = [ 'hidden' => true ];
		$components['responsive'] = [ 'hidden' => true ];
		$components['animation']  = [ 'hidden' => true ];

		return $components;
	}
}
