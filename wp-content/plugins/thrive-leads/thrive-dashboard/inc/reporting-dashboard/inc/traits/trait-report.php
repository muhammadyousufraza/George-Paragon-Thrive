<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */

namespace TVE\Reporting\Traits;

use TVE\Reporting\EventFields\Created;
use TVE\Reporting\EventFields\Event_Field;
use TVE\Reporting\EventFields\Item_Id;
use TVE\Reporting\EventFields\User_Id;
use TVE\Reporting\Logs;

trait Report {

	/**
	 * Who is allowed to use rest routes for reports
	 *
	 * @return bool
	 */
	public static function permission_callback(): bool {
		return current_user_can( TVE_DASH_CAPABILITY );
	}

	/**
	 * ways we can display data on the reporting dashboard
	 *
	 * @return string[]
	 */
	public static function get_display_types(): array {
		return [
			'card',
			'table',
			'column_chart',
			'line_chart',
			'pie_chart',
		];
	}

	/**
	 * Return an array of key-value for all the ways we can group this report
	 *
	 * @return string[]
	 */
	public static function get_group_by(): array {
		$group_by = [];

		foreach ( static::get_registered_fields() as $field ) {
			/** @var $field Event_Field */
			if ( $field::can_group_by() ) {
				$group_by[ $field::key() ] = $field::get_label();
			}
		}

		return $group_by;
	}

	/**
	 * Return an array of all filters supported by fields
	 *
	 * @return array
	 */
	public static function get_filters(): array {
		$filters = [];

		foreach ( static::get_registered_fields() as $field ) {
			/** @var $field Event_Field */
			if ( $field::can_filter_by() ) {
				$filters[ $field::key() ] = [
					'label' => $field::get_label(),
					'type'  => $field::get_filter_type(),
				];
			}
		}

		return $filters;
	}

	/**
	 * Register REST routes for each field to return its values
	 *
	 * @param $report_type_route
	 */
	public static function register_filter_routes( $report_type_route ) {
		if ( ! method_exists( static::class, 'get_registered_fields' ) ) {
			return;
		}

		foreach ( static::get_registered_fields() as $field ) {
			/** @var $field Event_Field */
			if ( $field::can_filter_by() ) {
				$field::register_options_route( $report_type_route, static::class );
			}
		}
	}

	/**
	 * Prepare query, ensure default values, filter only allowed fields
	 *
	 * @param $query
	 *
	 * @return array
	 */
	protected static function parse_query( $query ): array {
		global $reports_query;

		$reports_query = array_merge( [
			'event_type'     => static::key(),
			'count'          => 'id',
			'page'           => 0,
			'items_per_page' => 0,
			'group_by'       => [],
		], $query );

		if ( empty( $reports_query['date_format'] ) ) {
			$reports_query['date_format'] = Logs::get_instance()->get_date_format( $query['filters']['date']['from'] ?? 0, $query['filters']['date']['to'] ?? 0 );
		}

		if ( is_string( $reports_query['group_by'] ) ) {
			$reports_query['group_by'] = empty( $reports_query['group_by'] ) ? [] : explode( ',', $reports_query['group_by'] );
		}

		/* make sure we use the db key when we group/select */
		$reports_query['group_by'] = array_map( static function ( $field_key ) {
			if ( method_exists( static::class, 'get_registered_field' ) ) {
				$field = static::get_registered_field( trim( $field_key ) );

				if ( method_exists( $field, 'key' ) ) {
					$field_key = static::get_registered_field( trim( $field_key ) )::key();
				}
			}

			return $field_key;
		}, $reports_query['group_by'] );

		if ( ! empty( $reports_query['order_by'] ) && method_exists( static::class, 'get_field_table_col' ) ) {
			$reports_query['order_by'] = static::get_field_table_col( $reports_query['order_by'] );
		}

		/* we only need to select the fields we group by and the timestamp */
		$reports_query['fields'] = static::get_query_select_fields(
			empty( $reports_query['fields'] ) ?
				array_unique( array_merge( [ 'date' ], $reports_query['group_by'] ) ) :
				$reports_query['fields']
		);

		/* the fields should be db-compatible */
		if ( ! empty( $reports_query['filters'] ) && is_array( $reports_query['filters'] ) ) {
			$parsed_filters  = [];
			$allowed_filters = static::get_filters();

			foreach ( $reports_query['filters'] as $key => $value ) {
				if ( method_exists( static::class, 'get_field_table_col' ) ) {
					$db_col_key = static::get_field_table_col( $key );

					if ( isset( $allowed_filters[ $key ] ) || isset( $allowed_filters[ $db_col_key ] ) ) {
						$parsed_filters[ $db_col_key ] = $value;
					}
				}
			}

			$reports_query['filters'] = $parsed_filters;
		}

		return $reports_query;
	}

