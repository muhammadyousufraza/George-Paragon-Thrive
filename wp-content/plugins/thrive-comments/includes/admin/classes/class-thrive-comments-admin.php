<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-comments
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden.
}

/**
 * Class Thrive_Comments_Admin
 */
class Thrive_Comments_Admin {

	/**
	 * Constructor for Thrive_Comments_Admin
	 * Add all admin hooks
	 */
	public function __construct() {

		add_action( 'init', array( $this, 'init' ) );
		add_action( 'admin_init', array( $this, 'remove_scripts_from_iframe' ), PHP_INT_MAX );

		/**
		 * Add Thrive Comments To Dashboard
		 */
		add_filter( 'tve_dash_installed_products', array( $this, 'add_to_dashboard_list' ) );
		add_filter( 'tve_dash_features', array( $this, 'tcm_add_features' ) );
		add_filter( 'tve_filter_api_types', array( $this, 'tcm_api_types' ) );
		add_filter( 'tve_dash_admin_product_menu', array( $this, 'add_to_dashboard_menu' ) );
		add_filter( 'tve_dash_admin_product_menu', array( $this, 'add_to_dashboard_sub_menu' ) );
		add_action( 'admin_menu', array( $this, 'remove_moderation_menu' ), 999 );

		/* adds the svg file containing all the svg icons for the admin pages */
		add_action( 'admin_head', array( $this, 'add_admin_svg_file' ) );

		/**
		 * Add hook for comment approve callback function
		 */
		add_action( 'transition_comment_status', array( $this, 'tcm_approve_comment_callback' ), 10, 3 );
		add_action( 'transition_comment_status', array( $this, 'tcm_notifications' ), 10, 3 );
		add_action( 'delete_attachment', array( $this, 'tcm_delete_attachment' ) );
		/**
		 * Add admin scripts and styles
		 */

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'rest_api_init', array( $this, 'tcm_create_admin_rest_routes' ) );

