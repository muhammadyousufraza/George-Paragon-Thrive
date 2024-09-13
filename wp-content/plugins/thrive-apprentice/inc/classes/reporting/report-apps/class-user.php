<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

namespace TVA\Reporting\ReportApps;

use TVE\Reporting\Report_App;

class User extends Report_App {
	public static function key(): string {
		return 'tva_user';
	}

	public static function label(): string {
		return __( 'Apprentice User', 'thrive-apprentice' );
	}

	public static function get_report_types(): array {
		return [
			New_Members::class,
			Top_Members::class,
			Active_Members::class,
		];
	}
}
