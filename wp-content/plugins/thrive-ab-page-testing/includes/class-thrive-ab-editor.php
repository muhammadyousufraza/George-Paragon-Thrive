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
 * Class Thrive_AB_Editor
 *
 * Should work only in TAr Edit Mode
 */
class Thrive_AB_Editor {

	protected static $_instance;

	/**
	 * @var WP_Post
	 */
	protected $_post;

	private function __construct( $post ) {

		$this->_post = $post;

		add_action( 'tcb_sidebar_extra_links', array( $this, 'render_buttons' ) );
		add_action( 'tcb_main_frame_enqueue', array( $this, 'enqueue' ) );
		add_action( 'tcb_has_templates_tab', array( __CLASS__, 'has_templates_tab' ), 10, 1 );
		add_action( 'tcb_can_use_landing_pages', array( __CLASS__, 'can_use_landing_pages' ), 10, 1 );
		add_filter( 'tcb_main_frame_localize', array( 'Thrive_AB_Ajax', 'localize' ) );
		/**
		 * Includes the modal template files
		 */
		add_filter( 'tcb_modal_templates', array( __CLASS__, 'include_modal_files' ), 10, 1 );
	}

	/**
	 * Init the AB_Editor based on current post type
	 * and returns the instance if post type is allowed
	 *
	 * @return null|Thrive_AB_Editor
	 */
	public static function init() {

		$post = get_post();

		if ( ! thrive_ab()->is_cpt_allowed( $post->post_type ) && $post->post_type !== Thrive_AB_Post_Types::VARIATION ) {
			return null;
		}

		if ( ! self::$_instance ) {
			self::$_instance = new self( $post );
		}

		return self::$_instance;
	}

	public static function include_modal_files( $files = array() ) {
		$files[] = thrive_ab()->path( 'includes/views/backbone/editor-modals/reset-stats.php' );

		return $files;
	}

	public static function has_templates_tab( $has ) {

		global $post;

		if ( $post->post_type === Thrive_AB_Post_Types::VARIATION ) {
			$has = true;
		}

		return $has;
	}

	public static function can_use_landing_pages( $can ) {

		global $post;

		if ( $post->post_type === Thrive_AB_Post_Types::VARIATION ) {
			$can = true;
		}

		return $can;
	}

	/**
	 * Echoes the html buttons on the top sidebar of TAr Editor
	 */
	public function render_buttons() {

		$html = '';

		$page_id      = thrive_ab()->maybe_variation( $this->_post ) ? $this->_post->post_parent : $this->_post->ID;
		$test_manager = new Thrive_AB_Test_Manager();
		$test         = $test_manager->get_running_test( $page_id );
		if ( $test ) {
			$test_url = Thrive_AB_Test_Manager::get_test_url( $test->id );
			ob_start();
			include 'views/editor/html-running-test-button.php';
			$html = ob_get_clean();
		} elseif ( thrive_ab()->maybe_variation( $this->_post ) ) {
			ob_start();
			include 'views/editor/html-variation-button.php';
			$html = ob_get_clean();
		} elseif ( thrive_ab()->is_cpt_allowed( $this->_post->post_type ) && Thrive_AB_Product::has_access() ) {
			ob_start();
			include 'views/editor/html-buttons.php';
			$html = ob_get_clean();
		}

		echo $html;
	}

	/**
	 * Enqueues the required styles and scripts for TAr Editor
	 */
	public function enqueue() {

		/**
		 * scripts
		 */
		wp_enqueue_script( 'thrive-ab-testing-editor-script', plugin_dir_url( THRIVE_AB_PLUGIN_FILE ) . 'assets/js/dist/editor.min.js', array( 'tve-main' ), Thrive_AB::V, true );

		/**
		 * styles
		 */
		wp_enqueue_style( 'thrive-ab-testing-editor-style', plugin_dir_url( THRIVE_AB_PLUGIN_FILE ) . 'assets/css/editor.css', array( 'tve2_editor_style' ), Thrive_AB::V );
	}

	public function get_dashboard_url() {

		if ( ! thrive_ab()->license_activated() ) {
			return admin_url( 'admin.php?page=tve_dash_section' );
		}

		$is_variation = $this->_post->post_type === Thrive_AB_Post_Types::VARIATION || $this->_post->post_status === Thrive_AB_Post_Status::VARIATION;

		$url = get_permalink( $is_variation ? $this->_post->post_parent : $this->_post );
		$url = add_query_arg( 'thrive-variations', 'true', $url );

		return $url;
	}
}
