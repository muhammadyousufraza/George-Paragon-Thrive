<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-ab-page-testing
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

/**
 * Class Thrive_AB_Test_Manager
 *
 * This class can be instantiate but it also has static methods to easily work with test models
 */
class Thrive_AB_Test_Manager {

	public static $types
		= array(
			'monetary',
			'visits',
			'optins',
		);

	/**
	 * @var wpdb
	 */
	protected $_wpdb;

	public function __construct() {

		global $wpdb;

		$this->_wpdb = $wpdb;
	}

	/**
	 * init some hooks
	 */
	public static function init() {
		/**
		 * before TAr template_redirect which is at 0 priority
		 */
//		add_action( 'template_redirect', array( __CLASS__, 'template_redirect' ), - 1 );
	}

	/**
	 * redirects the user to post parent if he wants to edit the page with TAr
	 * and a test is running
	 */
	public static function template_redirect() {

		if ( ! is_editor_page() ) {
			return;
		}

		global $post;

		$post_id = $post->ID;

		/**
		 * if the posts is a variation
		 * check its parent for test
		 */
		if ( $post->post_type === Thrive_AB_Post_Types::VARIATION ) {
			$post_id = $post->post_parent;
		}

		$instance = new Thrive_AB_Test_Manager();
		$tests    = $instance->get_running_test( $post_id );

		if ( ! empty( $tests ) ) {
			wp_redirect( get_permalink( $post_id ) );
			die;
		}
	}

	/**
	 * Store the model into db and return it
	 * If model has items prop set it tries to save those into db and push them into local _data
	 *
	 * @param $model
	 *
	 * @return Thrive_AB_Test
	 * @throws Exception
	 */
	public static function save_test( $model ) {

		$test = new Thrive_AB_Test( $model );
		$test->save();

		try {
			if ( ! empty( $model['items'] ) ) {
				$test->items = array();
				foreach ( $model['items'] as $item ) {
					$item['test_id'] = $test->id;
					$item['page_id'] = $test->page_id;
					$test->save_item( $item );
				}
			}
		} catch ( Exception $e ) {

			$test->delete();
			throw new Exception( $e->getMessage() );
		}

		if ( ! empty( $model['page_id'] ) ) {
			$page = new Thrive_AB_Page( (int) $model['page_id'] );
			if ( 'draft' === $page->post_status ) {
				$page->get_post()->post_status = 'publish';
				wp_update_post( $page->get_post() );
			}
		}

		return $test;
	}

	/**
	 * Delete a test
	 *
	 * @param array $model
	 */
	public static function delete_test( $model = array() ) {
		if ( empty( $model ) ) {
			return;
		}

		$test = new Thrive_AB_Test( $model );
		$test->delete();
	}

	/**
	 * Based on $filters a list of test models should be read from DB and returned
	 *
	 * @param array  $filters
	 * @param string $type
	 *
	 * @return array
	 */
	public function get_tests( $filters = array(), $type = 'array' ) {

		$tests = array();
		$where = ' WHERE 1=1 ';

		$filters = array_merge( array(), $filters );
		$params  = array();

		if ( ! empty( $filters['page_id'] ) ) {
			$where    .= ' AND `page_id` = %s';
			$params[] = $filters['page_id'];
		}

		if ( ! empty( $filters['status'] ) ) {
			$where    .= ' AND `status` = %s';
			$params[] = $filters['status'];
		}

		if ( ! empty( $filters['id'] ) ) {
			$where    .= ' AND `id` = %s';
			$params[] = $filters['id'];
		}

		$sql = 'SELECT * FROM ' . thrive_ab()->table_name( 'tests' ) . $where;
		if ( ! empty( $params ) ) {
			$sql = $this->_wpdb->prepare( $sql, $params );
		}

		$results = $this->_wpdb->get_results( $sql, ARRAY_A );

		if ( ! empty( $results ) ) {
			foreach ( $results as $test ) {
				$tmp     = new Thrive_AB_Test( $test );
				$tests[] = $type === 'array' ? $tmp->get_data() : $tmp;
			}
		}

		return $tests;
	}

	/**
	 * Returns an array of test items by filters
	 *
	 * @param array  $filters
	 * @param string $output
	 *
	 * @return array|null|object
	 */
	public function get_items_by_filters( $filters = array(), $output = ARRAY_A ) {
		$where = ' WHERE 1=1 ';
		$sql   = 'SELECT * FROM ' . thrive_ab()->table_name( 'test_items' ) . $where;

		$params = array();
		if ( ! empty( $filters['test_id'] ) ) {
			$sql       .= 'AND `test_id` = %d ';
			$params [] = $filters['test_id'];
		}
		if ( ! empty( $filters['active'] ) ) {
			$sql       .= 'AND `active` = %d ';
			$params [] = $filters['active'];
		}

		$sql_prepared = $this->_wpdb->prepare( $sql, $params );
		$results      = $this->_wpdb->get_results( $sql_prepared, $output );

		return $results;
	}

	/**
	 * Get running test based on page_id
	 *
	 * @param int $page_id
	 *
	 * @return Thrive_AB_Test|null
	 */
	public function get_running_test( $page_id ) {

		$filters = array(
			'page_id' => $page_id,
			'status'  => 'running',
		);

		$tests = $this->get_tests( $filters, 'object' );

		return empty( $tests ) ? null : reset( $tests );
	}

	protected static function get_goals( $goal = '' ) {

		$goals = array(
			'monetary' => array(
				'icon'  => 'monetary-2',
				'label' => __( 'Goal one', 'thrive-ab-page-testing' ),
				'name'  => __( 'Revenue', 'thrive-ab-page-testing' ),
			),
			'visits'   => array(
				'icon'  => 'visit_gp',
				'label' => __( 'Goal two', 'thrive-ab-page-testing' ),
				'name'  => __( 'Visit goal page', 'thrive-ab-page-testing' ),
			),
			'optins'   => array(
				'icon'  => 'subs',
				'label' => __( 'Goal three', 'thrive-ab-page-testing' ),
				'name'  => __( 'Subscriptions', 'thrive-ab-page-testing' ),
			),
		);

		return in_array( $goal, self::$types ) ? $goals[ $goal ] : $goals;
	}

	public static function display_goal_option( $goal ) {

		$data = self::get_goals( $goal );

		$icon  = tcb_icon( $data['icon'], true, 'sidebar' );
		$label = $data['label'];
		$name  = $data['name'];

		include dirname( __FILE__ ) . '/../views/backbone/goals/goal.php';
	}

	/**
	 * @param $test_id int
	 *
	 * @return false|string
	 */
	public static function get_test_url( $test_id ) {

		$url = add_query_arg( array(
				'page'    => 'tab_admin_view_test',
				'test_id' => $test_id,
			), admin_url( 'admin.php' ) ) . '#test';

		return $url;
	}
}

Thrive_AB_Test_Manager::init();
