<?php
/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 10/4/2016
 * Time: 11:22 AM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

class TCB_Hooks {

	/**
	 * Actions
	 */
	const ACTION_TEMPLATE               = 'tqb_tpl';
	const ACTION_STATE                  = 'tqb_state';
	const ACTION_SAVE_VARIATION_CONTENT = 'tqb_save_variation_content';

	/**
	 * Security nonce
	 */
	const SECURITY_NONCE = 'tqb-verify-track-sender-007';

	public static function init() {
		/**
		 * Filter that gets called when the following situation occurs:
		 * TCB is installed and enabled, but there is no active license activated
		 * in this case, we should only allow users to edit: tve_ult_campaign
		 */
		add_filter( 'tcb_skip_license_check', array( __CLASS__, 'license_override' ) );

		/**
		 * called when enqueuing scripts from the editor on editor page. it needs to check if TQB has a valid license
		 */
		add_filter( 'tcb_user_can_edit', array( __CLASS__, 'editor_check_license' ) );

		/**
		 * called when enqueuing scripts on editor pages. It checks if the separate TCB plugin has the required version
		 */
		add_filter( 'tcb_user_can_edit', array( __CLASS__, 'editor_check_tcb_version' ) );

		/**
		 * called when trying to edit a post to check TQB capability with TA deactivated
		 */
		add_filter( 'tcb_user_has_plugin_edit_cap', array( __CLASS__, 'check_plugin_cap' ) );

		/**
		 * get the editing layout for variations
		 */
		add_filter( 'tcb_custom_post_layouts', array( __CLASS__, 'editor_layout' ), 10, 3 );

		/**
		 * modify the localization parameters for the javascript on the editor page (in editing mode)
		 */
		add_filter( 'tcb_editor_javascript_params', array(
			__CLASS__,
			'editor_javascript_params',
		), 10, 3 ); //TODO: rename editor_javascript_params hook to tcb_main_frame_localize

		/**
		 * action hook that overrides the default tve_save_post action from the editor
		 * used to save the editor contents in custom post fields specific
		 */
		add_action( 'tcb_ajax_' . self::ACTION_SAVE_VARIATION_CONTENT, array( __CLASS__, 'editor_save_content' ) );

		/**
		 * we need to modify the preview URL for tve_form_type post types
		 */
		add_filter( 'tcb_editor_preview_link_query_args', array(
			__CLASS__,
			'editor_append_preview_link_args',
		), 10, 2 );

		/**
		 * modify the edit url by inserting also the form variation key in the query vars
		 */
		add_filter( 'tcb_editor_edit_link_query_args', array( __CLASS__, 'editor_append_preview_link_args' ), 10, 2 );

		/**
		 * main entry point for template-related actions: choose new template, reset current template
		 */
		add_action( 'wp_ajax_' . self::ACTION_TEMPLATE, array( __CLASS__, 'template_action' ) );

		/**
		 * main entry point for state-related actions: add state, delete state
		 */
		add_action( 'wp_ajax_' . self::ACTION_STATE, array( __CLASS__, 'state_action' ) );

		/**
		 * Add "go forward" event to TCB
		 */
		add_filter( 'tcb_event_actions', 'tqb_event_actions', 10, 3 );

		/**
		 * Disable the style families change TCB button
		 */
		add_filter( 'tcb_style_families', array( __CLASS__, 'disable_style_families_option' ), 10, 1 );

		/**
		 * TCB Autoresponder after submit options
		 */
		add_filter( 'tve_autoresponder_show_submit', array( __CLASS__, 'tqb_filter_autoresponder_submit_option' ) );

		/**
		 * TCB Autoresponder connection type
		 */
		add_filter( 'tve_autoresponder_connection_types', array(
			__CLASS__,
			'tqb_filter_autoresponder_connection_type',
		), 10, 1 );

		/**
		 * TCB Hook to save user template method
		 */
		add_filter( 'tcb_hook_save_user_template', array( __CLASS__, 'tqb_save_user_template' ), 10, 1 );

		/**
		 * Filter that captures when the user is on a quiz custom post type page
		 */
		add_filter( 'template_include', array( __CLASS__, 'tqb_quiz_custom_template' ), 9999, 1 );

		/**
		 * Captures if the accessed page is from facebook.
		 *
		 * If so, it redirects the user to the page where the quiz is at
		 */
		add_action( 'template_redirect', array( __CLASS__, 'tqb_quiz_quiz_page_redirect' ), 10, 0 );

		/**
		 * TCB 2.0 HOOKS !!
		 */

		/**
		 * Adds TQB product to TCB
		 */
		add_filter( 'tcb_element_instances', array( __CLASS__, 'tqb_add_product_to_tcb' ), 10, 1 );

		/**
		 * Remove Some Plugin Instances From TCB - Quiz Builder Editor
		 */
		add_filter( 'tcb_remove_instances', array( __CLASS__, 'tqb_remove_element_instances' ), 10, 1 );

		/**
		 * Adds extra script(s) to the main frame
		 */
		add_action( 'tcb_main_frame_enqueue', array( __CLASS__, 'tqb_add_script_to_main_frame' ), 10, 0 );

		add_filter( 'tve_main_js_dependencies', array( __CLASS__, 'tqb_main_js_dependencies' ) );

		/**
		 * Adds extra SVG icons to editor page
		 */
		add_action( 'tcb_editor_iframe_after', array( __CLASS__, 'tqb_output_extra_control_panel_svg' ), 10, 0 );

		/**
		 * Output extra iFrame SVG
		 */
		add_action( 'tcb_output_extra_editor_svg', array( __CLASS__, 'tqb_output_extra_iframe_svg' ), 10, 0 );

		/**
		 * Include Quiz Component Menu
		 */
		add_filter( 'tcb_menu_path_quiz', array( __CLASS__, 'tqb_include_quiz_menu' ), 10, 1 );

		/**
		 * Include Social Share Badge Component Menu
		 */
		add_filter( 'tcb_menu_path_tqb_social_share_badge', array(
			__CLASS__,
			'tqb_include_social_share_badge_menu',
		), 10, 1 );

		/**
		 * Includes the TQB Modal template files
		 */
		add_filter( 'tcb_modal_templates', array( __CLASS__, 'tqb_modal_files' ), 10, 1 );

		/**
		 * Inserts the state bar after iFrame initialization
		 */
		add_action( 'tcb_editor_iframe_after', array( __CLASS__, 'tqb_hook_control_panel' ), 10, 0 );

		/**
		 * Enables Template Tab in Settings Section
		 */
		add_filter( 'tcb_has_templates_tab', array( __CLASS__, 'tqb_enable_template_tab' ), 10, 1 );

		/**
		 * Enable settings tab
		 */
		add_filter( 'tcb_has_settings', array( __CLASS__, 'tqb_enable_settings_tab' ), 10, 1 );
		/**
		 * Disable import content control
		 */
		add_filter( 'tcb_can_import_content', array( __CLASS__, 'tqb_can_import_content' ), 10, 1 );

		/**
		 * Adds Template Tab Menu Items
		 */
		add_action( 'tcb_right_sidebar_content_settings', array( __CLASS__, 'right_sidebar_content_settings' ), 10, 0 );
		add_action( 'tcb_right_sidebar_top_settings', array( __CLASS__, 'right_sidebar_top_settings' ), 10, 0 );

		/**
		 * Disable Revision Manager For Quiz Builder Pages
		 */
		add_filter( 'tcb_has_revision_manager', array( __CLASS__, 'tqb_disable_revision_manager' ), 10, 1 );

		/**
		 * Disable Page Events For Quiz Builder Pages
		 */
		add_filter( 'tcb_can_use_page_events', array( __CLASS__, 'tqb_disable_page_events' ), 10, 1 );

		/**
		 * Disable the adding of elements for Question Editor
		 */
		add_filter( 'tcb_can_add_elements', array( __CLASS__, 'tqb_disable_add_elements' ), 10, 1 );

		/**
		 * Disable the editor preview button for Question Editor
		 */
		add_filter( 'tcb_has_preview_button', array( __CLASS__, 'tqb_disable_editor_preview_button' ), 10, 1 );

		/**
		 * Add editor backbone templates
		 */
		add_filter( 'tcb_backbone_templates', array( __CLASS__, 'tqb_get_editor_backbone_templates' ), 10, 1 );

		/**
		 * Modify TCB Close Url For TQB Editor
		 */
		add_filter( 'tcb_close_url', array( __CLASS__, 'tqb_tcb_close_url' ), 10, 1 );

		/**
		 * Enable/Disable modification of facebook share options by other plugins
		 */
		add_filter( 'tcb_modify_facebook_share_options', array(
			__CLASS__,
			'tqb_modify_facebook_share_options',
		), 10, 1 );

		/**
		 * Add some Quiz Builder post types to Architect Post Grid Element Banned Types
		 */
		add_filter( 'tcb_post_grid_banned_types', array( __CLASS__, 'tqb_add_post_grid_banned_types' ), 10, 1 );

		/**
		 * Show TQB logo in the editor page
		 */
		add_filter( 'architect.branding', array( __CLASS__, 'architect_branding' ) );

		/**
		 * Adds TQB query string variation to preview link
		 */
		add_filter( 'preview_post_link', array( __CLASS__, 'tqb_preview_post_link' ), 10, 2 );

		/**
		 * Add TQB quiz result as custom field
		 */
		add_filter( 'tve_dash_mapped_custom_fields', array( __CLASS__, 'tqb_tve_dash_mapped_custom_fields' ) );

		/**
		 * Add TQb js translations in editor
		 */
		add_filter( 'tcb_js_translate', array( __CLASS__, 'tcb_js_translate' ) );

		/**
		 * Add TQB related shortcodes in lg
		 */
		add_filter( 'tve_lg_email_shortcodes', array( __CLASS__, 'tve_lg_email_shortcodes' ), 10, 2 );

		/**
		 * Overwrite default email messages
		 */
		add_filter( 'tcb_js_translate', array( __CLASS__, 'tcb_lg_email_messages' ), 100 );

		/**
		 * Modify the main localization parameters from TAr
		 */
		add_filter( 'tcb_main_frame_localize', array( __CLASS__, 'tcb_localize' ) );

		/**
		 * Search thrive quiz builder variations if they have a specific string in their architect content
		 */
		add_filter( 'tcb_architect_content_has_string', array( __CLASS__, 'search_string_in_design_content' ), 13, 3 );

		/**
		 * Add custom post types to post visibility options blacklist
		 */
		add_filter( 'tcb_post_visibility_options_availability', array( __CLASS__, 'tve_tqb_post_visibility_options' ) );

		/**
		 * Add info article url for Quiz element
		 */
		add_filter( 'thrive_kb_articles', static function ( $articles ) {
			$articles['quiz'] = 'https://api.intercom.io/articles/4426055';

			return $articles;
		} );

		add_filter( 'tve_lcns_attributes', static function ( $attributes, $post_type ) {
			$tag = 'tqb';
			if ( in_array( $post_type, [ TQB_Post_types::QUIZ_POST_TYPE, TQB_Post_types::SPLASH_PAGE_POST_TYPE, TQB_Post_types::QNA_PAGE_POST_TYPE, TQB_Post_types::OPTIN_PAGE_POST_TYPE, TQB_Post_types::RESULTS_PAGE_POST_TYPE ], true ) ) {
				return [
					'source'        => $tag,
					'exp'           => ! TD_TTW_User_Licenses::get_instance()->has_active_license( $tag ),
					'gp'            => TD_TTW_User_Licenses::get_instance()->is_in_grace_period( $tag ),
					'show_lightbox' => TD_TTW_User_Licenses::get_instance()->show_gp_lightbox( $tag ),
					'link'          => tvd_get_individual_plugin_license_link( $tag ),
					'product'       => 'Thrive Quiz Builder',
				];
			}

			return $attributes;
		}, 10, 2 );
	}

