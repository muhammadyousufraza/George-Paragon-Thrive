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
 * Class TCB_Element_Abstract
 */
abstract class TCB_Cloud_Template_Element_Abstract extends TCB_Element_Abstract {

	/**
	 * Whether or not this element has cloud templates
	 *
	 * @return bool
	 */
	public function has_cloud_templates() {
		return true;
	}

	/**
	 * All these elements act as placeholders
	 *
	 * @return true
	 */
	public function is_placeholder() {
		return true;
	}

	/**
	 * These elements do not have their own identifiers - they are built from base elements and inherit options from base elements
	 *
	 * @return string
	 */
	public function identifier() {
		return '';
	}

	/**
	 * HTML layout of the element for when it's dragged in the canvas
	 *
	 * @return string
	 */
	protected function html() {
		return $this->html_placeholder( sprintf( __( 'Insert %s', 'thrive-cb' ), $this->name() ) );
	}

	/**
	 * Returns the HTML placeholder for an element (contains a wrapper, and a button with icon + element name)
	 *
	 * @param string $title Optional. Defaults to the name of the current element
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
			'extra_attr' => 'data-ct="' . $this->tag() . '-0" data-tcb-elem-type="' . $this->tag() . '" data-element-name="' . esc_attr( $this->name() ) . '"',
		), true );
	}

	/**
	 * Fetches a list of cloud templates for an element
	 *
	 * @param array $args allows controlling aspects of the method:
	 *                    $nocache - do not use caching (transients)
	 *
	 * @return array|WP_Error
	 */
	public function get_cloud_templates( $args = [] ) {

		if ( ! $this->has_cloud_templates() ) {
			return new WP_Error( 'invalid_element', __( 'Element does not have cloud templates', 'thrive-cb' ) );
		}

		$type = ! empty( $_REQUEST['type'] ) ? sanitize_text_field( $_REQUEST['type'] ) : '';
		/**
		 *Allows changing cloud template tag
		 */
		$tag = apply_filters( 'tcb_cloud_templates_replace_featured_tag', $this->get_template_tag(), $type );

		return tve_get_cloud_content_templates( $tag, apply_filters( 'tcb_get_cloud_templates_default_args', $args ) );
	}

	/**
	 * Get information about a cloud template:
	 * html content
	 * css
	 * custom css
	 * etc
	 *
	 * If the template does not exist, download it from the cloud
	 *
	 * @param string $id   Template id
	 * @param array  $args allow modifying the behavior
	 *
	 * @return array|WP_Error
	 */
	public function get_cloud_template_data( $id, $args = [] ) {
		if ( ! $this->has_cloud_templates() ) {
			return new WP_Error( 'invalid_element', __( 'Element does not have cloud templates', 'thrive-cb' ) );
		}
		$args = wp_parse_args(
			$args,
			array(
				'type' => $this->tag(),
			)
		);

		return tve_get_cloud_template_data(
			$this->get_template_tag(),
			array(
				'id'      => $id,
				'type'    => $args['type'],
				'post_id' => empty( $args['post_id'] ) ? '' : $args['post_id'],
			)
		);
	}

	/**
	 * Allows cloud templates to have a different tag than this element
	 *
	 * @return string
	 */
	public function get_template_tag() {
		return $this->tag();
	}
}
