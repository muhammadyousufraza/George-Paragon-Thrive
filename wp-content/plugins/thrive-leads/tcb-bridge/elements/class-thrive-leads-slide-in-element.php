<?php

class Thrive_Leads_Slide_In_Element extends Thrive_Leads_Base_Element {

	protected $_tag = 'tl-slide-in';

	public function identifier() {
		return '.thrv-leads-slide-in';
	}

	public function own_components() {
		$config                                = parent::own_components();
		$config['layout']['disabled_controls'] = array(
			'Alignment',
			'Display',
			'.tve-advanced-controls',
		);

		return $config;
	}
}
