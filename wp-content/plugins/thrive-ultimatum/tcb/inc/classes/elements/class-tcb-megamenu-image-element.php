<?php

require_once plugin_dir_path( __FILE__ ) . 'class-tcb-image-element.php';

/**
 * Class TCB_Label_Disabled_Element
 *
 * Non edited label element. For inline text we use typography control
 */
class TCB_Megamenu_Image_Element extends TCB_Image_Element {

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Mega Menu Image', 'thrive-cb' );
	}

	/**
	 * Section element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.tcb-mega-drop .tcb-mm-image,.tcb-mega-drop .tcb-mm-image.tcb-elem-placeholder';
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
	public function general_components() {
		$components       = parent::general_components();
		$image_components = parent::own_components();
		unset(
			$components['background'],
			$components['typography'],
			$components['animation'],
			$components['scroll'],
			$components['responsive'],
			$components['styles-templates']
		);
		$components['layout']['disabled_controls']           = [
			'Width',
			'Height',
			'.tve-advanced-controls',
			'Display',
			'Alignment',
			'padding',
		];
		$components['shadow']['config']['disabled_controls'] = [ 'text' ];
		$components['megamenu_image']['config']              = array(
			'ImagePicker'    => array(
				'config' => array(
					'label' => __( 'Replace Image', 'thrive-cb' ),
				),
			),
			'ExternalFields' => array(
				'config'  => array(
					'main_dropdown'     => array(
						''         => __( 'Select a source', 'thrive-cb' ),
						'featured' => __( 'Featured image', 'thrive-cb' ),
						'author'   => __( 'Author image', 'thrive-cb' ),
						'user'     => __( 'User image', 'thrive-cb' ),
						'custom'   => __( 'Custom fields', 'thrive-cb' ),
					),
					'key'               => 'image',
					'shortcode_element' => 'img.tve_image',
				),
				'extends' => 'CustomFields',
			),
			'Height'         => array(
				'config'  => array(
					'default' => 'auto',
					'min'     => '20',
					'max'     => '600',
					'label'   => __( 'Size (height)', 'thrive-cb' ),
					'um'      => [ 'px' ],
					'css'     => 'width',
				),
				'extends' => 'Slider',
			),
		);

		$components['image-effects']                                               = $image_components['image-effects'];
		$components['image-effects']['config']['css_suffix']                       = ':not(.tcb-elem-placeholder)';
		$components['image-effects']['config']['ImageOverlaySwitch']['strategy']   = 'pseudo-element';
		$components['image-effects']['config']['ImageOverlaySwitch']['css_suffix'] = ':not(.tcb-elem-placeholder)::after';
		$components['image-effects']['config']['ImageOverlay']['css_suffix']       = ':not(.tcb-elem-placeholder)::after';

		return $components;
	}

	public function own_components() {
		return [];
	}


	/**
	 * Get all available menu item templates
	 *
	 * @return array
	 */
	public function get_templates() {
		return get_option( 'tve_menu_item_templates', [] );
	}
}
