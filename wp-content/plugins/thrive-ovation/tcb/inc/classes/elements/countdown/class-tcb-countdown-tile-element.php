<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class TCB_Countdown_Tile_Element
 */
class TCB_Countdown_Tile_Element extends TCB_Element_Abstract {

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Countdown Tile', 'thrive-cb' );
	}


	/**
	 * Section element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.tve-countdown-tile';
	}

	public function own_components() {
		$cfg = array( 'css_prefix' => tcb_selection_root( false ), 'css_suffix' => [ ' span', ' .tcb-editable-label', ' .tcb-plain-text' ] );

		return [
			'layout'     => [
				'disabled_controls' => [ 'Display', 'Alignment', '.tve-advanced-controls', 'Width', 'Height' ],
			],
			'typography' => [
				'disabled_controls' => [ '.tve-advanced-controls', '.typography-button-toggle-controls', 'TextAlign' ],
				'config'            => [
					'TextStyle' => $cfg,
					'FontColor' => $cfg,
					'FontFace'  => $cfg,
				],
			],
			'responsive' => [
				'hidden' => true,
			],
			'animation'  => [
				'hidden' => true,
			],
		];
	}


	public function hide() {
		return true;
	}
}
