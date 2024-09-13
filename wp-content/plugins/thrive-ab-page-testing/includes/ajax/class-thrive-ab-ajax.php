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
 * Class Thrive_AB_Ajax
 *
 * Defines the ajax actions and implements their hooks
 */
class Thrive_AB_Ajax {

	const NONCE_NAME                      = 'thrive-ab-ajax-nonce';
	const REGISTER_IMPRESSION_ACTION_NAME = 'register_impression';

	public static $controller_action = 'thrive_ab_ajax_controller';
	public static $action            = 'thrive_ab_ajax_action';
	public static $post_id           = null;

	public static function init() {

		self::add_ajax_actions();
	}

	public static function add_ajax_actions() {

		$actions = array(
			self::$action            => true,
			self::$controller_action => true,
		);

		foreach ( $actions as $action => $nopriv ) {
			add_action( 'wp_ajax_' . $action, array( __CLASS__, $action ) );

			if ( $nopriv ) {
				add_action( 'wp_ajax_nopriv_' . $action, array( __CLASS__, $action ) );
			}
		}

		/**
		 * hook of TD lazy load
		 */
		add_filter( 'tve_dash_main_ajax_top_lazy_load', array( __CLASS__, 'dashboard_lazy_load' ), 10, 2 );
	}

	/**
	 * Handler for all
	 */
	public static function thrive_ab_ajax_action() {

		$custom = isset( $_REQUEST['custom'] ) ? $_REQUEST['custom'] : '';

		$response = array();

		if ( method_exists( __CLASS__, $custom ) ) {
			$response = call_user_func( array( __CLASS__, $custom ), $_REQUEST );
		} else {
			wp_send_json_error();
		}

		wp_send_json( $response );
	}

	public static function thrive_ab_ajax_controller() {

		check_ajax_referer( self::NONCE_NAME, 'nonce' );

		require_once dirname( __FILE__ ) . '/class-thrive-ab-ajax-controller.php';
		$response = Thrive_AB_Ajax_Controller::instance()->handle();
		wp_send_json( $response );
	}

	public static function post_search( $filters ) {

		$s = trim( wp_unslash( $filters['q'] ) );
		$s = trim( $s );

		$selected_post_types = array_merge( array(
			'post',
			'product',
		), array_diff( get_post_types(), apply_filters( 'tcb_post_grid_banned_types', array() ) ) );

		if ( empty( $filters['exclude_id'] ) ) {
			$filters['exclude_id'] = array();
		}

		$args = array(
			'post_type'    => $selected_post_types,
			'post_status'  => 'publish',
			's'            => $s,
			'numberposts'  => - 1,
			'post__not_in' => $filters['exclude_id'],
			'orderby'      => 'title',
			'order'        => 'ASC',
		);

		$posts = array();
		foreach ( get_posts( $args ) as $item ) {
			$title = $item->post_title;
			if ( ! empty( $s ) ) {
				$item->post_title = preg_replace( "#($s)#i", '<b>$0</b>', $item->post_title );
			}

			$post = array(
				'label'    => $item->post_title,
				'title'    => $title,
				'id'       => $item->ID,
				'value'    => $item->post_title,
				'url'      => get_permalink( $item->ID ),
				'edit_url' => tcb_get_editor_url( $item->ID ),
				'type'     => $item->post_type,
				'is_popup' => isset( $post_types_data[ $item->post_type ] ) && ! empty( $post_types_data[ $item->post_type ]['event_action'] ),
			);
			if ( $post['is_popup'] ) {
				$post['url']            = '#' . $post_types_data[ $item->post_type ]['name'] . ': ' . $title;
				$post['event_action']   = $post_types_data[ $item->post_type ]['event_action'];
				$post['post_type_name'] = $post_types_data[ $item->post_type ]['name'];
			}

			$posts [] = $post;
		}

		return $posts;
	}

	public static function add_new_page( $data ) {

		if ( empty( $data['title'] ) ) {
			die( __( 'Page could not be saved!', 'thrive-ab-page-testing' ) );
		}

		$attrs = array(
			'post_content' => '<p>Thank you page</p>',
			'post_title'   => $data['title'],
			'post_status'  => 'publish',
			'post_type'    => 'page',
		);

		$post_id = wp_insert_post( $attrs );

		if ( $post_id === 0 || is_wp_error( $post_id ) ) {
			die( __( 'Page could not be saved!', 'thrive-ab-page-testing' ) );
		}

		$post           = get_post( $post_id );
		$post->edit_url = tcb_get_editor_url( $post_id );

		return array(
			'post' => $post,
		);
	}

