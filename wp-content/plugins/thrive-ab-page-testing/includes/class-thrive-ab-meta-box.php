<?php
/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 11/13/2017
 * Time: 12:58 PM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

/**
 * Class Thrive_AB_Meta_Box
 */
class Thrive_AB_Meta_Box {
	private static $_instance;

	private $_page;

	/**
	 * @var int
	 */
	private $_completed_tests_number = 0;

	/**
	 * Thrive_AB_Meta_Box constructor.
	 */
	private function __construct() {
		add_action( 'load-post.php', array( $this, 'add_meta_logic' ) );
		add_action( 'admin_init', array( $this, 'meta_box_request_handler' ) );

		add_action( 'admin_footer', array( $this, 'include_additional_files' ) );
	}

	/**
	 * Checks the post type to be compatible with the plugin post type
	 */
	public function add_meta_logic() {

		if ( empty( $_GET['post'] ) || ! thrive_ab()->is_cpt_allowed( get_post_type( $_GET['post'] ) ) ) {
			return;
		}

		if ( ! isset( $_GET['action'] ) || $_GET['action'] !== 'edit' ) {
			return;
		}

		$this->_page     = new Thrive_AB_Page( (int) $_GET['post'] );
		$variations      = $this->_page->get_variations();
		$completed_tests = $this->_page->get_tests( array( 'status' => 'completed' ), 'instance' );

		if ( count( $variations ) >= 2 ) {
			add_action( 'add_meta_boxes', array( $this, 'add_variation_table_meta_box' ) );
		}

		$this->_completed_tests_number = count( $completed_tests );

		if ( $this->_completed_tests_number >= 1 ) {
			add_action( 'add_meta_boxes', array( $this, 'add_completed_tests_table_meta_box' ) );
		}

		/**
		 * Enqueue Meta Box Scripts
		 */
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_meta_box_scripts' ) );
	}

	/**
	 * Adds Scripts to the Meta Box Screen
	 *
	 * @param $hook
	 */
	public function enqueue_meta_box_scripts( $hook ) {

		thrive_ab()->enqueue_style( 'thrive-ab-testing-edit-post', plugin_dir_url( THRIVE_AB_PLUGIN_FILE ) . 'assets/css/edit-post.css' );
		thrive_ab()->enqueue_script( 'thrive-ab-testing-edit-post', plugin_dir_url( THRIVE_AB_PLUGIN_FILE ) . 'assets/js/dist/edit-post.min.js', array( 'jquery' ) );

		wp_localize_script( 'thrive-ab-testing-edit-post', 'ThriveAbEditPost', $this->get_localization() );
	}

	/**
	 * Localization variables for Edit Post View
	 *
	 * @return array
	 */
	public function get_localization() {

		return array(
			'ajax' => array(
				'url'               => admin_url( 'admin-ajax.php' ),
				'nonce'             => wp_create_nonce( Thrive_AB_Ajax::NONCE_NAME ),
				'action'            => Thrive_AB_Ajax::$action,
				'controller_action' => Thrive_AB_Ajax::$controller_action,
			),
		);
	}

	public function meta_box_request_handler() {

		if ( ! empty( $_REQUEST['action'] ) && $_REQUEST['action'] === 'thrive-ab-tests-delete' ) {

			Thrive_AB_Test_Manager::delete_test( array(
				'id'      => $_REQUEST['ab_test_ID'],
				'page_id' => $_REQUEST['post_ID'],
			) );

			$return_url = get_edit_post_link( $_REQUEST['post_ID'], '' );
			wp_redirect( $return_url );
			exit;
		}
	}

	/**
	 * Includes svg icons to editor page
	 */
	public function include_additional_files() {
		if ( empty( $_GET['post'] ) || ! thrive_ab()->is_cpt_allowed( get_post_type( $_GET['post'] ) ) ) {
			return;
		}

		if ( ! isset( $_GET['action'] ) || $_GET['action'] !== 'edit' ) {
			return;
		}

		include dirname( __FILE__ ) . '/../assets/fonts/icons.svg';
	}

	public static function instance() {
		if ( ! self::$_instance ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Adds The Variations Meta Box to the screen
	 */
	public function add_variation_table_meta_box() {
		add_meta_box(
			'thrive_ab_test_variation_table_meta_box',
			__( 'Thrive Optimize - A/B Test Overview', 'thrive-ab-page-testing' ),
			array( $this, 'show_variation_table_meta_box' ),
			$this->_page->post_type,
			'normal',
			'high'
		);
	}

	/**
	 * Adds the Tests Meta Box to the screen
	 */
	public function add_completed_tests_table_meta_box() {
		add_meta_box(
			'thrive_ab_test_completed_tests_table_meta_box',
			__( 'Thrive Completed A/B Tests', 'thrive-ab-page-testing' ) . ' (' . $this->_completed_tests_number . ')',
			array( $this, 'show_completed_tests_table_meta_box' ),
			$this->_page->post_type,
			'normal',
			'high'
		);
	}

	/**
	 * Callback for variation meta box function
	 */
	public function show_variation_table_meta_box() {
		require_once 'class-thrive-ab-meta-box-variations-table.php';

		$variations_table = new Thrive_AB_Meta_Box_Variations_Table( $this->_page );
		$variations_table->prepare_items();
		$variations_table->display();
	}

	/**
	 * Callback for tests meta box function
	 */
	public function show_completed_tests_table_meta_box() {
		require_once 'class-thrive-ab-meta-box-completed-tests-table.php';

		$completed_tests_table = new Thrive_AB_Meta_Box_Completed_Tests_Table( $this->_page );
		$completed_tests_table->prepare_items();
		$completed_tests_table->display();
	}
}

Thrive_AB_Meta_Box::instance();
