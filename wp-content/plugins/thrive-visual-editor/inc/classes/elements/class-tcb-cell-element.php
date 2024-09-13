<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class TCB_Cell_Element
 *
 * Table cell editing
 */
class TCB_Cell_Element extends TCB_Element_Abstract {

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Table Cell', 'thrive-cb' );
	}

	/**
	 * Return icon class needed for display in menu
	 *
	 * @return string
	 */
	public function icon() {
		return 'none';
	}

	/**
	 * Element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.tve_table th, .tve_table td';
	}

	/**
	 * Table cells are not visible in the side menu
	 *
	 * @return bool
	 */
	public function hide() {
		return true;
	}

	/**
	 * Component and control config
	 *
	 * @return array
	 */
	public function own_components() {
		return array(
			'cell'             => array(
				'config' => array(
					'width'  => array(
						'config'  => array(
							'min'   => 50,
							'max'   => 500,
							'label' => __( 'Column Width', 'thrive-cb' ),
							'um'    => [ 'px' ],
						),
						'extends' => 'Slider',
					),
					'height' => array(
						'config'  => array(
							'min'   => 10,
							'max'   => 200,
							'label' => __( 'Row Height', 'thrive-cb' ),
							'um'    => [ 'px' ],
						),
						'extends' => 'Slider',
					),
					'valign' => array(
						'config'  => array(
							'name'    => __( 'Vertical Align', 'thrive-cb' ),
							'buttons' => [
								[
									'icon'    => 'none',
									'default' => true,
									'value'   => '',
								],
								[
									'icon'  => 'top',
									'value' => 'top',
								],
								[
									'icon'  => 'vertical',
									'value' => 'middle',
								],
								[
									'icon'  => 'bot',
									'value' => 'bottom',
								],
							],
						),
						'extends' => 'ButtonGroup',
					),
				),
			),
			'borders'          => [
				'hidden' => true,
			],
			'animation'        => [
				'hidden' => true,
			],
			'layout'           => [
				'hidden' => true,
			],
			'typography'       => [
				'hidden' => true,
			],
			'shadow'           => [
				'config' => [
					'disabled_controls' => [ 'text' ],
				],
			],
			'responsive'       => [
				'hidden' => true,
			],
			'styles-templates' => [
				'hidden' => true,
			],
		);
	}
}
