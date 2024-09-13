<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

namespace TVA\Architect\Visual_Builder;

use function TVA\TTB\thrive_apprentice_template;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

if ( ! class_exists( '\TVA\Architect\Abstract_Sub_Element', false ) ) {
	require_once \TVA\Architect\Utils::get_integration_path( 'editor-elements/class-abstract-sub-element.php' );
}

/**
 * Class Abstract_Visual_Builder_Element
 *
 * @package TVA\Architect\Visual_Builder
 * @project : thrive-apprentice
 */
abstract class Abstract_Visual_Builder_Element extends \TVA\Architect\Abstract_Sub_Element {

	/**
	 * WordPress element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '';
	}

	/**
	 * For school homepage we need to hide all the visual builder elements
	 *
	 * @return string
	 */
	public function hide() {
		return thrive_apprentice_template()->is_school_homepage();
	}

	/**
	 * This is a sub-element and we want to store this in the config
	 *
	 * @return bool
	 */
	public function is_sub_element() {
		return false;
	}

	/**
	 * The element category that will be displayed in the sidebar
	 *
	 * @return string
	 */
	public function category() {
		return tcb_tva_visual_builder()->get_elements_category();
	}

	/**
	 * @return array
	 */
	public function own_components() {
		return array();
	}
}
