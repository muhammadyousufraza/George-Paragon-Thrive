<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */

namespace TVE\Reporting;

use TVE\Reporting\EventFields\Event_Type;
use TVE\Reporting\EventFields\Item_Id;
use function register_rest_route;

abstract class Report_App {

	abstract public static function key();

	abstract public static function label();

	/**
	 * Registered report types for this app
	 *
	 * @return Report_Type[]|Event[]
	 */
	public static function get_report_types(): array {
		return [];
	}

	/**
	 * Register app in the store
	 *
	 * @return void
	 */
	public static function register() {
		Store::get_instance()->register_report_app( static::class );
	}

	/**
	 * Method called after the app is registered
	 *
	 * @return void
	 */
	public static function after_register() {
		add_action( 'rest_api_init', [ static::class, 'register_rest_routes' ] );

		static::set_auto_remove_logs();
	}

	public static function set_auto_remove_logs() {
		/* override when necessary - used for clearing Lesson and Course events */
	}

	/**
	 * When a post is deleted, we should also remove logs that belong to that specific post type
	 * Called from Apprentice for lessons and modules
	 *
	 * @param string $post_type
	 * @param array  $reports
	 *
	 * @return void
	 */
	public static function remove_logs_on_post_delete( $post_type, $reports ) {
		add_action( 'delete_post', static function ( $post_id ) use ( $post_type, $reports ) {
			if ( get_post_type( $post_id ) === $post_type ) {
				static::remove_report_logs( $post_id, $reports );
			}
		} );
	}

	/**
	 * @param $item_id
	 * @param $reports
	 *
	 * @return void
	 */
	public static function remove_report_logs( $item_id, $reports ) {
		$event_types = array_map( static function ( $report_class ) {
			return $report_class::key();
		}, $reports );

		Logs::get_instance()->delete( [
			Event_Type::key() => $event_types,
			Item_Id::key()    => $item_id
		] );
	}

	/**
	 * Register routes for each report type to get data
	 *
	 * @return void
	 */
	final public static function register_rest_routes() {
		foreach ( static::get_report_types() as $report_type_class ) {
			/** @var Report_Type|Event $report_type_class */

			$route = static::key() . '/' . $report_type_class::key();

			register_rest_route(
				Main::REST_NAMESPACE,
				'/' . $route,
				[
					[
						'methods'             => \WP_REST_Server::READABLE,
						'callback'            => static function ( \WP_REST_Request $request ) use ( $report_type_class ) {
							$data_type = $request->get_param( 'report-data-type' );
							$query     = $request->get_param( 'query' ) ?? [];


							$fn_name = "get_{$data_type}_data";

							if ( method_exists( $report_type_class, $fn_name ) ) {
								$data = $report_type_class::$fn_name( $query );
							} else {
								$data = [];
							}

							return new \WP_REST_Response( $data );
						},
						'args'                => [
							'query'            => [
								'type'     => 'object',
								'required' => false,
							],
							'report-data-type' => [
								'type'     => 'string',
								'required' => true,
							],
						],
						'permission_callback' => [ $report_type_class, 'permission_callback' ],
					],
				] );

			$report_type_class::register_filter_routes( $route );
		}

		register_rest_route(
			Main::REST_NAMESPACE,
			'/' . static::key() . '/report-types',
			[
				[
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => function () {
						return new \WP_REST_Response( array_map( static function ( $report_type ) {
							return [
								'key'     => $report_type::key(),
								'label'   => $report_type::label(),
								'group'   => $report_type::get_group_by(),
								'filters' => $report_type::get_filters(),
								'display' => $report_type::get_display_types(),
							];
						}, static::get_report_types() ) );
					},
					'permission_callback' => [ static::class, 'permission_callback' ],
				],
			] );
	}

	/**
	 * Permission for accessing app rest routes
	 *
	 * @return bool
	 */
	public static function permission_callback(): bool {
		return current_user_can( TVE_DASH_CAPABILITY );
	}
}
