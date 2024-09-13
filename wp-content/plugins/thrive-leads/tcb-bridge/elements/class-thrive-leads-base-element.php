<?php

class Thrive_Leads_Base_Element extends TCB_Element_Abstract {

	protected $_tag = 'tl-design';

	/**
	 * Hide from menu
	 *
	 * @return bool
	 */
	public function hide() {
		return true;
	}

	public function identifier() {
		return '.thrv-leads-form-box,.thrv-ribbon,.thrv-leads-screen-filler,.thrv-greedy-ribbon,.thrv-leads-widget';
	}

	public function name() {
		return __( 'Thrive Leads Element', 'thrive-leads' );
	}

	public function own_components() {
		return array(
			'typography'       => array(
				'hidden' => true,
			),
			'layout'           => array(
				'disabled_controls' => array(
					'Width',
					'Height',
					'Display',
					'Alignment',
					'.tve-advanced-controls',
					'hr',
				),
				'config'            => array(
					'Width' => array(
						'important' => true,
					),
				),
			),
			'borders'          => array(
				'config' => array(
					'Borders' => array(
						'important' => true,
					),
					'Corners' => array(
						'important' => true,
					),
				),
			),
			'animation'        => array(
				'hidden' => true,
			),
			'shadow'           => array(
				'config' => array(
					'default_shadow' => 'none',
					'important'      => true,
				),
			),
			'styles-templates' => array(
				'hidden' => true,
			),
		);
	}
}
