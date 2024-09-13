<?php

require_once plugin_dir_path( __FILE__ ) . 'class-tcb-menu-item-element.php';

/**
 * Class TCB_Label_Disabled_Element
 *
 * Non edited label element. For inline text we use typography control
 */
class TCB_Megamenu_Column_Element extends TCB_Element_Abstract {

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Dropdown Column', 'thrive-cb' );
	}

	/**
	 * Section element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.tve-regular.tcb-mega-std li.lvl-1';
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
	public function general_components() {
		$components = parent::general_components();
		unset( $components['animation'], $components['typography'], $components['responsive'], $components['styles-templates'], $components['scroll'] );
		$components['layout']['disabled_controls'] = [
			'margin',
			'.tve-advanced-controls',
			'Width',
			'Height',
			'Alignment',
			'Display',
		];

		$components['megamenu_column'] = array(
			'config' => array(
				'Description' => array(
					'config'  => array(
						'label' => __( 'Show menu description', 'thrive-cb' ),
					),
					'extends' => 'Switch',
				),
			),
		);

		return $components;
	}
}
