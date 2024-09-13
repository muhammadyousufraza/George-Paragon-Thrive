<?php
/**
 * Thrive Themes - https://thrivethemes.com
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

/**
 * Class Thrive_AB_Event_Manager
 */
class Thrive_AB_Event_Manager {

	protected static $_instance;

	protected $_page_id;
	protected $_test_id;

	/**
	 * @var wpdb
	 */
	protected $_wpdb;

	public function __construct() {

		global $wpdb;

		$this->_wpdb = $wpdb;
	}

	public static function instance() {

		if ( empty( self::$_instance ) ) {
			self::$_instance = new self();
		}

		add_action( 'tcb_api_form_submit', array( self::$_instance, 'on_form_submit' ) );

		return self::$_instance;
	}

	public function on_form_submit( $data ) {

		$post_id = ! empty( $data['frontend_post_id'] ) ? (int)( $data['frontend_post_id'] ) : 0;
		$post_id = ! empty( $post_id ) ? $post_id : ( isset( $data['post_id'] ) ? (int)$data['post_id'] : 0 );

		if ( empty( $post_id ) ) {
			return;
		}

		$post              = get_post( $post_id );
		$is_post_variation = thrive_ab()->maybe_variation( $post );
		$page_id           = (int)( $is_post_variation ? $post->post_parent : $post->ID );

		try {
			$page    = new Thrive_AB_Page( $page_id );
			$test_id = (int)$page->get_meta()->get( 'running_test_id' );
		} catch ( Exception $e ) {
			return;
		}

		/**
		 * page has no test running
		 */
		if ( empty( $test_id ) ) {
			return;
		}

		/**
		 * test type has to be optins
		 */
		$test = new Thrive_AB_Test( $test_id );
		if ( 'optins' !== $test->type ) {
			return;
		}

		$impression_cookie = Thrive_AB_Cookie_Manager::get_cookie( $test_id, $page_id, 1 );
		$conversion_cookie = Thrive_AB_Cookie_Manager::get_cookie( $test_id, $page_id, 2 );

		/**
		 * user already made impression and conversion
		 */
		if ( ! $impression_cookie || $conversion_cookie ) {
			return;
		}
		$goal_pages = $test->goal_pages;

		/**
		 * save conversion cookie
		 */
		self::do_conversion( $test_id, $page_id, $post->ID, empty( $goal_pages[ $post->ID ] ) ? null : $goal_pages[ $post->ID ] );
	}

	public static function check_thank_you_page( $post ) {

		if ( ! $post instanceof WP_Post || ! is_singular() ) {
			return;
		}

		$test_manager = new Thrive_AB_Test_Manager();

		$tests = $test_manager->get_tests( array( 'status' => 'running' ), 'object' );

		$do_not_cache = false;

		/** @var Thrive_AB_Test $test */
		foreach ( $tests as $test ) {
			/**
			 * If current page is goal page in one of the running tests do not cache
			 */
			$do_not_cache = $do_not_cache || ( ! empty( $goal_pages ) && ! empty( $goal_pages[ $post->ID ] ) );

			$impression_variation = Thrive_AB_Cookie_Manager::get_cookie( $test->id, $test->page_id, 1 );
			if ( empty( $impression_variation ) ) {
				continue;
			}

			$conversion_variation = Thrive_AB_Cookie_Manager::get_cookie( $test->id, $test->page_id, 2 );
			if ( ! empty( $conversion_variation ) ) {
				continue;
			}
			$goal_pages = $test->goal_pages;

			if ( $test->has_goal_page( $post->ID ) ) {
				Thrive_AB_Event_Manager::do_conversion( $test->id, $test->page_id, $impression_variation, $test->get_goal_page_details( $post->ID ) );
				continue;
			}
		}

		if ( ! empty( $do_not_cache ) && function_exists( 'tve_do_not_cache_page' ) ) {
			tve_do_not_cache_page();
		}
	}