	/**
	 * Array of fields to select from the logs table
	 *
	 * @return string[]
	 */
	public static function get_query_select_fields( $fields = [] ): array {
		$selected_fields = [];

		if ( method_exists( static::class, 'get_registered_field' ) ) {
			foreach ( $fields as $field ) {
				/** @var $event_field Event_Field */
				$event_field = static::get_registered_field( $field );

				if ( $event_field === null ) {
					/* add raw field */
					$selected_fields[] = $field;
				} else {
					/* get select query from field class */
					$selected_fields[] = $event_field::get_query_select_field( static::get_field_table_col( $event_field::key() ) );
				}
			}
		} else {
			$selected_fields = $fields;
		}

		return $selected_fields;
	}

	/**
	 * only count the number of items
	 *
	 * @param $query
	 *
	 * @return int
	 */
	public static function count_data( $query = [] ): int {
		return (int) Logs::get_instance()->set_query( static::parse_query( $query ) )->count_results();
	}

	/**
	 * General function for getting data. other functions use this one as base, but can be overwritten
	 *
	 * @param $query
	 *
	 * @return array
	 */
	public static function get_data( $query = [] ): array {
		$query = static::parse_query( $query );

		$items = Logs::get_instance()->set_query( $query )->get_results();

		$labels = static::get_data_labels( $items );

		return [
			'labels' => $labels,
			'items'  => $items,
		];
	}

	/**
	 * Get data for chart - has an extra key for tooltip :)
	 *
	 * @param $query
	 *
	 * @return array
	 */
	public static function get_chart_data( $query ): array {
		$data = static::get_data( $query );

		$data['tooltip_text'] = static::get_tooltip_text();

		return $data;
	}

	/**
	 * Get data for card. The number needs the count value
	 *
	 * @param $query
	 *
	 * @return array
	 */
	public static function get_card_data( $query ): array {
		if ( empty( $query['has_chart'] ) ) {
			$count = Logs::get_instance()->set_query( static::parse_query( $query ) )->sum_results_count();

			$chart_data['count'] = $count === null ? 0 : $count;

			$chart_data['no_data'] = $count === null ? 1 : 0;
		} else {
			$data = static::get_chart_data( $query );

			$chart_data = empty( $query['has_chart'] ) ? [] : $data;

			$chart_data['no_data'] = empty( $data['items'] ) ? 1 : 0;

			$chart_data['count'] = array_reduce( $data['items'], static function ( $total, $item ) {
				return $total + (int) $item['count'];
			}, 0 );
		}

		return $chart_data;
	}

	/**
	 * Get table data. also return the number of items so we can paginate and link in case we need
	 *
	 * @param $query
	 *
	 * @return array
	 */
	public static function get_table_data( $query ): array {
		/* for table display, we want data sorted by day because we retrieve all entries */
		$query['date_format'] = 'day';

		$data = static::get_data( $query );

		if ( ! empty( $query['has_pagination'] ) ) {
			$data['number_of_items'] = Logs::get_instance()->count_results();
		}

		$data['images'] = static::get_data_images( $data['items'] );
		$data['links']  = [];

		return $data;
	}

	/**
	 * @param $items
	 *
	 * @return array
	 */
	public static function get_data_labels( $items ): array {

		$items = array_map( static function ( $item ) {
			return new static( $item );
		}, $items );

		$labels = [];

		foreach ( $items as $item ) {
			foreach ( $item->get_fields() as $field_key => $field ) {
				if ( is_a( $field, Event_Field::class ) ) {

					$field_key = $field::key();

					if ( empty( $labels[ $field_key ] ) ) {
						$labels[ $field_key ] = [
							'key'    => $field_key,
							'text'   => $field::get_label(),
							'values' => [],
						];
					}

					$field_value = $field->get_value( false );

					if ( ! isset( $labels[ $field_key ]['values'][ $field_value ] ) ) {
						$labels[ $field_key ]['values'][ $field_value ] = $field->get_title();
					}
				} elseif ( $field_key === 'count' ) {
					$labels['count'] = [
						'key'  => 'count',
						'text' => __( 'Count', 'thrive-dashboard' ),
					];
				}
			}
		}

		return $labels;
	}

