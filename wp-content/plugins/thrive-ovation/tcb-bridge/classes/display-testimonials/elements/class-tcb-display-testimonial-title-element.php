<?php

namespace TVO\DisplayTestimonials;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

if ( ! class_exists( '\TCB_Post_List_Sub_Element_Abstract', false ) ) {
	require_once TVE_TCB_ROOT_PATH . 'inc/classes/elements/class-tcb-post-list-sub-element-abstract.php';
}

/**
 * Class TCB_Testimonial_Title_Element
 *
 */
class TCB_Testimonial_Title_Element extends \TCB_Post_List_Sub_Element_Abstract {

	/**
	 * Hide this.
	 *
	 * @return string
	 */
	public function hide() {
		return false;
	}

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Testimonial Title', 'thrive-ovation' );
	}

	/**
	 * Return icon class needed for display in menu
	 *
	 * @return string
	 */
	public function icon() {
		return 'testimonial-title';
	}

	/**
	 * WordPress element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.thrive-testimonial-title';
	}

	/**
	 * Return the shortcode tag of the element.
	 *
	 * @return string
	 */
	public function shortcode() {
		return 'tvo_testimonial_title';
	}

	/**
	 * Mark this as a sub-element
	 *
	 * @return bool
	 */
	public function is_sub_element() {
		return true;
	}

	/**
	 * Element category that will be displayed in the sidebar
	 *
	 * @return string
	 */
	public function category() {
		return Main::elements_group_label();
	}

	/**
	 * Add/disable controls.
	 *
	 * @return array
	 */
	public function own_components() {
		$components = parent::own_components();

		foreach ( $components['typography']['config'] as $control => $config ) {
			if ( in_array( $control, array( 'css_suffix', 'css_prefix' ) ) ) {
				continue;
			}
			if ( is_array( $config ) ) {
				$components['typography']['config'][ $control ]['css_suffix'] = [' h2'];
			}
		}

		return $components;
	}

	/**
	 * The testimonial title should have hover state
	 *
	 * @return bool
	 */
	public function has_hover_state() {
		return true;
	}
}