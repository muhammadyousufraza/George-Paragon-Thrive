<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

namespace TVA\Reporting\ReportApps;

class All_Engagements extends Engagement_Type {
	public static function key(): string {
		return 'all_engagements';
	}

	public static function label(): string {
		return __( 'Engagements', 'thrive-apprentice' );
	}

	public static function get_data( $query = [], $is_table = false ): array {
		$data = parent::get_data( $query );

		$data['items'] = static::order_items( $data['items'], [
			'order_by'           => 'date',
			'order_by_direction' => 'asc',
		], $data['labels'] );

		$items_count_by_date = [];

		foreach ( $data['items'] as $item ) {
			if ( empty( $items_count_by_date[ $item['date'] ] ) ) {
				$items_count_by_date[ $item['date'] ] = [
					'date'  => $item['date'],
					'count' => 0,
				];
			}

			$items_count_by_date[ $item['date'] ]['count'] += (int) $item['count'];
		}

		$data['items'] = array_values( $items_count_by_date );
		$data['count'] = array_reduce( $data['items'], static function ( $total, $item ) {
			return $total + (int) $item['count'];
		}, 0 );

		return $data;
	}
}
