<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

namespace TCB\Integrations\Automator;

use Thrive\Automator\Items\App;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Architect_App extends App {

	public static function get_id() {
		return 'architect';
	}

	public static function get_name() {
		return 'Architect';
	}

	public static function get_description() {
		return 'Architect related items';
	}

	public static function get_logo() {
		return 'tap-architect-logo';
	}

	public static function has_access() {
		return true;
	}
}
