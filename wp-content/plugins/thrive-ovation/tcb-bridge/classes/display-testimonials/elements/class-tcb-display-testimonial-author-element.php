<?php

namespace TVO\DisplayTestimonials;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}
if ( ! class_exists( '\TCB_Post_List_Sub_Element_Abstract', false ) ) {
	require_once TVE_TCB_ROOT_PATH . 'inc/classes/elements/class-tcb-post-list-sub-element-abstract.php';
}

/**
 * Class TCB_Testimonial_Author_Element
 *
 */
class TCB_Testimonial_Author_Element extends \TCB_Post_List_Sub_Element_Abstract {
	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Full Name', 'thrive-ovation' );
	}

	/**
	 * WordPress element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.thrive-testimonial-author';
	}

	/**
	 * Return the shortcode tag of the element.
	 *
	 * @return string
	 */
	public function shortcode() {
		return 'tvo_testimonial_author';
	}

	/**
	 * Return icon class needed for display in menu
	 *
	 * @return string
	 */
	public function icon() {
		return 'testimonial-name';
	}

	/**
	 * Component and control config.
	 *
	 * @return array
	 */
	public function own_components() {
		$components = parent::own_components();

		foreach ( $components['typography']['config'] as $control => $config ) {
			if ( in_array( $control, array( 'css_suffix', 'css_prefix' ) ) ) {
				continue;
			}
			/* make sure typography elements also apply on the link inside the tag */
			$components['typography']['config'][ $control ]['css_suffix'] = [ ' p', ' div.tcb-plain-text' ];
		}

		return $components;
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
