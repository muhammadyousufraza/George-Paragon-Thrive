<?php

namespace TVA\Reporting\ReportApps;

use TVA\Reporting\Events\Product_Access_Received;
use TVA\Reporting\Events\Product_Purchase;
use TVE\Reporting\Report_App;

class Product extends Report_App {
	public static function key(): string {
		return 'tva_product';
	}

	public static function label(): string {
		return __( 'Apprentice Product', 'thrive-apprentice' );
	}

	public static function get_report_types(): array {
		return [
			Product_Purchase::class,
			Average_Products::class,
		];
	}
}
