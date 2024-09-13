<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

namespace TVA\Architect\Course\Shortcodes;

use TVA_Const;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

require_once __DIR__ . '/class-shortcodes.php';

/**
 * Class Module_Shortcodes
 *
 * @package  TVA\Architect\Course
 * @project  : thrive-apprentice
 */
class Module_Shortcodes extends Shortcodes {
	/**
	 * Returns the Module Post type
	 *
	 * @return array
	 */
	protected function get_post_type() {
		return [ TVA_Const::MODULE_POST_TYPE ];
	}
}
