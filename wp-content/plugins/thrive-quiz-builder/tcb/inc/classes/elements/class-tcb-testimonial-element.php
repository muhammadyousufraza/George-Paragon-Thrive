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
 * Class TCB_Testimonial_Element
 */
class TCB_Testimonial_Element extends TCB_Cloud_Template_Element_Abstract {

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Testimonial', 'thrive-cb' );
	}

	/**
	 * Get element alternate
	 *
	 * @return string
	 */
	public function alternate() {
		return 'template, review';
	}


	/**
	 * Return icon class needed for display in menu
	 *
	 * @return string
	 */
	public function icon() {
		return 'testimonials';
	}

	/**
	 * Element category that will be displayed in the sidebar
	 *
	 * @return string
	 */
	public function category() {
		return static::get_thrive_advanced_label();
	}

	/**
	 * Element info
	 *
	 * @return string|string[][]
	 */
	public function info() {
		return [
			'instructions' => [
				'type' => 'help',
				'url'  => 'testimonial',
				'link' => 'https://help.thrivethemes.com/en/articles/4425805-how-to-add-a-testimonial-to-your-page-with-thrive-architect',
			],
		];
	}
}
