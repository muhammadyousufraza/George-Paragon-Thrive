<?php

class Thrive_Leads_Lightbox_Element extends Thrive_Leads_Base_Element {

	protected $_tag = 'tl-lightbox';

	public function identifier() {
		return '.tve_p_lb_content';
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
