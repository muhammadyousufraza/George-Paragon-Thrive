<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/* Class Thrive_Post_Meta_Element */

class Thrive_Post_Meta_Element extends Thrive_Theme_Element_Abstract {

	/**
	 * Thrive_Post_Meta_Element constructor.
	 *
	 * @param string $tag
	 */
	public function __construct( $tag = '' ) {
		parent::__construct( $tag );

		add_filter( 'tcb_element_' . $this->tag() . '_config', [ $this, 'add_config' ] );
	}

	/**
	 * @param $config
	 *
	 * @return mixed
	 */
	public function add_config( $config ) {
		$config['is_sub_element'] = $this->is_sub_element();

		return $config;
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
	 * Element name
	 * @return string
	 */
	public function name() {
		return __( 'Post Meta', 'thrive-theme' );
	}

	/**
	 * @return string
	 */
	public function icon() {
		return 'post-meta';
	}

	/**
	 * @return string
	 */
	public function identifier() {
		return '.no_class_is_needed';
	}

	/**
	 * @return string
	 */
	public function html() {
		return Thrive_Utils::get_element( 'meta-element', [], false );
	}

	/**
	 * @return string
	 */
	public function category() {
		return TCB_Post_List::elements_group_label();
	}
}

return new Thrive_Post_Meta_Element( 'thrive_post_meta' );
