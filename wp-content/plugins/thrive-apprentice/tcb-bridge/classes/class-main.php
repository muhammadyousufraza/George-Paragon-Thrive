<?php

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

namespace TVA\Architect;

use TVA\Architect\Course_List\Dropdown;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Architect Integration
 * Main entrypoint
 *
 * Loaded via Autoload
 */
class Main {

	/**
	 * @return void
	 */
	public static function init() {
		/**
		 * Dynamic Video Shortcode
		 */
		DynamicVideo\Main::instance();

		/**
		 * Lesson List Element
		 */
		Course\Main::get_instance();

		/**
		 * Resource Element
		 */
		Resources\Main::get_instance();

		/**
		 * Course List Element
		 */
		Course_List\Main::get_instance();

		/**
		 * Course List Dropdown Element
		 */
		Dropdown::get_instance();

		/**
		 * Dynamic Actions functionality - Template + Content functionality
		 */
		Dynamic_Actions\Main::get_instance();

		/**
		 * Visual Builder functionality - Templating
		 */
		Visual_Builder\Main::get_instance();

		/**
		 * Certificate Functionality
		 */
		Certificate\Main::init();

		/**
		 * Protected files functionality
		 */
		Protected_Files\Main::init();

		/**
		 * Assessment functionality
		 */
		Assessment\Main::init();

		/**
		 * Buy Now functionality
		 */
		Buy_now\Main::init();
	}

	/**
	 * @return void
	 */
	public static function load_conditional_display() {
		if ( class_exists( '\TCB\ConditionalDisplay\Main', false ) ) {
			ConditionalDisplay\Main::init();
		}
	}
}
