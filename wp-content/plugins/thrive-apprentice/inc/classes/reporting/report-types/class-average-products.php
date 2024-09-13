<?php

namespace TVA\Reporting\ReportApps;

use TVA\Access\History_Table;
use TVE\Reporting\EventFields\Created;
use TVE\Reporting\Report_Type;

class Average_Products extends Report_Type {
	public static function key(): string {
		return 'tva_average_products';
	}

	public static function label(): string {
		return __( 'Average products', 'thrive-apprentice' );
	}

	public static function get_filters(): array {
		$filters[ Created::key() ] = [
			'label' => Created::get_label(),
			'type'  => Created::get_filter_type(),
		];

		return $filters;
	}


	public static function get_card_data( $query ): array {
		$average = History_Table::get_instance()->get_average_products( $query['filters'] );

		return [
			'count' => ! empty( $average['average'] ) ? round( $average['average'], 2 ) : 0,
		];
	}
}
