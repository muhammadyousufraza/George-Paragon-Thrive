<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */

namespace TVE\Reporting;

use TVE\Reporting\EventFields\User_Id;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Privacy {
	public static function init() {
		add_filter( 'wp_privacy_personal_data_exporters', [ __CLASS__, 'add_personal_data_exporter' ] );
		add_filter( 'wp_privacy_personal_data_erasers', [ __CLASS__, 'add_personal_data_eraser' ] );
	}

	/**
	 * @param $exporters
	 *
	 * @return mixed
	 */
	public static function add_personal_data_exporter( $exporters ) {
		$exporters[] = [
			'exporter_friendly_name' => __( 'Thrive Reporting', 'thrive-dash' ),
			'callback'               => [ __CLASS__, 'export' ],
		];

		return $exporters;
	}

	/**
	 * @param $email_address
	 *
	 * @return array
	 */
	public static function export( $email_address ): array {
		$exported_items = [];
		$user           = get_user_by( 'email', $email_address );

		if ( $user && $user->ID ) {
			$events = Logs::get_instance()->set_query( [
				'filters' => [
					User_Id::key() => $user->ID,
				],
			] )->get_results();

			foreach ( $events as $event ) {
				$event_data     = [];
				$event_instance = Store::get_instance()->event_factory( $event );

				foreach ( $event_instance::get_registered_fields() as $field_key => $field_class ) {
					$event_data[] = [
						'name'  => $field_class::get_label(),
						'value' => $event_instance->get_field( $field_key )->get_title(),
					];
				}

				$exported_items[] = [
					'group_id'    => 'thrive-reporting-user-privacy',
					'group_label' => __( 'Thrive Reporting Event Data', 'thrive-dash' ),
					'item_id'     => $event_instance->get_field( 'id' ),
					'data'        => $event_data,
				];
			}
		}

		return [
			'data' => $exported_items,
			'done' => true,
		];
	}

	/**
	 * @param $erasers
	 *
	 * @return mixed
	 */
	public static function add_personal_data_eraser( $erasers ) {
		$erasers[] = [
			'eraser_friendly_name' => __( 'Thrive Reporting', 'thrive-dash' ),
			'callback'             => [ __CLASS__, 'erase' ],
		];

		return $erasers;
	}

	/**
	 * @param $email_address
	 *
	 * @return array
	 */
	public static function erase( $email_address ): array {
		$response = [
			'items_removed'  => false,
			'items_retained' => false,
			'messages'       => [],
			'done'           => true,
		];

		if ( empty( $email_address ) ) {
			return $response;
		}

		$user = get_user_by( 'email', $email_address );

		$logs_instance = Logs::get_instance();
		$logs_instance->remove_by( User_Id::key(), $user->ID );

		$user_data = $logs_instance->set_query( [
			'filters' => [
				User_Id::key() => $user->ID,
			],
		] )->get_results();

		$response['items_removed'] = empty( $user_data );

		return $response;
	}
}
