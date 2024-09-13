<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-quiz-builder
 */

namespace TQB\Reporting\ReportApps;

use TQB\Reporting\Events\Quiz_Completed;
use TQB\Reporting\ReportTypes\Quiz_Count;
use TQB\Reporting\ReportTypes\Quiz_Started;
use TVE\Reporting\Report_App;

class Quiz extends Report_App {
	public static function key(): string {
		return 'tqb_quiz';
	}

	public static function label(): string {
		return 'Thrive Quiz';
	}

	public static function get_report_types(): array {
		return [
			Quiz_Completed::class,
		];
	}
}
