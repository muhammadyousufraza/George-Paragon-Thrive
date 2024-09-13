<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-comments
 */

use TCB\Lightspeed\JS;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden.
}

/**
 * Class Thrive_Comments_Front
 */
class Thrive_Comments_Front {

	/**
	 * Constructor for Thrive_Comments_Front
	 * Add all frontend hooks
	 */
	public function __construct() {
		/**
		 * TTB is hooked in the 'tcm_active' ( called from init ) and when 3rd party plugins are also involved, it won't correctly identify the template, and return a 404 template.
		 * This happens because during the WP hook, functions such as is_page() are sometimes unreliable.
		 * As a fix, we use 'template_redirect' in the non-editor cases.
		 * However, in the architect editor ( more specifically on landing pages ) we still need to use the original 'wp' hook.
		 * Ticket of reference: https://github.com/ThriveThemes/thrive-comments/pull/139
		 */
		add_action( static::is_architect_editor_page() ? 'wp' : 'template_redirect', array( $this, 'init' ) );

		add_action( 'wp_head', array( $this, 'add_twitter_cards' ) );

		add_action( 'wp_head', array( $this, 'add_facebook_cards' ) );

		add_action( 'wp_head', array( $this, 'accent_color_css' ) );

		add_action( 'wp_login', array( $this, 'tcm_after_login' ) );

		add_filter( 'tcb_landing_head', array( $this, 'accent_color_css' ) );

		add_action( 'wp_footer', array( $this, 'add_frontend_svg_file' ) );

		add_action( 'wp_footer', array( $this, 'tcm_front_backbone_templates' ), 5 );

		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 100 );

		add_action( 'rest_api_init', array( $this, 'tcm_create_frontend_rest_routes' ) );

		add_filter( 'tve_dash_main_ajax_tcm_dash_data', array( $this, 'dash_ajax_data' ), 10, 2 );

		add_action( 'wp_loaded', array( $this, 'tcm_default_labels' ) );

		add_action( 'login_footer', array( $this, 'after_ajax_login' ) );

		add_action( 'wp_ajax_refresh_nonce', array( $this, 'refresh_nonce' ), 10, 2 );

		// Allow Thrive Leads ajax load for Thrive Comments.
		add_filter( 'tve_leads_ajax_load', '__return_true' );

		add_filter( 'tva_term_extra_content', array( $this, 'add_tcm_triggers' ) );

		add_filter( 'optml_dont_replace_url', array( $this, 'optimole_skip_url' ), 100, 2 );

		add_filter( 'thrive_theme_comments_content', array( $this, 'change_theme_comments' ), 90, 2 );