	/**
	 * Post visibility options blacklist
	 *
	 * @param $post_types
	 *
	 * @return array
	 */
	public static function tve_tqb_post_visibility_options( $post_types ) {
		$post_types = array_merge( $post_types, array(
			Thrive_Quiz_Builder::SHORTCODE_NAME,
			Thrive_Quiz_Builder::QUIZ_STRUCTURE_ITEM_SPLASH_PAGE,
			Thrive_Quiz_Builder::QUIZ_STRUCTURE_ITEM_QNA,
			Thrive_Quiz_Builder::QUIZ_STRUCTURE_ITEM_OPTIN,
			Thrive_Quiz_Builder::QUIZ_STRUCTURE_ITEM_RESULTS,
		) );

		return $post_types;
	}

	public static function search_string_in_design_content( $has_string, $string, $post_id ) {
		if ( ! $has_string ) {
			global $tqbdb;
			if ( $tqbdb->search_string_in_designs( $string ) ) {
				$has_string = true;
			}
		}

		return $has_string;
	}

	public static function dequeue_mm() {
		wp_dequeue_script( 'mm-common-core.js' );
		wp_dequeue_script( 'mm-preview.js' );
		wp_dequeue_script( 'membermouse-socialLogin' );
		wp_dequeue_script( 'membermouse-blockUI' );
	}

	/**
	 * Adds TQB query string variation to preview link
	 *
	 * @param $preview_link
	 * @param $post
	 *
	 * @return string
	 */
	public static function tqb_preview_post_link( $preview_link, $post ) {

		if ( self::is_editable( get_post_type( $post ) ) && ! empty( $_GET[ Thrive_Quiz_Builder::VARIATION_QUERY_KEY_NAME ] ) ) {

			$preview_link = add_query_arg( array(
				Thrive_Quiz_Builder::VARIATION_QUERY_KEY_NAME => sanitize_text_field( $_GET[ Thrive_Quiz_Builder::VARIATION_QUERY_KEY_NAME ] ),
			), $preview_link );
		}

		return $preview_link;
	}

	/**
	 * Push quiz result custom field
	 *
	 * @param array $fields
	 *
	 * @return array
	 */
	public static function tqb_tve_dash_mapped_custom_fields( $fields ) {

		$post_id            = ! empty( $_REQUEST['tqb-variation-page_id'] ) ? (int) $_REQUEST['tqb-variation-page_id'] : null;
		$post               = get_post( $post_id );
		$allowed_post_types = array(
			Thrive_Quiz_Builder::QUIZ_STRUCTURE_ITEM_OPTIN,
			Thrive_Quiz_Builder::QUIZ_STRUCTURE_ITEM_RESULTS,
		);

		$allowed = true === $post instanceof WP_Post && in_array( $post->post_type, $allowed_post_types );

		if ( ! $allowed ) {
			return $fields;
		}

		$fields[] = array(
			'id'          => 'mapping_quiz_result',
			'placeholder' => __( 'Result of quiz', 'thrive-quiz-builder' ),
		);

		return $fields;
	}

	/**
	 * @param array $shortcodes
	 *
	 * @return array
	 */
	public static function tve_lg_email_shortcodes( $shortcodes ) {

		$post               = get_post();
		$allowed_post_types = array(
			Thrive_Quiz_Builder::QUIZ_STRUCTURE_ITEM_OPTIN,
			Thrive_Quiz_Builder::QUIZ_STRUCTURE_ITEM_RESULTS,
		);

		if ( true !== $post instanceof WP_Post || ! in_array( $post->post_type, $allowed_post_types ) ) {
			return $shortcodes;
		}

		$quiz_sh = array(
			array(
				'key'        => 'quiz',
				'label'      => 'Quiz',
				'order'      => 1,
				'shortcodes' => array(
					'quiz_name'    => array(
						'label' => __( 'Quiz name', 'thrive-quiz-builder' ),
						'value' => '[quiz_name]',
					),
					'quiz_answers' => array(
						'label' => __( 'List all the questions and answers', 'thrive-quiz-builder' ),
						'value' => '[quiz_answers]',
					),
					'quiz_result'  => array(
						'label' => __( 'Quiz result', 'thrive-quiz-builder' ),
						'value' => '[quiz_result]',
					),
				),
			),
		);

		if ( count( $shortcodes ) >= $quiz_sh[0]['order'] ) {
			array_splice( $shortcodes, $quiz_sh[0]['order'], 0, $quiz_sh );
		} else {
			$shortcodes[] = $quiz_sh;
		}

		return $shortcodes;
	}

