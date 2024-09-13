<?php

namespace TVA\Reporting\ReportApps;

use TVA\Access\History_Table;
use TVA\Reporting\EventFields\Course_Id;
use TVA\Reporting\EventFields\Member_Id;
use TVA\Reporting\EventFields\Product_Id;
use TVE\Reporting\EventFields\Created;
use TVE\Reporting\EventFields\Event_Field;
use TVE\Reporting\EventFields\User_Id;
use TVE\Reporting\Report_Type;

class Course_Enrollments extends Report_Type {
	public static function key(): string {
		return 'tva_course_enrollments';
	}

	public static function label(): string {
		return __( 'Course Enrollments', 'thrive-apprentice' );
	}

	public static function get_tooltip_text(): string {
		return __( 'Enrolled members', 'thrive-apprentice' ) . ': <strong>{number}</strong>';
	}

	public static function get_filters(): array {
		$filters = [];

		foreach ( [ Created::class, Course_Id::class, User_Id::class, Product_Id::class ] as $field_class ) {
			/** @var $field_class Event_Field */
			$filters[ $field_class::key() ] = [
				'label' => $field_class::get_label(),
				'type'  => $field_class::get_filter_type(),
			];
		}

		return $filters;
	}

	public static function get_table_data( $query ): array {
		$items  = History_Table::get_instance()->get_course_enrollments_table( [ 'where' => $query['filters'] ] );
		$labels = static::get_default_labels();

		$items = array_filter( $items, static function ( $item ) {
			return ! empty( $item[ Course_Id::key() ] );
		} );

		foreach ( $items as &$item ) {
			if ( empty( $labels['date']['values'][ $item['created'] ] ) ) {
				$labels['date']['values'][ $item['created'] ] = Created::format_value( $item['created'] );
			}

			foreach ( [ User_Id::class, Course_Id::class, Product_Id::class ] as $field ) {
				/** @var $field Event_Field */
				if ( empty( $labels[ $field::key() ]['values'][ $item[ $field::key() ] ] ) ) {
					$labels[ $field::key() ]['values'][ $item[ $field::key() ] ] = ( new $field( $item[ $field::key() ] ) )->get_title();
				}
			}

			if ( empty( $labels['source']['values'][ $item['source'] ] ) ) {
				$labels['source']['values'][ $item['source'] ] = ucfirst( $item['source'] );
			}

			$item['count'] = empty( $item['active'] ) ? - 1 : 1;
			$item['date']  = $item['created'];

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

	public static function get_card_data( $query ): array {
		return static::get_chart_data( $query );
	}

	public static function get_chart_data( $query = [] ): array {
		$labels = static::get_default_labels();
		$items  = [];

		$enrollments = History_Table::get_instance()->get_course_enrollments( $query['filters'] );

		$enrollments = array_filter( $enrollments, static function ( $item ) {
			return ! empty( $item[ Course_Id::key() ] );
		} );

		foreach ( $enrollments as $enrollment ) {
			if ( empty( $items[ $enrollment['created'] ] ) ) {
				$items[ $enrollment['created'] ] = [
					'count' => 0,
					'date'  => $enrollment['created'],
				];
			}

			$items[ $enrollment['created'] ]['count'] += (int) $enrollment['status'];

			if ( empty( $labels['date']['values'][ $enrollment['created'] ] ) ) {
				$labels['date']['values'][ $enrollment['created'] ] = Created::format_value( $enrollment['created'] );
			}
		}

		return [
			'items'        => array_values( $items ),
			'labels'       => $labels,
			'tooltip_text' => static::get_tooltip_text(),
			'count'        => array_reduce( $items, static function ( $total, $item ) {
				return $total + $item['count'];
			}, 0 ),
		];
	}

	private static function get_default_labels() {
		return [
			Created::key()    => Created::get_label_structure(),
			Member_Id::key()  => Member_Id::get_label_structure(),
			Course_Id::key()  => Course_Id::get_label_structure(),
			Product_Id::key() => Product_Id::get_label_structure(),
			'source'          => [
				'key'    => 'source',
				'text'   => __( 'Source', 'thrive-apprentice' ),
				'values' => [],
			],
			'status'          => [
				'key'    => 'status',
				'text'   => __( 'Status', 'thrive-apprentice' ),
				'values' => [
					1   => __( 'Added', 'thrive-apprentice' ),
					- 1 => __( 'Removed', 'thrive-apprentice' ),
				],
			],
		];
	}

	/**
	 * @param $query
	 *
	 * @return array
	 */
	public static function get_enrollment_count_per_date( $query ) {
		$enrollment_count_per_date = [];

		foreach ( History_Table::get_instance()->get_course_enrollments_table( [ 'where' => $query['filters'] ] ) as $enrollment ) {
			if ( empty( $enrollment_count_per_date[ $enrollment['created'] ] ) ) {
				$enrollment_count_per_date[ $enrollment['created'] ] = 1;
			} else {
				$enrollment_count_per_date[ $enrollment['created'] ] ++;
			}
		}

		return $enrollment_count_per_date;
	}
}
