<?php
/**
 * FileName  class-tcb-footer-element.php.
 *
 * @project  : thrive-visual-editor
 */

/**
 * Class TCB_Footer_Element
 */
class TCB_Footer_Element extends TCB_Symbol_Element_Abstract {
	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Footer', 'thrive-cb' );
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
		return '.thrv_symbol.thrv_footer';
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
			'footer'           => array(
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
						'config'  => array(
							'default' => '1080',
							'min'     => '1',
							'max'     => '1980',
							'label'   => __( 'Content Width', 'thrive-cb' ),
							'um'      => [ 'px' ],
							'css'     => 'max-width',
						),
						'extends' => 'Slider',
					),
					'StretchContent'     => array(
						'config'  => array(
							'name'    => '',
							'label'   => __( 'Stretch content to full width', 'thrive-cb' ),
							'default' => true,
						),
						'extends' => 'Switch',
					),
					'Height'             => array(
						'config'  => array(
							'default' => '1024',
							'min'     => '1',
							'max'     => '1000',
							'label'   => __( 'Footer Minimum Height', 'thrive-cb' ),
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
							'name'    => __( 'Vertical Position', 'thrive-cb' ),
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
				),
			),
			'background'       => [
				'config'            => [
					'css_suffix' => ' .symbol-section-out',
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
			'styles-templates' => [ 'hidden' => true ],
		);

		$components['layout']['config']['MarginAndPadding']['padding_to'] = $content_selector;

		return $components;
	}
}