	/**
	 * Overwrite default email message for optin gate and result page
	 *
	 * @param array $data
	 *
	 * @return array
	 */
	public static function tcb_lg_email_messages( $data ) {

		$post               = get_post();
		$allowed_post_types = array(
			Thrive_Quiz_Builder::QUIZ_STRUCTURE_ITEM_OPTIN,
			Thrive_Quiz_Builder::QUIZ_STRUCTURE_ITEM_RESULTS,
		);

		if ( true !== $post instanceof WP_Post || ! in_array( $post->post_type, $allowed_post_types ) ) {
			return $data;
		}

		$data['lg_email'] = array(
			'email_subject'              => __( 'Someone completed the quiz: [quiz_name]', 'thrive-quiz-builder' ),
			'email_message'              => __( "Someone completed the quiz called [quiz_name] on this page: [page_url] \n\n<b>The user's details:</b>\n[all_form_fields] \n<b>The answers that the user gave:</b> \n\n[quiz_answers] \n<b>The result that the user got:</b>\n\n[quiz_result]", 'thrive-quiz-builder' ),
			'email_confirmation_subject' => __( 'Thank you for completing the quiz', 'thrive-quiz-builder' ),
			'email_confirmation_message' => __( "Thank you for taking the time to complete the quiz. \n\n<b>Here are the answers you gave:</b> \n\n[quiz_answers] \n<b>Your result is:</b>\n\n[quiz_result]", 'thrive-quiz-builder' ),
		);

		return $data;
	}

	/**
	 * Adds Extra Scripts to Main Frame
	 */
	public static function tqb_add_script_to_main_frame() {

		$type = get_post_type();

		if ( self::is_editable( $type ) ) {
			global $variation;
			if ( empty( $variation ) ) {
				$variation = tqb_get_variation( ! empty( $_GET[ Thrive_Quiz_Builder::VARIATION_QUERY_KEY_NAME ] ) ? absint( $_GET[ Thrive_Quiz_Builder::VARIATION_QUERY_KEY_NAME ] ) : 0 );
			}

			$allow_tqb_advanced = TCB_Hooks::enable_tqb_advanced_menu( $variation['post_type'] );
			$absolute_limits    = tqb_compute_quiz_absolute_max_min_values( $variation['quiz_id'], $allow_tqb_advanced );
			$quiz_type          = TQB_Post_meta::get_quiz_type_meta( $variation['quiz_id'] );

			$variation_manager = new TQB_Variation_Manager( $variation['quiz_id'], $variation['page_id'] );
			$child_variations  = $variation_manager->get_page_variations( array( 'parent_id' => $variation['id'] ) );

			if ( TCB_Hooks::enable_tqb_advanced_menu( $variation['post_type'] ) ) {
				wp_enqueue_script( 'tqb-bootstrap-tooltip', tqb()->plugin_url( 'tcb-bridge/assets/js/lib/bootstrap.min.js' ), array( 'jquery' ), '', true );
			}


			$intervals = array();
			foreach ( $child_variations as $child ) {
				$intervals[] = array(
					'post_title' => $child['post_title'],
					'id'         => $child['id'],
				);
			}

			$page_data = array(
				'variation_id'         => $variation['id'],
				'page_id'              => $variation['page_id'],
				'tpl_action'           => self::ACTION_TEMPLATE,
				'state_action'         => self::ACTION_STATE,
				'ajaxurl'              => admin_url( 'admin-ajax.php' ),
				'has_content'          => ! empty( $variation['content'] ),
				'allow_tqb_advanced'   => $allow_tqb_advanced,
				'is_personality_type'  => ( $quiz_type['type'] === Thrive_Quiz_Builder::QUIZ_TYPE_PERSONALITY ) ? true : false,
				'quiz_type'            => $quiz_type['type'],
				'quiz_config'          => array(
					'absolute_min_value'  => $absolute_limits['min'],
					'absolute_max_value'  => $absolute_limits['max'],
					'max_interval_number' => Thrive_Quiz_Builder::STATES_MAXIMUM_NUMBER_OF_INTERVALS,
				),
				'variation_type'       => tqb()->get_structure_type_name( $variation['post_type'] ),
				'intervals'            => $intervals,
				'security'             => wp_create_nonce( self::SECURITY_NONCE ),
				'kb_next_step_article' => Thrive_Quiz_Builder::KB_NEXT_STEP_ARTICLE,
				'L'                    => array(
					'alert_choose_tpl'                    => __( 'Please choose a template', 'thrive-quiz-builder' ),
					'tpl_name_required'                   => __( 'Please enter a template name, it will be easier to reload it after.', 'thrive-quiz-builder' ),
					'fetching_saved_templates'            => __( 'Fetching saved templates...', 'thrive-quiz-builder' ),
					'intervals_min_val_cannot_be_changed' => __( 'The minimum value cannot be changed!', 'thrive-quiz-builder' ),
					'intervals_max_val_cannot_be_changed' => __( 'The maximum value cannot be changed!', 'thrive-quiz-builder' ),
					'min_value_limit'                     => __( 'The minimum value cannot be less than ', 'thrive-quiz-builder' ),
					'max_value_limit'                     => __( 'The maximum value cannot be greater than ', 'thrive-quiz-builder' ),
				),
			);
			tqb_enqueue_script( 'tqb-internal-editor', tqb()->plugin_url( 'tcb-bridge/assets/js/tqb-tcb-internal.min.js' ), array( 'tve-main' ) );
			wp_localize_script( 'tqb-internal-editor', 'tqb_page_data', $page_data );

			tqb_enqueue_style( 'tqb-main-frame-css', tqb()->plugin_url( 'tcb-bridge/assets/css/main-frame.css' ) );
		} else {
			tqb_enqueue_script( 'tqb-external-editor', tqb()->plugin_url( 'tcb-bridge/assets/js/tqb-tcb-external.min.js' ) );
			wp_localize_script( 'tqb-external-editor', 'TQB', array(
				'action'   => 'tcb-tqb-quiz-action',
				'ajaxurl'  => admin_url( 'admin-ajax.php' ),
				'security' => wp_create_nonce( 'tqb_frontend_ajax_request' ),
				'quizzes'  => TQB_Quiz::get_items_for_architect_integration(),
			) );
		}
	}

	/**
	 * Inject the editor js file as a dependency over the tve-main file
	 * For now we do this only for tqb-external-editor.js
	 *
	 * @param array $dependencies
	 *
	 * @return array
	 */
	public static function tqb_main_js_dependencies( $dependencies = array() ) {
		if ( ! static::is_editable( get_post_type() ) ) {
			$dependencies[] = 'tqb-external-editor';
		}

		return $dependencies;
	}

	/**
	 * Fetch extra list of templates needed by TQB on editor pages
	 *
	 * @param array $templates list of templates from TCB
	 *
	 * @return array
	 */
	public static function tqb_get_editor_backbone_templates( $templates = array() ) {
		$templates = array_merge( $templates, tve_dash_get_backbone_templates( plugin_dir_path( dirname( __FILE__ ) ) . 'tcb-bridge/editor-backbone', 'backbone' ) );

		return $templates;
	}

