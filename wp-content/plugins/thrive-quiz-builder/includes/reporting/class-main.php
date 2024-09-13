<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-quiz-builder
 */

namespace TQB\Reporting;

use TQB\Reporting\Events\Quiz_Completed;
use TQB\Reporting\ReportApps\Quiz;

class Main {
	public static function init() {
		add_action( 'thrive_reporting_init', [ __CLASS__, 'includes' ] );
		add_action( 'thrive_reporting_register_events', [ __CLASS__, 'register_events' ] );
		add_action( 'thrive_reporting_register_report_apps', [ __CLASS__, 'register_report_apps' ] );
	}

	public static function includes() {
		require_once __DIR__ . '/events/class-quiz-completed.php';

		require_once __DIR__ . '/report-apps/class-quiz.php';

		require_once __DIR__ . '/event-fields/class-quiz-score.php';
		require_once __DIR__ . '/event-fields/class-quiz-id.php';
	}

	public static function register_events() {
		Quiz_Completed::register();
	}

	public static function register_report_apps() {
		Quiz::register();
	}
}
