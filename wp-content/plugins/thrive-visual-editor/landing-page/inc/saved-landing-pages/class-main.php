<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

namespace TCB\SavedLandingPages;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Main {
	const TRAITS_DIR = __DIR__ . '/traits';

	public static function init() {
		static::includes();

		Saved_Lp::register_post_type();
	}

	public static function includes() {
		require_once static::TRAITS_DIR . '/trait-has-post-type.php';

		require_once __DIR__ . '/class-saved-lp.php';
		require_once __DIR__ . '/class-migrator.php';
	}
}