	public static function set_winner( $data ) {

		if ( empty( $data['page_id'] ) || empty( $data['variation_id'] ) ) {
			return null;
		}

		$page = new Thrive_AB_Page( (int) $data['page_id'] );

		$winner_variation = new Thrive_AB_Page_Variation( (int) $data['variation_id'] );
		$page_variation   = new Thrive_AB_Page_Variation( (int) $data['page_id'] );

		/**
		 * close test
		 */
		$running_test = $page->get_running_test();
		if ( ! ( $running_test instanceof Thrive_AB_Test ) ) {
			return null;
		}

		$test_item = new Thrive_AB_Test_Item();
		$filters   = array(
			'test_id'      => $running_test->id,
			'page_id'      => $data['page_id'],
			'variation_id' => $data['page_id'],
		);
		$test_item->init_by_filters( $filters );

		/**
		 * add new page variation to db with content from page
		 */
		$new_variation = $page->save_variation( array(
			'post_title' => $page_variation->post_title,
		) );

		$page_variation->get_meta()->init( array(
			'page',
			'template',
			'variation',
		) )->copy_to( $new_variation->ID );

		/**
		 * Changes the Winner Variation ID in the log table to be relevant for the report.
		 * This was done because on winning, a test item changes the variation_id
		 */
		Thrive_AB_Event_Manager::bulk_update_log( array( 'variation_id' => $new_variation->ID ), array(
			'variation_id' => $test_item->variation_id,
			'test_id'      => $test_item->test_id,
			'page_id'      => $test_item->page_id,
		) );

		/**
		 * for page test item set the new variation which has the content from page
		 */

		$test_item->variation_id = $new_variation->ID;


		/**
		 * set the content of winning variation to page
		 */
		$winner_variation->get_meta()->init( array(
			'page',
			'template',
			'variation',
		) );
		$winner_variation->get_meta()->copy_to( $page_variation->ID );

		if ( $winner_variation->is_control() ) {
			$new_variation->get_meta()->update( 'status', 'deleted' );
		} else {
			$winner_variation->get_meta()->update( 'status', 'deleted' );
		}
		$notification_manager_item = null;

		if ( (int) $test_item->is_control === 1 && (int) $test_item->id === (int) $data['id'] ) {
			$test_item->is_winner      = 1;
			$notification_manager_item = $test_item;
		} else {
			/**
			 * Save Winner Item
			 */
			$winner_item            = $winner_variation->get_test_item();
			$winner_item->is_winner = 1;
			$winner_item->save();

			$notification_manager_item = $winner_item;
		}

		$test_item->save();

		/**
		 * archive all other variations
		 */
		$all_variations = $page->get_variations( array(), 'obj' );

		/** @var Thrive_AB_Page_Variation $item */
		foreach ( $all_variations as $item ) {
			if ( $item->is_control() ) {
				continue;
			}

			$item->get_meta()->update( 'status', 'archived' );
		}
		/**
		 * Set status to completed once everything is done
		 */
		$running_test->stop()->save();

		$_nm_variation = (object) $notification_manager_item->get_data();
		$_nm_test      = (object) $running_test->get_data();

		$_nm_test->trigger_source = 'tab';
		$_nm_test->url            = $page->get_test_link( $running_test->id );
		$_nm_variation->variation = array( 'post_title' => $_nm_variation->title, 'key' => $_nm_variation->id );

		do_action( 'tab_action_set_test_item_winner', $_nm_variation, $_nm_test );

		return $data;
	}

	public static function localize( $data ) {

		if ( ! is_array( $data ) ) {
			$data = array( 'ajax' );
		}

		$post_id = $data['post']->post_parent ? (int) $data['post']->post_parent : (int) $data['post']->ID;
		try {
			$page         = new Thrive_AB_Page( $post_id );
			$running_test = $page->get_running_test();

			$data['ajax']['thrive_ab'] = array(
				'action'       => self::$action,
				'running_test' => $running_test instanceof Thrive_AB_Test ? $running_test->id : false,
			);
		} catch ( Exception $e ) {
		}

		return $data;
	}

	public static function save_variation_thumb() {

		if ( ! empty( $_REQUEST['reset_data'] ) ) {
			Thrive_AB_Event_Manager::reset_test_data( $_REQUEST['reset_data'] );
		}

		if ( ! isset( $_FILES['preview_file'] ) ) {
			return array(
				'success' => false,
			);
		}

		self::$post_id = $_REQUEST['post_id'];

		if ( ! function_exists( 'wp_handle_upload' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/file.php' );
		}

		add_filter( 'upload_dir', array( __CLASS__, 'upload_dir' ) );

		$moved_file = wp_handle_upload( $_FILES['preview_file'], array(
			'action'                   => 'thrive_ab_ajax_action',
			'unique_filename_callback' => array( __CLASS__, 'get_preview_filename' ),
		) );

		remove_filter( 'upload_dir', array( __CLASS__, 'upload_dir' ) );

		if ( empty( $moved_file['url'] ) ) {
			return array(
				'success' => false,
			);
		}

		$editor = wp_get_image_editor( $moved_file['file'] );

		$editor->resize( 800, 500 );
		$editor->save( $moved_file['file'] );

		return array(
			'success' => true,
		);
	}


	public static function get_preview_filename() {

		return self::$post_id . '.png';
	}


	public static function upload_dir( $upload ) {

		$sub_dir = '/thrive-ab-page-testing/variations';

		$upload['path']   = $upload['basedir'] . $sub_dir;
		$upload['url']    = $upload['baseurl'] . $sub_dir;
		$upload['subdir'] = $sub_dir;

		return $upload;
	}

	/**
	 * Callback of TD Lazy Load
	 *
	 * @param $array
	 * @param $params
	 *
	 * @return mixed
	 */
	public static function dashboard_lazy_load( $array, $params ) {

		/**
		 * register unique impression
		 */
		if ( ! empty( $params ) && is_array( $params ) && isset( $params['action'] ) && $params['action'] === self::REGISTER_IMPRESSION_ACTION_NAME ) {
			$page_id      = $params['page_id'];
			$test_id      = $params['test_id'];
			$variation_id = $params['variation_id'];

			Thrive_AB_Event_Manager::do_impression( $page_id, $test_id, $variation_id );
		}

		return $array;
	}
}

Thrive_AB_Ajax::init();
