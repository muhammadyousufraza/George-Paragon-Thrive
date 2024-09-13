<?php
/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 4/10/2017
 * Time: 10:15 AM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class TCB_Wordpress_Element
 */
class TCB_Wordpress_Element extends TCB_Element_Abstract {
	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'WordPress Content', 'thrive-cb' );
	}

	/**
	 * Get element alternate
	 *
	 * @return string
	 */
	public function alternate() {
		return 'wp';
	}


	/**
	 * Return icon class needed for display in menu
	 *
	 * @return string
	 */
	public function icon() {
		return 'wordpress';
	}

	/**
	 * WordPress element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.tve_wp_shortcode'; // For backwards compatibility
	}

	/**
	 * Component and control config
	 *
	 * @return array
	 */
	public function own_components() {
		return [
			'wordpress'  => [
				'config' => [],
			],
			'typography' => [ 'hidden' => true ],
			'borders'    => [ 'hidden' => true ],
			'animation'  => [ 'hidden' => true ],
			'background' => [ 'hidden' => true ],
			'shadow'     => [ 'hidden' => true ],
			'layout'     => [
				'disabled_controls' => [],
			],
		];
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
				'url'  => 'wordpress_content',
				'link' => 'https://help.thrivethemes.com/en/articles/4425781-how-to-use-the-wordpress-content-element',
			],
		];
	}
}
