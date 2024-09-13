<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package TCB2.0
 */


if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

/**
 * Class TCB_Countdownevergreen_Template_Element
 */
class TCB_Countdownevergreen_Template_Element extends TCB_Countdown_Element {

	/**
	 * @return string
	 */
	public function name() {
		return __( 'Countdown Evergreen', 'thrive-cb' );
	}

	/**
	 * HTML layout of the element for when it's dragged in the canvas
	 *
	 * @return string
	 */
	public function html_placeholder( $title = null ) {
		if ( empty( $title ) ) {
			$title = $this->name();
		}

		return tcb_template( 'elements/element-placeholder', array(
			'icon'       => $this->icon(),
			'class'      => 'tcb-ct-placeholder tve-countdown-evergreen',
			'title'      => $title,
			'extra_attr' => 'data-ct="' . $this->tag() . '-0" data-tcb-elem-type="' . $this->tag() . '" data-specific-modal="countdown"',
		), true );
	}

	/**
	 * Get element alternate
	 *
	 * @return string
	 */
	public function alternate() {
		return 'scarcity ';
	}


	/**
	 * Return icon class needed for display in menu
	 *
	 * @return string
	 */
	public function icon() {
		return 'countdown_evergreen';
	}

	/**
	 * Element info
	 *
	 * @return string|string[][]
	 */
	public function info() {
		return [
			'instructions' => [
				'type' => 'help',
				'url'  => 'countdown_evergreen',
				'link' => 'https://help.thrivethemes.com/en/articles/4425793-how-to-use-the-countdown-elements',
			],
		];
	}
}
