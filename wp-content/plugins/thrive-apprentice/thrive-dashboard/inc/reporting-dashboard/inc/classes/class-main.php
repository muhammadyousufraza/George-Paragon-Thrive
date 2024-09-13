<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */

namespace TVE\Reporting;

class Main {
	const REST_NAMESPACE = 'trd/v1';

	const SCRIPTS_HANDLE = 'thrive-reporting';

	public static function init() {
		static::includes();

		Hooks::register();

		User_Events::add_hooks();

		Privacy::init();
	}

	public static function includes() {
		require_once dirname( __DIR__ ) . '/traits/trait-event.php';
		require_once dirname( __DIR__ ) . '/traits/trait-report.php';

		require_once __DIR__ . '/class-hooks.php';
		require_once __DIR__ . '/class-logs.php';
		require_once __DIR__ . '/class-store.php';
		require_once __DIR__ . '/class-privacy.php';
		require_once __DIR__ . '/class-user-events.php';

		require_once __DIR__ . '/abstract/class-shortcode.php';

		require_once __DIR__ . '/abstract/class-event.php';
		require_once __DIR__ . '/abstract/class-event-field.php';

		require_once __DIR__ . '/abstract/class-report-app.php';
		require_once __DIR__ . '/abstract/class-report-type.php';

		require_once __DIR__ . '/event-fields/class-event-type.php';
		require_once __DIR__ . '/event-fields/class-created.php';
		require_once __DIR__ . '/event-fields/class-item-id.php';
		require_once __DIR__ . '/event-fields/class-post-id.php';
		require_once __DIR__ . '/event-fields/class-user-id.php';
	}

	public static function add_shortcodes() {
		/* life beats the movie and card depends on chart so we read them in reverse so we won't have conflicts */
		foreach ( array_reverse( glob( __DIR__ . '/shortcodes/class-*.php' ) ) as $file_path ) {
			require_once $file_path;

			preg_match( '/shortcodes\/class-([^.]*)\.php/', $file_path, $matches );

			$class_name = empty( $matches[1] ) ? null : 'TVE\Reporting\Shortcodes\\' . ucfirst( $matches[1] );
			if ( class_exists( $class_name, false ) && method_exists( $class_name, 'add' ) ) {
				$class_name::add();
			}
		}
	}
}