	/**
	 * Adds QUIZ BUILDER Product to TCB Editor page
	 *
	 * @param array $elements
	 *
	 * @return mixed
	 */
	public static function tqb_add_product_to_tcb( $elements = array() ) {

		$post = get_post();

		if ( ! $post instanceof WP_Post ) {
			return $elements;
		}

		$type      = $post->post_type;
		$quiz_type = TQB_Post_meta::get_quiz_type_meta( $post->post_parent, true );

		if ( self::is_editable( $type ) ) {

			require_once tqb()->plugin_path( 'tcb-bridge/editor-elements/class-tcb-tqb-page-element.php' );

			$elements['tqb_page'] = new TCB_TQB_Page_Element( 'tqb_page' );

			if ( TCB_Hooks::enable_tqb_advanced_menu( $type ) && Thrive_Quiz_Builder::QUIZ_TYPE_SURVEY !== $quiz_type ) {
				require_once tqb()->plugin_path( 'tcb-bridge/editor-elements/class-tcb-dynamic-content-element.php' );

				$elements['tqb_dynamic_content'] = new TCB_Dynamic_Content_Element( 'tqb_dynamic_content' );
				if ( $type === Thrive_Quiz_Builder::QUIZ_STRUCTURE_ITEM_RESULTS ) {
					require_once tqb()->plugin_path( 'tcb-bridge/editor-elements/class-tcb-social-share-badge-element.php' );

					$elements['tqb_social_share_badge'] = new TCB_Social_Share_Badge_Element( 'tqb_social_share_badge' );
				}
			}
		} else {
			require_once tqb()->plugin_path( 'tcb-bridge/editor-elements/class-tcb-quiz-element.php' );

			$elements['quiz'] = new TCB_Quiz_Element( 'quiz' );
		}


		return $elements;
	}

	/**
	 * Modifies Thrive Architect close URL when in TQB Editor
	 *
	 * @param string $close_url
	 *
	 * @return string
	 */
	public static function tqb_tcb_close_url( $close_url = '' ) {
		$type = get_post_type();
		if ( self::is_editable( $type ) ) {
			$close_url = 'javascript:window.close();';
		}

		return $close_url;
	}

	/**
	 * Remove Elements Instances
	 *
	 * @param array $elements
	 *
	 * @return array
	 */
	public static function tqb_remove_element_instances( $elements = array() ) {
		$type = get_post_type();
		if ( self::is_editable( $type ) ) {
			/**
			 * Remove Thrive Leads ShortCode
			 */
			if ( ! empty( $elements['tl_shortcode'] ) ) {
				unset( $elements['tl_shortcode'] );
			}

			/**
			 * Remove Thrive Ultimatum Countdown
			 */
			if ( ! empty( $elements['ultimatum_countdown'] ) ) {
				unset( $elements['ultimatum_countdown'] );
			}
		}

		return $elements;
	}

	/**
	 * Outputs Extra SVG Icons to editor page (Control Panel)
	 */
	public static function tqb_output_extra_control_panel_svg() {
		include tqb()->plugin_path( 'tcb-bridge/assets/css/fonts/quiz-builder-main.svg' );
	}

	/**
	 * Outputs Extra SVG Icons to editor page (Editor)
	 */
	public static function tqb_output_extra_iframe_svg() {
		include tqb()->plugin_path( 'tcb-bridge/assets/css/fonts/quiz-builder-editor.svg' );
	}

	/**
	 * Returns the new Quiz Component Menu path
	 *
	 * @return string
	 */
	public static function tqb_include_quiz_menu() {
		$type = get_post_type();
		if ( ! self::is_editable( $type ) ) {
			return tqb()->plugin_path( 'tcb-bridge/editor-layouts/menus/quiz.php' );
		}
	}

	/**
	 * Returns the new Social Share Badge Menu Path
	 *
	 * @return string
	 */
	public static function tqb_include_social_share_badge_menu() {
		$type = get_post_type();
		if ( $type === Thrive_Quiz_Builder::QUIZ_STRUCTURE_ITEM_RESULTS ) {
			return tqb()->plugin_path( 'tcb-bridge/editor-layouts/menus/social-share-badge.php' );
		}
	}


	/**
	 * Enable/Disable modification of facebook share options by other plugins
	 *
	 * @param bool $allow
	 *
	 * @return bool
	 */
	public static function tqb_modify_facebook_share_options( $allow = false ) {
		$type = get_post_type();
		if ( $type === Thrive_Quiz_Builder::QUIZ_STRUCTURE_ITEM_RESULTS ) {
			return ! $allow;
		}

		return $allow;
	}

	/**
	 * Add some Quiz Builder post types to Architect Post Grid Element Banned Types
	 *
	 * @param array $banned_types
	 *
	 * @return array
	 */
	public static function tqb_add_post_grid_banned_types( $banned_types = array() ) {
		$banned_types[] = TIE_Post_Types::THRIVE_IMAGE;
		$banned_types[] = TQB_Post_types::QUIZ_POST_TYPE;
		$banned_types[] = TQB_Post_types::OPTIN_PAGE_POST_TYPE;
		$banned_types[] = TQB_Post_types::QNA_PAGE_POST_TYPE;
		$banned_types[] = TQB_Post_types::RESULTS_PAGE_POST_TYPE;
		$banned_types[] = TQB_Post_types::SPLASH_PAGE_POST_TYPE;

		return $banned_types;
	}

	/**
	 * Includes the TQB Modal template files
	 *
	 * @param array $files existing modal files
	 *
	 * @return array
	 */
	public static function tqb_modal_files( $files = array() ) {
		$type = get_post_type();

		if ( self::is_editable( $type ) ) {

			$files[] = tqb()->plugin_path( 'tcb-bridge/editor-lightbox/variation-templates.php' );
			$files[] = tqb()->plugin_path( 'tcb-bridge/editor-lightbox/variation-reset.php' );
			$files[] = tqb()->plugin_path( 'tcb-bridge/editor-lightbox/variation-save.php' );
			$files[] = tqb()->plugin_path( 'tcb-bridge/editor-lightbox/save-validation.php' );

			if ( TCB_Hooks::enable_tqb_advanced_menu( $type ) ) {
				$files[] = tqb()->plugin_path( 'tcb-bridge/editor-lightbox/result-intervals.php' );
				$files[] = tqb()->plugin_path( 'tcb-bridge/editor-lightbox/equalize-intervals.php' );
				$files[] = tqb()->plugin_path( 'tcb-bridge/editor-lightbox/import-content.php' );
				$files[] = tqb()->plugin_path( 'tcb-bridge/editor-lightbox/remove-interval.php' );
				$files[] = tqb()->plugin_path( 'tcb-bridge/editor-lightbox/delete-dynamic-content.php' );
				$files[] = tqb()->plugin_path( 'tcb-bridge/editor-lightbox/social-share-badge-template.php' );
			}
		}

		return $files;
	}

	/**
	 * Inserts the state bar after iFrame initialization
	 */
	public static function tqb_hook_control_panel() {
		$type = get_post_type();
		if ( TCB_Hooks::enable_tqb_advanced_menu( $type ) ) {
			include tqb()->plugin_path( 'tcb-bridge/editor/page/states.php' );
		}
	}

	/**
	 * Enables Template Tab in Settings Section
	 *
	 * @param bool $status
	 *
	 * @return bool
	 */
	public static function tqb_enable_template_tab( $status ) {
		$type = get_post_type();

		if ( self::is_editable( $type ) ) {
			return true;
		}

		return $status;
	}

	public static function tqb_enable_settings_tab( $status ) {

		if ( get_post_type() === 'tqb_quiz' ) {
			//Disable settings tab for Question Editor
			$status = false;
		}

		return $status;
	}

	public static function tqb_can_import_content( $status ) {
		$type = get_post_type();

		if ( self::is_editable( $type ) ) {
			$status = false;
		}

		return $status;
	}

	/**
	 * Adds Template Tab Menu Items
	 */
	public static function right_sidebar_content_settings() {
		$type = get_post_type();

		if ( self::is_editable( $type ) ) {
			include tqb()->plugin_path( 'tcb-bridge/editor-layouts/element-menus/content-settings.php' );
		}
	}

	/**
	 * Adds Template Tab Menu Items
	 */
	public static function right_sidebar_top_settings() {
		$type = get_post_type();

		if ( self::is_editable( $type ) ) {
			include tqb()->plugin_path( 'tcb-bridge/editor-layouts/element-menus/reset-settings.php' );
		}
	}

