<?php

class TQB_Export_Step_Clearexport extends TQB_Export_Step_Abstract {

	protected function _prepare_data() {
	}

	public function execute() {
		return tqb_empty_folder( $this->get_path() );
	}
}
