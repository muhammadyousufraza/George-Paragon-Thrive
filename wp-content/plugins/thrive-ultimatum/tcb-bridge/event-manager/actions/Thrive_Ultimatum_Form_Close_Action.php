<?php

if ( ! class_exists( 'Thrive_Ultimatum_Form_Close_Action' ) ) {
	class Thrive_Ultimatum_Form_Close_Action extends TCB_Event_Action_Abstract {

		protected $key = 'tve_ult_close';

		public function getName() {
			return __( 'Close ultimatum', 'thrive-ult');
		}

		public function getJsActionCallback() {
			return 'function(t, a, c){TVE_Ult.hide_design(); return false;}';
		}

		public function get_options() {
			return array(
				'labels'  => $this->getName(),
				'trigger' => 'click',
			);
		}
	}
}