	/**
	 * Disable Revision Manager For Quiz Builder Pages
	 *
	 * @param bool $status
	 *
	 * @return bool
	 */
	public static function tqb_disable_revision_manager( $status = true ) {
		$post_type = get_post_type();

		if ( self::is_editable( $post_type ) ) {
			return ! $status;
		}

		return $status;
	}

	/**
	 * Disabled the adding of elements in certain cases
	 *
	 * @param bool $allow
	 *
	 * @return bool
	 */
	public static function tqb_disable_add_elements( $allow = true ) {
		$post_type = get_post_type();

		if ( $post_type === Thrive_Quiz_Builder::SHORTCODE_NAME ) {
			$allow = false;
		}

		return $allow;
	}

	/**
	 * Disabled the editor preview button in certain scenarios
	 *
	 * @param bool $allow
	 *
	 * @return bool
	 */
	public static function tqb_disable_editor_preview_button( $allow = true ) {
		$post_type = get_post_type();

		if ( $post_type === Thrive_Quiz_Builder::SHORTCODE_NAME ) {
			$allow = false;
		}

		return $allow;
	}

	/**
	 * Disable Page Events for Quiz Builder Pages
	 *
	 * @param bool $allow
	 *
	 * @return bool
	 */
	public static function tqb_disable_page_events( $allow = true ) {
		$post_type = get_post_type();

		if ( $post_type === Thrive_Quiz_Builder::SHORTCODE_NAME || self::is_editable( $post_type ) ) {
			return false;
		}

		return $allow;
	}

	/**
	 * END TCB 2.0 HOOKS
	 */


	/**
	 * called when there is no active license for TCB, but it is installed and enabled
	 * the function returns true only for pieces of content that "belong" to Thrive Ultimatum, so only the following:
	 *
	 * @param bool $override
	 *
	 * @return bool whether or not the current piece of content can be edited with TCB core functions
	 */
	public static function license_override( $override ) {
		/* this means that the license check should be skipped, possibly from thrive leads */
		if ( $override ) {
			return true;
		}

		$post_type = get_post_type();

		return self::is_editable( $post_type );
	}

	/**
	 * Checks if TQB license if valid (only if the user is trying to edit a design)
	 *
	 * @param bool $valid
	 *
	 * @return bool
	 */
	public static function editor_check_license( $valid ) {
		if ( empty( $_GET[ Thrive_Quiz_Builder::VARIATION_QUERY_KEY_NAME ] ) ) {
			return $valid;
		}

		if ( ! tqb()->license_activated() ) {
			add_action( 'wp_print_footer_scripts', array( __CLASS__, 'tcb_license_warning' ) );


			return false;
		}

		return true;
	}

	/**
	 * Check if TCB version is valid
	 *
	 * @param bool $valid
	 *
	 * @return bool
	 */
	public static function editor_check_tcb_version( $valid ) {
		if ( empty( $_GET[ Thrive_Quiz_Builder::VARIATION_QUERY_KEY_NAME ] ) ) {
			return $valid;
		}

		if ( ! tqb()->check_tcb_version() ) {
			add_action( 'wp_print_footer_scripts', array( __CLASS__, 'tcb_version_warning' ) );

			return false;
		}

		return true;
	}

	/**
	 * Check if the post can be edited by checking access and post type
	 *
	 *
	 * @return bool
	 */
	public static function check_plugin_cap( $valid ) {
		if ( ! in_array( get_post_type(), tqb_get_all_post_types() ) ) {
			return $valid;

		}

		return TQB_Product::has_access();
	}

	/**
	 * show a box with a warning message notifying the user to update the TCB plugin to the latest version
	 * this will be shown only when the TCB version is lower than a minimum required version
	 */
	public static function tcb_version_warning() {
		return include tqb()->plugin_path( 'includes/admin/views/tcb_version_incompatible.phtml' );
	}

	public static function tcb_license_warning() {
		return include tqb()->plugin_path( '/includes/admin/views/license-inactive.phtml' );
	}

	/**
	 * Filter autoresponder actions after submit
	 *
	 * @param bool $show_submit
	 *
	 * @return bool
	 */
	public static function tqb_filter_autoresponder_submit_option( $show_submit ) {

		if ( empty( $_POST['post_id'] ) ) {
			return $show_submit;
		}
		$post = get_post( absint( $_POST['post_id'] ) );
		if ( empty( $post ) ) {
			return $show_submit;
		}
		if ( $post->post_type == Thrive_Quiz_Builder::QUIZ_STRUCTURE_ITEM_OPTIN || $post->post_type == Thrive_Quiz_Builder::QUIZ_STRUCTURE_ITEM_SPLASH_PAGE ) {
			return false;
		}

		return $show_submit;
	}

