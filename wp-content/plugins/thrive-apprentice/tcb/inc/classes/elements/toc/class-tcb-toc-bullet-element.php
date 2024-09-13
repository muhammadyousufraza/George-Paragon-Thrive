<?php


class TCB_Toc_Bullet_Element extends TCB_Icon_Element {
	public function name() {
		return __( 'Icon', 'thrive-cb' );
	}

	/**
	 * Element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.tve-toc-bullet';
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
		$components = parent::own_components();

		unset( $components['icon'] );

		$components['toc_bullet'] = array(
			'config' => array(
				'ColorPicker' => array(
					'css_prefix' => tcb_selection_root() . ' ',
					'css_suffix' => ' > :first-child',
					'config'     => array(
						'label'   => __( 'Color', 'thrive-cb' ),
						'options' => [ 'noBeforeInit' => false ],
					),
				),
				'Slider'      => array(
					'config' => array(
						'default' => '12',
						'min'     => '6',
						'max'     => '100',
						'label'   => __( 'Size', 'thrive-cb' ),
						'um'      => [ 'px' ],
						'css'     => 'fontSize',
					),
				),
			),
		);
		$components['scroll']     = [ 'hidden' => true ];
		$components['responsive'] = [ 'hidden' => true ];
		$components['animation']  = [ 'hidden' => true ];

		return $components;
	}
}
