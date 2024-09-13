<?php

namespace TVO\DisplayTestimonials;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

if ( ! class_exists( '\TCB_Post_List_Sub_Element_Abstract', false ) ) {
	require_once TVE_TCB_ROOT_PATH . 'inc/classes/elements/class-tcb-post-list-sub-element-abstract.php';
}

/**
 * Class TCB_Testimonial_image_Element
 *
 */
class TCB_Testimonial_Image_Element extends \TCB_Post_List_Sub_Element_Abstract {
	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Testimonial Image', 'thrive-ovation' );
	}

	public function identifier() {
		return '.thrive-testimonial-image';
	}

	/**
	 * Return the icon class needed for display in menu
	 *
	 * @return string
	 */
	public function icon() {
		return 'testimonial-image';
	}

	/**
	 * Element category that will be displayed in the sidebar
	 *
	 * @return string
	 */
	public function category() {
		return Main::elements_group_label();
	}
}
