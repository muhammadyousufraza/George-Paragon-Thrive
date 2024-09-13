<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

namespace TVA\Architect\Visual_Builder\Elements;

use TVA\Architect\Visual_Builder\Abstract_Visual_Builder_Element;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Visual_Builder_Post_Summary
 *
 * @package  TVA\Architect\Visual_Builder\Elements
 * @project  : thrive-apprentice
 */
class Visual_Builder_Post_Summary extends Abstract_Visual_Builder_Element {
	/**
	 * @var string
	 */
	protected $_tag = 'visual_builder_post_summary';

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Summary', 'thrive-apprentice' );
	}

	/**
	 * Return the icon class needed for display in menu
	 *
	 * @return string
	 */
	public function icon() {
		return 'post-read-more';
	}
}

return new Visual_Builder_Post_Summary();