	/**
	 * Registers unique impression
	 * If there is a cookie set counts visit only
	 *
	 * @param $page_id
	 * @param $test_id
	 * @param $variation_id
	 *
	 * @return null|Thrive_AB_Event|Thrive_AB_Test_Item
	 *
	 * @throws Exception
	 */
	public static function do_impression( $page_id, $test_id, $variation_id ) {

		$model = array(
			'page_id'      => $page_id,
			'test_id'      => $test_id,
			'variation_id' => $variation_id,
			'event_type'   => 1,
			'unique'       => true,
		);

		/**
		 * Create here an event model so that:
		 * - it is thrown in an action
		 * - other plugins can hook into
		 */
		$event_log = new Thrive_AB_Event( $model );
		do_action( 'thrive_ab_pre_impression', $event_log );

		$cookie_variation_id = Thrive_AB_Cookie_Manager::get_cookie( $test_id, $page_id, 1 );

		if ( $cookie_variation_id ) {
			unset( $model['unique'] );

			return Thrive_AB_Event_Manager::count_visit( $model );
		}

		$event = Thrive_AB_Event_Manager::save_event( $model );
		Thrive_AB_Cookie_Manager::set_cookie( $event->test_id, $event->page_id, $event->variation_id, 1 );

		return $event;
	}

	public static function do_conversion( $test_id, $page_id, $variation_id, $goal_page ) {
		if ( empty( $test_id ) || empty( $page_id ) || empty( $variation_id ) ) {
			return false;
		}
		$log_model = array(
			'page_id'      => $page_id,
			'test_id'      => $test_id,
			'variation_id' => $variation_id,
			'event_type'   => 2,
			'revenue'      => ! empty( $goal_page['revenue'] ) ? $goal_page['revenue'] : 0,
			'goal_page'    => ! empty( $goal_page['post_id'] ) ? $goal_page['post_id'] : null,
		);

		Thrive_AB_Event_Manager::save_event( $log_model );
		Thrive_AB_Cookie_Manager::set_cookie( $test_id, $page_id, $variation_id, 2 );
	}

	public static function save_event( $model ) {

		delete_transient( Thrive_AB_Report_Manager::$_transient_stats_name );

		$defaults = array(
			'date' => date( 'Y-m-d H:i:s' ),
		);

		$model = array_merge( $defaults, $model );

		$event_log = new Thrive_AB_Event( $model );

		try {
			$event_log->save();

		} catch ( Exception $e ) {

		}

		do_action( 'thrive_ab_event_saved', $event_log );

		/**
		 * save test item data
		 */
		Thrive_AB_Event_Manager::count_visit( $model );

		Thrive_AB_Event_Manager::check_auto_winner( $model );

		return $event_log;
	}

	/**
	 * Increments test_item model impression and/or unique impression column
	 * Sets a cookie with 5 seconds expiration time, just not to spam impression column
	 *
	 * @param $model
	 *
	 * @return Thrive_AB_Test_Item|null
	 *
	 * @throws Exception
	 */
	public static function count_visit( $model ) {

		if ( function_exists( 'tve_dash_is_crawler' ) && tve_dash_is_crawler( true ) ) {
			return null;
		}

		$test_item = new Thrive_AB_Test_Item();
		$test_item->init_by_filters(
			array(
				'page_id'      => $model['page_id'],
				'variation_id' => $model['variation_id'],
				'test_id'      => $model['test_id'],
			)
		);

		if ( 1 == $model['event_type'] ) {

			/**
			 * increment impressions only if cookie not set, which is set to expire in 5 seconds
			 */
			if ( ! Thrive_AB_Cookie_Manager::get_impression_cookie( $test_item->id ) ) {
				$test_item->impressions ++;
				Thrive_AB_Cookie_Manager::set_impression_cookie( $test_item->id );
			}

			if ( ! empty( $model['unique'] ) ) {
				$test_item->unique_impressions ++;
			}
		} else {
			$test_item->conversions ++;
			if ( ! empty( $model['revenue'] ) ) {
				$test_item->revenue += $model['revenue'];
			}
		}

		return $test_item->save();
	}