	/**
	 * @param $items
	 *
	 * @return array
	 */
	public static function get_data_images( $items ): array {
		$items = array_map( static function ( $item ) {
			return new static( $item );
		}, $items );

		$images = [];

		foreach ( $items as $item ) {
			static::collect_field_images( $item->get_fields(), $images );
		}

		return $images;
	}

	/**
	 * Used by custom reports (the ones in Apprentice)
	 *
	 * @param array $items
	 * @param array $fields
	 *
	 * @return array
	 */
	public static function get_custom_data_images( array $items, array $fields ): array {
		$images = [];

		foreach ( $items as $item ) {
			$fields = array_map( static function ( $field ) use ( $item ) {
				return new $field( $item[ $field::key() ] );
			}, $fields );

			static::collect_field_images( $fields, $images );
		}

		return $images;
	}

	/**
	 * @param $fields
	 * @param $images
	 *
	 * @return void
	 */
	public static function collect_field_images( $fields, &$images ) {
		foreach ( $fields as $field ) {
			if ( is_a( $field, Event_Field::class ) && method_exists( $field, 'has_image' ) && $field::has_image() ) {
				$field_key = $field::key();

				if ( empty( $images[ $field_key ] ) ) {
					$images[ $field_key ] = [];
				}

				$field_value = $field->get_value( false );

				if ( ! isset( $images[ $field_key ][ $field_value ] ) ) {
					$images[ $field_key ][ $field_value ] = $field->get_image();
				}
			}
		}
	}

	/**
	 * Order items based on query. mostly used when we have custom data that is no retrieved from db
	 *
	 * @param array $items  Items to sort
	 * @param array $query  query that let's us know how to order and slice items
	 * @param array $labels labels used for comparison
	 *
	 * @return array|mixed
	 */
	protected static function order_items( $items = [], $query = [], $labels = [] ): array {

		if ( isset( $query['order_by'] ) ) {
			if ( isset( $labels[ $query['order_by'] ] ) ) {
				$labels = $labels[ $query['order_by'] ]['values'];
			}

			usort( $items, static function ( $a, $b ) use ( $query, $labels ) {

				if ( ! isset( $a[ $query['order_by'] ] ) || ! isset( $b[ $query['order_by'] ] ) ) {
					return 0;
				}

				$a = $a[ $query['order_by'] ];
				$b = $b[ $query['order_by'] ];

				if ( $query['order_by'] === 'date' ) {
					/* compare dates */
					$a = strtotime( $a );
					$b = strtotime( $b );
				} elseif ( isset( $labels[ $a ] ) && isset( $labels[ $b ] ) ) {
					/* usually we store ids in values, so we have to compare the labels */
					$a = $labels[ $a ];
					$b = $labels[ $b ];
				}

				return ( strtolower( $query['order_by_direction'] ) === 'desc' ? - 1 : 1 ) * ( is_numeric( $a ) && is_numeric( $b ) ? $a - $b : strcasecmp( $a, $b ) );
			} );
		}

		return $items;
	}

	/**
	 * Slice data when we have custom queries
	 *
	 * @param $items
	 * @param $query
	 *
	 * @return array|mixed
	 */
	public static function slice_items( $items = [], $query = [] ) {
		if ( ! empty( $query['items_per_page'] ) && is_numeric( $query['items_per_page'] ) ) {
			$page = empty( $query['page'] ) ? 1 : (int) $query['page'];

			$items = array_slice( $items, ( $page - 1 ) * $query['items_per_page'], (int) $query['items_per_page'] );
		}

		return $items;
	}

	/**
	 * Text that will be displayed as tooltips for points in charts
	 *
	 * @return string
	 */
	public static function get_tooltip_text(): string {
		return static::label() . ': <strong>{number}</strong>';
	}

	/**
	 * Event description - used for user timeline
	 *
	 * @return string
	 */
	public function get_event_description(): string {
		$item = $this->get_field( Item_Id::key() )->get_title();

		return " did this event for $item.";
	}

	public function get_event_user() {
		return $this->get_field( User_Id::key() );
	}

	public function get_event_date(): string {
		return $this->get_field_value( Created::key(), false );
	}
}
