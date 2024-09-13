<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

namespace TCB\Integrations\WooCommerce\Shortcodes\MiniCart;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Element
 *
 * @package TCB\Integrations\WooCommerce\Shortcodes\MiniCart
 */
class Element extends \TCB_Element_Abstract {
	/**
	 * @return string
	 */
	public function name() {
		return __( 'Mini Cart', 'thrive-cb' );
	}

	/**
	 * @return string
	 */
	public function icon() {
		return 'mini-cart';
	}

	/**
	 * @return string
	 */
	public function identifier() {
		return '.tcb-woo-mini-cart';
	}

	/**
	 * @return array
	 */
	public function own_components() {
		$components = array(
			'mini-cart'        => array(
				'config' => array(
					'color'         => array(
						'config'  => array(
							'default' => '000',
							'label'   => __( 'Color', 'thrive-cb' ),
						),
						'extends' => 'ColorPicker',
					),
					'align'         => array(
						'config'  => array(
							'name'    => __( 'Alignment', 'thrive-cb' ),
							'buttons' => array(
								array(
									'icon'    => 'a_left',
									'text'    => '',
									'value'   => 'left',
									'default' => true,
								),
								array(
									'icon'  => 'a_center',
									'text'  => '',
									'value' => 'center',
								),
								array(
									'icon'  => 'a_right',
									'text'  => '',
									'value' => 'right',
								),
							),
						),
						'extends' => 'ButtonGroup',
					),
					'size'          => array(
						'config'  => array(
							'min'   => '10',
							'max'   => '100',
							'um'    => [ 'px' ],
							'label' => __( 'Size', 'thrive-cb' ),
						),
						'extends' => 'Slider',
					),
					'cart-type'     => array(
						'config'  => array(
							'buttons' => array(
								array(
									'value'   => 'icon',
									'text'    => __( 'Icon', 'thrive-cb' ),
									'default' => true,
								),
								array(
									'value' => 'amount',
									'text'  => __( 'Amount', 'thrive-cb' ),
								),
								array(
									'value' => 'text',
									'text'  => __( 'Text', 'thrive-cb' ),
								),
							),
						),
						'extends' => 'ButtonGroup',
					),
					'cart-text'     => array(
						'config'  => array(
							'label'       => __( 'Text', 'thrive-cb' ),
							'extra_attrs' => '',
							'placeholder' => 'e.g. Cart',
							'default'     => 'Cart',
						),
						'extends' => 'LabelInput',
					),
					'icon-position' => array(
						'config'  => array(
							'name'    => __( 'Icon Position', 'thrive-cb' ),
							'buttons' => array(
								array(
									'icon'    => '',
									'text'    => 'Left',
									'value'   => 'left',
									'default' => true,
								),
								array(
									'icon'  => '',
									'text'  => 'Right',
									'value' => 'right',
								),
							),
							'default' => 'left',
						),
						'extends' => 'ButtonGroup',
					),
					'trigger'       => array(
						'config'  => array(
							'name'    => __( 'Show items on', 'thrive-cb' ),
							'options' => array(
								array(
									'name'  => __( 'Click', 'thrive-cb' ),
									'value' => 'click',
								),
								array(
									'name'  => __( 'Hover', 'thrive-cb' ),
									'value' => 'hover',
								),
							),
							'default' => 'click',
						),
						'extends' => 'Select',
					),
					'direction'     => array(
						'config'  => array(
							'name'    => __( 'Position', 'thrive-cb' ),
							'options' => array(
								array(
									'name'  => __( 'Underneath', 'thrive-cb' ),
									'value' => 'under',
								),
								array(
									'name'  => __( 'From the right', 'thrive-cb' ),
									'value' => 'right',
								),
								array(
									'name'  => __( 'From the left', 'thrive-cb' ),
									'value' => 'left',
								),
							),
							'default' => 'under',
						),
						'extends' => 'Select',
					),
				),
			),
			'styles-templates' => [ 'hidden' => true ],
			'layout'           => [ 'disabled_controls' => [ 'Alignment', 'Display', 'Overflow', 'ScrollStyle' ] ],
			'animation'        => [ 'disabled_controls' => [ '.anim-popup', '.anim-link' ] ],
		);

		$general_components = $this->general_components();

		$components['typography'] = $general_components['typography'];

		foreach ( $components['typography']['config'] as $control => $config ) {
			if ( in_array( $control, [ 'css_suffix', 'css_prefix' ] ) ) {
				continue;
			}

			$components['typography']['config'][ $control ]['css_suffix'] = [ ' .tcb-woo-mini-cart-amount', ' .tcb-woo-mini-cart-text' ];
		}

		return $components;
	}

	/**
	 * Element category that will be displayed in the sidebar
	 *
	 * @return string
	 */
	public function category() {
		return 'WooCommerce';
	}

	/**
	 * Whether or not this element can be edited while under :hover state
	 *
	 * @return bool
	 */
	public function has_hover_state() {
		return true;
	}
}

return new Element( 'mini-cart' );
