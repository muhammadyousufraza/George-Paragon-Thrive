<?php

namespace TVA\Reporting\ReportApps;

use TVA\Reporting\Events\Drip_Unlocked_For_User;
use TVE\Reporting\Report_App;

class Drip extends Report_App {
	public static function key(): string {
		return 'tva_drip';
	}

	public static function label(): string {
		return __( 'Drip', 'thrive-apprentice' );
	}

	public static function get_report_types(): array {
		return [
			Drip_Unlocked_For_User::class,
		];
	}
}
