<?php
/**
 * Thrive Themes - https://thrivethemes.com
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

/**
 * Base class for extending the WP_REST_Controller
 */
class TVD_REST_Controller {

	public static $version = 1;
	public static $namespace = 'tss/v';
	public $base = '';

	/**
	 * Register the routes for the objects of the controller.
	 */
	public function register_routes() {}
}