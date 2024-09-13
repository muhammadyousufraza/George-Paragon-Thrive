<?php

/**
 * Class TCB_Menu_Hamburger_Wrapper_Element
 */
class TCB_Menu_Hamburger_Wrapper_Element extends TCB_Element_Abstract {

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Menu Box', 'thrive-cb' );
	}

	/**
	 * Section element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.thrv_wrapper:not(.tve-regular) .tve-ham-wrap';
	}

	/**
	 * There is no need for HTML for this element since we need it only for control filter
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
		$components = array_merge( $this->general_components(), [
			'menu_hamburger_wrapper' => [
				'config' => [
					'BoxWidth'           => [
						'config'  => [
							'default' => '1024',
							'min'     => '100',
							'max'     => '2000',
							'label'   => __( 'Maximum Width', 'thrive-cb' ),
							'um'      => [ 'px', '%' ],
							'css'     => 'max-width',
						],
						'extends' => 'Slider',
					],
					'HorizontalPosition' => [
						'config'  => [
							'name'    => __( 'Horizontal position', 'thrive-cb' ),
							'buttons' => [
								[
									'icon'    => 'a_right',
									'text'    => '',
									'value'   => 'left',
								],
								[
									'icon'  => 'a_center',
									'text'  => '',
									'value' => 'center',
									'default' => true,
								],
								[
									'icon'  => 'a_left',
									'text'  => '',
									'value' => 'right',
								],
							],
						],
						'extends' => 'ButtonGroup',
					],
					'BoxHeight'          => [
						'config'  => [
							'default' => '80',
							'min'     => '1',
							'max'     => '1000',
							'label'   => __( 'Minimum Height', 'thrive-cb' ),
							'um'      => [ 'px', 'vh' ],
							'css'     => 'min-height',
						],
						'extends' => 'Slider',
					],
					'VerticalPosition'   => [
						'config'  => [
							'name'    => __( 'Vertical Position', 'thrive-cb' ),
							'buttons' => [
								[
									'icon'    => 'top',
									'default' => true,
									'value'   => 'flex-start',
								],
								[
									'icon'  => 'vertical',
									'value' => 'center',
								],
								[
									'icon'  => 'bot',
									'value' => 'flex-end',
								],
							],
						],
						'extends' => 'ButtonGroup',
					],
				],
			],
			'background'             => [
				'config' => [
					'ColorPicker' => [
						'config' => [
							'important' => false,
						],
					],
				],
			],
			'layout'                 => [
				'disabled_controls' => [
					'Display',
					'Width',
					'Height',
					'Alignment',
					'.tve-advanced-controls',
					'margin',
				],
			],
			'borders'                => [
				'config' => [
					'Corners' => [
						'overflow' => false,
					],
				],
			],
			'animation'              => [ 'hidden' => true ],
			'responsive'             => [ 'hidden' => true ],
			'styles-templates'       => [ 'hidden' => true ],
		] );

		foreach ( $components['typography']['config'] as $control => $config ) {
			if ( is_array( $config ) ) {
				$components['typography']['config'][ $control ]['important'] = true;
			}
		}

		return $components;
	}

	public function hide() {
		return true;
	}
}
