<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

if ( ! class_exists( 'Thrive_Theme_Cloud_Element_Abstract' ) ) {
	require_once ARCHITECT_INTEGRATION_PATH . '/classes/class-thrive-theme-cloud-element-abstract.php';
}


/**
 * Class Thrive_Comments_Element
 */
class Thrive_Comments_Element extends Thrive_Theme_Cloud_Element_Abstract {
	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		$comments_section_name = apply_filters( 'thrive_theme_comments_section_name', 'Comments Section' );

		return __( $comments_section_name, 'thrive-theme' );
	}

	/**
	 * Return icon class needed for display in menu
	 *
	 * @return string
	 */
	public function icon() {
		return 'comments';
	}

	/**
	 * WordPress element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '#comments';
	}

	/**
	 * Hide this for non-singular templates
	 *
	 * @return bool
	 */
	public function hide() {
		return apply_filters( 'thrive_theme_hide_comments_element', ! thrive_template()->is_singular() );
	}

	/**
	 * Element HTML
	 *
	 * @return string
	 */
	public function html() {
		return TCB_Utils::wrap_content( '', 'div', 'comments' );
	}

	/**
	 * This element is a shortcode
	 *
	 * @return bool
	 */
	public function is_shortcode() {
		return true;
	}

	/**
	 * Return the shortcode tag of the element.
	 *
	 * @return string
	 */
	public static function shortcode() {
		return 'thrive_comments';
	}

	/**
	 * If an element has selector or a data-css will be generated
	 *
	 * @return bool
	 */
	public function has_selector() {
		return true;
	}

	public function own_components() {
		return [
			'thrive_comments'  => [],
			'typography'       => [ 'hidden' => true ],
			'background'       => [ 'hidden' => true ],
			'borders'          => [ 'hidden' => true ],
			'animation'        => [ 'hidden' => true ],
			'shadow'           => [ 'hidden' => true ],
			'styles-templates' => [ 'hidden' => true ],
		];
	}
}

return new Thrive_Comments_Element( 'thrive_comments' );
