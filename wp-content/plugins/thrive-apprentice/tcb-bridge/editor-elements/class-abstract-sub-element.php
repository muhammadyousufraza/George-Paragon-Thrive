<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

namespace TVA\Architect;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Abstract_Sub_Element
 * Contains general sub-element configurations
 *
 * @package TVA\Architect
 */
abstract class Abstract_Sub_Element extends \TCB_Element_Abstract {

	/**
	 * Abstract_Sub_Element constructor.
	 *
	 * @param string $tag
	 */
	public function __construct( $tag = '' ) {
		parent::__construct( $tag );

		/* add some extra configurations */
		add_filter( 'tcb_element_' . $this->tag() . '_config', array( $this, 'add_config' ) );
	}

	/**
	 * @param array $config
	 *
	 * @return mixed
	 */
	public function add_config( $config ) {
		$config['is_sub_element'] = $this->is_sub_element();

		return $config;
	}

	/**
	 * Check if this is a sub-element
	 *
	 * @return bool
	 */
	public function is_sub_element() {
		return false;
	}

	/**
	 * All sub-elements are hidden
	 *
	 * @return bool
	 */
	public function hide() {
		return true;
	}

	/**
	 * @return array
	 */
	public function own_components() {
		return array();
	}
}
