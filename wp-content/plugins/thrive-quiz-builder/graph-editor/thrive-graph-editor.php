<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-graph-editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

if ( ! class_exists( 'Thrive_Graph_editor' ) ) {
	final class Thrive_Graph_Editor {

		const EDITOR_FLAG = 'tge';

		/**
		 * Graph Editor Database Version
		 */
		const DB = '1.0.6';

		/**
		 * Database prefix for tables
		 */
		const DB_PREFIX = 'tge_';

		/**
		 * @var TGE_Query
		 */
		private $query;

		/**
		 * @var TGE_Editor
		 */
		private $editor;

		/**
		 * @var Thrive_Graph_Editor
		 */
		private static $instance = null;

		/**
		 * Thrive_Graph_Editor constructor.
		 */
		private function __construct() {

			$this->_includes();

			$this->query = new TGE_Query();

			$this->_init();
		}

		private function _init() {
			add_action( 'template_redirect', array( $this, 'init_editor' ) );
			add_action( 'tqb-quiz-results-modified', array( $this, 'on_results_updated' ), 10, 3 );
			$this->fix_conflicts();
			$this->load_plugin_textdomain();
		}

		public function fix_conflicts() {

			if ( empty( $_REQUEST['tge'] ) ) {
				return;
			}

			//fix for X-Theme, don't let it enqueue its js
			add_filter( 'x_legacy_cranium_headers', '__return_false' );
			add_filter( 'x_legacy_cranium_footers', '__return_false' );

			//don't let MailChimp plugin initiate on Question Editor Page
			remove_action( 'init', 'mailchimpSF_plugin_init' );

			//Conflict with LeadPages
			//Added this for when the user modifies the url with index.php.
			add_filter( 'redirect_canonical', '__return_false' );
			add_action( 'wp_print_scripts', array( $this, 'dequeue_select2_js' ), 100 );
		}

		private function _includes() {
			require_once( 'includes/tge-global-functions.php' );
			require_once( 'includes/classes/class-tge-query.php' );
			//TODO: check these files and maybe move them
			require_once( 'includes/classes/class-tge-db.php' );
			require_once( 'includes/classes/class-tge-question-manager.php' );
			require_once( 'includes/classes/class-tge-link-manager.php' );

			require_once( 'database/class-tge-database-manager.php' );
			require_once( 'includes/classes/class-tge-ajax.php' );
			require_once( 'includes/classes/class-tge-ajax-controller.php' );
		}

		public function is_request( $type ) {
			switch ( $type ) {
				case 'admin' :
					return is_admin();
				case 'ajax' :
					return defined( 'DOING_AJAX' );
				case 'cron' :
					return defined( 'DOING_CRON' );
				case 'frontend' :
					return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' );
			}
		}

		/**
		 * @return Thrive_Graph_Editor
		 */
		public static function instance() {
			if ( empty( self::$instance ) ) {
				self::$instance = new self;
			}

			return self::$instance;
		}

		public function init_editor() {

			if ( true !== (bool) $this->query->get_var( self::EDITOR_FLAG ) ) {
				return;
			}

			require_once( 'includes/classes/class-tge-editor.php' );
			$this->editor = TGE_Editor::instance();
		}

		public function load_plugin_textdomain() {
			$locale = apply_filters( 'plugin_locale', get_locale(), 'thrive-graph-editor' );

			load_textdomain( 'thrive-graph-editor', WP_LANG_DIR . '/thrive/thrive-graph-editor-' . $locale . '.mo' );
			load_plugin_textdomain( 'thrive-graph-editor', false, plugin_basename( dirname( __FILE__ ) ) . '/languages' );
		}

		public function url( $file = '' ) {
			return plugin_dir_url( __FILE__ ) . ltrim( $file, '\\/' );
		}

		public function path( $file = '' ) {
			return plugin_dir_path( __FILE__ ) . ltrim( $file, '\\/' );
		}

		/**
		 * @param int|WP_Post $post_id
		 *
		 * @return null|string
		 */
		public function editor_url( $post_id ) {

			if ( ! is_numeric( $post_id ) && ! ( $post_id instanceof WP_Post ) ) {
				return null;
			}

			if ( $post_id instanceof WP_Post ) {
				$post_id = $post_id->ID;
			}

			/**
			 * we need to make sure that if the admin is https, then the editor link is also https, otherwise any ajax requests through wp ajax api will not work
			 */
			$admin_ssl = strpos( admin_url(), 'https' ) === 0;
			$post_id   = $post_id ? $post_id : get_the_ID();

			$post        = get_post( $post_id );
			$editor_link = set_url_scheme( get_permalink( $post_id ) );
			$editor_link = esc_url( apply_filters( 'preview_post_link', add_query_arg( apply_filters( 'tge_edit_link_query_args', array( Thrive_Graph_Editor::EDITOR_FLAG => 'true' ), $post_id ), $editor_link ), $post ) );

			return $admin_ssl ? str_replace( 'http://', 'https://', $editor_link ) : $editor_link;
		}

		public function get_editor() {
			return $this->editor;
		}

		/**
		 * Global function for graph editor that counts the quiz questions
		 *
		 * @param int   $post_id
		 * @param array $filters
		 *
		 * @return int|null
		 */
		public function count_questions( $post_id = 0, $filters = [] ) {

			if ( ! is_numeric( $post_id ) && ! ( $post_id instanceof WP_Post ) ) {
				return null;
			}

			if ( $post_id instanceof WP_Post ) {
				$post_id = $post_id->ID;
			}

			$question_manager = new TGE_Question_Manager( $post_id );

			return $question_manager->count_questions( $filters );
		}

		/**
		 * Deletes all quiz data from the graph editor table
		 *
		 * @param int $post_id
		 *
		 * @return bool|null
		 */
		public function delete_all_quiz_dependencies( $post_id = 0 ) {

			if ( ! is_numeric( $post_id ) && ! ( $post_id instanceof WP_Post ) ) {
				return null;
			}

			if ( $post_id instanceof WP_Post ) {
				$post_id = $post_id->ID;
			}

			$question_manager = new TGE_Question_Manager( $post_id );

			return $question_manager->delete_quiz_dependencies();
		}

		public function on_results_updated( $quiz_id, $old_results, $new_results ) {

			function get_ids( $result ) {
				return $result['id'];
			}

			$old_ids     = array_map( 'get_ids', $old_results );
			$new_ids     = array_map( 'get_ids', $new_results );
			$deleted_ids = array_diff( $old_ids, $new_ids );

			if ( empty( $deleted_ids ) ) {
				return true;
			}

			$qm = new TGE_Question_Manager( $quiz_id );

			return $qm->set_answers_on_none( $deleted_ids );
		}

		public function dequeue_select2_js() {
			global $post;

			if ( $post instanceof WP_Post && TQB_Post_types::QUIZ_POST_TYPE === $post->post_type ) {
				wp_dequeue_script( 'select2' );
				wp_deregister_script( 'select2' );
			}
		}
	}
}


function tge() {
	return Thrive_Graph_Editor::instance();
}

tge();