	public static function check_auto_winner( $event ) {

		if ( empty( $event ) ) {
			return null;
		}

		$test_manager = new Thrive_AB_Test_Manager();
		$test         = $test_manager->get_running_test( $event['page_id'] );

		if ( ! $test->auto_win_enabled || 'running' !== $test->status ) {
			return null;
		}

		if ( $test->auto_win_min_duration ) {
			if ( time() < strtotime( $test->date_started . ' +' . $test->auto_win_min_duration . 'days' ) ) {
				return false;
			}
		}
		$test_item         = new Thrive_AB_Test_Item();
		$total_conversions = $test_item->get_total_conversions( $test->id );

		if ( intval( $total_conversions->total_conversions ) <= intval( $test->auto_win_min_conversions ) ) {
			return false;
		}

		$test_items      = $test->get_items();
		$winner          = array(
			'item'   => null,
			'chance' => 0,
		);
		$underperforming = array();
		$leftovers       = array();
		foreach ( $test_items as $item ) {
			if ( ! intval( $item->active ) ) {
				continue;
			}
			if ( ! $item->is_control ) {

				$current_chance = $item->get_chance_to_beat_original( '' );

				if ( floatval( $current_chance ) > floatval( $test->auto_win_chance_original ) && floatval( $current_chance ) > $winner['chance'] ) {
					$winner['item']   = $item;
					$winner['chance'] = floatval( $current_chance );
				}

				if ( floatval( $current_chance ) < ( 100 - floatval( $test->auto_win_chance_original ) ) ) {
					array_push( $underperforming, $item );
				} else {
					array_push( $leftovers, $item );
				}
			} else {
				array_push( $leftovers, $item );
			}
		}
		/**
		 * If there is a winner chosen, set the winner and stop the test
		 */
		if ( ! empty( $winner['item'] ) ) {
			$data = $winner['item']->get_data();
			Thrive_AB_Ajax::set_winner( $data );
		} else {
			/**
			 * Check if besides the underperforming variations there are any leftovers
			 * If so, then set it as winner. If not, take out the underperformers
			 */
			$removed_traffic = 0;
			foreach ( $underperforming as $test_item ) {
				$test_item->stop()->save();

				$variation = new Thrive_AB_Variation( (int)$test_item->variation_id );
				$meta      = $variation->get_meta();

				$removed_traffic += $meta->get( 'traffic' );

				$meta->update( 'traffic', 0 );
				$meta->update( 'status', 'archived' );
			}

			if ( sizeof( $leftovers ) == 1 ) {
				$data = $leftovers[0]->get_data();
				Thrive_AB_Ajax::set_winner( $data );
			}

			$extra_traffic = floor( $removed_traffic / sizeof( $leftovers ) );

			foreach ( $leftovers as $test_item ) {
				$variation = new Thrive_AB_Variation( (int)$test_item->variation_id );
				$meta      = $variation->get_meta();
				$traffic   = $meta->get( 'traffic' ) + $extra_traffic;

				$meta->update( 'traffic', $traffic );
				$last_test_item_meta = $meta;
			}
			$traffic_diff = $removed_traffic % sizeof( $leftovers );
			if ( $traffic_diff ) {
				$last_test_item_traffic = $last_test_item_meta->get( 'traffic' ) + $traffic_diff;
				$last_test_item_meta->update( 'traffic', $last_test_item_traffic );
			}
		}

		return true;
	}

	/**
	 * Bulk update the log table
	 *
	 * @param array $data
	 * @param array $where
	 *
	 * @return int|bool
	 */
	public static function bulk_update_log( $data = array(), $where = array() ) {
		if ( empty( $data ) || empty( $where ) ) {
			return false;
		}

		global $wpdb;

		$update_rows = $wpdb->update( thrive_ab()->table_name( 'event_log' ), $data, $where );

		return false !== $update_rows;
	}

	/**
	 * Bulk delete from log table
	 *
	 * @param array $where
	 *
	 * @return bool
	 */
	public static function bulk_delete_log( $where = array() ) {
		if ( empty( $where ) ) {
			return false;
		}

		global $wpdb;

		$deleted_rows = $wpdb->delete( thrive_ab()->table_name( 'event_log' ), $where );

		return false !== $deleted_rows;
	}

	/**
	 * Reset test data
	 *
	 * @param bool $test_id
	 *
	 * @return bool
	 *
	 * @throws Exception
	 */
	public static function reset_test_data( $test_id = false ) {
		if ( empty( $test_id ) ) {
			return false;
		}
		Thrive_AB_Event_Manager::bulk_delete_log( array( 'test_id' => $test_id ) );

		$test_manager = new Thrive_AB_Test_Manager();
		$items        = $test_manager->get_items_by_filters( array( 'test_id' => $test_id ) );
		foreach ( $items as $item ) {
			$test_item                     = new Thrive_AB_Test_Item( $item );
			$test_item->unique_impressions = 0;
			$test_item->impressions        = 0;
			$test_item->conversions        = 0;
			$test_item->revenue            = 0;
			$test_item->save();
		}

		return true;
	}
}

return Thrive_AB_Event_Manager::instance();
