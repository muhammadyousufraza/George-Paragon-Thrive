<?php

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

require_once plugin_dir_path( __FILE__ ) . 'class-tcb-menu-dropdown-element.php';

/**
 * Class TCB_Menu_Child_Element
 */
class TCB_Megamenu_Dropdown_Element extends TCB_Element_Abstract {

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Mega Menu Dropdown', 'thrive-cb' );
	}

	/**
	 * Section element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.tve-regular .tcb-mega-drop-inner';
	}

	/**
	 * Hidden element
	 *
	 * @return string
	 */
	public function hide() {
		return true;
	}

	/**
	 * Component and control config
	 *
	 * @return array
	 */
	protected function general_components() {
		$components = parent::general_components();
		unset( $components['typography'], $components['animation'], $components['responsive'], $components['styles-templates'], $components['scroll'] );

		$components['layout']['disabled_controls'] = [
			'margin',
			'.tve-advanced-controls',
			'Height',
			'Width',
			'Alignment',
			'Display',
		];

		$components['megamenu_dropdown'] = array(
			'order'  => 1,
			'config' => array(
				'Type'            => array(
					'config'  => array(
						'default' => 'grid',
						'name'    => __( 'Display Type', 'thrive-cb' ),
						'buttons' => [
							[
								'icon'    => '',
								'text'    => 'GRID',
								'value'   => 'grid',
								'default' => true,
							],
							[
								'icon'  => '',
								'text'  => 'MASONRY',
								'value' => 'masonry',
							],
						],
					),
					'extends' => 'ButtonGroup',
				),
				'ColumnsNumber'   => array(
					'config'  => array(
						'default' => '5',
						'min'     => '2',
						'max'     => '10',
						'label'   => __( 'Number of Columns', 'thrive-cb' ),
						'um'      => [ '' ],
					),
					'extends' => 'Slider',
				),
				'HorizontalSpace' => array(
					'config'  => array(
						/* in order to set the default SpaceBetween, you actually have to set a new margin-bottom for the article */
						'min'   => '0',
						'max'   => '150',
						'label' => __( 'Horizontal Spacing', 'thrive-cb' ),
						'um'    => [ 'px' ],
					),
					'extends' => 'Slider',
				),
				'VerticalSpace'   => array(
					'config'  => array(
						/* in order to set the default SpaceBetween, you actually have to set a new margin-bottom for the article */
						'min'   => '0',
						'max'   => '150',
						'label' => __( 'Vertical Spacing', 'thrive-cb' ),
						'um'    => [ 'px' ],
					),
					'extends' => 'Slider',
				),
				'MaxWidth'        => array(
					'config'  => array(
						'min'   => '0',
						'max'   => '2000',
						'label' => __( 'Dropdown max width', 'thrive-cb' ),
						'um'    => [ '%', 'px' ],
						'css'   => 'max-width',
					),
					'extends' => 'Slider',
				),
			),
		);

		return $components;
	}
}
