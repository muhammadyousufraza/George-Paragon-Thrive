<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class TCB_Capture_Testimonials
 */
class TCB_Capture_Testimonials_v2 extends TCB_Lead_Generation_Element {
	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Capture Testimonials', 'thrive-cb' );
	}

	/**
	 * Get element alternate
	 *
	 * @return string
	 */
	public function alternate() {
		return 'thrive, ovation';
	}

	/**
	 * Return icon class needed for display in menu
	 *
	 * @return string
	 */
	public function icon() {
		return 'capture_testimonials';
	}

	/**
	 * WordPress element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.tvo_capture_testimonials_v2';
	}

	public function is_placeholder() {
		return false;
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
			'class'      => 'tcb-ct-placeholder',
			'title'      => $title,
			'extra_attr' => 'data-ct="' . $this->tag() . '-0" data-tcb-elem-type="' . $this->tag() . '" data-tcb-lg-type="' . $this->tag() . '"',
		), true );
	}

	/**
	 * Element category that will be displayed in the sidebar
	 * @return string
	 */
	public function category() {
		return static::get_thrive_integrations_label();
	}

	public function inherit_components_from() {
		return 'lead_generation';
	}

	/**
	 * Element info
	 *
	 * @return string|string[][]
	 */
	public function info() {
		return array(
			'instructions' => array(
				'type' => 'help',
				'url'  => 'capture_testimonials',
				'link' => 'https://help.thrivethemes.com/en/articles/7175657-how-to-use-the-capture-testimonials-element',
			),
		);
	}
}
