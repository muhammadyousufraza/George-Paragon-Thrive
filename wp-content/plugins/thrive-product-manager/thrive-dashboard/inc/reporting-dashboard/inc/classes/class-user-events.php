<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */

namespace TVE\Reporting;

use TVE\Reporting\Traits\Report;

class User_Events {
	public static function add_hooks() {
		add_action( 'rest_api_init', static function () {
			register_rest_route( Main::REST_NAMESPACE, '/user-events', [
				[
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => [ __CLASS__, 'get_user_events' ],
					'permission_callback' => static function () {
						return current_user_can( TVE_DASH_CAPABILITY );
					},
					'args'                => [
						'filters'        => [
							'type'     => 'object',
							'required' => true,
						],
						'page'           => [
							'type'     => 'integer',
							'required' => true,
						],
						'items-per-page' => [
							'type'     => 'integer',
							'required' => true,
						],
					],
				],
			] );
		} );
	}

	/**
	 * Get user data
	 *
	 * @param \WP_REST_Request $request The request data from admin.
	 *
	 * @return \WP_REST_Response
	 */
	public static function get_user_events( $request ): \WP_REST_Response {
		$filters        = $request->get_param( 'filters' );
		$page           = $request->get_param( 'page' ) ?? 1;
		$items_per_page = $request->get_param( 'items-per-page' ) ?? 4;

		return new \WP_REST_Response( static::get_events( $filters, $page, $items_per_page ) );
	}

	/**
	 * @param $filters
	 * @param $page
	 * @param $items_per_page
	 *
	 * @return array
	 */
	public static function get_events( $filters, $page, $items_per_page ): array {
		$items = [];
		$users = [];

		if ( empty( $filters ) ) {
			$filters = [];
		}

		$query = [
			'filters'            => [],
			'items_per_page'     => $items_per_page,
			'page'               => $page,
			'order_by'           => 'created',
			'order_by_direction' => 'desc',
		];

		/** @var $all_events Event[] */
		$all_events = Store::get_instance()->get_registered_events();

		foreach ( $filters as $filter_key => $filter_value ) {
			$db_col = $filter_key;

			foreach ( $all_events as $event ) {
				foreach ( $event::get_registered_fields() as $field_db_col => $field ) {
					if ( $filter_key === $field_db_col || $filter_key === $field::key() ) {
						$db_col = $field_db_col;
					}
				}
			}

			$query['filters'][ $db_col ] = $filter_value;
		}

		/* For now, we are retrieving all the events. In the future we could provide an array of event types here. */
		$events = Logs::get_instance()->set_query( $query )->get_results();

		$events = array_map( static function ( $event ) {
			return Store::get_instance()->event_factory( $event );
		}, $events );

		/* in case we don't have the event class for something */
		$events = array_filter( $events, 'is_object' );

		foreach ( $events as $event ) {
			/** @var Report|Event $event */
			$event_date = $event->get_event_date();
			$user       = $event->get_event_user();
			$user_id    = $user->get_value();

			$items[] = [
				'user'        => $user_id,
				'description' => $event->get_event_description(),
				'date'        => $event_date,
			];

			if ( empty( $users[ $user_id ] ) ) {
				$users[ $user_id ] = [
					'name'    => $user->get_title(),
					'picture' => $user->get_image(),
				];
			}
		}

		return [
			'items'           => $items,
			'users'           => $users,
			'number_of_items' => Logs::get_instance()->set_query( $query )->count_results(),
		];
	}
}