	/**
	 * Filter autoresponder connection types
	 *
	 * @param array $connection_types
	 *
	 * @return array
	 */
	public static function tqb_filter_autoresponder_connection_type( $connection_types ) {

		$post = get_post( ! empty( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0 );
		if ( empty( $post ) ) {
			return $connection_types;
		}
		if ( $post->post_type == Thrive_Quiz_Builder::QUIZ_STRUCTURE_ITEM_OPTIN || $post->post_type == Thrive_Quiz_Builder::QUIZ_STRUCTURE_ITEM_SPLASH_PAGE ) {
			if ( isset( $connection_types['custom-html'] ) ) {
				unset( $connection_types['custom-html'] );
			}
		}

		return $connection_types;
	}

	/**
	 * Disable the style families option from TCB when editing a quiz page
	 *
	 * @param array $style_families
	 *
	 * @return array
	 */
	public static function disable_style_families_option( $style_families = array() ) {

		$post_type = get_post_type();;

		if ( ! self::is_editable( $post_type ) ) {
			return $style_families;
		}

		unset( $style_families['Classy'] );
		unset( $style_families['Minimal'] );

		return $style_families;
	}

	/**
	 * Hook to save user template method
	 *
	 * @param array $template
	 *
	 * @return array
	 */
	public static function tqb_save_user_template( $template = array() ) {
		$post_type = get_post_type( ! empty( $_REQUEST['post_id'] ) ? absint( $_REQUEST['post_id'] ) : 0 );

		if ( ! self::is_editable( $post_type ) ) {
			return $template;
		}

		$template['template_content'] = str_replace( array(
			'tqb-dynamic-content-container',
			'tqb-content-inner',
			addslashes( Thrive_Quiz_Builder::STATES_DYNAMIC_CONTENT_PATTERN ),
		), '', $template['template_content'] );

		return $template;
	}

	/**
	 * Hook from TCB, this loads the editor layout file
	 *
	 * @param $current_templates
	 * @param $post_id
	 * @param $post_type
	 *
	 * @return mixed
	 */
	public static function editor_layout( $current_templates, $post_id, $post_type ) {

		global $variation;

		if ( ! self::is_editable( $post_type ) ) {
			return $current_templates;
		}

		if ( empty( $variation ) ) {
			$variation_id = isset( $_GET[ Thrive_Quiz_Builder::VARIATION_QUERY_CHILD_KEY_NAME ] ) ? absint( $_GET[ Thrive_Quiz_Builder::VARIATION_QUERY_CHILD_KEY_NAME ] ) : $_GET[ Thrive_Quiz_Builder::VARIATION_QUERY_KEY_NAME ];
			$parent_id    = isset( $_GET[ Thrive_Quiz_Builder::VARIATION_QUERY_CHILD_KEY_NAME ] ) ? absint( $_GET[ Thrive_Quiz_Builder::VARIATION_QUERY_KEY_NAME ] ) : 0;

			$variation = tqb_get_variation( $variation_id, $parent_id );

			if ( isset( $variation[ Thrive_Quiz_Builder::FIELD_INLINE_CSS ] ) ) {
				$variation[ Thrive_Quiz_Builder::FIELD_INLINE_CSS ] = tqb_merge_media_query_styles( $variation[ Thrive_Quiz_Builder::FIELD_INLINE_CSS ] );
			}
		}

		if ( empty( $variation ) ) {
			return $current_templates;
		}

		$current_templates['variation'] = tqb()->plugin_path( 'tcb-bridge/editor/page/' . TQB_Template_Manager::type( $variation['post_type'] ) . '.php' );
		$variation['style']             = TQB_Post_meta::get_quiz_style_meta( $variation['quiz_id'] );

		$allow_tqb_advanced = ( ! empty( $variation[ Thrive_Quiz_Builder::FIELD_TEMPLATE ] ) && TCB_Hooks::enable_tqb_advanced_menu( $variation['post_type'] ) ) ? true : false;

		/* flat is the default style for Thrive Quiz Builder designs */
		tve_enqueue_style( 'tve_style_family_tve_flt', tve_editor_css() . '/thrive_flat.css' );

		if ( is_editor_page() ) {
			tqb_enqueue_style( 'tqb-variation-editor', tqb()->plugin_url( 'tcb-bridge/assets/css/editor.css' ) );
		} else {
			//this is the preview page
			tqb_enqueue_default_scripts();

			/**
			 * Checking page content for events before preview
			 */
			$content = TCB_Hooks::tqb_editor_custom_content( $variation );
			tve_parse_events( $content );
			tqb_enqueue_script( 'tqb-frontend-preview', tqb()->plugin_url( 'assets/js/dist/preview.min.js' ), array( 'jquery' ) );

			// Include draggable only for result page
			if ( $allow_tqb_advanced ) {
				wp_enqueue_script( 'jquery-ui-draggable', false, array( 'jquery' ) );
				// enqueue the state-picker js
				tqb_enqueue_script( 'tqb-state-picker', tqb()->plugin_url( 'tcb-bridge/assets/js/tqb-tcb-state-picker.min.js' ) );

				$state_options = array(
					'state_action' => self::ACTION_STATE,
					'ajaxurl'      => admin_url( 'admin-ajax.php' ),
					'variation_id' => $variation['id'],
					'page_id'      => $variation['page_id'],
					'security'     => wp_create_nonce( self::SECURITY_NONCE ),
				);

				wp_localize_script( 'tqb-state-picker', 'tqb_state_data', $state_options );
			}
		}

		add_action( 'wp_enqueue_scripts', 'tqb_enqueue_variation_scripts' );

		/**
		 * Remove MemberMouse scripts from TQB Editor Layouts SUPP-8019
		 */
		add_action( 'wp_print_scripts', array( __CLASS__, 'dequeue_mm' ), 100 );

		return $current_templates;
	}

	public static function inner_frame_body_class( $classes ) {
		$classes [] = 'tve_editor_page';
		$classes [] = 'preview-desktop';

		return $classes;
	}

	/**
	 * Appends any required parameters to the global JS configuration array on the editor page
	 *
	 * @param $js_params
	 * @param $post_id
	 * @param $post_type
	 *
	 * @return mixed
	 */
	public static function editor_javascript_params( $js_params, $post_id, $post_type ) {
		if ( ! self::is_editable( $post_id ) || empty( $_GET[ Thrive_Quiz_Builder::VARIATION_QUERY_KEY_NAME ] ) ) {
			return $js_params;
		}

		global $variation;
		if ( empty( $variation ) ) {
			//TODO: implement this.
		}

		/** clear out any data that's not necessary on the editor and add form variation custom data */
		$js_params['landing_page']          = '';
		$js_params['landing_page_config']   = array();
		$js_params['landing_pages']         = array();
		$js_params['page_events']           = array();
		$js_params['landing_page_lightbox'] = array();
		$js_params['custom_post_data']      = array(
			Thrive_Quiz_Builder::VARIATION_QUERY_KEY_NAME => $variation['id'],
			'post_type'                                   => $variation['post_type'],
			'disabled_controls'                           => array(
				'page_events'   => 1,
				'text'          => array( 'more_link' ),
				'event_manager' => array(),
			),
		);

		$js_params['save_post_action'] = self::ACTION_SAVE_VARIATION_CONTENT;
		$js_params['show_more_tag']    = false;

		return $js_params;
	}

	/**
	 * Is Editable with TCB
	 *
	 * @param int|string $post_or_type
	 *
	 * @return bool
	 */
	public static function is_editable( $post_or_type ) {
		$post_or_type = is_numeric( $post_or_type ) ? get_post_type( $post_or_type ) : $post_or_type;

		return in_array(
			$post_or_type,
			array(
				TQB_Post_types::OPTIN_PAGE_POST_TYPE,
				TQB_Post_types::RESULTS_PAGE_POST_TYPE,
				TQB_Post_types::SPLASH_PAGE_POST_TYPE,
			),
			true
		);
	}


	/**
	 * called via AJAX
	 * receives editor content and various fields needed throughout the editor
	 */
	public static function editor_save_content() {
		$response = array(
			'success' => true,
		);

		if ( empty( $_POST['post_id'] ) || ! TQB_Product::has_access() || ! current_user_can( 'edit_post', absint( $_POST['post_id'] ) ) || empty( $_POST[ Thrive_Quiz_Builder::VARIATION_QUERY_KEY_NAME ] ) ) {
			$response = array(
				'success' => false,
				'message' => __( 'Invalid Parameters', 'thrive-quiz-builder' ),
			);

			return $response;
		}

		if ( ob_get_contents() ) {
			ob_clean();
		}

		$variation = tqb_get_variation( absint( $_POST[ Thrive_Quiz_Builder::VARIATION_QUERY_KEY_NAME ] ) );
		if ( empty( $variation ) ) {
			$response = array(
				'success' => false,
				'message' => __( 'Could not find the variation you are editing... Is it possible that someone deleted it from the admin panel?', 'thrive-quiz-builder' ),
			);

			return $response;
		}

		/*
		 * Prepare the child variation content
		 */
		if ( ! empty( $_POST['tqb_child_variation_id'] ) && is_numeric( $_POST['tqb_child_variation_id'] ) ) {
			$pattern = '#__TQB__dynamic_DELIMITER</div>(.+?)<div style=\\\"display:(\s*)none;?\\\">__TQB__dynamic_DELIMITER#s';
			/**
			 * SOME IE VALIDATIONS:
			 * IE inserts a space after the "display:" -> (\s*)
			 * IE puts a semicolon after none ;?
			 */
			preg_match( $pattern, ! empty( $_POST['tve_content'] ) ? $_POST['tve_content'] : '', $m ); //phpcs:ignore
			$dynamic_content = '';
			if ( ! empty( $m[1] ) ) { // . '<div class="tve_content_inner tqb-content-inner">'  . '</div>'
				$dynamic_content = Thrive_Quiz_Builder::STATES_DYNAMIC_CONTENT_PATTERN . $m[1] . Thrive_Quiz_Builder::STATES_DYNAMIC_CONTENT_PATTERN;
			}

			$child_variation = TQB_Variation_Manager::get_variation( $_POST['tqb_child_variation_id'] );

			$child_variation['tcb_fields'][ Thrive_Quiz_Builder::FIELD_INLINE_CSS ] = ! empty( $_POST['tqb_child_variation_css'] ) ? json_decode( stripslashes( $_POST['tqb_child_variation_css'] ), true ) : ''; //phpcs:ignore

			TQB_Lightspeed::save_optimized_assets( $_POST['post_id'], $_POST['tqb_child_variation_id'] );

			TQB_Variation_Manager::save_child_variation( array(
				'id'         => sanitize_text_field( $_POST['tqb_child_variation_id'] ),
				'parent_id'  => $variation['id'],
				'content'    => $dynamic_content,
				'tcb_fields' => $child_variation['tcb_fields'],
			) );
		}
		/*
		 * END: Prepare the child variation content
		 */
		$variation[ Thrive_Quiz_Builder::FIELD_CONTENT ]            = $_POST['tve_content']; //phpcs:ignore
		$variation[ Thrive_Quiz_Builder::FIELD_INLINE_CSS ]         = trim( $_POST['inline_rules'] ); //phpcs:ignore
		$variation[ Thrive_Quiz_Builder::FIELD_USER_CSS ]           = $_POST['tve_custom_css']; //phpcs:ignore
		$variation[ Thrive_Quiz_Builder::FIELD_CUSTOM_FONTS ]       = self::tqb_get_custom_font_links( empty( $_POST['custom_font_classes'] ) ? array() : $_POST['custom_font_classes'] ); //phpcs:ignore
		$variation[ Thrive_Quiz_Builder::FIELD_TYPEFOCUS ]          = empty( $_POST['tve_has_typefocus'] ) ? 0 : 1;
		$variation[ Thrive_Quiz_Builder::FIELD_MASONRY ]            = empty( $_POST['tve_has_masonry'] ) ? 0 : 1;
		$variation[ Thrive_Quiz_Builder::FIELD_ICON_PACK ]          = empty( $_POST['has_icons'] ) ? 0 : 1;
		$variation[ Thrive_Quiz_Builder::FIELD_SOCIAL_SHARE_BADGE ] = strpos( $_POST['tve_content'], '"tqb-social-share-badge-container' ) !== false ? 1 : 0;

		$variation_manager = new TQB_Variation_Manager( $variation['quiz_id'], $variation['page_id'] );

		//Save only the content and the tcb_fields. nothing else.
		$variation = $variation_manager->prepare_variation_for_tcb_save( $variation );

		TQB_Lightspeed::save_optimized_assets( $variation['page_id'], $variation['id'] );

		$variation_manager->save_variation( $variation, false );

		return $response;
	}

	/**
	 * Transform an array of font classes into links to the actual google font
	 *
	 * @param array $custom_font_classes the classes used for custom fonts
	 *
	 * @return array
	 */
	public static function tqb_get_custom_font_links( $custom_font_classes = array() ) {
		$all_fonts = tve_get_all_custom_fonts();

		$post_fonts = array();
		foreach ( array_unique( $custom_font_classes ) as $cls ) {
			foreach ( $all_fonts as $font ) {
				if ( Tve_Dash_Font_Import_Manager::isImportedFont( $font->font_name ) ) {
					$post_fonts[] = Tve_Dash_Font_Import_Manager::getCssFile();
				} elseif ( $font->font_class == $cls && ! tve_is_safe_font( $font ) ) {
					$post_fonts[] = tve_custom_font_get_link( $font );
					break;
				}
			}
		}

		return array_unique( $post_fonts );
	}

	/**
	 * Append the variation id as a parameter for the preview link
	 * Link that is built for the "Preview" button in the editor
	 * This should always lead to the main (Default) state of the variation
	 *
	 * @param $current_args
	 * @param $post_id
	 *
	 * @return array $current_args
	 */
	public static function editor_append_preview_link_args( $current_args, $post_id ) {

		if ( self::is_editable( $post_id ) && ! empty( $_GET[ Thrive_Quiz_Builder::VARIATION_QUERY_KEY_NAME ] ) ) {
			$current_args [ Thrive_Quiz_Builder::VARIATION_QUERY_KEY_NAME ] = sanitize_text_field( $_GET[ Thrive_Quiz_Builder::VARIATION_QUERY_KEY_NAME ] );
		}

		return $current_args;
	}

	/**
	 * Handles template-related actions:
	 */
	public static function template_action() {
		add_filter( 'tcb_is_editor_page_ajax', '__return_true' );
		add_filter( 'tcb_is_editor_page_raw_ajax', '__return_true' );

		if ( empty( $_POST['page_id'] ) || ! TQB_Product::has_access() || ! current_user_can( 'edit_post', absint( $_POST['page_id'] ) ) || empty( $_POST['variation_id'] ) || ! is_numeric( $_POST['variation_id'] ) || empty( $_POST['custom'] ) ) {
			exit();
		}

		if ( ! ( $variation = tqb_get_variation( absint( $_POST['variation_id'] ) ) ) ) {
			exit( '1' );
		}

		TQB_Template_Manager::get_instance( $variation )->api( sanitize_text_field( $_POST['custom'] ) );
	}

	public static function state_action() {
		add_filter( 'tcb_is_editor_page_ajax', '__return_true' );
		add_filter( 'tcb_is_editor_page_raw_ajax', '__return_true' );

		if ( empty( $_POST['page_id'] ) || ! TQB_Product::has_access() || ! current_user_can( 'edit_post', absint( $_POST['page_id'] ) ) || empty( $_POST['variation_id'] ) || ! is_numeric( $_POST['variation_id'] ) || empty( $_POST['custom'] ) ) {
			exit();
		}

		if ( ! ( $variation = tqb_get_variation( absint( $_POST['variation_id'] ) ) ) ) {
			exit( '1' );
		}

		TQB_State_Manager::get_instance( $variation )->api( sanitize_text_field( $_POST['custom'] ) );

	}


	/**
	 * Gets the default variation content from a pre-defined template
	 *
	 * @param $variation    array
	 * @param $template_key string formatted like {variation_type}|{template_name}
	 *
	 * @return string for content
	 */
	public static function tqb_editor_get_template_content( &$variation, $template_key = null ) {
		if ( $template_key === null && ! empty( $variation ) && ! empty( $variation[ Thrive_Quiz_Builder::FIELD_TEMPLATE ] ) ) {
			$template_key = $variation[ Thrive_Quiz_Builder::FIELD_TEMPLATE ];
		}

		if ( empty( $template_key ) ) {
			return '';
		}
		list( $type, $template ) = explode( '|', $template_key );

		$base = tqb()->plugin_path( 'tcb-bridge/editor-templates' );

		$templates = TQB_Template_Manager::get_templates( $type, $variation['quiz_id'] );

		if ( ! isset( $templates[ $template ] ) || ! is_file( $base . '/' . $type . '/' . $template . '.php' ) ) {
			return '';
		}

		$tie_image     = new TIE_Image( $variation['page_id'] );
		$quiz_style_id = TQB_Post_meta::get_quiz_style_meta( $variation['quiz_id'] );
		$style_config  = tqb()->get_style_config( $quiz_style_id );

		ob_start();
		$main_content_style = $style_config[ $type ]['config']['main-content-style'];
		$tie_image_url      = $tie_image->get_image_url();
		if ( empty( $tie_image_url ) ) {
			$tie_image_url = tqb()->plugin_url( 'tcb-bridge/assets/images/share-badge-default.png' );
		}
		include $base . '/' . $type . '/' . $template . '.php';
		$content = ob_get_contents();
		ob_end_clean();

		/** we need to make sure we don't have any left-over data from the previous template */
		$variation[ Thrive_Quiz_Builder::FIELD_INLINE_CSS ]         = '';
		$variation[ Thrive_Quiz_Builder::FIELD_USER_CSS ]           = '';
		$variation[ Thrive_Quiz_Builder::FIELD_CUSTOM_FONTS ]       = array();
		$variation[ Thrive_Quiz_Builder::FIELD_TYPEFOCUS ]          = '';
		$variation[ Thrive_Quiz_Builder::FIELD_MASONRY ]            = '';
		$variation[ Thrive_Quiz_Builder::FIELD_ICON_PACK ]          = '';
		$variation[ Thrive_Quiz_Builder::FIELD_SOCIAL_SHARE_BADGE ] = ( strpos( $content, '"tqb-social-share-badge-container' ) !== false ) ? 1 : 0;

		return $content;
	}

	/**
	 * This is the main controller for editor and preview page
	 *
	 * @param array $variation
	 * @param array $is_editor_or_preview true if we are on the editor / preview page
	 *
	 * @return string
	 */
	public static function tqb_editor_custom_content( $variation, $is_editor_or_preview = true ) {

		if ( empty( $variation ) ) {
			return __( 'Variation cannot be empty', 'thrive-quiz-builder' );
		}

		$tve_saved_content = $variation[ Thrive_Quiz_Builder::FIELD_CONTENT ];

		/**
		 * if in editor page or preview, replace the data-date attribute for the countdown timers with the current_date + 1 day (just for demo purposes)
		 */

		/* this will hold the html for the tinymce editor instantiation, only if we're on the editor page */
		$tinymce_editor = $page_loader = '';

		$is_editor_page = $is_editor_or_preview && tqb_is_editor_page();

		/**
		 * this means we are getting the content to output it on a targeted page => include also the custom CSS rules
		 */
		$custom_css = TCB_Hooks::tqb_editor_output_custom_css( $variation, true );

		$wrap = array(
			'start' => '<div id="tve_editor" class="tve_shortcode_editor">',
			'end'   => '</div>',
		);

		if ( $is_editor_page ) {

//			add_action( 'wp_footer', 'tve_output_wysiwyg_editor' ); TODO: research this!

			$page_loader = '';

		} else {
			$tve_saved_content = tve_restore_script_tags( $tve_saved_content );
		}

		/**
		 * custom Thrive shortcodes
		 */
		$tve_saved_content = tve_thrive_shortcodes( $tve_saved_content, $is_editor_page );

		/* render the content added through WP Editor (element: "WordPress Content") */
		$tve_saved_content = tve_do_wp_shortcodes( $tve_saved_content, $is_editor_page );

		if ( ! $is_editor_page ) {
			$tve_saved_content = shortcode_unautop( $tve_saved_content );
			$tve_saved_content = do_shortcode( $tve_saved_content );
		}

		$tve_saved_content = preg_replace_callback( '/__CONFIG_lead_generation__(.+?)__CONFIG_lead_generation__/s', 'tcb_lg_err_inputs', $tve_saved_content );

		if ( ! $is_editor_page ) {
			$tve_saved_content = apply_filters( 'tcb_clean_frontend_content', $tve_saved_content );
		}

		/**
		 * append any needed custom CSS - only on regular pages, and not on editor / preview page
		 */
		return ( $is_editor_or_preview ? '' : '' . $custom_css ) . $wrap['start'] . $tve_saved_content . $wrap['end'] . $tinymce_editor . $page_loader;
	}

	/**
	 * Get the configuration array used in editor for a specific design template
	 *
	 * @param string $key
	 *
	 * @return array
	 */
	public static function tqb_editor_get_template_config( $key ) {

		if ( strpos( $key, '|' ) === false ) {
			return array();
		}

		list( $variation_type, $key ) = TQB_Template_Manager::tpl_type_key( $key );
		$config = require tqb()->plugin_path( 'tcb-bridge/editor-templates/config.php' );

		return isset( $config[ $variation_type ][ $key ] ) ? $config[ $variation_type ][ $key ] : array();
	}

	/**
	 * TCB Enqueues fonts and returns them for a specific design
	 *
	 * @param $variation array
	 *
	 * @return array
	 */
	public static function tqb_editor_enqueue_custom_fonts( $variation ) {
		if ( empty( $variation[ Thrive_Quiz_Builder::FIELD_CUSTOM_FONTS ] ) ) {
			return array();
		}

		return tve_enqueue_fonts( $variation[ Thrive_Quiz_Builder::FIELD_CUSTOM_FONTS ] );
	}

	/**
	 * Identify if the Post Type is Quiz Type and applies a new layout to it.
	 *
	 * @param $single
	 *
	 * @return string
	 */
	public static function tqb_quiz_custom_template( $single ) {
		global $post;

		if ( tcb_editor()->is_inner_frame() ) {
			return $single;
		}

		/* Checks for single template by post type */
		if ( is_object( $post ) && ! empty( $post->post_type ) && $post->post_type === Thrive_Quiz_Builder::SHORTCODE_NAME ) {

			$quiz_layout = tqb()->plugin_path( 'tcb-bridge/editor-templates/tqb_quiz.php' );
			if ( file_exists( $quiz_layout ) ) {

				return $quiz_layout;
			}
		}

		return $single;
	}

	/**
	 * Captures if the accessed page is from facebook.
	 *
	 * If so, it redirects the user to the page where the quiz is at
	 */
	public static function tqb_quiz_quiz_page_redirect() {
		if ( ! empty( $_GET['tqb_redirect_post_id'] )
		     && is_numeric( $_GET['tqb_redirect_post_id'] )
		     && ! empty( $_SERVER['HTTP_REFERER'] )
		     && ( strpos( sanitize_text_field( $_SERVER['HTTP_REFERER'] ), 'facebook' ) !== false )
			/**
			 * It is important to have the referer here for when the user comes to the site from facebook, we can redirect him to the post where the badge is located
			 */
		) {
			wp_redirect( get_permalink( absint( $_GET['tqb_redirect_post_id'] ) ) );
			exit();
		}
	}

	/**
	 * Outputs custom CSS for a design
	 *
	 * @param mixed $variation can be either a numeric value - for variation_key or an already loaded variation array
	 * @param bool  $return    whether to output the CSS or return it
	 *
	 * @return string the CSS, if $return was true
	 */
	public static function tqb_editor_output_custom_css( $variation, $return = false ) {

		if ( empty( $variation ) || ! is_array( $variation ) ) {
			return '';
		}

		$css = '';
		if ( ! empty( $variation[ Thrive_Quiz_Builder::FIELD_INLINE_CSS ] ) ) { /* inline style rules = custom colors */
			$css = sprintf( '<style type="text/css" class="tve_custom_style">%s</style>', $variation[ Thrive_Quiz_Builder::FIELD_INLINE_CSS ] );
		}

		/** user-defined Custom CSS rules for the form */
		$custom_css = '';

		if ( ! empty( $variation[ Thrive_Quiz_Builder::FIELD_USER_CSS ] ) ) {
			$custom_css = $variation[ Thrive_Quiz_Builder::FIELD_USER_CSS ] . $custom_css;
		}

		if ( ! empty( $custom_css ) ) {
			$css .= sprintf(
				'<style type="text/css" id="tve_head_custom_css" class="tve_user_custom_style">%s</style>',
				$custom_css
			);
		}

		if ( ! is_editor_page() && function_exists( 'tve_get_shared_styles' ) ) {
			$css .= tve_get_shared_styles( $variation[ Thrive_Quiz_Builder::FIELD_CONTENT ] );
		}

		$css = apply_filters( 'tcb_custom_css', $css );

		if ( $return === true ) {
			return $css;
		}

		echo $css; // phpcs:ignore
	}

	/**
	 * Enable the TQB advanced menu
	 *
	 * @param $post_type
	 *
	 * @return bool
	 */
	public static function enable_tqb_advanced_menu( $post_type ) {
		return in_array( $post_type, array(
			Thrive_Quiz_Builder::QUIZ_STRUCTURE_ITEM_RESULTS,
			Thrive_Quiz_Builder::QUIZ_STRUCTURE_ITEM_OPTIN,
		) );
	}

	/**
	 * @param $logo_url
	 *
	 * @return string
	 */
	public static function architect_branding( $logo_url ) {

		if ( self::is_editable( get_post_type() ) ) {
			$logo_url = tqb()->plugin_url( 'assets/images/tqb-logo.png' );
		}

		return $logo_url;
	}

	/**
	 * Make TQB related strings translatable
	 *
	 * @param array $data
	 *
	 * @return array
	 */
	public static function tcb_js_translate( $data ) {

		$data['tqb'] = array(
			'send_quiz_result'      => __( 'Send quiz result as tag', 'thrive-quiz-builder' ),
			'scroll_settings_saved' => __( 'Scroll settings saved for the quiz', 'thrive-quiz-builder' ),
		);

		return $data;
	}

	/**
	 * Modifies TAr localization parameters to fit the needs of TQB
	 *
	 * @param array $params
	 *
	 * @return array
	 */
	public static function tcb_localize( $params ) {

		/**
		 * Modify the link component configuration so that it reads its values from CSS instead of default typography
		 * This should only be applied when editing a TQB-related piece of content.
		 * Needed because TQB templates for pages are stored locally in the plugin.
		 */
		if ( isset( $params['elements']['link']['components']['link'] ) && tqb_is_editor_page() ) {
			foreach ( $params['elements']['link']['components']['link']['config'] as $control_key => & $config ) {
				$config['read_from'] = 'head';
			}
		}

		return $params;
	}
}

return TCB_Hooks::init();
