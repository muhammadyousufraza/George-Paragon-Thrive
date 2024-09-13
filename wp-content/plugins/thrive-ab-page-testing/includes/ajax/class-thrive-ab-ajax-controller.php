<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-ab-page-testing
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

class Thrive_AB_Ajax_Controller {

	/**
	 * @var Thrive_AB_Ajax_Controller
	 */
	protected static $_instance;

	private function __construct() {
	}

	public static function instance() {

		if ( ! self::$_instance ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Sets the request's header with server protocol and status
	 * Sets the request's body with specified $message
	 *
	 * @param string $message the error message.
	 * @param string $status  the error status.
	 */
	protected function error( $message, $status = '404 Not Found' ) {

		header( $_SERVER['SERVER_PROTOCOL'] . ' ' . $status );
		wp_send_json_error( array( 'message' => $message ) );
	}

	/**
	 * Returns the params from $_POST or $_REQUEST
	 *
	 * @param int  $key     the parameter kew.
	 * @param null $default the default value.
	 *
	 * @return mixed|null|$default
	 */
	protected function param( $key, $default = null ) {

		return isset( $_POST[ $key ] ) ? $_POST[ $key ] : ( isset( $_REQUEST[ $key ] ) ? $_REQUEST[ $key ] : $default );
	}

	/**
	 * Entry-point for each ajax request
	 * This should dispatch the request to the appropriate method based on the "route" parameter
	 *
	 * @return array|object
	 */
	public function handle() {
		/* Check if user still has the cap to use the plugin */
		if ( ! Thrive_AB_Product::has_access() ) {
			$this->error( __( 'You do not have this capability anymore', 'thrive-ab-page-testing' ) );
		}

		if ( ! check_ajax_referer( Thrive_AB_Ajax::NONCE_NAME, 'nonce', false ) ) {
			$this->error( __( 'Invalid request.', 'thrive-ab-page-testing' ) );
		}

		$route = $this->param( 'route' );

		$route    = preg_replace( '#([^a-zA-Z0-9-])#', '', $route );
		$function = $route . '_action';

		if ( ! method_exists( $this, $function ) ) {
			$this->error( sprintf( __( 'Method %s not implemented', 'thrive-ab-page-testing' ), $function ) );
		}

		$method = empty( $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'] ) ? 'GET' : $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'];
		$model  = json_decode( file_get_contents( 'php://input' ), true );

		return call_user_func( array( $this, $function ), $method, $model );
	}

	protected function tests_action( $method, $model ) {

		$response = array();

		switch ( $method ) {
			case 'POST':
			case 'PUT':
				try {

					if ( ! empty( $model['save_test_settings'] ) ) {
						unset( $model['save_test_settings'] );
						unset( $model['items'] );
						Thrive_AB_Test_Manager::save_test( $model );

						return true;
					}
					$test = Thrive_AB_Test_Manager::save_test( $model );
					$test->start()->save();

					$response = $test->get_data();

				} catch ( Exception $e ) {
					$this->error( $e->getMessage() );
				}
				break;
		}

		return $response;
	}

	protected function variations_action( $method, $model ) {

		$response = array();

		switch ( $method ) {
			case 'PATCH':
				try {
					$id          = (int) $this->param( 'ID' );
					$variation   = new Thrive_AB_Page_Variation( $id );
					$model['ID'] = $id;
					$response    = $variation->save( $model );
				} catch ( Exception $e ) {
					$this->error( $e->getMessage() );
				}
				break;
			case 'PUT':
			case 'POST':
				try {
					$post_parent = ! empty( $model['post_parent'] ) ? $model['post_parent'] : null;
					$page        = new Thrive_AB_Page( $post_parent );

					$model['meta']['traffic']    = ! empty( $model['traffic'] ) ? (int) $model['traffic'] : 0;
					$model['meta']['is_control'] = ! empty( $model['is_control'] ) ? (bool) $model['is_control'] : false;

					if ( ! empty( $model['action'] ) && $model['action'] == 'publish' ) {
						//case it is an archived variation and we want it restored
						$model['meta']['status'] = 'deleted';
						$variation               = $page->save_variation( $model );
						$model['source_id']      = $model['ID'];
						$model['ID']             = null;
						$model['meta']['status'] = 'published';
					} elseif ( ! empty( $model['action'] ) && $model['action'] == 'archive' ) {
						// case it is a published archived and we want it archived
						$model['meta']['status'] = 'archived';
					} else {
						// anything else
						$model['meta']['status'] = 'published';
					}

					$variation = $page->save_variation( $model );
					$variation->set_page( $page->get_post() );

					if ( ! empty( $model['source_id'] ) ) {
						$source_variation = new Thrive_AB_Page_Variation( $model['source_id'] );
						$variation_data   = $variation->get_data();
						if ( ! empty( $variation_data ) ) {
							$variation_id = $variation_data['ID'];
							$source_variation->get_meta()->init( array(
								get_post_type( $post_parent ),
								'template',
							) )->copy_to( $variation_id );
							$source_variation->copy_thumb_to( $variation_id );
						}
					}

					$response = $variation->get_data();
				} catch ( Exception $e ) {
					$this->error( $e->getMessage() );
				}
				break;
			case 'DELETE':
				try {
					$id        = (int) $this->param( 'ID', null );
					$variation = new Thrive_AB_Page_Variation( $id );
					$response  = $variation->get_meta()->update( 'status', 'deleted' );
				} catch ( Exception $e ) {
					$this->error( $e->getMessage() );
				}
				break;
		}

		return $response;
	}

	/**
	 * Report Action Endpoint
	 *
	 * @param $method
	 * @param $model
	 *
	 * @return array
	 */
	protected function report_action( $method, $model ) {

		$response = array();
		switch ( $method ) {
			case 'GET':
				$id       = (int) $this->param( 'ID' );
				$interval = $this->param( 'interval' );
				$type     = $this->param( 'type' );

				$report_manager = new Thrive_AB_Report_Manager();

				return $report_manager->get_test_chart_data( array(
					'test_id'  => $id,
					'interval' => $interval,
					'type'     => $type,
				) );

				break;
			default:
				break;
		}

		return $response;
	}

	protected function testitem_action( $method, $model ) {
		$response = array();

		switch ( $method ) {
			case 'POST':
			case 'PUT':

				if ( ! empty( $model['stop_test_item'] ) ) {

					$variation = new Thrive_AB_Page_Variation( (int) $model['variation_id'] );
					$meta      = $variation->get_meta();
					$meta->update( 'traffic', 0 );
					$meta->update( 'status', 'archived' );

					$item = new Thrive_AB_Test_Item( (int) $model['id'] );
					$item->stop()->save();
					$item->variation = $variation;

					$data = $item->get_data();

					return $data;
				}
				break;
			default:
				break;
		}

		return $response;
	}

	protected function traffic_action( $method, $model ) {

		if ( isset( $model['ID'] ) ) {
			unset( $model['ID'] );
		}

		$edit_post_traffic = $this->param( 'tab_edit_post_traffic' );
		if ( empty( $model ) && ! empty( $edit_post_traffic ) && is_array( $edit_post_traffic ) ) {
			/**
			 * Traffic From Edit Post View
			 */
			if ( array_sum( $edit_post_traffic ) < 100 ) {
				$model = $edit_post_traffic;
			}
		}

		foreach ( $model as $id => $traffic ) {
			$variation = new Thrive_AB_Variation( $id );
			$variation->get_meta()->update( 'traffic', (int) $traffic );
		}
	}

	/**
	 * Called From Optimize Admin Dashboard
	 * Returns all tests that are stored in database for display
	 *
	 * @return array
	 * @throws Exception
	 */
	protected function testsforadmin_action() {

		$test_manager   = new Thrive_AB_Test_Manager();
		$report_manager = new Thrive_AB_Report_Manager();
		$all_tests      = $test_manager->get_tests();
		$stats          = $report_manager->get_admin_dashboard_stats();

		$goals = array(
			'monetary' => __( 'Revenue', 'thrive-ab-page-testing' ),
			'visits'   => __( 'Goal Page Visit', 'thrive-ab-page-testing' ),
			'optins'   => __( 'Subscriptions', 'thrive-ab-page-testing' ),
		);

		$return                    = array();
		$return['running_tests']   = array();
		$return['completed_tests'] = array();
		$return['dashboard_stats'] = $stats;

		foreach ( $all_tests as $test ) {
			//When a post is in trash list, we should hide it from the Admin Tests Table
			if ( get_post_status( $test['page_id'] ) !== 'publish' ) {
				continue;
			}

			$test['date_started_pretty']   = date_i18n( 'd F Y', strtotime( $test['date_started'] ) );
			$test['date_completed_pretty'] = date_i18n( 'd F Y', strtotime( $test['date_completed'] ) );
			$test['goal']                  = $goals[ $test['type'] ];
			$test['unique_impressions']    = 0;
			$test['conversions']           = 0;

			try {
				$ab_page = new Thrive_AB_Page( (int) $test['page_id'] );
			} catch ( Exception $e ) {
				continue;
			}

			$test['test_link']  = $ab_page->get_test_link( $test['id'] );
			$test['page_title'] = $ab_page->post_title;

			$items = $test_manager->get_items_by_filters( array( 'test_id' => $test['id'] ) );
			foreach ( $items as $item ) {

				$test['unique_impressions'] += (int) $item['unique_impressions'];
				$test['conversions']        += (int) $item['conversions'];
			}

			if ( $test['status'] === 'running' ) {
				$return['running_tests'][] = $test;
			} elseif ( $test['status'] === 'completed' ) {
				$return['completed_tests'][] = $test;
			}
		}

		function running_tests_sort( $a, $b ) {
			return strtotime( $b['date_started'] ) - strtotime( $a['date_started'] );
		}

		function completed_tests_sort( $a, $b ) {
			return strtotime( $b['date_completed'] ) - strtotime( $a['date_completed'] );
		}

		usort( $return['running_tests'], 'running_tests_sort' );
		usort( $return['completed_tests'], 'completed_tests_sort' );


		return $return;
	}

	/**
	 * Deletes a test from the Admin Dashboard
	 *
	 * @return array
	 */
	protected function deletecompletedtestadmin_action() {
		$return = array(
			'success' => 0,
			'text'    => __( 'There was an error in the process.', 'thrive-ab-page-testing' ),
		);

		$id      = (int) $this->param( 'id' );
		$page_id = (int) $this->param( 'page_id' );

		if ( ! empty( $id ) && ! empty( $page_id ) ) {
			Thrive_AB_Test_Manager::delete_test( array(
				'id'      => $id,
				'page_id' => $page_id,
			) );

			$return['success'] = 1;
			$return['text']    = __( 'The test was deleted successfully!', 'thrive-ab-page-testing' );
		}

		return $return;
	}
}
