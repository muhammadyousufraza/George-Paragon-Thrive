<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

namespace TCB\UserTemplates;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Main {
	const TRAITS_DIR = __DIR__ . '/traits';

	public static function init() {
		Template::register_post_type();
		Category::register_taxonomy();

		Category::maybe_migrate();
	}

	public static function includes() {
		require_once static::TRAITS_DIR . '/trait-has-preview.php';
		require_once static::TRAITS_DIR . '/trait-has-post-type.php';
		require_once static::TRAITS_DIR . '/trait-has-taxonomy.php';

		require_once __DIR__ . '/class-template.php';
		require_once __DIR__ . '/class-category.php';
		require_once __DIR__ . '/class-migrator.php';
	}
}
