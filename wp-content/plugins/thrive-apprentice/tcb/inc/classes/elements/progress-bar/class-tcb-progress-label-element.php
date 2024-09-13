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


class TCB_Progress_Label_Element extends TCB_Label_Advanced_Element {

	/**
	 * Section element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.tve-progress-label';
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
}
