<?php

namespace TVA\Reporting\ReportApps;

use TVA\Access\History_Table;
use TVA\Reporting\EventFields\Course_Id;
use TVA\Reporting\EventFields\Member_Id;
use TVE\Reporting\EventFields\Created;
use TVE\Reporting\EventFields\Event_Field;
use TVE\Reporting\EventFields\User_Id;
use TVE\Reporting\Report_Type;

class New_Members extends Report_Type {
	public static function key(): string {
		return 'tva_new_members';
	}

	public static function label(): string {
		return __( 'New members', 'thrive-apprentice' );
	}

	public static function get_filters(): array {
		$filters = [];

		foreach ( [ Created::class, Course_Id::class ] as $field_class ) {
			/** @var $field_class Event_Field */
			$filters[ $field_class::key() ] = [
				'label' => $field_class::get_label(),
				'type'  => $field_class::get_filter_type(),
			];
		}

		return $filters;
	}

	public static function count_data( $query = [] ): int {
		$query['filters']['status'] = [ 1 ];

		return (int) History_Table::get_instance()->get_total_students( $query['filters'] );
	}

	public static function get_card_data( $query = [] ): array {
		return [
			'count' => static::count_data( $query ),
		];
	}

	/**
	 * Return latest enrollments for timeline display
	 *
	 * @param $query
	 *
	 * @return array
	 */
	public static function get_latest_members_data( $query ) {
		$items = [];
		$users = [];

		$items_per_page = empty( $query['items_per_page'] ) ? 10 : (int) $query['items_per_page'];
		$page           = empty( $query['page'] ) ? 1 : (int) $query['page'];

		$enrollments = History_Table::get_instance()->get_course_enrollments_table( [
			'where'    => array_merge( $query['filters'], [
				'status'    => 1,
				'course_id' => 'IS NOT NULL',
			] ),
			'order_by' => [ 'created' => 'DESC' ],
			'limit'    => [
				( $page - 1 ) * $items_per_page,
				$items_per_page,
			],
		] );

		if ( ! empty( $enrollments ) ) {
			foreach ( $enrollments as $item ) {
				$date   = $item['created'];
				$user   = new User_Id( $item['user_id'] );
				$course = new Course_Id( $item['course_id'] );

				$items[] = [
					'user'        => $item['user_id'],
					'description' => ' enrolled in "' . $course->get_title() . '"',
					'date'        => $date,
				];

				if ( empty( $users[ $item['user_id'] ] ) ) {
					$users[ $item['user_id'] ] = [
						'name'    => $user->get_title(),
						'picture' => $user->get_image(),
					];
				}
			}
		}

		return [
			'items'           => $items,
			'users'           => $users,
			'number_of_items' => $items_per_page,
		];
	}

	public static function get_chart_data( $query = [] ): array {
		$labels = static::get_default_labels();
		$items  = [];

		$query['filters']['status'] = [ 1 ];

		foreach ( History_Table::get_instance()->get_students( $query['filters'] ) as $item ) {

			$items[] = [
				'date'  => $item['created'],
				'count' => (int) $item['number'],
			];

			if ( empty( $labels['date']['values'][ $item['created'] ] ) ) {
				$labels['date']['values'][ $item['created'] ] = Created::format_value( $item['created'] );
			}
		}

		return [
			'items'  => $items,
			'labels' => $labels,
		];
	}

	public static function get_table_data( $query ): array {

		$items = History_Table::get_instance()->get_course_enrollments_table( [
			'where'    => array_merge( $query['filters'], [ 'status' => 1 ] ),
			'order_by' => [ 'created' => 'ASC' ],
			'group_by' => [ User_Id::key() ],
		] );

		$labels = static::get_default_labels();

		foreach ( $items as &$item ) {
			if ( empty( $labels['date']['values'][ $item['created'] ] ) ) {
				$labels['date']['values'][ $item['created'] ] = Created::format_value( $item['created'] );
			}

			if ( empty( $labels['user_id']['values'][ $item['user_id'] ] ) ) {
				$labels['user_id']['values'][ $item['user_id'] ] = ( new User_Id( $item['user_id'] ) )->get_title();
			}

			$item['date'] = $item['created'];

			unset( $item['active'], $item['inactive'], $item['created'] );
		}

		$number_of_items = count( $items );

		if ( empty( $query['order_by'] ) ) {
			$query['order_by'] = 'date';
		}

		$items = static::order_items( $items, $query, $labels );
		$items = static::slice_items( $items, $query );

		return [
			'labels'          => $labels,
			'items'           => array_values( $items ),
			'number_of_items' => $number_of_items,
			'images'          => static::get_custom_data_images( $items, [ User_Id::class ] ),
		];
	}

	private static function get_default_labels() {
		return [
			Created::key()   => Created::get_label_structure(),
			Member_Id::key() => Member_Id::get_label_structure(),
		];
	}
}
