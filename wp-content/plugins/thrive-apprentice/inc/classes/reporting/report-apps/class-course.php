<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

namespace TVA\Reporting\ReportApps;

use TVA\Reporting\Events\Course_Finish;
use TVA\Reporting\Events\Course_Start;
use TVE\Reporting\Report_App;

class Course extends Report_App {
	public static function key(): string {
		return 'tva_course';
	}

	public static function label(): string {
		return __( 'Apprentice Course', 'thrive-apprentice' );
	}

	/**
	 * @return void
	 */
	public static function set_auto_remove_logs() {
		add_action( 'tva_course_after_delete', static function ( $course_id ) {
			static::remove_report_logs( $course_id, [
				Course_Start::class,
				Course_Finish::class,
			] );
		} );
	}

	public static function get_report_types(): array {
		return [
			Course_Start::class,
			Course_Finish::class,
			Popular_Courses::class,
			Course_Progress::class,
			Engagement_Type::class,
			All_Engagements::class,
			Course_Enrollments::class,
		];
	}
}
