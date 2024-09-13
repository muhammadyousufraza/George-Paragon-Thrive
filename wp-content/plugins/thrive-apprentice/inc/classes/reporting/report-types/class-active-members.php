<?php

namespace TVA\Reporting\ReportApps;

use TVA\Reporting\Events\Course_Finish;
use TVA\Reporting\Events\Course_Start;
use TVA\Reporting\Events\Lesson_Complete;
use TVA\Reporting\Events\Lesson_Start;
use TVE\Reporting\EventFields\Created;
use TVE\Reporting\EventFields\User_Id;
use TVE\Reporting\Logs;
use TVE\Reporting\Report_Type;

class Active_Members extends Report_Type {
	public static function key(): string {
		return 'tva_active_members';
	}

	public static function label(): string {
		return __( 'Active members', 'thrive-apprentice' );
	}

	/**
	 * Return an array of all filters supported by fields
	 *
	 * @return array
	 */
	public static function get_filters(): array {
		$filters[ Created::key() ] = [
			'label' => Created::get_label(),
			'type'  => Created::get_filter_type(),
		];

		return $filters;
	}

	public static function get_card_data( $query ): array {
		return [
			'count' => Logs::get_instance()->set_query( [
				'count'      => User_Id::key(),
				'event_type' => [
					Course_Finish::key(),
					Course_Start::key(),
					Lesson_Complete::key(),
					Lesson_Start::key(),
				],
				'group_by'   => User_Id::key(),
				'filters'    => [
					'created' => $query['filters']['date'],
				],
			] )->count_results(),
		];
	}
}