		add_filter( 'tve_dash_metrics_should_enqueue', array( $this, 'tcm_should_enqueue_metrics' ), 10, 2 );
	}

	/**
	 * Init admin and include files
	 */
	public function init() {

		if ( ! tcm()->license_activated() ) {
			tcah()->tcm_update_option( Thrive_Comments_Constants::TCM_PLUGIN_READY_OPTION, 0 );
			add_action( 'admin_notices', array( $this, 'tcm_admin_notice_inactive_license' ) );
		} else {
			add_action( 'admin_notices', array( $this, 'tcm_admin_notice_plugin_ready' ) );
		}

	}

	/**
	 * Dont enqueue scripts if the plugin is not licensed
	 *
	 * @param $should_enqueue
	 *
	 * @return false|mixed
	 */
	public function tcm_should_enqueue_metrics( $should_enqueue ) {
		$screen = tve_get_current_screen_key();
		if ( $screen === 'thrive-dashboard_page_tcm_admin_dashboard' && ! tcm()->license_activated() ) {
			$should_enqueue = false;
		}

		return $should_enqueue;
	}

	/**
	 * Show notice if the plugin is activated but the license it's not
	 */
	public function tcm_admin_notice_inactive_license() {

		$screen = get_current_screen();
		if ( 'thrive-dashboard_page_tve_dash_license_manager_section' === $screen->base ) {
			return;
		}

		$html = '<div class="is-dismissible notice notice-warning"><p>%s</p></div>';
		$text = sprintf( __( 'The Thrive Comments plugin has been activated. To start using its features, please', Thrive_Comments_Constants::T ) );

		if ( $screen ) {
			$text .= ' <a href="' . admin_url( 'admin.php?page=tve_dash_license_manager_section' ) . '">' . __( 'activate the license here', Thrive_Comments_Constants::T ) . '</a>';
		}

		echo sprintf( $html, $text );
	}

	/**
	 * Show notice after the plugin is ready to use
	 */
	public function tcm_admin_notice_plugin_ready() {

		if ( tcah()->tcm_get_option( Thrive_Comments_Constants::TCM_PLUGIN_READY_OPTION ) ) {
			return;
		}

		$screen = get_current_screen();

		if ( Thrive_Comments_Constants::TCM_ADMIN_DASHBOARD_HOOK === $screen->base ) {
			tcah()->tcm_update_option( Thrive_Comments_Constants::TCM_PLUGIN_READY_OPTION, 1 );

			return;
		}

		$html = '<div class="is-dismissible notice notice-success"><p>%s</p></div>';
		$text = sprintf( __( 'The Thrive Comments plugin is ready to use. To get started, ', Thrive_Comments_Constants::T ) );
		$text .= ' <a href="' . admin_url( 'admin.php?page=tcm_admin_dashboard' ) . '">' . __( 'visit the settings page here', Thrive_Comments_Constants::T ) . '</a>';


		echo sprintf( $html, $text );
	}


	/**
	 * Include Thrive Comments to Thrive Dashboard installed plugins
	 *
	 * @param array $items All thrive products.
	 *
	 * @return array
	 */
	public function add_to_dashboard_list( $items ) {


		$items[] = new Tcm_Product();

		return $items;
	}

	/**
	 * Add Comments Moderation to the Comments menu
	 *
	 * @param array $menus current products.
	 *
	 * @return array
	 */
	public function add_to_dashboard_menu( $menus = array() ) {
		$menus['tcm'] = array(
			'parent_slug' => 'tve_dash_section',
			'page_title'  => __( 'Thrive Comments', Thrive_Comments_Constants::T ),
			'menu_title'  => __( 'Thrive Comments', Thrive_Comments_Constants::T ),
			'capability'  => TCM_Product::cap(),
			'menu_slug'   => 'tcm_admin_dashboard',
			'function'    => array( $this, 'tcm_admin_dashboard' ),
		);


		return $menus;
	}

	/**
	 * Add Thrive Comments to the Dashboard menu
	 *
	 * @param array $menus current products.
	 *
	 * @return array
	 */
	public function add_to_dashboard_sub_menu( $menus = array() ) {


		$menus['tcm_sub_menu'] = array(
			'parent_slug' => 'edit-comments.php',
			'page_title'  => __( 'Thrive Comments Moderation', Thrive_Comments_Constants::T ),
			'menu_title'  => __( 'Thrive Comments Moderation', Thrive_Comments_Constants::T ),
			'capability'  => 'read',
			'menu_slug'   => 'tcm_comment_moderation',
			'function'    => array( $this, 'tcm_comment_moderation' ),
		);

		return $menus;
	}

	/**
	 * Display Comments Moderation - the moderation submenu
	 */
	public function tcm_comment_moderation() {
		tve_dash_enqueue();
		include tcm()->plugin_path( 'includes/admin/views/comments-moderation.php' );
	}

	/**
	 * Remove Comments Moderation subMenu if the user does not have credentials
	 */
	public function remove_moderation_menu() {
		if ( ! tcah()->can_see_moderation() || ! tcm()->license_activated() ) {
			remove_submenu_page( 'edit-comments.php', 'tcm_comment_moderation' );
		}
	}

	/**
	 * Display Thrive Comments Dashboard - the main plugin page
	 */
	function tcm_admin_dashboard() {

		if ( ! tcm()->license_activated() ) {
			return include tcm()->plugin_path( '/includes/admin/views/templates/license-inactive.phtml' );
		}

		include tcm()->plugin_path( 'includes/admin/views/dashboard.php' );
	}

	/**
	 * Load all backbone templates for admin
	 */
	public function tcm_admin_backbone_templates() {
		$templates = tve_dash_get_backbone_templates( tcm()->plugin_path( 'includes/admin/views/templates' ), 'templates' );
		tve_dash_output_backbone_templates( $templates );
	}

	/**
	 * Remove scripts from iframe
	 */
	public function remove_scripts_from_iframe() {

		if ( $this->is_inner_frame() ) {

			remove_all_actions( 'admin_print_styles' );
			remove_all_actions( 'admin_print_scripts' );
			remove_all_actions( 'wp_head' );

			remove_all_actions( 'wp_enqueue_scripts' );
			remove_all_actions( 'wp_print_scripts' );
			remove_all_actions( 'wp_print_footer_scripts' );
			remove_all_actions( 'wp_footer' );
		}
	}

	/**
	 * Enqueue Scripts
	 *
	 * @param
	 */
	public function enqueue_scripts( $hook ) {

		/**
		 * Accepted hooks for admin pages
		 */
		$accepted_hooks = apply_filters( 'tcm_accepted_admin_pages', array(
			Thrive_Comments_Constants::TCM_ADMIN_DASHBOARD_HOOK,
			Thrive_Comments_Constants::TCM_ADMIN_MODERATION_HOOK,
		) );

		if ( ! in_array( $hook, $accepted_hooks ) ) {
			return;
		}

		tve_dash_enqueue();
		tcm()->tcm_enqueue_style( 'tcm-front-styles-css', tcm()->plugin_url( '/assets/css/styles.css' ) );
		tcm()->tcm_enqueue_style( 'libs-admin-css', tcm()->plugin_url( '/assets/css/libs-admin.css' ) );

		wp_enqueue_style( 'wp-color-picker' );

		wp_enqueue_media();

		wp_enqueue_script( 'jquery-ui-autocomplete' );
		tcm()->tcm_enqueue_script( 'libs-admin', tcm()->plugin_url( 'assets/js/libs-admin.min.js' ), array( 'jquery' ), false, true );
		tcm()->tcm_enqueue_script( 'iris', admin_url( 'js/iris.min.js' ), array(
			'jquery-ui-draggable',
			'jquery-ui-slider',
			'jquery-touch-punch',
		), false, true );


		switch ( $hook ) {
			case Thrive_Comments_Constants::TCM_ADMIN_DASHBOARD_HOOK:
				tcm()->tcm_enqueue_style( 'tcm-front-styles-css', tcm()->plugin_url( '/assets/css/styles.css' ) );

				if ( $this->is_inner_frame() ) {
					add_action('tve_dash_metrics_should_enqueue', '__return_false');
					tcm()->tcm_enqueue_style( 'tcm-iframe-styles-css', tcm()->plugin_url( '/assets/css/iframe-styles.css' ) );
					wp_dequeue_script( 'wp-auth-check' );
				}

				tcm()->tcm_enqueue_script( 'tcm-admin-js', tcm()->plugin_url( '/assets/js/admin.min.js' ), array(
					'jquery',
					'backbone',
				), false, true );

				wp_localize_script( 'tcm-admin-js', 'ThriveComments', $this->tcm_get_localization_parameters() );
				break;
			case Thrive_Comments_Constants::TCM_ADMIN_MODERATION_HOOK:
				tcm()->tcm_enqueue_style( 'tcm-moderation-styles-css', tcm()->plugin_url( '/assets/css/moderation.css' ) );


				tcm()->tcm_enqueue_script( 'tcm-moderation-js', tcm()->plugin_url( '/assets/js/moderation.min.js' ), array(
					'jquery',
					'backbone',
				), false, true );

				$ovation_active = is_plugin_active( 'thrive-ovation/thrive-ovation.php' );
				if ( $ovation_active ) {
					tcm()->tcm_enqueue_script( 'tcm-ovation-js', plugin_dir_url( 'thrive-ovation/admin/js/' ) . 'js/comments.js', array(
						'jquery',
						'backbone',
					), false, true );

					if ( function_exists( 'tvo_get_localization_parameters' ) ) {
						wp_localize_script( 'tcm-ovation-js', 'ThriveOvation', tvo_get_localization_parameters() );
					}
				}
				wp_localize_script( 'tcm-moderation-js', 'ThriveComments', $this->get_moderation_localization() );
				break;
			default:
				break;
		}

		wp_enqueue_script( 'jquery-ui-autocomplete' );

		/**
		 * Output the main templates for backbone views used in dashboard.
		 */
		add_action( 'admin_print_scripts', array( $this, 'tcm_admin_backbone_templates' ), 5 );

	}

	/**
	 * Get path url to badges default svg and return an array of badges names.
	 */
	public function get_badges_default_images() {
		//result arary that contain both badges image( default and custom )
		$badges_model = array();
		//get default badges images url and ids
		$svg_url              = tcm()->plugin_url( 'assets/images/default_badges' ) . '/all_badges.svg';
		$default_badge_images = tcah()->get_svg_symbols_ids();
		//set default badges images
		$id_count = 0;
		foreach ( $default_badge_images as $badge ) {
			$badge_model       = new stdClass();
			$badge_model->id   = $id_count;
			$badge_model->name = $badge;
			$badge_model->url  = $svg_url . '#' . $badge;
			$id_count ++;
			array_push( $badges_model, $badge_model );
		}

		//set custom images to result array
		$custom_badge_images = tcms()->tcm_get_setting_by_name( 'tcm_badges_custom_images' );
		if ( ! empty( $custom_badge_images ) ) {
			foreach ( $custom_badge_images as $key => $badge ) {
				$badge_model       = new stdClass();
				$badge_model->id   = $id_count;
				$path_info         = pathinfo( $badge );
				$badge_model->name = $path_info['basename'];
				$badge_model->url  = $badge;
				$id_count ++;

				if ( tcmh()->picture_exists( $badge ) ) {
					array_push( $badges_model, $badge_model );
				} else {
					/* Delete the image from options to not look for it again */
					unset( $custom_badge_images[ $key ] );

					$images = array_values( $custom_badge_images );
					tcah()->tcm_update_option( 'tcm_badges_custom_images', $images );
				}
			}
		}

		return $badges_model;
	}

	/**
	 * Get params to be used in javascript for comments dashboard
	 *
	 * @return array
	 */
	public function tcm_get_localization_parameters() {
		$fb_credentials     = Thrive_Dash_List_Manager::credentials( 'facebook' );
		$google_credentials = Thrive_Dash_List_Manager::credentials( 'google' );

		return array(
			'translations'               => include tcm()->plugin_path( 'includes/i18n.php' ),
			'nonce'                      => wp_create_nonce( 'wp_rest' ),
			'routes'                     => array(
				'settings'  => tcm()->tcm_get_route_url( 'settings' ),
				'reporting' => tcm()->tcm_get_route_url( 'settings' ) . '/reporting',
			),
			'post_id'                    => get_the_ID(),
			'default_avatar_url'         => tcm()->plugin_url( 'assets/images/' . Thrive_Comments_Constants::TCM_DEFAULT_PICTURE ),
			'const'                      => array(
				'default_design'    => Thrive_Comments_Constants::TCM_DESIGN_DEFAULT,
				'light_design'      => Thrive_Comments_Constants::TCM_DESIGN_LIGHT,
				'dark_design'       => Thrive_Comments_Constants::TCM_DESIGN_DARK,
				'absolute_date'     => Thrive_Comments_Constants::TCM_ABSOLUTE_DATE,
				'relative_date'     => Thrive_Comments_Constants::TCM_RELATIVE_DATE,
				'hide_date'         => Thrive_Comments_Constants::TCM_HIDE_DATE,
				'tcm_live_update'   => Thrive_Comments_Constants::TCM_LIVE_UPDATE,
				'tcm_thrivebox'     => Thrive_Comments_Constants::TCM_THRIVEBOX,
				'tcm_redirect'      => Thrive_Comments_Constants::TCM_REDIRECT,
				'tcm_related_posts' => Thrive_Comments_Constants::TCM_RELATED_POSTS,
				'tcm_social_share'  => Thrive_Comments_Constants::TCM_SOCIAL_SHARE,
				'moderation'        => array(
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
				),
				'wp_content'        => rtrim( WP_CONTENT_URL, '/' ) . '/',
			),
			'tcm_customize_labels'       => tcms()->tcm_get_setting_by_name( Thrive_Comments_Constants::TCM_LABELS_KEY ),
			'tcm_labels_key'             => Thrive_Comments_Constants::TCM_LABELS_KEY,
			'tcm_sync_settings'          => Thrive_Comments_Constants::$_sync_settings,
			'leads_active'               => defined( 'TVE_LEADS_URL' ),
			'thrive_boxes'               => tcmc()->get_thrive_boxes(),
			'thrive_roles'               => tcah()->get_all_roles(),
			'current_user'               => tcmh()->tcm_get_current_user(),
			'badges_default'             => $this->get_badges_default_images(),
			'badges_enable'              => tcms()->tcm_get_setting_by_name( 'tcm_badges_option' ),
			'tcm_social_apis'            => array(
				array(
					'name'   => 'facebook',
					'status' => empty( $fb_credentials ) ? 'unset' : 'set',
				),
				array(
					'name'   => 'google',
					'status' => empty( $google_credentials ) ? 'unset' : 'set',
				),
			),
			'email_services'             => tcamh()->get_email_services(),
			'email_apis'                 => tcamh()->get_email_apis(),
			'tcm_notification_labels'    => tcah()->tcm_default_notification_labels(),
			'tcm_hide_live_preview'      => tcah()->tcm_get_option( 'tcm_hide_live_preview' ),
			'default_author_picture_url' => tcmh()->get_picture_url(),
			'license'  => [
				'exp'           => ! TD_TTW_User_Licenses::get_instance()->has_active_license( 'tcm' ),
				'gp'            => TD_TTW_User_Licenses::get_instance()->is_in_grace_period( 'tcm' ),
				'show_lightbox' => TD_TTW_User_Licenses::get_instance()->show_gp_lightbox( 'tcm' ),
				'grace_time'    => (string)TD_TTW_User_Licenses::get_instance()->get_grace_period_left( 'tcm' ),
				'link'          => tvd_get_individual_plugin_license_link( 'tcm' )
			]
		);
	}

	/**
	 * Get params to be used in javascript for comments dashboard and moderation
	 *
	 * @return array
	 */
	public function get_moderation_localization() {

		$admin_localization = $this->tcm_get_localization_parameters();
		$mod_localization   = array(
			'moderation_routes'                         => array(
				'comments'       => tcm()->tcm_get_route_url( 'moderation' ),
				'bulk_actions'   => tcm()->tcm_get_route_url( 'moderation' ) . '/bulk_actions',
				'get_moderators' => tcm()->tcm_get_route_url( 'moderation' ) . '/moderators',
				'gravatar'       => tcm()->tcm_get_route_url( 'moderation' ) . '/gravatar',
			),
			'moderation_keyboard'                       => Thrive_Comments_Constants::$_moderation_keyboard,
			'badges_default'                            => $this->get_badges_default_images(),
			'badges_enable'                             => tcms()->tcm_get_setting_by_name( 'tcm_badges_option' ),
			'tcm_display_keyboard_notification_tooltip' => tcah()->tcm_get_option( 'tcm_display_keyboard_notification_tooltip' ),
		);

		return array_merge( $admin_localization, $mod_localization );
	}

	/**
	 * TCM initialize admin rest routes
	 */
	public function tcm_create_admin_rest_routes() {
		$endpoints = array(
			'TCM_REST_Settings_Controller',
			'TCM_REST_Conversion_Settings_Controller',
			'TCM_REST_Moderation_Controller',
		);
		foreach ( $endpoints as $e ) {
			$controller = new $e();
			$controller->register_routes();
		}
	}

	/**
	 * Tcm approve/unapprove comment callback to update user badges counter
	 *
	 * @param $new_status
	 * @param $old_status
	 * @param $comment
	 */
	public function tcm_approve_comment_callback( $new_status, $old_status, $comment ) {
		if ( $old_status != $new_status && $new_status !== 'delete' ) {
			$author_email          = $comment->comment_author_email;
			$user_tcm_achievements = tcmdb()->get_log( array( 'achievement' ), array( 'email' => $author_email ) );
			$is_insert             = false;
			if ( null === $user_tcm_achievements ) {
				$user_tcm_achievements = Thrive_Comments_Constants::$_default_achievements;
				$is_insert             = true;
			}
			//check if comment is a reply or a stand alone comment
			$badge_type = ( '0' == $comment->comment_parent ) ? 'approved_comments' : 'approved_replies';

			if ( 'approved' == $new_status ) {
				$user_tcm_achievements[ $badge_type ] += 1;
			} else {
				if ( $user_tcm_achievements[ $badge_type ] > 0 ) {
					$user_tcm_achievements[ $badge_type ] -= 1;
				}
			}

			if ( $is_insert ) {
				tcmdb()->insert_log( array( 'email' => $author_email, 'achievement' => json_encode( $user_tcm_achievements ) ) );
			} else {
				tcmdb()->update_log(
					array(
						'achievement' => json_encode( $user_tcm_achievements ),
					),
					array(
						'email' => $author_email,
					)
				);
			}
		}
	}

	/**
	 * Send notification to the subscribers of the post when a comment gets approved
	 *
	 * @param string     $new_status new comment status
	 * @param string     $old_status old comment status
	 * @param WP_Comment $comment    comment
	 */
	public function tcm_notifications( $new_status, $old_status, $comment ) {
		if ( $new_status === 'approved' ) {
			tcmh()->tcm_send_notification( $comment );
		}
	}

	/**
	 * When an image attachment is deleted we delete his url from settings.
	 *
	 * @param $id
	 */
	public function tcm_delete_attachment( $id ) {
		//Delete image url from settings
		$badge_img_url = wp_get_attachment_url( $id );
		$images        = tcms()->tcm_get_setting_by_name( 'tcm_badges_custom_images' );

		if ( ! $images ) {
			return true;
		}

		if ( ( $key = array_search( $badge_img_url, $images ) ) !== false ) {
			unset( $images[ $key ] );
		}
		$images = array_values( $images );
		tcah()->tcm_update_option( 'tcm_badges_custom_images', $images );
		//Delete image url from badges that have this image setted
		$badges = tcms()->tcm_get_setting_by_name( 'tcm_badges' );

		if ( ! $badges ) {
			return true;
		}

		foreach ( $badges as &$badge ) {
			if ( $badge['image_url'] == $badge_img_url ) {
				$badge['image_url'] = '';
				$badge['image']     = '';
			}
		}
		tcah()->tcm_update_option( 'tcm_badges', $badges );
	}

	/**
	 * Check if we show the live preview frame
	 */
	public function is_inner_frame() {
		return isset( $_REQUEST[ Thrive_Comments_Constants::TCM_FRAME ] ) && $_REQUEST[ Thrive_Comments_Constants::TCM_FRAME ];
	}

	/* adds the hidden svg file with the icons from the admin pages to the header */
	public function add_admin_svg_file() {
		tcah()->include_svg_file( 'admin-page-icons.svg' );
	}

	/**
	 * make sure all the features required by TC are shown in the dashboard
	 *
	 * @param array $features
	 *
	 * @return array
	 */
	function tcm_add_features( $features ) {
		$features['api_connections'] = true;

		return $features;
	}

	/**
	 * Add email delivery apis.
	 * ( They were only included from leads )
	 *
	 * @param $types
	 *
	 * @return mixed
	 */
	function tcm_api_types( $types ) {
		$types['email'] = __( "Email Delivery", Thrive_Comments_Constants::T );

		return $types;
	}
}

return new Thrive_Comments_Admin();
