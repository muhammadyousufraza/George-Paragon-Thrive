<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Use this class just in case we don't have all the requirements
 * Class Thrive_Noop
 */
class Thrive_Noop {

	/**
	 * Thrive_Noop constructor.
	 * Instantiates the bare miminum needed things for a default theme
	 */
	public function __construct() {
		/**
		 * Enqeueue the style.css file
		 */
		add_action( 'wp_enqueue_scripts', function () {
			wp_enqueue_style( 'thrive', get_stylesheet_uri(), [], THEME_VERSION );
		} );
	}

	public function render() {
		/* “To do nothing is the way to be nothing.” ― Nathaniel Hawthorne */

		/* Instead of doing nothing, let's output a simple theme template */
		get_template_part( 'inc/templates/noop/index' );
	}
}

global $ttb_noop_theme;
$ttb_noop_theme = new Thrive_Noop();

/**
 * Do nothing!!!
 *
 * @return Thrive_Noop
 */
function thrive_template() {
	global $ttb_noop_theme;

	return $ttb_noop_theme;
}

