<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

if ( ! class_exists( 'Thrive_Theme_Cloud_Element_Abstract' ) ) {
	require_once ARCHITECT_INTEGRATION_PATH . '/classes/class-thrive-theme-cloud-element-abstract.php';
}

/**
 * Class Thrive_Author_Box_Element
 */
class Thrive_Author_Box_Element extends Thrive_Theme_Cloud_Element_Abstract {

	/**
	 * Element name
	 *
	 * @return string
	 */
	public function name() {
		return __( 'About the Author', 'thrive-theme' );
	}

	/**
	 * @return string
	 */
	public function icon() {
		return 'author-box';
	}

	/**
	 * @return string
	 */
	public function identifier() {
		return '.thrive_author_box';
	}

	/**
	 * @return array
	 */
	public function own_components() {
		return [
			'thrive_author_box' => [
				'config' => [],
			],
			'background'        => [],
			'borders'           => [],
			'layout'            => [],
			'shadow'            => [],
			'animation'         => [ 'hidden' => true ],
			'responsive'        => [ 'hidden' => true ],
			'styles-templates'  => [ 'hidden' => true ],
			'typography'        => [],
		];
	}

	/**
	 * @return string
	 */
	public function html() {
		return Thrive_Utils::get_element( 'author-box-element', [], false );
	}

	/**
	 * Display the element only on single
	 *
	 * @return bool
	 */
	public function hide() {
		return apply_filters( 'thrive_theme_hide_author_box_element', ! thrive_template()->is_singular() );
	}
}

return new Thrive_Author_Box_Element( 'thrive_author_box' );
