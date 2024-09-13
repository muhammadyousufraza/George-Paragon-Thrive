<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package TCB2.0
 */


if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}
require_once __DIR__ . '/../class-tcb-lead-generation-element.php';

/**
 * Class TCB_Lead_Generation_Element
 */
class TCB_Lead_Generation_Contact_Form_Element extends TCB_Lead_Generation_Element {

	/**
	 * @return string
	 */
	public function name() {
		return __( 'Contact Form', 'thrive-cb' );
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
			'extra_attr' => 'data-ct="' . $this->tag() . '-0" data-tcb-elem-type="' . $this->tag() . '" data-tcb-lg-type="contact_form" data-specific-modal="lead-generation"',
		), true );
	}

	/**
	 * Get element alternate
	 *
	 * @return string
	 */
	public function alternate() {
		return 'contact-form';
	}

	/**
	 * @return string
	 */
	public function icon() {
		return 'contact_form';
	}
}
