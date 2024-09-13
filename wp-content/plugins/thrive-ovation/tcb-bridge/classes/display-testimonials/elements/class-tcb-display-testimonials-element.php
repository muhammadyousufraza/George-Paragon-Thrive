<?php

namespace TVO\DisplayTestimonials;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

if ( ! class_exists( '\TCB_Post_List_Element', false ) ) {
	require_once TVE_TCB_ROOT_PATH . 'inc/classes/elements/class-tcb-post-list-element.php';
}

/**
 * Class TCB_Display_Testimonials
 */
class TCB_Display_Testimonials extends \TCB_Post_List_Element {
	//TCB_Element_Abstract \TCB_Post_List_Element
	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Display Testimonials', 'thrive-ovation' );
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
		return 'display_testimonials';
	}

	/**
	 * WordPress element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.thrive-display-testimonials';
	}

	/**
	 * Component and control config
	 *
	 * @return array
	 */
	public function own_components() {
		$components = parent::own_components();

		/* re-use the post-list component */
		$components['display_testimonials'] = $components['post_list'];

		unset( $components['post_list'] );
		/* Unset unused controls */
		unset( $components['display_testimonials']['config']['Linker'] );

		$components['display_testimonials']['config']['MessageColor'] = array(
			'config'  => array(
				'default' => '#999999',
				'label'   => 'Color',
				'options' => array(
					'output' => 'object',
				),
			),
			'extends' => 'ColorPicker',
		);

		return $components;
	}

	public function html_placeholder( $title = null ) {
		$attr = [
			'ct'             => $this->tag() . '-default',
			'query'          => Main::get_default_query( 'string' ),
			'tcb-elem-type'  => $this->tag(),
			'element-name'   => esc_attr( $this->name() ),
			'specific-modal' => 'display-testimonials',
		];

		$extra_attr = '';
		foreach ( $attr as $key => $value ) {
			$extra_attr .= " data-$key=\"$value\"";
		}

		return tcb_template( 'elements/element-placeholder', array(
			'icon'       => parent::icon(),
			'class'      => 'tcb-ct-placeholder',
			'title'      => $this->name(),
			'extra_attr' => $extra_attr,
		), true );
	}

	/**
	 * Element category that will be displayed in the sidebar
	 *
	 * @return string
	 */
	public function category() {
		return static::get_thrive_integrations_label();
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
				'url'  => 'display_testimonials',
				'link' => 'https://help.thrivethemes.com/en/articles/7172144-using-the-display-testimonial-element',
			),
		);
	}
}
