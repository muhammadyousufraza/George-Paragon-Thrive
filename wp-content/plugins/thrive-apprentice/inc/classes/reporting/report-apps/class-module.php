<?php

namespace TVA\Reporting\ReportApps;

use TVA\Reporting\Events\Module_Finish;
use TVA\Reporting\Events\Module_Start;
use TVE\Reporting\Report_App;

class Module extends Report_App {
	public static function key(): string {
		return 'tva_module';
	}

	public static function label(): string {
		return __( 'Apprentice Module', 'thrive-apprentice' );
	}

	/**
	 * @return void
	 */
	public static function set_auto_remove_logs() {
		static::remove_logs_on_post_delete( \TVA_Const::MODULE_POST_TYPE, static::get_report_types() );
	}

	public static function get_report_types(): array {
		return [
			Module_Start::class,
			Module_Finish::class,
		];
	}
}
