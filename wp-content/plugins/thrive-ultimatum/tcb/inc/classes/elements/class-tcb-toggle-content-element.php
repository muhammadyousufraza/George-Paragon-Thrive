<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package TCB2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

require_once 'class-tcb-contentbox-element.php';

class TCB_Toggle_Content_Element extends TCB_ContentBox_Element {
	/**
	 * @return string
	 */
	public function name() {
		return __( 'Toggle Content', 'thrive-cb' );
	}

	/**
	 * @return string
	 */
	public function identifier() {
		return '.thrv_toggle_content';
	}


	public function hide() {
		return true;
	}

	/**
	 * Inherit all the controls from the Image Element, then remove what we don't need and add our own.
	 *
	 * @return array
	 */
	public function own_components() {
		$components = parent::own_components();

		unset( $components['contentbox'] );
		unset( $components['shared-styles'] );
		$components['layout']['disabled_controls'] = [
			'margin',
			'.tve-advanced-controls',
			'Height',
			'Width',
			'Alignment',
			'Display',
		];
		$components['borders']                     = [
			'config' => [
				'Borders' => [
					'important' => true,
				],
				'Corners' => [
					'important' => true,
				],
			],
		];
		$prefix_config                             = array( 'css_prefix' => tcb_selection_root( false ) . ' .thrv_toggle_content ' );
		$components['typography']                  = [
			'disabled_controls' => [],
			'config'            => [
				'to'         => '.tve-cb',
				'FontSize'   => $prefix_config,
				'FontColor'  => $prefix_config,
				'LineHeight' => $prefix_config,
				'FontFace'   => $prefix_config,
			],
		];
		unset( $components['scroll'] );

		return $components;
	}

	public function has_hover_state() {
		return false;
	}
}