		/**
		 * WordPress version compatibility
		 * Starting with 5.5 wp changed the name of some comments options
		 */
		add_filter( 'tcm_default_settings', array( $this, 'change_default_settings' ) );
	}

	/**
	 * Hide date also from the comments list ( the comments shown for SEO )
	 */
	public function hide_date() {

		if ( ! tcms()->tcm_get_setting_by_name( 'comment_date' ) ) {
			add_filter( 'get_comment_date', '__return_false', 10, 3 );

			add_filter( 'get_comment_time', '__return_false', 10, 3 );
		}

	}

	/**
	 * Add twitter cards to post
	 */
	public function add_twitter_cards() {
		tcmc()->add_twitter_cards();
	}

	/**
	 * Add facebook cards to post
	 */
	public function add_facebook_cards() {
		tcmc()->add_facebook_cards();
	}


	/**
	 * Init frontend and include files
	 */
	public function init() {

		if ( tcm()->is_active() ) {

			add_filter( 'comments_template', array( $this, 'render_comments' ), 50 );

			add_filter( 'the_content', array( $this, 'add_tcm_triggers' ), 11 );

			add_filter( 'get_comment_link', array( $this, 'tcm_change_comments_link' ) );

			// Enqueue the dash frontend script.
			add_filter( 'tve_dash_enqueue_frontend', '__return_true' );

			/*
			 * "dra_allow_rest_api" is a hook provided by Disable REST API (DRA) for whitelisting visitor request availability
			 * filter added to prevent the DRA plugin from blocking TCM requests
			 */
			add_filter( 'dra_allow_rest_api', array( $this, 'allow_rest_api_overwrite' ), 50, 1 );

			add_action( 'wp_print_footer_scripts', array( $this, 'add_leads_shortcode_to_content' ), 11 );

			// We are adding this because conversion thrive boxes need to work on landing pages also.
			add_action( 'tve_landing_page_content', array( $this, 'add_tcm_triggers' ), 11 );

			add_filter( 'tve_leads_ajax_load_forms', array( $this, 'add_thrivebox_flat_css' ) );

			add_filter( 'tva_term_extra_content', array( $this, 'add_tcm_triggers' ) );

			add_filter( 'get_comment_author_url', array( $this, 'comment_author_url' ), 10, 3 );

			$this->hide_date();
		}
	}

	/**
	 * Add triggers for the ThriveBoxes set in conversion options.
	 *
	 * @param string $content The content.
	 *
	 * @return string
	 */
	public function add_tcm_triggers( $content ) {

		if ( empty( $content ) || trim( $content ) === '<div class="tcb_flag" style="display: none"></div>' ) {
			return '';
		}

		$conversion_settings = tcms()->tcm_get_setting_by_name( 'tcm_conversion' );

		$first_trigger  = sprintf( '<span class="tve-leads-two-step-trigger tl-2step-trigger-%d"></span>', $conversion_settings['tcm_thrivebox']['first_time']['thrivebox_id'] );
		$second_trigger = sprintf( '<span class="tve-leads-two-step-trigger tl-2step-trigger-%d"></span>', $conversion_settings['tcm_thrivebox']['second_time']['thrivebox_id'] );

		return $content . $first_trigger . $second_trigger;
	}

	/**
	 * Include thrive_flat css for ThriveBoxes.
	 *
	 * @param WP_Ajax_Response $response response.
	 *
	 * @return WP_Ajax_Response $response response.
	 */
	public function add_thrivebox_flat_css( $response ) {
		if ( ! function_exists( 'tve_get_style_families' ) ) {
			/**
			 * If this function does not exist, we can return safely the response cause Thrive Flat is not necessary since this function is called during Leads Lazy Load
			 */
			return $response;
		}

		/* flat is the default style for Thrive Leads */
		$tve_style_families = tve_get_style_families();
		$style_family       = 'Flat';

		if ( empty( $response['tve_flat_included'] ) ) {
			$response['res']['css']['tve_leads_flat'] = $tve_style_families[ $style_family ];
		}

		return $response;
	}

	/* overwrites the Disable REST API plugin's request handling so it lets through TCM requests */
	public function allow_rest_api_overwrite( $is_user_logged_in ) {
		if ( strpos( $GLOBALS['wp']->query_vars['rest_route'], 'tcm/' ) ) {
			return true;
		}

		return $is_user_logged_in;
	}

	/**
	 * Enqueue scripts and styles
	 */
	public function enqueue_scripts() {
		$js_suffix     = defined( 'TVE_DEBUG' ) && TVE_DEBUG ? '.js' : '.min.js';
		$show_comments = $this->tcm_show_comments();

		/**
		 * Allow to display comments on other pages beside posts
		 *
		 * @param bool $show_comments default value from thrive comments logic
		 */
		$show_comments = apply_filters( 'tcm_show_comments', $show_comments );

		if ( $show_comments ) {
			tcm()->tcm_enqueue_style( 'tcm-front-styles-css', tcm()->plugin_url( '/assets/css/styles.css' ) );

			tcm()->tcm_enqueue_script( 'tcm-frontend-js', tcm()->plugin_url( '/assets/js/frontend.min.js' ), array(
				'jquery',
				'backbone',
			), false, true );

			tcm()->tcm_enqueue_script( 'libs-frontend', tcm()->plugin_url( 'assets/js/libs-frontend.min.js' ), array( 'jquery' ) );

			wp_localize_script( 'tcm-frontend-js', 'ThriveComments', $this->tcm_get_localization_parameters() );

			// Enqueue all scripts and styles needed for ThriveBoxes.
			if ( defined( 'TVE_LEADS_URL' ) ) {
				if ( ! wp_script_is( 'tve_frontend' ) ) {
					if ( class_exists( '\TCB\Lightspeed\JS', false ) ) {
						JS::get_instance( get_the_ID() )->enqueue_scripts();
					} else {
						tcm()->tcm_enqueue_script( 'tve_frontend', tve_editor_js() . '/frontend' . $js_suffix, array( 'jquery' ), false, true );
					}

					$frontend_options = array(
						'is_editor_page' => is_editor_page(),
						'ajaxurl'        => admin_url( 'admin-ajax.php' ),
					);
					wp_localize_script( 'tve_frontend', 'tve_frontend_options', $frontend_options );
				}

				if ( ! wp_script_is( 'tve_leads_frontend' ) ) {
					tcm()->tcm_enqueue_script( 'tve_leads_frontend', TVE_LEADS_URL . 'js/frontend' . $js_suffix, array( 'jquery' ), false, true );
				}
			}

			if ( ! is_user_logged_in() ) {
				add_action( 'wp_footer', 'wp_auth_check_html', 5 );
				wp_enqueue_style( 'wp-auth-check' );
			}

			if ( tcms()->tcm_get_setting_by_name( 'tcm_vote_type' ) !== 'no_vote' ) {
				//load recaptcha
				$recaptcha = tcm()->recaptcha();
				if ( $recaptcha ) {
					$site_key = $recaptcha->get_credentials()['site_key'];
					wp_enqueue_script( 'tcm_recaptcha_challenge', 'https://www.google.com/recaptcha/api.js?render=' . $site_key );
				}

			}
		}
	}


	/**
	 * Check if we can show the comments on the post or page
	 *
	 * @return bool
	 */
	public function tcm_show_comments() {

		// If we have a tc element on a landing page than show comments only if the settings from TC allow that.
		if ( function_exists( 'tve_post_is_landing_page' ) && $landing_page_id = tve_post_is_landing_page( get_the_ID() ) ) {
			$post                  = tcmc()->tc_get_post();
			$tc_comments_closed    = tcms()->tcm_get_setting_by_name( 'activate_comments' );
			$post_content          = get_post_meta( $post->ID, 'tve_updated_post_' . $landing_page_id, true );
			$sections              = get_post_meta( $post->ID, 'sections', true );
			$has_comment_container = strpos( $post_content . json_encode( $sections ), 'thrive-comments' );

			return is_editor_page() || ( $tc_comments_closed && $has_comment_container !== false && ! tcms()->close_comments( $post->ID ) );
		}

		$comment_count = get_comment_count( get_the_ID() );
		// If comments are closed, display the comments but show that they are closed.
		$show_comments = is_singular() && ( comments_open() || $comment_count['all'] );

		/**
		 * Filter if the comments needs to show on some other pages
		 *
		 * @param bool $show_comments - if the comments are shown based on the thrive comments criteria
		 */
		return apply_filters( 'tcm_show_comments', $show_comments );
	}

	/**
	 * Add frontend comments placeholder
	 *
	 * @return string
	 */
	public function render_comments() {
		return tcm()->plugin_path( 'includes/frontend/views/comments.php' );
	}

	/**
	 *  Create and register frontend rest routes
	 */
	public function tcm_create_frontend_rest_routes() {
		$endpoints = array(
			'TCM_REST_Comments_Controller',
		);

		foreach ( $endpoints as $e ) {
			$controller = new $e();
			$controller->register_routes();
		}
	}

	/**
	 * Get params to be used in javascript
	 *
	 * @return array
	 */
	public function tcm_get_localization_parameters() {
		$post    = $this->post_for_localization();
		$post_id = empty( $post['ID'] ) ? 0 : $post['ID'];

		$recaptcha = tcm()->recaptcha();

		$localization = array(
			'current_user'               => tcmh()->tcm_get_current_user(),
			'translations'               => include tcm()->plugin_path( 'includes/i18n.php' ),
			'nonce'                      => $this->create_nonce(),
			'routes'                     => array(
				'comments'               => tcm()->tcm_get_route_url( 'comments' ),
				'gravatar'               => tcm()->tcm_get_route_url( 'comments' ) . '/gravatar',
				'live_update'            => tcm()->tcm_get_route_url( 'comments' ) . '/live_update',
				'update_post_subscriber' => tcm()->tcm_get_route_url( 'comments' ) . '/update_post_subscriber',
				'generate_nonce'         => admin_url( 'admin-ajax.php' ),
			),
			'post'                       => $post,
			'related_posts'              => tcmc()->get_related_posts( Thrive_Comments_Constants::TCM_NO_RELATED_POSTS, $args = array() ),
			'const'                      => array(
				'toast_timeout' => Thrive_Comments_Constants::TCM_TOAST_TIMEOUT, // Not sure if we really need this.
				'wp_content'    => rtrim( WP_CONTENT_URL, '/' ) . '/',
				'ajax_dash'     => array( Thrive_Comments_Constants::TCM_AJAX_DASH ),
				'site_url'      => get_site_url(),
				'post_url'      => apply_filters( 'tcm_post_url', get_permalink() ),
				'moderation'    => array(
					'approve'              => Thrive_Comments_Constants::TCM_APPROVE,
					'unapprove'            => Thrive_Comments_Constants::TCM_UNAPPROVE,
					'spam'                 => Thrive_Comments_Constants::TCM_SPAM,
					'unspam'               => Thrive_Comments_Constants::TCM_UNSPAM,
					'trash'                => Thrive_Comments_Constants::TCM_TRASH,
					'untrash'              => Thrive_Comments_Constants::TCM_UNTRASH,
					'unreplied'            => Thrive_Comments_Constants::TCM_UNREPLIED,
					'tcm_delegate'         => Thrive_Comments_Constants::TCM_DELEGATE,
					'tcm_featured'         => Thrive_Comments_Constants::TCM_FEATURED,
					'tcm_keyboard_tooltip' => Thrive_Comments_Constants::TCM_KEYBOARD_TOOLTIP,
					'featured'             => Thrive_Comments_Constants::TCM_FEATURE_VALUE,
					'not_featured'         => Thrive_Comments_Constants::TCM_NOT_FEATURE_VALUE,
				),
			),
			'settings'                   => tcms()->tcm_get_settings(),
			'close_comments'             => tcms()->close_comments( $post_id ) || ! $this->tcm_show_comments(),
			'sorting'                    => tcms()->get_comment_sorting(),
			'tcm_customize_labels'       => tcms()->tcm_get_setting_by_name( Thrive_Comments_Constants::TCM_LABELS_KEY ),
			'tcm_social_apis'            => array(
				'facebook' => Thrive_Dash_List_Manager::credentials( 'facebook' ),
				'google'   => Thrive_Dash_List_Manager::credentials( 'google' ),
			),
			'email_services'             => tcamh()->get_email_services(),
			'tcm_accent_color'           => tcms()->tcm_get_setting_by_name( Thrive_Comments_Constants::TCM_ACCENT_COLOR ),
			'has_plugin_cache'           => tve_dash_detect_cache_plugin(),
			'default_author_picture_url' => tcmh()->get_picture_url(),
			'recaptcha_enabled'          => ! empty( $recaptcha ),
			'recaptcha'                  => [
				'site_key' => $recaptcha ? $recaptcha->get_credentials()['site_key'] : '',
			],
		);

		/**
		 * Filter for adding extra params for comments localization in fronted
		 *
		 * @param array $localization the already built localization by TC
		 */
		return apply_filters( 'tcm_comments_localization', $localization );
	}

	/**
	 * Unset some variable from the post, that we don't need in the frontend
	 *
	 * @return mixed|void|null
	 */
	public function post_for_localization() {
		$not_allowed_fields = array(
			'post_content',
			'post_password',
			'post_content_filtered',
			'post_data',
			'post_date_gmt',
			'ping_status',
			'post_modified',
			'post_modified_gmt',
			'page_template',
		);
		$post               = tcmc()->tc_get_post();
		if ( $post ) {
			$post = $post->to_array();

			foreach ( $not_allowed_fields as $not_allowed_field ) {
				if ( isset( $post[ $not_allowed_field ] ) ) {
					unset( $post[ $not_allowed_field ] );
				}
			}
		}

		return $post;
	}

	/**
	 * Load all backbone templates for frontend
	 */
	public function tcm_front_backbone_templates() {
		if ( tcm()->is_active() && $this->tcm_show_comments() ) {
			$templates = tve_dash_get_backbone_templates( tcm()->plugin_path( 'includes/frontend/views/templates' ), 'templates' );
			tve_dash_output_backbone_templates( $templates );
		}

	}

	/**
	 * Initialize default labels if the options does not exists already in the db.
	 *
	 * @return bool
	 */
	public function tcm_default_labels() {

		$default_labels = Thrive_Comments_Constants::$tcm_default_labels;
		$saved_labels   = get_option( Thrive_Comments_Constants::TCM_LABELS_KEY );
		$update         = 0;

		foreach ( $default_labels as $key => $value ) {
			if ( ! isset( $saved_labels[ $key ] ) || empty( $saved_labels[ $key ]['text'] ) ) {
				$saved_labels[ $key ] = array(
					'default' => __( $value ),
					'text'    => __( $value ),
				);
				$update               = 1;
			}
		}

		if ( $update ) {
			update_option( Thrive_Comments_Constants::TCM_LABELS_KEY, $saved_labels );
		}

		return true;
	}

	/**
	 * Call used to change the nonce when the user logs in with ajax from frontend.
	 */
	public function refresh_nonce() {
		$nonce        = wp_create_nonce( 'wp_rest' );
		$current_user = tcmh()->tcm_get_current_user();

		echo json_encode( array( 'nonce' => $nonce, 'current_user' => $current_user ) );
		die();
	}

	/**
	 * Actions taken after the user logs in with ajax.
	 *
	 * @param $id
	 */
	public function after_ajax_login( $id ) {
		global $interim_login;
		if ( $interim_login ) {
			tcm()->tcm_enqueue_script( 'tcm-login-js', tcm()->plugin_url( '/assets/js/after-ajax-login.js' ), array(
				'jquery',
			), false, true );
		}
	}

	/**
	 * Insert shortcode for thrive boxes
	 *
	 * @return string
	 */
	public function add_leads_shortcode_to_content() {
		if ( ! defined( 'TVE_LEADS_URL' ) ) {
			return '';
		}
		$thrive_boxes_content = tcmc()->get_thrive_boxes_shortcodes();//take in consideration the case when the comments are on a landing page
		$leads_content        = ( function_exists( 'tve_post_is_landing_page' ) && tve_post_is_landing_page( get_the_ID() ) ) ? do_shortcode( $thrive_boxes_content ) : $thrive_boxes_content;

		echo $leads_content;
	}

	/* adds css for comment colors inside the header */
	public function accent_color_css( $landing_page_id ) {
		include tcm()->plugin_path( 'includes/frontend/views/accent-color-styles.php' );

		return $landing_page_id;
	}

	/* adds the hidden svg file with the icons from the frontend pages to the header */
	public function add_frontend_svg_file() {
		tcmh()->include_svg_file( 'frontend-page-icons.svg' );
	}

	/**
	 * Change comments link in widget
	 *
	 * @param string $link Comment link.
	 *
	 * @return string $link
	 */
	public function tcm_change_comments_link( $link ) {
		$link                          = preg_replace( '/(\/comment-page-)([0-9]+)/', '', $link );
		$link                          = str_replace( '#comment', '#comments', $link );
		$link[ strrpos( $link, '-' ) ] = '/';

		return $link;
	}

	/**
	 * If the user logs in with an admin account and he is logged in already with a social account, we log him out automatically from his social account
	 */
	public function tcm_after_login() {

		if ( isset( $_COOKIE['social-login'] ) ) {

			unset( $_COOKIE['social-login'] );
			setcookie( 'social-login', '', time() - 3600 );
		}
	}

	/**
	 * Create rest nonce for ajax requests
	 *
	 * @return mixed|void
	 */
	public function create_nonce() {
		/**
		 * Add filter for compatibility with woocommerce and remove it afterwards
		 * They are changing the nonce user logged out uid and when the nonce is created is different. We need it to be the same.
		 */
		add_filter( 'nonce_user_logged_out', array( $this, 'nonce_user_logged_out' ), 50 );

		$nonce = wp_create_nonce( 'wp_rest' );

		remove_filter( 'nonce_user_logged_out', array( $this, 'nonce_user_logged_out' ), 50 );

		return apply_filters( 'tc_rest_nonce', $nonce );
	}

	/**
	 * When a user is logged out, ensure that the uid is 0
	 *
	 * @param int $uid User ID.
	 *
	 * @return string
	 */
	public function nonce_user_logged_out( $uid ) {
		return 0;
	}

	/**
	 * Return data with the help of the ajax from dash
	 *
	 * @param $data
	 *
	 * @return mixed
	 */
	public function dash_ajax_data( $data = array() ) {
		$data['secret'] = tve_dash_generate_secret();

		/* If we have a caching plugin active we need to generate the nonce at each request */
		if ( tve_dash_detect_cache_plugin() ) {
			$data['nonce'] = $this->create_nonce();
		}

		return $data;
	}

	/**
	 * If the comment author url doesn't exists or it's empty, we will show the website url from the user profile
	 *
	 * @param string     $url
	 * @param string     $id
	 * @param WP_Comment $comment
	 *
	 * @return string
	 */
	public function comment_author_url( $url, $id, $comment ) {
		if ( empty( $url ) ) {
			$user_data = get_userdata( $comment->user_id );
			$url       = empty( $user_data ) ? '' : esc_url( $user_data->user_url, array( 'http', 'https' ) );
		}

		return $url;
	}

	/**
	 * Exclude the default avatar URL from optimole processing
	 *
	 * @param boolean $skip
	 * @param string  $url
	 *
	 * @return boolean
	 */
	public function optimole_skip_url( $skip, $url ) {
		$default = basename( tcmh()->get_picture_url() );

		if ( strpos( $url, $default ) !== false ) {
			$skip = true;
		}

		return $skip;
	}

	/**
	 * Used instead of is_editor_page_raw because that function does not exist yet.
	 * @return bool
	 */
	public static function is_architect_editor_page() {
		return ! empty( $_GET['tve'] );
	}

	/**
	 * We need to change some of the default settings because of a wp update where they changed the names of some comments options
	 *
	 * @param array $settings
	 *
	 * @return mixed
	 */
	public function change_default_settings( $settings ) {
		global $wp_version;

		if ( version_compare( $wp_version, '5.5-beta', '>=' ) ) {
			$settings['comment_previously_approved'] = $settings['comment_whitelist'];
			unset( $settings['comment_whitelist'] );

			$settings['disallowed_keys'] = $settings['blacklist_keys'];
			unset( $settings['blacklist_keys'] );
		}

		return $settings;
	}

	/**
	 * When TC is active change theme comments layout
	 *
	 * @param string $content
	 *
	 * @param array  $data
	 *
	 * @return string
	 */
	public function change_theme_comments( $content, $data ) {
		/* Only the styled scrollbar attribute will be kept in the frontend */
		$attr = isset( $data['styled-scrollbar'] ) ? 'data-styled-scrollbar=' . $data['styled-scrollbar'] : '';
		if ( ! empty( $data['id'] ) ) {
			$attr .= ' id="' . $data['id'] . '"';
		}

		return '<div class="thrv_wrapper thrv-comments"' . $attr . ' >' . $content . '</div>';
	}
}

return new Thrive_Comments_Front();
