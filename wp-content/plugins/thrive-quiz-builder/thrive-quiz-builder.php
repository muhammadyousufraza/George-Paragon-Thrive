<?php

/*
Plugin Name: Thrive Quiz Builder
Plugin URI: https://thrivethemes.com
Version: 3.28
Author: <a href="https://thrivethemes.com">Thrive Themes</a>
Description: The plugin is built to deliver the following benefits to users: engage visitors with fun and interesting quizzes, lower bounce rate, generate more leads and gain visitor insights to find out about their interests.
Text Domain: thrive-quiz-builder
*/

if ( ! class_exists( 'Thrive_Quiz_Builder' ) ) :

	/**
	 * Main TQB Class
	 */
	final class Thrive_Quiz_Builder {

		/**
		 * Plugin version
		 */
		const V = '3.28';

		/**
		 * Quiz Builder Database Version
		 */
		const DB = '1.0.10';

		/**
		 * Quiz Builder database prefix
		 */
		const DB_PREFIX = 'tqb_';

		/**
		 * The single instance of the class.
		 *
		 * @var Thrive_Quiz_Builder singleton instance.
		 */
		protected static $_instance = null;

		public $tvd_tags_text = 'Tags obtained from the visitor\'s answers will be automatically added.';

		/**
		 * Quiz shortcode name
		 */
		const SHORTCODE_NAME = 'tqb_quiz';

		/**
		 * Results page test conversion goals
		 */
		const CONVERSION_GOAL_OPTIN = '1';

		const CONVERSION_GOAL_SOCIAL = '2';

		/**
		 * Quiz types
		 */
		const QUIZ_TYPE_NUMBER = 'number';

		const QUIZ_TYPE_PERCENTAGE = 'percentage';

		const QUIZ_TYPE_PERSONALITY = 'personality';

		const QUIZ_TYPE_RIGHT_WRONG = 'right_wrong';

		const QUIZ_TYPE_SURVEY = 'survey';

		const QUIZ_TYPE_SURVEY_TPL_ID = 4;

		const TQB_LAST_7_DAYS = 1;

		const TQB_LAST_30_DAYS = 2;

		const TQB_THIS_MONTH = 3;

		const TQB_LAST_MONTH = 4;

		const TQB_THIS_YEAR = 5;

		const TQB_LAST_YEAR = 6;

		const TQB_LAST_12_MONTHS = 7;

		const TQB_CUSTOM_DATE_RANGE = 8;

		const TQB_DASH_MAX_QUIZZES_IDENTIFIER = 15;

		/**
		 * Chart Colors
		 */
		const CHART_RED = '#F60000';

		const CHART_GREEN = '#006600';

		const CHART_GREY = '#C0C0C0';

		/**
		 * Quiz structure item types
		 */
		const QUIZ_STRUCTURE_ITEM_SPLASH_PAGE = 'tqb_splash';

		const QUIZ_STRUCTURE_ITEM_QNA = 'tqb_qna';

		const QUIZ_STRUCTURE_ITEM_OPTIN = 'tqb_optin';

		const QUIZ_STRUCTURE_ITEM_RESULTS = 'tqb_results';

		/**
		 * Impression and conversion event type representation
		 */
		const TQB_SKIP_OPTIN = 3;
		const TQB_CONVERSION = 2;
		const TQB_IMPRESSION = 1;

		/**
		 * Variation status
		 */
		const VARIATION_STATUS_PUBLISH = 'publish';

		const VARIATION_STATUS_ARCHIVE = 'archive';

		const RESTART_QUIZ_IDENTIFIER = 'restart_quiz';

		const NEXT_STEP_IN_QUIZ = 'next_step_in_quiz';

		/**
		 * Other Constants
		 */
		const VARIATION_QUERY_KEY_NAME = 'tqb_key';
		const VARIATION_QUERY_CHILD_KEY_NAME = 'tqb_child_key';

		/**
		 * Fields
		 */
		const FIELD_TEMPLATE = 'tpl';

		const FIELD_CONTENT = 'content';

		const FIELD_INLINE_CSS = 'inline_css';

		const FIELD_USER_CSS = 'user_css';

		const FIELD_CUSTOM_FONTS = 'fonts';

		const FIELD_ICON_PACK = 'icons';

		const FIELD_MASONRY = 'masonry';

		const FIELD_TYPEFOCUS = 'typefocus';

		const FIELD_SOCIAL_SHARE_BADGE = 'social_share_badge';

		/**
		 * States
		 */
		const STATES_MAXIMUM_NUMBER_OF_INTERVALS = 25;

		const STATES_DYNAMIC_CONTENT_DEFAULT = '<div class="thrv_wrapper thrv_text_element"><p>Your dynamic content here...</p></div>';

		const STATES_DYNAMIC_CONTENT_PATTERN = '<div style="display:none">__TQB__dynamic_DELIMITER</div>';

		const STATES_MINIMUM_WIDTH_SIZE = 20; // The minimum width size (in pixel) that a state could have.

		/**
		 * YouTube videos
		 */
		const VIDEO_QUIZ_TYPE_NUMBER = 'vE_FPPKYnp4';

		const VIDEO_QUIZ_TYPE_PERCENTAGE = 'Tgdb6Nt-YAU';

		const VIDEO_QUIZ_TYPE_PERSONALITY = 'NgnuvywZdoo';

		const VIDEO_QUIZ_TYPE_RIGHT_WRONG = 'MxuWm78ESsw';

		const VIDEO_PAGE_SPLASH = 'Y2LfQRyx9CU';

		const VIDEO_PAGE_OPTIN = 'OqGLZXo0Roo';

		const VIDEO_PAGE_RESULTS = '1hPXGG_-9MY';

		const VIDEO_PAGE_REDIRECT = 'ZoOI-K4PHe0';

		const VIDEO_PLUGIN_DASHBOARD = 'YhRSbKisxPE';

		const VIDEO_QUIZ_TEMPLATE_SCRATCH = 'tY-06QauMIw';

		const VIDEO_QUIZ_TEMPLATE_LIST = 'vUxywURtmQU';

		const VIDEO_QUIZ_TEMPLATE_SOCIAL = 'BcrWez4FRSo';

		const VIDEO_QUIZ_TEMPLATE_SURVEY = 'C96NXPB8ows';

		const VIDEO_QUIZ_TEMPLATE_GAIN_CUSTOMER = 'QdErG9NFpVY';

		const VIDEO_QUIZ_STYLES = 'EF8pBKrpMfo';

		const VIDEO_A_B_TEST = 'Jo2WjsFb4kw';

		/**
		 * Knowledge base article about linking variations to next step in quiz
		 */
		const KB_NEXT_STEP_ARTICLE = 'https://thrivethemes.com/tkb_item/how-to-link-to-the-next-stage-in-the-quiz-manually-using-the-live-editor/';

		/**
		 * Quiz final result shortcode
		 */
		const QUIZ_RESULT_SHORTCODE = '%result%';

		const QUIZ_RESULT_SOCIAL_MEDIA_MSG = 'I got: %result%';

		/**
		 * Default shortcode used for result page
		 */
		const QUIZ_RESULT_DEFAULT_SHORTCODE = "[tqb_quiz_result result_type='default']";

		/**
		 * Upload directory custom folder for thrive quiz builder
		 */
		const UPLOAD_DIR_CUSTOM_FOLDER = 'thrive-quiz-builder';

		/**
		 * Quiz Builder Plugin Settings
		 */
		const PLUGIN_SETTINGS = 'tqb_settings';

		/**
		 * Plugin Sales Page URL
		 */
		const PLUGIN_SALES_PAGE = 'https://thrivethemes.com/quizbuilder';

		/**
		 * Plugin Idev ref id
		 */
		const PLUGIN_IDEV_ID = 37;

		/**
		 * Comma Placeholder
		 */
		const COMMA_PLACEHOLDER = 'comma_placeholder';

		/**
		 * A list with all fields that TCB uses to store various pieces of content / flags
		 *
		 * @return array
		 */
		public static function editor_fields() {
			return array(
				self::FIELD_CUSTOM_FONTS,
				self::FIELD_INLINE_CSS,
				self::FIELD_USER_CSS,
				self::FIELD_TEMPLATE,
				self::FIELD_TYPEFOCUS,
				self::FIELD_MASONRY,
				self::FIELD_ICON_PACK,
				self::FIELD_SOCIAL_SHARE_BADGE,
			);
		}

		/**
		 * Thrive Quiz Builder Constructor.
		 */
		private function __construct() {
			$this->includes();
			$this->init_hooks();
		}

		/**
		 * Hook into actions and filters
		 */
		private function init_hooks() {
			add_action( 'plugins_loaded', array( $this, 'load_dashboard_module' ) );
			add_action( 'init', array( $this, 'init' ) );
			add_action( 'init', 'TQB_Blocks::init' );
			add_action( 'thrive_dashboard_loaded', array( $this, 'dash_loaded' ) );

			/**
			 * Register impression and conversion hooks
			 */
			add_action( 'tqb_register_impression', array( 'TQB_Quiz_Manager', 'tqb_register_impression' ), 10, 2 );
			add_action( 'tqb_register_conversion', array( 'TQB_Quiz_Manager', 'tqb_register_conversion' ), 10, 2 );
			add_action(
				'tqb_register_skip_optin',
				array(
					'TQB_Quiz_Manager',
					'tqb_register_skip_optin_event',
				),
				10,
				2
			);
			add_action( 'tcb_api_form_submit', array( 'TQB_Quiz_Manager', 'tqb_register_optin_conversion' ) );
			add_action( 'tqb_register_social_media_conversion', array(
				'TQB_Quiz_Manager',
				'tqb_register_social_media_conversion',
			) );
			add_action( 'thrive_core_lead_signup', array( 'TQB_Quiz_Manager', 'tqb_lead_signup' ), 10, 2 );

			/**
			 * Load Thrive Dashboard Ajax Load
			 */
			add_filter( 'tve_dash_main_ajax_tqb_lazy_load', array( $this, 'tqb_frontend_ajax_load' ) );
			add_filter( 'tve_dash_enqueue_frontend', '__return_true' );

			add_filter( 'tcb_api_subscribe_data_instance', array( $this, 'filter_subscribe_data' ), 10, 2 );

			/**
			 * Modify default no-cache headers
			 */
			add_filter( 'nocache_headers', array( $this, 'filter_nocache_headers' ), 10, 1 );

			add_filter( 'td_nm_trigger_types', array( $this, 'filter_nm_trigger_types' ) );

			add_filter( 'tvd_tags_text_for_activecampaign', array( $this, 'filter_tags_text' ) );
			add_filter( 'tvd_tags_text_for_aweber', array( $this, 'filter_tags_text' ) );
			add_filter( 'tvd_autoresponder_render_extra_editor_settings_infusionsoft', array(
				$this,
				'display_infusion_soft_tags_text',
			) );

			add_action( 'tvd_after_infusionsoft_contact_added', array( $this, 'assign_infusionsoft_tags' ), 10, 4 );

			/**
			 * Add the tqb product all the time to able to check external capabilities
			 * e.g global elements requests permissions
			 */
			add_filter( 'tve_dash_installed_products', array( $this, 'add_to_dashboard_list' ) );

			add_filter( 'tve_dash_metrics_should_enqueue', array( $this, 'should_enqueue_metrics' ) );

			add_filter( 'tcb_symbol_content', array( $this, 'change_symbol_content' ) );

			add_filter( 'tcb_dynamiclink_data', array( $this, 'tcb_dynamiclink_data' ) );

			add_filter( 'tcb_inline_shortcodes', array( $this, 'inline_shortcodes' ) );

			add_filter( 'is_protected_meta', array( $this, 'is_protected_meta' ), 10, 2 );

			register_activation_hook( __FILE__, array( $this, 'activation_hook' ) );

			/**
			 * WP-Rocket Compatibility - exclude files from caching
			 */
			add_filter( 'rocket_exclude_css', array( $this, 'rocket_exclude_css' ) );
			add_filter( 'rocket_exclude_js', array( $this, 'rocket_exclude_js' ) );

			add_filter( 'tve_dash_email_data', array( $this, 'tve_dash_email_data' ), 10, 2 );

			if ( wp_doing_ajax() ) {
				add_filter( 'tcb_form_api_tags', array( $this, 'process_api_result_tags' ) );
			}

			add_action( 'wp_head', 'tve_load_custom_css', 100, 0 );

			TQB_Lightspeed::init();

			TQB\Reporting\Main::init();

			add_action( 'thrive_automator_init', array( 'TQB\Automator\Main', 'init' ) );

			add_filter( 'tcb_can_export_content', array( $this, 'hide_export_content' ), 10, 2 );
			add_filter( 'tve_aut_banned_post_types', array( $this, 'tcb_hide_templates' ) );
		}


		/**
		 * Push Thrive Quiz Builder to Thrive Dashboard installed products list
		 *
		 * @param array $items all the thrive products.
		 *
		 * @return array
		 */
		public function add_to_dashboard_list( $items ) {
			$items[] = new TQB_Product();

			return $items;
		}

		/**
		 * Use Thrive Dashboard Infusionsoft Connection to assign TQB tags to contact
		 * This is a callback of a hook(action) thrown in Thrive Dashboard
		 *
		 * @param Thrive_Dash_List_Connection_Infusionsoft $connection
		 * @param array                                    $contact
		 * @param int                                      $list_id
		 * @param array                                    $arguments
		 */
		public function assign_infusionsoft_tags( $connection, $contact, $list_id, $arguments ) {

			if ( empty( $arguments['tqb_tags'] ) ) {
				return;
			}

			$new_tags = explode( ',', $arguments['tqb_tags'] );

			$new_tags = array_map( 'strtolower', $new_tags );
			$new_tags = array_map( 'trim', $new_tags );
			$new_tags = array_unique( $new_tags );

			/** @var $connection Thrive_Dash_List_Connection_Infusionsoft */
			$contact_tags = $connection->get_contact_tags( $contact['Id'] );
			if ( ! empty( $contact_tags ) ) {
				$contact_tags = array_map( 'strtolower', $contact_tags );
			}

			$existing_tags = $connection->get_tags();
			if ( ! empty( $existing_tags ) ) {
				$existing_tags = array_map( 'strtolower', $existing_tags );
			}

			$tags_to_be_assigned = array_diff( $new_tags, $contact_tags );

			foreach ( $tags_to_be_assigned as $tag_name ) {
				$tag_id = array_search( $tag_name, $existing_tags, false );
				if ( empty( $tag_id ) ) {
					$tag_id = $connection->create_tag( $tag_name );
				}
				$connection->get_api()->contact( 'addToGroup', $contact['Id'], $tag_id );
			}
		}

		/**
		 * Based on $connection instance append tags to it
		 *
		 * @param                                      $data
		 * @param Thrive_Dash_List_Connection_Abstract $connection
		 *
		 * @return mixed
		 */
		public function filter_subscribe_data( $data, $connection ) {

			if ( ! isset( $data['tqb-variation-user_unique'] ) && ! isset( $data['tqb-variation-page_id'] ) ) {
				return $data;
			}

			if ( ! $connection->has_tags() ) {
				return $data;
			}

			$user_unique = $data['tqb-variation-user_unique'];
			$page_id     = $data['tqb-variation-page_id'];
			$page        = get_post( $page_id );
			$quiz_id     = $page->post_parent;

			$display_tags = (bool) get_post_meta( $quiz_id, 'tge_display_tags', true );
			if ( ! $display_tags ) {
				return $data;
			}

			$user_id = TQB_Quiz_Manager::get_quiz_user( $user_unique, $quiz_id );

			$reporting_manager = new TQB_Reporting_Manager( $quiz_id, 'users' );
			$tags              = $reporting_manager->get_users_chosen_tags( $user_id );

			if ( empty( $tags ) ) {
				return $data;
			}

			return $connection->push_tags( $tags, $data );
		}

		public function display_infusion_soft_tags_text() {

			if ( ! empty( $_REQUEST['post_id'] ) ) {
				$page = get_post( absint( $_REQUEST['post_id'] ) );

				if ( $page instanceof WP_Post && $page->post_parent ) {
					$display_tags = (bool) get_post_meta( $page->post_parent, 'tge_display_tags', true );
				}
			}

			if ( isset( $_REQUEST['tqb_key'], $display_tags, $display_tags ) ) {
				echo '<br/><p>' . esc_html__( $this->tvd_tags_text, 'thrive-quiz-builder' ) . '</p>';
			}
		}

		/**
		 * Append TQB text to existing text for tags set from TD auto-responder
		 *
		 * @param $text
		 *
		 * @return string
		 */
		public function filter_tags_text( $text ) {

			if ( ! empty( $_REQUEST['post_id'] ) ) {
				$page = get_post( absint( $_REQUEST['post_id'] ) );

				if ( $page instanceof WP_Post && $page->post_parent ) {
					$display_tags = (bool) get_post_meta( $page->post_parent, 'tge_display_tags', true );
				}
			}

			if ( isset( $_REQUEST['tqb_key'], $display_tags, $display_tags ) ) {
				$text .= '. <br/> ' . esc_html__( $this->tvd_tags_text, 'thrive-quiz-builder' );
			}

			return $text;
		}

		public function filter_nm_trigger_types( $trigger_types ) {

			if ( ! array_key_exists( 'quiz_completion', $trigger_types ) ) {
				$trigger_types['quiz_completion'] = __( 'Quiz Completion', 'thrive-quiz-builder' );
			}

			if ( ! array_key_exists( 'split_test_ends', $trigger_types ) ) {
				$trigger_types['split_test_ends'] = __( 'A/B Test Ends', 'thrive-quiz-builder' );
			}

			return $trigger_types;
		}

		/**
		 * Handle lazy loading
		 */
		public function tqb_frontend_ajax_load() {

			$quiz_ids     = ! empty( $_REQUEST['quiz_ids'] ) ? array_map( 'absint', $_REQUEST['quiz_ids'] ) : [];
			$restart_quiz = ! empty( $_POST['restart_quiz'] ) && (int) $_POST['restart_quiz'] === 1;
			$post_id      = (int) $_POST['post_id'];
			$data         = array();
			foreach ( $quiz_ids as $key => $id ) {
				if ( $restart_quiz ) {

					if ( is_user_logged_in() ) {
						/**
						 * Generate a new User Try in when restart quiz button is pressed
						 */
						TQB_Quiz_Manager::get_quiz_user( uniqid( 'tqb-user-', true ), $id, false, array(
							'object_id' => $post_id,
						) );
					}

					/**
					 * The hook is triggered when a user restarts the same quiz. It can be fired multiple times, if the user chooses to restart the quiz multiple times
					 * </br></br>
					 * Example use case:- Record the number of times a student took the quiz in order to achieve a score.
					 *
					 * @param array Quiz Details
					 * @param array User Details
					 *
					 * @api
					 */
					do_action( 'thrive_quizbuilder_quiz_restarted', TQB_Quiz_Manager::get_quiz_details( $id, null ), tvd_get_current_user_details() );
				}

				$question_manager = new TGE_Question_Manager( $id );
				$questions        = $question_manager->get_quiz_questions( array( 'with_answers' => true ) );

				$quiz_type = TQB_Post_meta::get_quiz_type_meta( $id, true );
				$progress  = tqb_progress_settings_instance( (int) $id )->get();

				if ( is_user_logged_in() ) {
					$user_unique = (string) tqb_customer()->get_user_unique_id( $id, $post_id );
					if ( $user_unique === $key ) {
						$page      = tqb_customer()->get_resume_quiz_page( $id, $post_id );
						$answer_id = tqb_customer()->get_resume_quiz_last_answer_id( $id, $post_id );

						$data[ $key ] = TQB_Quiz_Manager::get_shortcode_content( $id, $page, $answer_id, $key );

						if ( $page === 'splash' && is_array( $progress ) && ! empty( $progress['display_progress'] ) ) {
							$data[ $key ]['resume_flow'] = tqb_customer()->get_resume_quiz_user_answers( $id, $post_id );
						}

						$data[ $key ]['user_answers'] = tqb_customer()->get_resume_quiz_user_answers( $id, $post_id );

						$data[ $key ]['resume'] = 1;
					} elseif ( $restart_quiz ) {
						$data[ $key ]              = TQB_Quiz_Manager::get_shortcode_content( $id, null, null, $user_unique );
						$data[ $key ]['restarted'] = true;
					}
				}

				if ( empty( $data[ $key ] ) ) {
					$data[ $key ] = TQB_Quiz_Manager::get_shortcode_content( $id );
				}

				$data[ $key ]['all_questions'] = $questions;
				$data[ $key ]['quiz_id']       = $id;
				$data[ $key ]['quiz_url']      = str_replace( array( '?p=', '&p=' ), array( '?tqb_quiz_id=', '&tqb_quiz_id=' ), get_permalink( $id ) );
				$data[ $key ]['quiz_type']     = $quiz_type;

				$data[ $key ]['results_settings'] = array();
				$results_page                     = get_posts( array(
					'post_parent' => $id,
					'post_type'   => self::QUIZ_STRUCTURE_ITEM_RESULTS,
				) );
				if ( ! empty( $results_page ) ) {
					$data[ $key ]['results_settings'] = $results_page;
					$result_page_instance             = new TQB_Results_Page( current( $results_page ) );
					$settings                         = $result_page_instance->to_json();
					$settings->links                  = $result_page_instance->get_links();
					$data[ $key ]['results_settings'] = $settings;
				}
				$data[ $key ]['structure'] = get_post_meta( $id, TQB_Post_meta::META_NAME_FOR_QUIZ_STRUCTURE, true );

				$data[ $key ]['feedback_settings']  = TQB_Post_meta::get_feedback_settings_meta( $id );
				$data[ $key ]['highlight_settings'] = TQB_Post_meta::get_highlight_settings_meta( $id );
				$data[ $key ]['progress_settings']  = $progress;
				$data[ $key ]['scroll_settings']    = TQB_Post_meta::get_quiz_scroll_settings_meta( $id );
				$data[ $key ]['quiz_style']         = TQB_Post_meta::get_quiz_style_meta( $id );
				$data[ $key ]['tve_qna_templates']  = get_post_meta( $id, 'tve_qna_templates', true );

				$tve_custom_css = tve_get_post_meta( $id, 'tve_custom_css', true );
				$tve_custom_css = tve_prepare_global_variables_for_front( $tve_custom_css );

				$data[ $key ]['tve_custom_style'] = $tve_custom_css;

				global $shared_styles;

				$data[ $key ]['tve_shared_styles'] = $shared_styles;
			}

			/**
			 * Based on shortcode quizzes put in content of a page/post
			 * An ajax request is made for them and this is its response
			 * - allow vendors to filter this data
			 */
			return apply_filters( 'tqb_frontend_quizzes', $data );
		}

		/**
		 * Load Thrive Dashboard
		 */
		public function load_dashboard_module() {
			$tve_dash_path      = __DIR__ . '/thrive-dashboard';
			$tve_dash_file_path = $tve_dash_path . '/version.php';

			if ( is_file( $tve_dash_file_path ) ) {
				$version                                  = require_once( $tve_dash_file_path );
				$GLOBALS['tve_dash_versions'][ $version ] = array(
					'path'   => $tve_dash_path . '/thrive-dashboard.php',
					'folder' => '/thrive-quiz-builder',
					'from'   => 'plugins',
				);
			}
		}

		/**
		 * Init Thrive Quiz Builder when Wordpress initializes
		 */
		public function init() {
			$this->load_plugin_textdomain();
			$this->update_checker();

			add_filter( 'tge_filter_edit_post', array( $this, 'set_responses' ) );
			add_filter( 'tge_filter_edit_post', array( $this, 'set_quiz_type' ) );
		}

		/**
		 * called after dash has loaded
		 */
		public function dash_loaded() {
			require_once 'includes/admin/classes/class-tqb-product.php';
		}

		/**
		 * Checks for updates
		 */
		public function update_checker() {
			/** plugin updates script **/

			new TVE_PluginUpdateChecker(
				'http://service-api.thrivethemes.com/plugin/update',
				__FILE__,
				'thrive-quiz-builder',
				12,
				'',
				'thrive_quiz_builder'
			);
			/**
			 * Adding icon of the product for update-core page
			 */
			add_filter( 'puc_request_info_result-thrive-quiz-builder', array( $this, 'tqb_set_product_icon' ) );
		}

		/**
		 * Main Quiz Builder Instance.
		 * Ensures only one instance of Quiz Builder is loaded or can be loaded.
		 *
		 * @return Thrive_Quiz_Builder
		 */
		public static function instance() {
			if ( empty( self::$_instance ) ) {
				self::$_instance = new self();
			}

			return self::$_instance;
		}

		/**
		 * Include required core files used in admin and on the frontend.
		 */
		private function includes() {

			/**
			 * Include TCB
			 */
			$this->include_tcb();

			require_once( 'image-editor/thrive-image-editor.php' );
			require_once( 'graph-editor/thrive-graph-editor.php' );

			/**
			 * Here we include all core function needed in admin and frontend as well.
			 */
			require_once( 'includes/admin/classes/class-tqb-structure-page.php' );
			require_once( 'includes/admin/classes/class-tqb-results-page.php' );
			require_once( 'includes/tqb-global-functions.php' );
			require_once( 'includes/class-tqb-post-types.php' );
			require_once( 'includes/class-tqb-post-meta.php' );
			require_once( 'includes/tqb-data-functions.php' );
			require_once( 'includes/class-tqb-request-handler.php' );
			require_once( 'includes/class-tqb-template-manager.php' );
			require_once( 'includes/class-tqb-state-manager.php' );
			require_once( 'includes/class-tqb-privacy.php' );
			require_once( 'includes/database/class-tqb-database-manager.php' );
			require_once( 'includes/class-tqb-db.php' );
			require_once( 'includes/class-tqb-customer.php' );
			require_once( 'includes/class-tqb-quiz.php' );
			require_once( 'includes/class-tqb-lightspeed.php' );
			require_once( 'includes/managers/class-tqb-structure-manager.php' );
			require_once( 'includes/managers/class-tqb-variation-manager.php' );
			require_once( 'includes/managers/class-tqb-page-manager.php' );
			require_once( 'includes/managers/class-tqb-quiz-manager.php' );
			require_once( 'includes/managers/class-tqb-test-manager.php' );
			require_once( 'includes/managers/class-tqb-reporting-manager.php' );
			require_once( 'includes/class-tqb-badge.php' );
			require_once( 'includes/class-tqb-progress-settings.php' );
			require_once( 'blocks/quiz-block.php' );

			if ( $this->is_request( 'admin' ) ) {
				require_once( 'includes/admin/class-tqb-admin.php' );
			}

			require_once( 'includes/reporting/class-main.php' );

			/**
			 *  Include ajax controllers
			 */
			require_once( 'includes/class-tqb-ajax.php' );

			/* Include the hooks file only if the variation query name exists in request */
			require_once( 'tcb-bridge/tqb-class-hooks.php' );
			require_once( 'tcb-bridge/tqb-class-qna-editor.php' );
			require_once( 'tcb-bridge/class-tqb-quiz-palette.php' );

			/**
			 *  Include frontend files
			 */
			require_once( 'includes/class-tqb-shortcodes.php' );

			/**
			 *  Include automator and its files
			 */
			require_once( 'automator/class-main.php' );

			/**
			 * Include the Thrive Apprentice integration entrypoint
			 */
			require_once( 'tva-bridge/class-main.php' );

			$this->resolve_tqb_conflicts();
		}

		private function include_tcb() {
			include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

			$tcb_exists = file_exists( dirname( $this->plugin_path() ) . '/thrive-visual-editor/thrive-visual-editor.php' );
			$tcb_active = is_plugin_active( 'thrive-visual-editor/thrive-visual-editor.php' );

			if ( ! $tcb_exists || ! $tcb_active ) {
				require_once( 'tcb-bridge/tqb-class-tcb.php' );
			}
		}

		/**
		 * Add extra headers for it to actually work on most browsers
		 *
		 * @param $headers
		 *
		 * @return array
		 */
		public function filter_nocache_headers( $headers ) {
			$headers['Cache-Control'] = $headers['Cache-Control'] . ', no-store';
			$headers['pragma']        = 'no-cache';

			return $headers;
		}

		/**
		 * What type of request is this?
		 *
		 * @param string $type admin, ajax, cron or frontend.
		 *
		 * @return bool
		 */
		private function is_request( $type ) {
			switch ( $type ) {
				case 'admin':
					return is_admin();
				case 'ajax':
					return defined( 'DOING_AJAX' );
				case 'cron':
					return defined( 'DOING_CRON' );
				case 'frontend':
					return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' );
				default:
					return false;
			}
		}

		/**
		 * Load Localisation files.
		 *
		 * Note: the first-loaded translation file overrides any following ones if the same translation is present.
		 *
		 * Locales found in:
		 *      - WP_LANG_DIR/thrive/thrive-quiz-builder-LOCALE.mo
		 */
		public function load_plugin_textdomain() {

			$locale = apply_filters( 'plugin_locale', get_locale(), 'thrive-quiz-builder' );

			load_textdomain( 'thrive-quiz-builder', WP_LANG_DIR . '/thrive/thrive-quiz-builder-' . $locale . '.mo' );
			load_plugin_textdomain( 'thrive-quiz-builder', false, plugin_basename( dirname( __FILE__ ) ) . '/languages' );
		}

		/**
		 * Full plugin url to file if specified
		 *
		 * @param string $file to be appended to the plugin url.
		 *
		 * @return string
		 */
		public function plugin_url( $file = '' ) {
			return plugin_dir_url( __FILE__ ) . ltrim( $file, '\\/' );
		}

		/**
		 * Chart colors
		 *
		 * @return array
		 */
		public function chart_colors() {
			return array(
				'#75b343',
				'#925699',
				'#ffa143',
				'#0679b6',
				'#16db94',
				'#c4ad88',
				'#844a17',
				'#e5e339',
				'#ef5780',
				'#8cd6dd',
				'#2f672c',
				'#faa8ff',
			);
		}

		/**
		 * Full plugin path to file if specified
		 *
		 * @param string $file to be appended to the plugin path.
		 *
		 * @return string
		 */
		public function plugin_path( $file = '' ) {
			return plugin_dir_path( __FILE__ ) . ltrim( $file, '\\/' );
		}

		/**
		 * Quiz templates
		 *
		 * @return array
		 */
		public function get_quiz_templates() {

			$templates = array();

			$templates[] = array(
				'id'                     => '1',
				'is_empty'               => true, //build from scratch
				'name'                   => __( 'Build from scratch', 'thrive-quiz-builder' ),
				'description'            => __( 'Build a quiz from scratch with no predefined settings.', 'thrive-quiz-builder' ),
				'learn_more'             => '<a data-source=\'' . self::VIDEO_QUIZ_TEMPLATE_SCRATCH . '\' class=\'tvd-open-video\'><span class=\'tvd-icon-play tqb-purple-icon\'></span></a>',
				'image'                  => $this->plugin_url( 'assets/images/tqb-quiz-template1.png' ),
				'splash'                 => false,
				'qna'                    => true,
				'optin'                  => false,
				'results'                => true,
				'default_page_templates' => array(
					'tqb_splash'  => 'template_1',
					'tqb_optin'   => 'template_1',
					'tqb_results' => 'template_1',
				),
			);

			$templates[] = array(
				'id'                     => '2',
				'is_empty'               => false,
				'name'                   => __( 'List building', 'thrive-quiz-builder' ),
				'description'            => __( 'Quiz optimized for building an email list. The Results Page is visible only if the user signs up.', 'thrive-quiz-builder' ),
				'learn_more'             => '<a data-source=\'' . self::VIDEO_QUIZ_TEMPLATE_LIST . '\' class=\'tvd-open-video\'><span class=\'tvd-icon-play tqb-purple-icon\'></span></a>',
				'image'                  => $this->plugin_url( 'assets/images/tqb-quiz-template2.png' ),
				'splash'                 => true,
				'qna'                    => true,
				'optin'                  => true,
				'results'                => true,
				'default_page_templates' => array(
					'tqb_splash'  => 'template_1',
					'tqb_optin'   => 'template_1',
					'tqb_results' => 'template_1',
				),
			);

			$templates[] = array(
				'id'                     => '3',
				'is_empty'               => false,
				'name'                   => __( 'Social shares', 'thrive-quiz-builder' ),
				'description'            => __( 'Quiz optimized for social sharing. The Results Page contains a Social Share Badge that your visitors can share with their friends to increase the popularity of the quiz.', 'thrive-quiz-builder' ),
				'learn_more'             => '<a data-source=\'' . self::VIDEO_QUIZ_TEMPLATE_SOCIAL . '\' class=\'tvd-open-video\'><span class=\'tvd-icon-play tqb-purple-icon\'></span></a>',
				'image'                  => $this->plugin_url( 'assets/images/tqb-quiz-template3.png' ),
				'splash'                 => true,
				'qna'                    => true,
				'optin'                  => false,
				'results'                => true,
				'default_page_templates' => array(
					'tqb_splash'  => 'template_1',
					'tqb_optin'   => 'template_1',
					'tqb_results' => 'template_3',
				),
			);

			$templates[] = array(
				'id'                     => '4',
				'is_empty'               => false,
				'name'                   => __( 'Gain customer insights', 'thrive-quiz-builder' ),
				'description'            => __( 'Quiz optimized for getting customer insights.', 'thrive-quiz-builder' ),
				'learn_more'             => "<a data-source='" . self::VIDEO_QUIZ_TEMPLATE_GAIN_CUSTOMER . "' class='tvd-open-video'><span class='tvd-icon-play tqb-purple-icon'></span></a>",
				'image'                  => $this->plugin_url( 'assets/images/tqb-quiz-template4.png' ),
				'splash'                 => false,
				'qna'                    => true,
				'optin'                  => false,
				'results'                => true,
				'default_page_templates' => array(
					'tqb_splash'  => 'template_3',
					'tqb_optin'   => 'template_1',
					'tqb_results' => 'template_4',
				),
			);

			return $templates;
		}

		/**
		 * Social share badge share templates
		 *
		 * @return array
		 */
		public function get_tcb_social_share_badge_templates() {
			$templates = array();

			$templates[] = array(
				'name'  => 'Style 1',
				'file'  => 'set_02',
				'image' => $this->plugin_url( 'tcb-bridge/assets/images/social-template-top.png' ),
			);

			$templates[] = array(
				'name'  => 'Style 2',
				'file'  => 'set_01',
				'image' => $this->plugin_url( 'tcb-bridge/assets/images/social-template-bottom.png' ),
			);

			$templates[] = array(
				'name'  => 'Style 3',
				'file'  => 'set_04',
				'image' => $this->plugin_url( 'tcb-bridge/assets/images/social-template-left.png' ),
			);

			$templates[] = array(
				'name'  => 'Style 4',
				'file'  => 'set_03',
				'image' => $this->plugin_url( 'tcb-bridge/assets/images/social-template-right.png' ),
			);

			return $templates;
		}

		public function get_style_thumbnail( $style_image ) {
			return $this->plugin_url( 'assets/images/' . $style_image . '.png' );
		}

		/**
		 * Quiz styles
		 *
		 * @param $style_id string|int
		 *
		 * @return array
		 */
		public function get_quiz_styles( $style_id = null ) {
			$styles = array();

			$styles[] = array(
				'id'                                  => '5',
				'name'                                => __( 'Lush', 'thrive-quiz-builder' ),
				'cover'                               => $this->plugin_url( 'assets/images/style-5-cover.png' ),
				self::QUIZ_STRUCTURE_ITEM_SPLASH_PAGE => array(
					'name'   => $this->get_style_page_name( self::QUIZ_STRUCTURE_ITEM_SPLASH_PAGE ),
					'image'  => $this->get_style_thumbnail( 'splash-5' ),
					'config' => array(
						'main-content-style' => '',
					),
				),
				self::QUIZ_STRUCTURE_ITEM_QNA         => array(
					'name'   => $this->get_style_page_name( self::QUIZ_STRUCTURE_ITEM_QNA ),
					'image'  => $this->get_style_thumbnail( 'qa-5' ),
					'config' => array(
						'main-content-style' => '',
					),
				),
				self::QUIZ_STRUCTURE_ITEM_OPTIN       => array(
					'name'   => $this->get_style_page_name( self::QUIZ_STRUCTURE_ITEM_OPTIN ),
					'image'  => $this->get_style_thumbnail( 'optin-5' ),
					'config' => array(
						'main-content-style' => '',
					),
				),
				self::QUIZ_STRUCTURE_ITEM_RESULTS     => array(
					'name'   => $this->get_style_page_name( self::QUIZ_STRUCTURE_ITEM_RESULTS ),
					'image'  => $this->get_style_thumbnail( 'results-5' ),
					'config' => array(
						'main-content-style' => '',
					),
				),
			);

			$styles[] = array(
				'id'                                  => '4',
				'name'                                => __( 'Minimalist', 'thrive-quiz-builder' ),
				'cover'                               => $this->plugin_url( 'assets/images/style-4-cover.png' ),
				self::QUIZ_STRUCTURE_ITEM_SPLASH_PAGE => array(
					'name'   => $this->get_style_page_name( self::QUIZ_STRUCTURE_ITEM_SPLASH_PAGE ),
					'image'  => $this->get_style_thumbnail( 'splash-4' ),
					'config' => array(
						'main-content-style' => '',
					),
				),
				self::QUIZ_STRUCTURE_ITEM_QNA         => array(
					'name'   => $this->get_style_page_name( self::QUIZ_STRUCTURE_ITEM_QNA ),
					'image'  => $this->get_style_thumbnail( 'qa-4' ),
					'config' => array(
						'main-content-style' => '',
					),
				),
				self::QUIZ_STRUCTURE_ITEM_OPTIN       => array(
					'name'   => $this->get_style_page_name( self::QUIZ_STRUCTURE_ITEM_OPTIN ),
					'image'  => $this->get_style_thumbnail( 'optin-4' ),
					'config' => array(
						'main-content-style' => '',
					),
				),
				self::QUIZ_STRUCTURE_ITEM_RESULTS     => array(
					'name'   => $this->get_style_page_name( self::QUIZ_STRUCTURE_ITEM_RESULTS ),
					'image'  => $this->get_style_thumbnail( 'results-4' ),
					'config' => array(
						'main-content-style' => '',
					),
				),
			);

			$styles[] = array(
				'id'                                  => '3',
				'name'                                => __( 'Deep Ocean Blue', 'thrive-quiz-builder' ),
				'cover'                               => $this->plugin_url( 'assets/images/style-3-cover.png' ),
				self::QUIZ_STRUCTURE_ITEM_SPLASH_PAGE => array(
					'name'   => $this->get_style_page_name( self::QUIZ_STRUCTURE_ITEM_SPLASH_PAGE ),
					'image'  => $this->get_style_thumbnail( 'splash-3' ),
					'config' => array(
						'main-content-style' => '',
					),
				),
				self::QUIZ_STRUCTURE_ITEM_QNA         => array(
					'name'   => $this->get_style_page_name( self::QUIZ_STRUCTURE_ITEM_QNA ),
					'image'  => $this->get_style_thumbnail( 'qa-3' ),
					'config' => array(
						'main-content-style' => '',
					),
				),
				self::QUIZ_STRUCTURE_ITEM_OPTIN       => array(
					'name'   => $this->get_style_page_name( self::QUIZ_STRUCTURE_ITEM_OPTIN ),
					'image'  => $this->get_style_thumbnail( 'optin-3' ),
					'config' => array(
						'main-content-style' => '',
					),
				),
				self::QUIZ_STRUCTURE_ITEM_RESULTS     => array(
					'name'   => $this->get_style_page_name( self::QUIZ_STRUCTURE_ITEM_RESULTS ),
					'image'  => $this->get_style_thumbnail( 'results-3' ),
					'config' => array(
						'main-content-style' => '',
					),
				),
			);

			$styles[] = array(
				'id'                                  => '2',
				'name'                                => __( 'Gray Orange', 'thrive-quiz-builder' ),
				'cover'                               => $this->plugin_url( 'assets/images/style-2-cover.png' ),
				self::QUIZ_STRUCTURE_ITEM_SPLASH_PAGE => array(
					'name'   => $this->get_style_page_name( self::QUIZ_STRUCTURE_ITEM_SPLASH_PAGE ),
					'image'  => $this->get_style_thumbnail( 'splash-2' ),
					'config' => array(
						'main-content-style' => '',
					),
				),
				self::QUIZ_STRUCTURE_ITEM_QNA         => array(
					'name'   => $this->get_style_page_name( self::QUIZ_STRUCTURE_ITEM_QNA ),
					'image'  => $this->get_style_thumbnail( 'qa-2' ),
					'config' => array(
						'main-content-style' => '',
					),
				),
				self::QUIZ_STRUCTURE_ITEM_OPTIN       => array(
					'name'   => $this->get_style_page_name( self::QUIZ_STRUCTURE_ITEM_OPTIN ),
					'image'  => $this->get_style_thumbnail( 'optin-2' ),
					'config' => array(
						'main-content-style' => '',
					),
				),
				self::QUIZ_STRUCTURE_ITEM_RESULTS     => array(
					'name'   => $this->get_style_page_name( self::QUIZ_STRUCTURE_ITEM_RESULTS ),
					'image'  => $this->get_style_thumbnail( 'results-2' ),
					'config' => array(
						'main-content-style' => '',
					),
				),
			);

			$styles[] = array(
				'id'                                  => '1',
				'name'                                => __( 'Dark', 'thrive-quiz-builder' ),
				'cover'                               => $this->plugin_url( 'assets/images/style-1-cover.png' ),
				self::QUIZ_STRUCTURE_ITEM_SPLASH_PAGE => array(
					'name'   => $this->get_style_page_name( self::QUIZ_STRUCTURE_ITEM_SPLASH_PAGE ),
					'image'  => $this->get_style_thumbnail( 'splash-1' ),
					'config' => array(
						'main-content-style' => '',
					),
				),
				self::QUIZ_STRUCTURE_ITEM_QNA         => array(
					'name'   => $this->get_style_page_name( self::QUIZ_STRUCTURE_ITEM_QNA ),
					'image'  => $this->get_style_thumbnail( 'qa-1' ),
					'config' => array(
						'main-content-style' => '',
					),
				),
				self::QUIZ_STRUCTURE_ITEM_OPTIN       => array(
					'name'   => $this->get_style_page_name( self::QUIZ_STRUCTURE_ITEM_OPTIN ),
					'image'  => $this->get_style_thumbnail( 'optin-1' ),
					'config' => array(
						'main-content-style' => '',
					),
				),
				self::QUIZ_STRUCTURE_ITEM_RESULTS     => array(
					'name'   => $this->get_style_page_name( self::QUIZ_STRUCTURE_ITEM_RESULTS ),
					'image'  => $this->get_style_thumbnail( 'results-1' ),
					'config' => array(
						'main-content-style' => '',
					),
				),
			);

			$styles[] = array(
				'id'                                  => '0',
				'name'                                => __( 'Light Blue', 'thrive-quiz-builder' ),
				'cover'                               => $this->plugin_url( 'assets/images/style-0-cover.jpg' ),
				self::QUIZ_STRUCTURE_ITEM_SPLASH_PAGE => array(
					'name'   => $this->get_style_page_name( self::QUIZ_STRUCTURE_ITEM_SPLASH_PAGE ),
					'image'  => $this->get_style_thumbnail( 'splash-0' ),
					'config' => array(
						'main-content-style' => '',
					),
				),
				self::QUIZ_STRUCTURE_ITEM_QNA         => array(
					'name'   => $this->get_style_page_name( self::QUIZ_STRUCTURE_ITEM_QNA ),
					'image'  => $this->get_style_thumbnail( 'qa-0' ),
					'config' => array(
						'main-content-style' => '',
					),
				),
				self::QUIZ_STRUCTURE_ITEM_OPTIN       => array(
					'name'   => $this->get_style_page_name( self::QUIZ_STRUCTURE_ITEM_OPTIN ),
					'image'  => $this->get_style_thumbnail( 'optin-0' ),
					'config' => array(
						'main-content-style' => '',
					),
				),
				self::QUIZ_STRUCTURE_ITEM_RESULTS     => array(
					'name'   => $this->get_style_page_name( self::QUIZ_STRUCTURE_ITEM_RESULTS ),
					'image'  => $this->get_style_thumbnail( 'results-0' ),
					'config' => array(
						'main-content-style' => '',
					),
				),
			);

			return $styles;
		}

		/**
		 * Gets the style config
		 *
		 * @param int $id
		 *
		 * @return array|null
		 */
		public function get_style_config( $id = 0 ) {
			$styles = $this->get_quiz_styles();
			foreach ( $styles as $style ) {
				if ( (int) $style['id'] === (int) $id ) {
					return $style;
				}
			}

			return null;
		}

		/**
		 * The function return the style css file name or null if quiz style meta is empty
		 *
		 * @param null $quiz_style_meta
		 *
		 * @return null|string
		 */
		public function get_style_css( $quiz_style_meta = null ) {
			if ( is_numeric( $quiz_style_meta ) ) {
				return 'style-' . $quiz_style_meta . '.css';
			}

			return null;
		}

		/**
		 * Gets the page name based on page type.
		 *
		 * @param string $type
		 *
		 * @return string|void
		 */
		public function get_style_page_name( $type = '' ) {
			$label = '';
			switch ( $type ) {
				case self::QUIZ_STRUCTURE_ITEM_SPLASH_PAGE:
					$label = __( 'Splash Page', 'thrive-quiz-builder' );
					break;
				case self::QUIZ_STRUCTURE_ITEM_OPTIN:
					$label = __( 'Opt-in Gate', 'thrive-quiz-builder' );
					break;
				case self::QUIZ_STRUCTURE_ITEM_QNA:
					$label = __( 'Q&A', 'thrive-quiz-builder' );
					break;
				case self::QUIZ_STRUCTURE_ITEM_RESULTS:
					$label = __( 'Results Page', 'thrive-quiz-builder' );
					break;
				default:
					break;
			}

			return $label;
		}

		/**
		 * Gets the post_type using internal identifier.
		 *
		 * @param string $type
		 *
		 * @return string|void
		 */
		public function get_structure_post_type_name( $type = '' ) {
			$post_type = '';
			switch ( $type ) {
				case 'splash':
				case 'tqb_splash':
					$post_type = self::QUIZ_STRUCTURE_ITEM_SPLASH_PAGE;
					break;
				case 'qna':
					$post_type = self::QUIZ_STRUCTURE_ITEM_QNA;
					break;
				case 'tqb_optin':
				case 'optin':
					$post_type = self::QUIZ_STRUCTURE_ITEM_OPTIN;
					break;
				case 'tqb_results':
				case 'results':
					$post_type = self::QUIZ_STRUCTURE_ITEM_RESULTS;
					break;
				default:
					break;
			}

			return $post_type;
		}

		/**
		 * Gets the internal identifier using post_type .
		 *
		 * @param string $post_type
		 *
		 * @return string|void
		 */
		public function get_structure_type_name( $post_type = '' ) {
			return $this->get_structure_post_type_name( $post_type );
		}

		/**
		 * Get array of internal identifiers.
		 *
		 * @return array
		 */
		public function get_structure_internal_identifiers() {
			return array(
				'splash',
				'qna',
				'optin',
				'results',
			);
		}

		/**
		 * Gets the page description based on page type.
		 *
		 * @param string $type
		 *
		 * @return string|void
		 */
		public function get_style_page_description( $type = '' ) {
			$description = '';
			switch ( $type ) {
				case self::QUIZ_STRUCTURE_ITEM_SPLASH_PAGE:
					$description = __( 'The variation marked with (control) will be displayed as your splash page. If you want to improve your conversion rate, add more variations and start an A/B Test.', 'thrive-quiz-builder' );
					break;
				case self::QUIZ_STRUCTURE_ITEM_OPTIN:
					$description = __( 'The variation marked with (control) will be displayed as your opt-in gate. If you want to improve your conversion rate, add more variations and start an A/B Test.', 'thrive-quiz-builder' );
					break;
				case self::QUIZ_STRUCTURE_ITEM_QNA:
					$description = '';
					break;
				case self::QUIZ_STRUCTURE_ITEM_RESULTS:
					$description = __( 'The variation marked with (control) will be displayed as your results page. If you want to improve your conversion rate, add more variations and start an A/B Test.', 'thrive-quiz-builder' );
					break;
				default:
					break;
			}

			return $description;
		}

		/**
		 * Quiz Types
		 *
		 * @return array
		 */
		public function get_quiz_types() {

			$types = array();

			$types[] = array(
				'key'           => self::QUIZ_TYPE_NUMBER,
				'label'         => __( 'Number', 'thrive-quiz-builder' ),
				'image'         => $this->plugin_url( 'assets/images/logo-score-lg.png' ),
				'tooltip'       => __( 'Use this quiz type if you want to display the final result of the quiz as a number.', 'thrive-quiz-builder' ),
				'learn_more'    => '<a><span data-source=\'' . self::VIDEO_QUIZ_TYPE_NUMBER . '\' class=\'tvd-icon-play tqb-purple-icon tvd-open-video\'></span></a>',
				'has_next_step' => true,
			);
			$types[] = array(
				'key'           => self::QUIZ_TYPE_PERCENTAGE,
				'label'         => __( 'Percentage', 'thrive-quiz-builder' ),
				'image'         => $this->plugin_url( 'assets/images/logo-percentage.png' ),
				'tooltip'       => __( 'Use this quiz type if you want to display the final result of the quiz as a percentage.', 'thrive-quiz-builder' ),
				'learn_more'    => '<a><span data-source=\'' . self::VIDEO_QUIZ_TYPE_PERCENTAGE . '\' class=\'tvd-icon-play tqb-purple-icon tvd-open-video\'></span></a>',
				'has_next_step' => true,
			);
			$types[] = array(
				'key'           => self::QUIZ_TYPE_PERSONALITY,
				'label'         => __( 'Category', 'thrive-quiz-builder' ),
				'image'         => $this->plugin_url( 'assets/images/logo-personality.png' ),
				'tooltip'       => __( 'In this quiz you set up a number of possible result categories. An example of this would be a personality type quiz.', 'thrive-quiz-builder' ),
				'learn_more'    => '<a><span data-source=\'' . self::VIDEO_QUIZ_TYPE_PERSONALITY . '\' class=\'tvd-icon-play tqb-purple-icon tvd-open-video\'></span></a>',
				'has_next_step' => true,
			);
			$types[] = array(
				'key'           => self::QUIZ_TYPE_RIGHT_WRONG,
				'label'         => __( 'Right/Wrong', 'thrive-quiz-builder' ),
				'image'         => $this->plugin_url( 'assets/images/right-wrong.png' ),
				'tooltip'       => __( 'With the help of this quiz type, you can have one or more correct answers to each question. The correct answer(s) can be highlighted to let the visitors know whether the answers they have selected are the correct ones or not.', 'thrive-quiz-builder' ),
				'learn_more'    => '<a><span data-source=\'' . self::VIDEO_QUIZ_TYPE_RIGHT_WRONG . '\' class=\'tvd-icon-play tqb-purple-icon tvd-open-video\'></span></a>',
				'has_next_step' => true,
			);
			$types[] = array(
				'key'           => self::QUIZ_TYPE_SURVEY,
				'label'         => __( 'Survey', 'thrive-quiz-builder' ),
				'image'         => $this->plugin_url( 'assets/images/survey.png' ),
				'tooltip'       => __( "The survey quiz allows you to gain valuable insights about your existing customers. The participant in this quiz type doesn't receive a specific result. Instead the results page contains the same content for everyone. This type of quiz is especially useful for learning more about your visitors and segmenting your existing customer base.", 'thrive-quiz-builder' ),
				'learn_more'    => "<a><span data-source='" . self::VIDEO_QUIZ_TEMPLATE_SURVEY . "' class='tvd-icon-play tqb-purple-icon tvd-open-video'></span></a>",
				'has_next_step' => true,
			);

			return $types;
		}

		/**
		 * Sets on the post the results saved in db based on post->ID
		 *
		 * @param WP_Post $post
		 *
		 * @return WP_Post
		 */
		public function set_responses( $post ) {
			$quiz_manager  = new TQB_Quiz_Manager( $post );
			$post->results = $quiz_manager->get_results();

			return $post;
		}

		public function set_quiz_type( $post ) {
			$type            = TQB_Post_meta::get_quiz_type_meta( $post->ID );
			$post->quiz_type = $type['type'];

			return $post;
		}

		/**
		 * Check if there is a valid activated license for the TQB plugin.
		 *
		 * @return bool
		 */
		public function license_activated() {
			return TVE_Dash_Product_LicenseManager::getInstance()->itemActivated( TVE_Dash_Product_LicenseManager::TQB_TAG );
		}

		/**
		 * check if the current TCB version is the one required by Thrive Quiz Builder
		 */
		public function check_tcb_version() {
			if ( ! tve_in_architect() ) { // the internal TCB code will always be up to date
				return true;
			}

			$internal_architect_version = include $this->plugin_path() . 'tcb/version.php';

			/* make sure that the we have the same version of architect inside the plugin and as individual plugin, otherwise conflicts can appear */
			if ( ! defined( 'TVE_VERSION' ) || ! version_compare( TVE_VERSION, $internal_architect_version, '=' ) ) {
				return false;
			}

			return true;
		}

		/**
		 * Resolves the conflicts with other plugins
		 */
		private function resolve_tqb_conflicts() {

			/**
			 * Resolves the conflict with Better WordPress Minify
			 * https://ro.wordpress.org/plugins/bwp-minify/
			 *
			 * TQB registers the scripts at a low priority then BWM plugin.
			 * BWM plugin registers the scripts with priority the same as  `wp_print_head_scripts`
			 * TQB plugin registers the scripts with priority the same as `wp_footer`
			 */
			add_filter( 'bwp_minify_is_loadable', '__return_false', 10 );
		}

		/**
		 * Get sales page url
		 *
		 * @return string
		 */
		public function get_sales_page_url() {
			$affiliate_id = get_option( 'thrive_affiliate_id' );

			if ( ! empty( $affiliate_id ) ) {
				return 'https://thrivethemes.com/affiliates/ref.php?id=' . $affiliate_id . '_' . self::PLUGIN_IDEV_ID;
			}

			return 'https://thrivethemes.com/suite';
		}

		/**
		 * Called on plugin activation.
		 * Check for minimum required WordPress version
		 */
		public function activation_hook() {
			if ( function_exists( 'tcb_wordpress_version_check' ) && ! tcb_wordpress_version_check() ) {
				/**
				 * Dashboard not loaded yet, force it to load here
				 */
				if ( ! function_exists( 'tve_dash_show_activation_error' ) ) {
					/* Load the dashboard included in this plugin */
					$this->load_dashboard_module();
					tve_dash_load();
				}

				tve_dash_show_activation_error( 'wp_version', 'Thrive Quiz Builder', TCB_MIN_WP_VERSION );
			} else {
				if ( method_exists( '\TCB\Lightspeed\Main', 'first_time_enable_lightspeed' ) ) {
					\TCB\Lightspeed\Main::first_time_enable_lightspeed();
				}
			}
		}

		/**
		 * Getting quizzes order and their visibility
		 *
		 * @return array
		 */
		public function get_shown_quizzes() {

			$defaults      = array(
				'posts_per_page' => - 1,
				'post_type'      => TQB_Post_types::QUIZ_POST_TYPE,
				'orderby'        => 'post_date',
				'order'          => 'ASC',
			);
			$posts         = get_posts( $defaults );
			$quizzes       = array();
			$shown_quizzes = array(
				'order'   => array(),
				'visible' => array(),
			);

			foreach ( $posts as $post ) {
				$quizzes[ $post->ID ] = (int) TQB_Post_meta::get_quiz_order( $post->ID );
			}
			asort( $quizzes );

			$count = 0;
			foreach ( $quizzes as $quiz_id => $order ) {
				$shown_quizzes['visible'][ $quiz_id ] = $count < self::TQB_DASH_MAX_QUIZZES_IDENTIFIER;
				$shown_quizzes['order'][ $count ]     = $quiz_id;
				$count ++;
			}

			return $shown_quizzes;
		}

		/**
		 * Adding the product icon for the update core page
		 *
		 * @param $info
		 *
		 * @return mixed
		 */
		public function tqb_set_product_icon( $info ) {
			$info->icons['1x'] = tqb()->plugin_url( 'assets/images/tqb-logo.png' );

			return $info;
		}

		/**
		 * Change symbol content if it has a quiz inside
		 *
		 * @param $content
		 *
		 * @return mixed
		 */
		public function change_symbol_content( $content ) {

			if ( strpos( $content, 'CONFIG_quiz_shortcode' ) !== false && ! is_editor_page() && ! wp_doing_ajax() ) {
				$content = str_replace( ',"in_tcb_editor":"inside_tcb"', '', $content );
			}

			return $content;
		}

		/**
		 * Add quiz options to dynamic links ui
		 *
		 * @param $dynamic_links
		 *
		 * @return mixed
		 */
		public function tcb_dynamiclink_data( $dynamic_links ) {
			$post = get_post();

			if ( ! $post instanceof WP_Post ) {
				return $dynamic_links;
			}

			$post_types = array(
				self::QUIZ_STRUCTURE_ITEM_SPLASH_PAGE,
				self::QUIZ_STRUCTURE_ITEM_OPTIN,
				self::QUIZ_STRUCTURE_ITEM_RESULTS,
			);

			if ( ! in_array( $post->post_type, $post_types ) ) {
				return $dynamic_links;
			}

			$dynamic_links['Quiz'] = array(
				'links'     => array(
					0 => array(),
				),
				'shortcode' => 'tqb_quiz_options',
			);

			switch ( $post->post_type ) {
				case self::QUIZ_STRUCTURE_ITEM_SPLASH_PAGE:
					$dynamic_links['Quiz']['links'][0][] = array(
						'id'   => self::NEXT_STEP_IN_QUIZ . '_' . $post->post_parent,
						'name' => 'Next step in quiz',
						'url'  => '',
						'show' => 1,
					);

					break;

				case self::QUIZ_STRUCTURE_ITEM_OPTIN:
					$dynamic_links['Quiz']['links'][0] = array(
						array(
							'id'   => self::NEXT_STEP_IN_QUIZ . '_' . $post->post_parent,
							'name' => 'Next step in quiz',
							'url'  => '',
							'show' => 1,
						),
						array(
							'id'   => self::RESTART_QUIZ_IDENTIFIER . '_' . $post->post_parent,
							'name' => 'Restart Quiz',
							'url'  => '',
							'show' => 1,
						),
					);

					break;

				case self::QUIZ_STRUCTURE_ITEM_RESULTS:
					$dynamic_links['Quiz']['links'][0][] = array(
						'id'   => self::RESTART_QUIZ_IDENTIFIER . '_' . $post->post_parent,
						'name' => 'Restart Quiz',
						'url'  => '',
						'show' => 1,
					);

					break;
			}

			return $dynamic_links;
		}

		/**
		 * Add quiz result shortcode in TAR inline shortcodes list
		 *
		 * @param array $shortcodes
		 *
		 * @return mixed
		 */
		public function inline_shortcodes( $shortcodes ) {

			$post = get_post();

			if ( ! $post instanceof WP_Post || $post->post_type !== self::QUIZ_STRUCTURE_ITEM_RESULTS ) {
				return $shortcodes;
			}

			$parent = get_post( $post->post_parent );

			if ( ! $parent instanceof WP_Post ) {
				return $shortcodes;
			}

			if ( self::QUIZ_TYPE_SURVEY === TQB_Post_meta::get_quiz_type_meta( $parent->ID, true ) ) {
				return $shortcodes;
			}

			$type = get_post_meta( $parent->ID, TQB_Post_meta::META_NAME_FOR_QUIZ_TYPE, true );

			$shortcodes['Quiz Shortcodes'] = array(
				0 => array(
					'name'   => 'Quiz Result',
					'value'  => 'tqb_quiz_result',
					'option' => 'Quiz Result',
				),
			);

			/**
			 * Only add round options on percentage quiz
			 */
			if ( is_array( $type ) && isset( $type['type'] ) && $type['type'] === 'percentage' ) {
				$shortcodes['Quiz Shortcodes'][0]['input'] = array(
					'result_type' => array(
						'type'  => 'select',
						'label' => 'Round result to: ',
						'value' => array(
							'whole_number' => 'Whole number',
							'one_decimal'  => '1 decimal point',
							'two_decimal'  => '2 decimal points',
						),
					),
				);
			}

			return $shortcodes;
		}

		/**
		 * Remove TQB Post Types from TAR custom fields screen
		 *
		 * @param bool   $protected
		 * @param static $meta_key
		 *
		 * @return bool
		 */
		public function is_protected_meta( $protected, $meta_key ) {

			$excluded = array(
				'tqb_results_type',
				'tqb_redirect_display_message',
				'tqb_redirect_message',
				'tqb_redirect_forward_results',
			);

			if ( in_array( $meta_key, $excluded ) ) {
				$protected = true;
			}

			return $protected;
		}

		/**
		 * Exclude the dist folders from minify-ing by the WP-Rocket plugin
		 *
		 * @param $excluded_js
		 *
		 * @return array
		 */
		public function rocket_exclude_js( $excluded_js ) {
			$home_url = home_url();

			$excluded_js[] = str_replace( $home_url, '', plugins_url( '/thrive-quiz-builder/assets/js/dist' ) ) . '/(.*).js';
			$excluded_js[] = str_replace( $home_url, '', plugins_url( '/thrive-quiz-builder/graph-editor/assets/js/dist' ) ) . '/(.*).js';
			$excluded_js[] = str_replace( $home_url, '', plugins_url( '/thrive-quiz-builder/image-editor/assets/js/dist' ) ) . '/(.*).js';

			return $excluded_js;
		}

		/**
		 * Exclude the css files from minify-ing by the WP-Rocket plugin
		 *
		 * @param $excluded_css
		 *
		 * @return array
		 */
		public function rocket_exclude_css( $excluded_css ) {
			$home_url = home_url();

			$excluded_css[] = str_replace( $home_url, '', plugins_url( '/thrive-quiz-builder/assets/css' ) ) . '/(.*).css';
			$excluded_css[] = str_replace( $home_url, '', plugins_url( '/thrive-quiz-builder/graph-editor/assets/css' ) ) . '/(.*).css';
			$excluded_css[] = str_replace( $home_url, '', plugins_url( '/thrive-quiz-builder/image-editor/assets/css' ) ) . '/(.*).css';

			return $excluded_css;
		}

		/**
		 * Filter email message content in order to add quiz data
		 *
		 * @param array $data
		 * @param array $args
		 *
		 * @return array
		 */
		public function tve_dash_email_data( $data, $args ) {

			if ( empty( $args['tqb-variation-page_id'] ) || empty( $args['tqb-variation-user_unique'] ) ) {
				return $data;
			}

			$user_unique = $args['tqb-variation-user_unique'];
			$page_id     = $args['tqb-variation-page_id'];
			$page        = get_post( $page_id );
			$quiz_id     = $page->post_parent;
			$quiz        = get_post( $quiz_id );
			$points      = TQB_Quiz_Manager::get_user_points( $user_unique, $quiz_id );
			$answers     = TQB_Quiz_Manager::get_user_answers_in_order( $quiz_id, TQB_Quiz_Manager::get_quiz_user( $user_unique, $quiz_id ) );

			$html = '';
			$last = count( $answers ) - 1;

			foreach ( (array) $answers as $key => $answer ) {

				if ( ! is_array( $answer ) ) {
					continue;
				}

				$answer_text = 3 === (int) $answer['q_type'] ? nl2br( $answer['answer_text'] ) : $answer['a_text'];
				$margin      = $key < $last ? 'margin-bottom: 5px' : '';

				$html .= '<div style="border: 1px dashed; border-radius: 5px; padding: 5px; width: 50%; ' . $margin . '">';
				$html .= '<p><b>' . $answer['q_text'] . '</b></p>';
				$html .= '<p>' . $answer_text . '</p>';
				$html .= '</div>';

				if ( $key < $last ) {
					$html .= '</br>';
				}
			}

			$data['html_content'] = str_replace(
				array(
					'[quiz_result]',
					'[quiz_name]',
					'[quiz_answers]',
				),
				array(
					$points,
					$quiz->post_title,
					$html,
				),
				$data['html_content']
			);

			$data['confirmation_html'] = str_replace(
				array(
					'[quiz_answers]',
					'[quiz_name]',
					'[quiz_result]',
				),
				array(
					$html,
					$quiz->post_title,
					$points,
				),
				$data['confirmation_html']
			);

			$data['subject']              = str_replace(
				array( '[quiz_name]', '[quiz_result]' ),
				array( $quiz->post_title, $points ),
				$data['subject']
			);
			$data['confirmation_subject'] = str_replace(
				array( '[quiz_name]', '[quiz_result]' ),
				array( $quiz->post_title, $points ),
				$data['confirmation_subject']
			);

			return $data;
		}

		/**
		 * Enqueue scripts needed for the quiz in frontend
		 *
		 * @param int $quiz_id
		 */
		public static function enqueue_frontend_scripts( $quiz_id = 0 ) {

			$scripts_loaded = isset( $_POST['tve_dash_data']['tqb_lazy_load']['tqb_scripts_loaded'] ) && true === (bool) $_POST['tve_dash_data']['tqb_lazy_load']['tqb_scripts_loaded'];

			if ( ! $scripts_loaded ) {
				add_action( 'wp_print_footer_scripts', array( 'TQB_Shortcodes', 'render_backbone_templates' ) );
				add_action( 'wp_print_footer_scripts', 'tqb_add_frontend_svg_file' );

				tqb_enqueue_default_scripts();
				TCB_Icon_Manager::enqueue_icon_pack(); // Include Thrive Icon pack

				$deps = array( 'backbone' );

				if ( ! defined( 'DOING_AJAX' ) ) {
					$deps[] = 'tve-dash-frontend';
				}

				tqb_enqueue_script(
					'tqb-frontend',
					tqb()->plugin_url( 'assets/js/dist/tqb-frontend.min.js' ),
					$deps
				);
				wp_localize_script(
					'tqb-frontend',
					defined( 'DOING_AJAX' ) && DOING_AJAX ? 'TQB_Front_Ajax' : 'TQB_Front',
					array(
						'nonce'        => wp_create_nonce( 'tqb_frontend_ajax_request' ),
						'ajax_url'     => admin_url( 'admin-ajax.php' ) . '?action=tqb_frontend_ajax_controller',
						'is_preview'   => TQB_Product::has_access(),
						'post_id'      => get_the_ID(),
						'settings'     => Thrive_Quiz_Builder::get_settings(),
						'submit_icon'  => tqb_get_svg_icon( 'thumbs-up', 'tcb-icon', true ),
						'quiz_options' => array(),
						't'            => array(
							'chars' => __( 'Characters', 'thrive-quiz-builder' ),
						),
					)
				);
			}

			if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
				add_action(
					'wp_print_footer_scripts',
					static function () use ( $quiz_id ) {
						ob_start();
						include __DIR__ . '/includes/frontend/views/trigger.tqb_quiz_loaded.php';
						$html = ob_get_clean();
						echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					}
				);
			}
		}

		/**
		 * Search through $tags and replace the %result% tag with the actual submitted quiz result
		 *
		 * @param string $tags
		 *
		 * @return string
		 */
		public function process_api_result_tags( $tags ) {
			$user_result = isset( $_POST['tqb-quiz-user-result'], $_POST['tqb-variation-page_id'] ) ? sanitize_text_field( $_POST['tqb-quiz-user-result'] ) : '';

			return str_replace( self::QUIZ_RESULT_SHORTCODE, $user_result, $tags );
		}

		/**
		 * Return all settings or a specific setting
		 *
		 * @param null $key
		 *
		 * @return array|mixed
		 */
		public static function get_settings( $key = null ) {
			$settings = tqb_get_option( Thrive_Quiz_Builder::PLUGIN_SETTINGS, tqb_get_default_values( Thrive_Quiz_Builder::PLUGIN_SETTINGS ) );

			return $key === null ? $settings : $settings[ $key ];
		}

		public function hide_export_content( $allow, $post ) {
			if ( in_array( $post->post_type, array(
				self::QUIZ_STRUCTURE_ITEM_SPLASH_PAGE,
				self::QUIZ_STRUCTURE_ITEM_OPTIN,
				self::QUIZ_STRUCTURE_ITEM_RESULTS,
			) ) ) {
				$allow = false;
			}

			return $allow;
		}

		/**
		 * Data related to quiz pages should not be displayed since the frontend data would be based on actual post
		 *
		 * @param $post_types
		 *
		 * @return mixed
		 */
		public static function tcb_hide_templates( $post_types ) {
			return array_merge( $post_types, array(
				self::QUIZ_STRUCTURE_ITEM_SPLASH_PAGE,
				self::QUIZ_STRUCTURE_ITEM_OPTIN,
				self::QUIZ_STRUCTURE_ITEM_RESULTS,
			) );
		}

		/**
		 * Don't display metrics ribbon if we don't have any license
		 */
		public function should_enqueue_metrics( $should_enqueue ) {
			$screen = tve_get_current_screen_key();
			if ( $screen === 'thrive-dashboard_page_tqb_admin_dashboard' && ! $this->license_activated() ) {
				$should_enqueue = false;
			}

			return $should_enqueue;
		}
	}

endif;

/**
 *  Main instance of Thrive Quiz Builder.
 *
 * @return Thrive_Quiz_Builder
 */
function tqb() {
	return Thrive_Quiz_Builder::instance();
}

tqb();
