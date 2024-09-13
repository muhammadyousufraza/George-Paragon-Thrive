<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

namespace TVA\Reporting\ReportApps;

use TVA\Reporting\Events\All_Free_Lessons_Completed;
use TVA\Reporting\Events\Free_Lesson_Complete;
use TVA\Reporting\Events\Lesson_Complete;
use TVA\Reporting\Events\Lesson_Start;
use TVA\Reporting\Events\Lesson_Unlocked_By_Quiz;
use TVE\Reporting\Report_App;

class Lesson extends Report_App {
	public static function key(): string {
		return 'tva_lesson';
	}

	public static function label(): string {
		return __( 'Apprentice Lesson', 'thrive-apprentice' );
	}

	/**
	 * @return void
	 */
	public static function set_auto_remove_logs() {
		static::remove_logs_on_post_delete( \TVA_Const::LESSON_POST_TYPE, static::get_report_types() );
	}

	public static function get_report_types(): array {
		return [
			Lesson_Start::class,
			Lesson_Complete::class,
			Free_Lesson_Complete::class,
			All_Free_Lessons_Completed::class,
			Lesson_Unlocked_By_Quiz::class,
		];
	}
}
