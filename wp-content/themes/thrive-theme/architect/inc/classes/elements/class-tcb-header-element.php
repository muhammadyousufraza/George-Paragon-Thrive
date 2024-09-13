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
 * Class TCB_Footer_Element
 */
class TCB_Header_Element extends TCB_Symbol_Element_Abstract {

	/**
	 * TCB_Header_Element constructor.
	 *
	 * @param string $tag element tag.
	 */
	public function __construct( $tag = '' ) {
		parent::__construct( $tag );
	}

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Header', 'thrive-cb' );
	}

	/**
	 * Return icon class needed for display in menu
	 *
	 * @return string
	 */
	public function icon() {
		return 'post_grid';
	}

	/**
	 * Element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.thrv_symbol.thrv_header';
	}

	/**
	 * Whether or not this element is only a placeholder ( it has no menu, it's not selectable etc )
	 * e.g. Content Templates
	 *
	 * @return bool
	 */
	public function is_placeholder() {
		return false;
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
	 * Component and control config
	 *
	 * @return array
	 */
	public function own_components() {

		$background_selector = '.symbol-section-out';
		$content_selector    = '.symbol-section-in';

		$components = array(
			'header'           => array(
				'config' => array(
					'Visibility'         => array(
						'config'  => array(
							'name'    => '',
							'label'   => __( 'Visibility', 'thrive-cb' ),
							'default' => true,
						),
						'extends' => 'Switch',
					),
					'InheritContentSize' => array(
						'config'  => array(
							'name'    => '',
							'label'   => __( 'Inherit content size from layout', 'thrive-cb' ),
							'default' => true,
						),
						'extends' => 'Switch',
					),
					'StretchBackground'  => array(
						'config'  => array(
							'name'    => '',
							'label'   => __( 'Stretch background to full width', 'thrive-cb' ),
							'default' => true,
						),
						'extends' => 'Switch',
					),
					'ContentWidth'       => array(
						'config'     => array(
							'default' => '1080',
							'min'     => '1',
							'max'     => '1980',
							'label'   => __( 'Content width', 'thrive-cb' ),
							'um'      => [ 'px' ],
							'css'     => 'max-width',
						),
						'extends'    => 'Slider',
						'css_suffix' => $content_selector,
					),
					'StretchContent'     => array(
						'config'     => array(
							'name'    => '',
							'label'   => __( 'Stretch content to full width', 'thrive-cb' ),
							'default' => true,
						),
						'extends'    => 'Switch',
						'css_suffix' => ' .symbol-section-in',
					),
					'HeaderPosition'     => array(
						'config'  => array(
							'name'       => 'Header position',
							'full-width' => true,
							'buttons'    => array(
								array( 'value' => 'push', 'text' => __( 'Push content' ), 'default' => true ),
								array( 'value' => 'over', 'text' => __( 'Over content' ) ),
							),
						),
						'extends' => 'ButtonGroup',
					),
					'Height'             => array(
						'config'  => array(
							'default' => '80',
							'min'     => '1',
							'max'     => '1000',
							'label'   => __( 'Content minimum height', 'thrive-cb' ),
							'um'      => [ 'px', 'vh' ],
							'css'     => 'min-height',
						),
						'to'      => $content_selector,
						'extends' => 'Slider',
					),
					'FullHeight'         => array(
						'config'  => array(
							'name'    => '',
							'label'   => __( 'Match height to screen', 'thrive-cb' ),
							'default' => true,
						),
						'to'      => $content_selector,
						'extends' => 'Switch',
					),
					'VerticalPosition'   => array(
						'config'  => array(
							'name'    => __( 'Vertical position', 'thrive-cb' ),
							'buttons' => [
								[
									'icon'    => 'top',
									'default' => true,
									'value'   => '',
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
						),
						'to'      => $content_selector,
						'extends' => 'ButtonGroup',
					),
					'StateSelect'        => array(
						'config'  => array(
							'name'    => __( 'Header state', 'thrive-cb' ),
							'options' => array(
								'tve-default-state' => __( 'On load', 'thrive-cb' ),
								'tve-scroll-state'  => __( 'On scroll', 'thrive-cb' ),
							),
						),
						'extends' => 'Select',
					),
				),
			),
			'background'       => [
				'config'            => [
					'to' => $background_selector,
				],
				'disabled_controls' => [],
			],
			'shadow'           => [
				'config' => [
					'to' => $background_selector,
				],
			],
			'layout'           => [
				'disabled_controls' => [ '.tve-advanced-controls', 'Float', 'hr', 'Position', 'PositionFrom', 'zIndex', 'Width', 'Height', 'Alignment', 'Display' ],
			],
			'borders'          => [
				'config' => [
					'Borders'    => [],
					'Corners'    => [],
					'css_suffix' => ' .thrive-symbol-shortcode',
				],
			],
			'typography'       => [
				'disabled_controls' => [],
				'config'            => [
					'to' => $content_selector,
				],
			],
			'decoration'       => [
				'config' => [
					'to' => $background_selector,
				],
			],
			'animation'        => [ 'hidden' => true ],
			'scroll'           => [
				'order'             => 2,
				'config'            => [
					'to' => '.thrive-symbol-shortcode',
				],
				'disabled_controls' => [ '[data-value="parallax"]' ],
				'hidden'            => false,
			],
			'styles-templates' => [ 'hidden' => true ],
		);

		$components['layout']['config']['MarginAndPadding']['padding_to'] = $content_selector;

		return $components;
	}

	/**
	 * Update meta for scroll on behaviour
	 *
	 * @param $meta
	 *
	 * @return bool
	 */
	public function update_meta( $meta ) {
		$header_id = $meta['header_id'];

		update_post_meta( $header_id, $meta['meta_key'], $meta['meta_value'] );

		return true;
	}
}
