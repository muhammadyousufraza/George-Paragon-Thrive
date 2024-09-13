<?php
/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 11/6/2017
 * Time: 5:27 PM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}


class TCB_Progress_Icon_Element extends TCB_Icon_Element {

	/**
	 * Section element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.tve-progress-icon';
	}

	/**
	 * @inheritDoc
	 */
	public function expanded_state_config() {
		return true;
	}

	/**
	 * @return bool
	 */
	public function has_hover_state() {
		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function expanded_state_apply_inline() {
		return true;
	}

	/**
	 *
	 * @inheritDoc
	 */
	public function expanded_state_label() {
		return __( 'Completed', 'thrive-cb' );
	}

	public function own_components() {
		$components = parent::own_components();

		$components['icon']['disabled_controls'] = [ 'ToggleURL', 'link' ];

		return $components;
	}


	public function hide() {
		return true;
	}
}
