<?php
/**
 * all defined action hooks go here
 */

/**
 * 'init' action hook
 * register the required custom post types
 */
function tve_leads_init() {
	/* the first level is the "lead group" post type */
	register_post_type( TVE_LEADS_POST_GROUP_TYPE, array(
		'labels'              => array(
			'name' => 'Thrive Leads - Lead Group',
		),
		'public'              => false,
		'exclude_from_search' => true,
		'hierarchical'        => true, //Allows Parent to be specified.
		'_edit_link'          => 'post.php?post=%d',
		'map_meta_cap'        => true,
		'capabilities'        => array(
			'edit_others_posts'    => TVE_DASH_EDIT_CPT_CAPABILITY,
			'edit_published_posts' => TVE_DASH_EDIT_CPT_CAPABILITY,
		),
	) );

	/* slightly different behaviour: shortcodes */
	register_post_type( TVE_LEADS_POST_SHORTCODE_TYPE, array(
		'labels'              => array(
			'name' => 'Thrive Leads - Shortcodes',
		),
		'public'              => false,
		'publicly_queryable'  => TL_Product::has_access(), // These should only be publicly_queryable for logged users with sufficient access
		'query_var'           => false,
		'exclude_from_search' => true,
		'rewrite'             => false,
		'show_in_rest'        => true,
		'hierarchical'        => true, //Allows Parent to be specified.
		'_edit_link'          => 'post.php?post=%d',
		'map_meta_cap'        => true,
		'capabilities'        => array(
			'edit_others_posts'    => TVE_DASH_EDIT_CPT_CAPABILITY,
			'edit_published_posts' => TVE_DASH_EDIT_CPT_CAPABILITY,
		),
	) );

	/* slightly different behaviour: 2 step lightbox (new name: ThriveBox) */
	register_post_type( TVE_LEADS_POST_TWO_STEP_LIGHTBOX, array(
		'labels'              => array(
			'name'          => 'Thrive Leads - ThriveBox',
			'singular_name' => 'ThriveBox',
			'menu_name'     => 'ThriveBoxes',
		),
		'public'              => false,
		'publicly_queryable'  => TL_Product::has_access(), // These should only be publicly_queryable for logged users with sufficient access
		'query_var'           => false,
		'exclude_from_search' => true,
		'rewrite'             => false,
		'hierarchical'        => true, //Allows Parent to be specified.
		'show_in_nav_menus'   => true,
		'label'               => 'ThriveBoxes Menu Trigger',
		'_edit_link'          => 'post.php?post=%d',
		'map_meta_cap'        => true,
		'capabilities'        => array(
			'edit_others_posts'    => TVE_DASH_EDIT_CPT_CAPABILITY,
			'edit_published_posts' => TVE_DASH_EDIT_CPT_CAPABILITY,
		),
	) );

	/* one click signup (new name: Signup Segue)*/
	register_post_type( TVE_LEADS_POST_ONE_CLICK_SIGNUP, array(
		'labels'              => array(
			'name' => 'Thrive Leads - Signup Segue',
		),
		'public'              => false,
		'publicly_queryable'  => true,
		'query_var'           => false,
		'exclude_from_search' => true,
		'rewrite'             => false,
		'hierarchical'        => true, //Allows Parent to be specified.
		'_edit_link'          => 'post.php?post=%d',
		'map_meta_cap'        => true,
		'capabilities'        => array(
			'edit_others_posts'    => TVE_DASH_EDIT_CPT_CAPABILITY,
			'edit_published_posts' => TVE_DASH_EDIT_CPT_CAPABILITY,
		),
	) );

	/* second level: "form_type" post type */
	register_post_type( TVE_LEADS_POST_FORM_TYPE, array(
		'labels'              => array(
			'name' => 'Thrive Leads - Forms',
		),
		'public'              => false,
		'publicly_queryable'  => TL_Product::has_access(), // These should only be publicly_queryable for logged users with sufficient access
		'query_var'           => false,
		'exclude_from_search' => true,
		'rewrite'             => false,
		'hierarchical'        => true, //Allows Parent to be specified.
		'_edit_link'          => 'post.php?post=%d',
		'map_meta_cap'        => true,
		'capabilities'        => array(
			'edit_others_posts'    => TVE_DASH_EDIT_CPT_CAPABILITY,
			'edit_published_posts' => TVE_DASH_EDIT_CPT_CAPABILITY,
		),
	) );

	/**
	 * Allow/hide thrive leads shortcodes
	 *
	 * @return boolean
	 */
	if ( apply_filters( 'tve_leads_allow_shortcodes', true ) ) {
		/* register the shortcode rendering */
		add_shortcode( 'thrive_leads', 'tve_leads_shortcode_render' );

		/* register the shortcode lock rendering */
		add_shortcode( 'thrive_lead_lock', 'tve_leads_shortcode_lock_render' );

		/* register the shortcode rendering - 2-Step Lightboxes */
		add_shortcode( 'thrive_2step', 'tve_leads_two_step_render' );
	}

	/* should happen in admin-ajax and in frontend / editor inner frame */
	if ( ! is_admin() || is_editor_page_raw( true ) ) {
		/**
		 * filter the nav menu items for ThriveBox Menu Triggers
		 */
		add_filter( 'wp_nav_menu_objects', 'tve_leads_wp_nav_menu_objects' );
	}
}

function tl_hide_export_content( $allow, $post ) {
	if ( in_array( $post->post_type, array( TVE_LEADS_POST_FORM_TYPE, TVE_LEADS_POST_TWO_STEP_LIGHTBOX, TVE_LEADS_POST_SHORTCODE_TYPE, TVE_LEADS_POST_TWO_STEP_LIGHTBOX ) ) ) {
		$allow = false;
	}

	return $allow;
}

/**
 * Register widgets used in the plugin.
 */
function tve_leads_widget_init() {
	register_widget( 'Thrive_Leads_Widget' );
}

/**
 * called immediately after a group has been detected
 * to be displayed, we'll need to hook into other WP points, such as footer, etc
 *
 * Prepare a form variation to be displayed - add action for enqueueing scripts, CSS, fonts etc
 */
function tve_leads_register_group() {
	global $tve_lead_group;
	if ( $tve_lead_group === null ) {
		return;
	}

	/**
	 * Change flag for loading leads forms with ajax
	 *
	 * @param bool $lazy_load whether or not the "Lazyload" setting is ON
	 *
	 * @return bool
	 */
	$ajax_load_forms = apply_filters( 'tve_leads_lazy_load_forms', tve_leads_get_option( 'ajax_load' ) );
	// each form variation will require styles and javascript added to the page

	$form_types_to_be_shown = tve_leads_get_targeted_form_types( $tve_lead_group, $ajax_load_forms );
	if ( empty( $form_types_to_be_shown ) ) {
		return;
	}

	$found                     = false;
	$GLOBALS['tve_lead_forms'] = isset( $GLOBALS['tve_lead_forms'] ) ? $GLOBALS['tve_lead_forms'] : array();

	//used to increment impressions for each form type that is rendered in its renderer; see tve_leads_display_form_$type
	$GLOBALS['tve_lead_impressions'] = isset( $GLOBALS['tve_lead_impressions'] ) ? $GLOBALS['tve_lead_impressions'] : array();

	/**
	 * Leave the possibility for others to change the forms which will be shown to the users
	 *
	 * @form_types_to_be_shown array
	 */
	$form_types_to_be_shown = tve_filter_intrusive_forms( 'tl', $form_types_to_be_shown );

	/* STEP2 : check for tests at form type level */
	foreach ( $form_types_to_be_shown as $form_type ) {

		if ( ! $ajax_load_forms ) {
			$variation = tve_leads_determine_variation( $form_type );
			if ( empty( $variation ) ) {
				continue;
			}
		}

		$found = true;
		/* save them in a GLOBALS field and remember to register the hook */
		$GLOBALS['tve_lead_forms'][ $form_type->tve_form_type ] = array(
			'variation'      => isset( $variation ) ? $variation : array(),
			'form_type'      => $form_type,
			'active_test_id' => ! empty( $variation['test_model'] ) ? $variation['test_model']->id : 0,
		);
	}

	if ( $found ) {
		/**
		 * prepare the actual action hooks that will display the form contents
		 */
		foreach ( tve_leads_get_default_form_types() as $_type => $config ) {
			if ( ! isset( $GLOBALS['tve_lead_forms'][ $_type ] ) || ( $_type !== 'widget' && $_type !== 'php_insert' && empty( $config['wp_hook'] ) ) ) {
				continue;
			}

			if ( isset( $config['wp_hook'] ) ) {
				add_action( $config['wp_hook'], 'tve_leads_display_form_' . $_type, isset( $config['priority'] ) ? $config['priority'] : 10 );
			}

			if ( $ajax_load_forms ) {
				/**
				 * if ajax-loading of forms is enabled, we just need to output a placeholder for the form_type to be shown
				 * from javascript, this placeholder will be replaced with the actual form contents
				 */
				$GLOBALS['tve_lead_forms'][ $_type ]['placeholder'] = true;
				$GLOBALS['tve_lead_forms'][ $_type ]['form_output'] = tve_leads_get_form_placeholder( $_type );
				/**
				 * none of the following is required if we load forms with AJAX
				 */
				continue;
			}

			$data = $GLOBALS['tve_lead_forms'][ $_type ];

			//initiate data for impressions for each form type without shortcode and 2step lightbox
			$GLOBALS['tve_lead_impressions'][ $_type ] = array(
				'group_id'       => $tve_lead_group->ID,
				'form_type_id'   => $data['form_type']->ID,
				'variation_key'  => $data['variation']['key'],
				'active_test_id' => $data['active_test_id'],
			);
		}
	}
}

/**
 * Query all lead groups order by priority desc (highest to lowest)
 * Run through the display settings of each group until you find one that returns true for display
 * Exit algorithm because you know that the highest priority lead group will be found first.
 */
function tve_leads_query_group() {
	/**
	 * no forms should be displayed on WP feeds. avoid expensive queries when identifying crawlers
	 */
	if ( is_feed() || is_comment_feed() || tve_dash_is_crawler() ) {
		return;
	}

	/**
	 * Filter thrive_leads_skip_request
	 * Allows one to prevent Thrive Leads for running Lead Group displays checks on the current REQUEST
	 *
	 * @since 1.95.2
	 */
	$short_circuit = apply_filters( 'thrive_leads_skip_request', false );
	if ( $short_circuit || ( defined( 'THRIVE_LEADS_NO_GROUPS' ) && THRIVE_LEADS_NO_GROUPS ) ) {
		return;
	}

	$blacklist_post_types = array(
		TVE_LEADS_POST_FORM_TYPE,
		TVE_LEADS_POST_GROUP_TYPE,
		TVE_LEADS_POST_SHORTCODE_TYPE,
		'tcb_lightbox',
	);

	/**
	 * Filter thrive_leads_post_types_blacklist
	 *
	 * Allows adding post types to the exclusion list. Appending to this list will make sure the following code is not executed for these post types
	 *
	 * @since 1.95.2
	 */
	$blacklist_post_types = apply_filters( 'thrive_leads_post_types_blacklist', $blacklist_post_types );

	/* first, some general basic restrictions */
	if ( is_editor_page_raw() || ( is_singular() && in_array( get_post_type(), $blacklist_post_types, true ) ) ) {
		return;
	}
	if ( wp_doing_ajax() ) {
		return;
	}

	$GLOBALS['tve_leads_form_config']              = tve_leads_prepare_script_config();
	$GLOBALS['tve_leads_form_config']['ajax_load'] = tve_leads_get_option( 'ajax_load' );

	tve_leads_set_inbound_link_cookies();

	global $tve_lead_group;
	require plugin_dir_path( __DIR__ ) . 'admin/inc/classes/display_settings/Thrive_Leads_Display_Settings_Manager.php';
	$manager = new Thrive_Leads_Display_Settings_Manager( TVE_LEADS_VERSION );
	$manager->load_dependencies();

	/** @var Thrive_Leads_DB $tvedb */
	global $tvedb;
	$groups = $tvedb->get_groups_with_options();

	global $wp_query;
	$should_reset_queried_object = $wp_query->queried_object === null;

	foreach ( $groups as $group ) {
		$saved_options = new Thrive_Leads_Group_Options( $group->ID, $group->show_group_options, $group->hide_group_options );

		/* if at least one is_page() check is made, we need to unset the queried_object field */
		if ( $saved_options->displayGroup() ) {
			$inbound_cookie_key = "tl_inbound_link_params_{$group->ID}";
			if ( isset( $_COOKIE[ $inbound_cookie_key ] ) ) {
				$inbound_link_params = thrive_safe_unserialize( stripslashes( $_COOKIE[ $inbound_cookie_key ] ) );
				if ( empty( $inbound_link_params['tl_form_type'] ) ) {
					continue;
				}
			}
			$tve_lead_group                        = $group;
			$tve_lead_group->saved_display_options = array(
				'allowed_post_types' => $saved_options->getTabSavedOptions( 5, 'show_group_options' ),
				'flag_url_match'     => $saved_options->flag( 'direct_url_match' ),
			);
			/* only get form types for a single group - the one that is matched by the current request */
			$tve_lead_group->form_types = tve_leads_get_form_types( array(
				'lead_group_id'  => $group->ID,
				'tracking_data'  => false,
				'get_variations' => true,
				'no_content'     => false,
			) );
			break;
		}
	}
	/**
	 * in some custom setups, this has the potential of redirecting the user an incorrect URL:
	 * there is this condition in wp-includes/canonical.php: } elseif ( is_page() && !is_feed() && isset($wp_query->queried_object) && 'page' == get_option('show_on_front') && $wp_query->queried_object->ID == get_option('page_on_front')  && ! $redirect_url ) {
	 * in our case, $wp_query->queried_object will be set and on homepage, if the URL of the page is not the same it will cause a redirect
	 */
	if ( $should_reset_queried_object && isset( $wp_query->queried_object ) ) {
		unset( $wp_query->queried_object, $wp_query->queried_object_id );
	}

	if ( ! empty( $tve_lead_group ) ) {
		$GLOBALS['tve_leads_form_config']['main_group_id']   = $tve_lead_group->ID;
		$GLOBALS['tve_leads_form_config']['display_options'] = $tve_lead_group->saved_display_options;
		if ( get_post_meta( $tve_lead_group->ID, 'tve_leads_masonry', true ) ) {
			wp_enqueue_script( 'jquery-masonry' );
		}
		tve_leads_register_group();
	}
}

/**
 * enqueue the default styles when they are needed
 *
 */
function tve_leads_enqueue_default_scripts() {
	$js_suffix = defined( 'TVE_DEBUG' ) && TVE_DEBUG ? '.js' : '.min.js';

	wp_style_is( 'tve_leads_forms' ) || tve_leads_enqueue_style( 'tve_leads_forms', TVE_LEADS_URL . 'editor-layouts/css/frontend.css' );

	if ( ! wp_script_is( 'tve_frontend' ) ) {
		if ( tve_leads_has_lightspeed() ) {
			\TCB\Lightspeed\JS::get_instance( get_the_ID() )->enqueue_scripts();
		}

		if ( is_editor_page() ) {
			tve_enqueue_script( 'jquery-zclip', TVE_DASH_URL . '/js/util/jquery.zclip.1.1.1/jquery.zclip.js', array( 'jquery' ) );
		}

		$frontend_options = array(
			'is_editor_page'   => is_editor_page(),
			'page_events'      => array(),
			'is_single'        => (string) ( (int) is_singular() ),
			'ajaxurl'          => admin_url( 'admin-ajax.php' ),
			'social_fb_app_id' => function_exists( 'tve_get_social_fb_app_id' ) ? tve_get_social_fb_app_id() : '',
			'dash_url'         => TVE_DASH_URL,
			'translations'     => array(
				'Copy' => __( 'Copy', 'thrive-leads' ),
			),
		);

		if ( ! empty( $frontend_options['is_single'] ) ) {
			global $post;
			$frontend_options['post_id'] = $post instanceof WP_Post ? $post->ID : null;
		}

		/**
		 * Allows adding frontend options from different plugins
		 *
		 * @param $frontend_options
		 */
		$frontend_options = apply_filters( 'tve_frontend_options_data', $frontend_options );

		wp_localize_script( 'tve_frontend', 'tve_frontend_options', $frontend_options );
	}
	/**
	 * enqueue the general frontend script for forms - this one needs to be included in the footer - the last parameter is set to true
	 * this is to allow it to be localized in the wp_print_footer_scripts hook
	 */
	tve_leads_enqueue_script( 'tve_leads_frontend', TVE_LEADS_URL . 'js/frontend' . $js_suffix, array( 'jquery' ), false, true );
}

/**
 * enqueue scripts and styles for a specific form variation (design)
 *
 * @param array $variation
 *
 * @return array
 */
function tve_leads_enqueue_variation_scripts( $variation ) {

	$scripts = array(
		'fonts' => array(),
		'css'   => array(),
		'js'    => array(),
	);

	if ( empty( $variation[ TVE_LEADS_FIELD_TEMPLATE ] ) ) {
		return $scripts;
	}

	/* enqueue Custom Fonts, if any */
	$fonts = tve_leads_enqueue_custom_fonts( $variation );

	$config = tve_leads_get_editor_template_config( $variation[ TVE_LEADS_FIELD_TEMPLATE ] );

	if ( empty( $config ) ) {
		return $scripts;
	}

	/* custom fonts for the form */
	if ( ! empty( $config['fonts'] ) ) {
		foreach ( $config['fonts'] as $font ) {
			$fonts[ 'tve-leads-font-' . md5( $font ) ] = $font;
			wp_enqueue_style( 'tve-leads-font-' . md5( $font ), $font );
		}
	}

	$css = array(
		'tve_leads_forms' => TVE_LEADS_URL . 'editor-layouts/css/frontend.css?ver=' . TVE_LEADS_VERSION,
	);

	/* include also the CSS for each form type design */
	if ( ! empty( $config['css'] ) ) {
		$css_key = 'tve-leads-' . str_replace( '.css', '', $config['css'] );

		/** is from cloud */
		if ( isset( $config['API_VERSION'] ) ) {
			$css_url = $config['base_url'] . '/css/' . $config['css'];
		} else {
			$css_url = TVE_LEADS_URL . 'editor-templates/_form_css/' . $config['css'];
		}

		if ( ! empty( $css_url ) ) {
			tve_leads_enqueue_style( $css_key, $css_url );
			$css[ $css_key ] = $css_url . '?ver=' . TVE_LEADS_VERSION;
		}
	}

	/**
	 * if any sdk is needed for the social sharing networks, enqueue that also
	 */
	$globals = $variation[ TVE_LEADS_FIELD_GLOBALS ];
	$js      = array();
	if ( ! empty( $globals['js_sdk'] ) ) {
		foreach ( $globals['js_sdk'] as $handle ) {
			$link                          = tve_social_get_sdk_link( $handle );
			$js[ 'tve_js_sdk_' . $handle ] = $link;
			wp_script_is( 'tve_js_sdk_' . $handle ) || wp_enqueue_script( 'tve_js_sdk_' . $handle, $link, array(), false );
		}
	}

	$return = array(
		'fonts' => $fonts,
		'js'    => $js,
		'css'   => $css,
	);

	/**
	 * Keep these so that they are returned when lazy-loading the forms
	 */
	if ( ! isset( $GLOBALS['tve_leads_ajax_load_res_global'] ) ) {
		$GLOBALS['tve_leads_ajax_load_res_global'] = $return;
	} else {
		$GLOBALS['tve_leads_ajax_load_res_global']['js']    = array_merge( $GLOBALS['tve_leads_ajax_load_res_global']['js'], $return['js'] );
		$GLOBALS['tve_leads_ajax_load_res_global']['fonts'] = array_merge( $GLOBALS['tve_leads_ajax_load_res_global']['fonts'], $return['fonts'] );
		$GLOBALS['tve_leads_ajax_load_res_global']['css']   = array_merge( $GLOBALS['tve_leads_ajax_load_res_global']['css'], $return['css'] );
	}

	return $return;
}

/**
 * prepare the default configuration (localization) script for the TL frontend script
 *
 * @return array
 */
function tve_leads_prepare_script_config() {
	$admin_url = admin_url( 'admin-ajax.php' );
	if ( strpos( $admin_url, 'https' ) !== 0 ) {
		$admin_url = str_replace( array( 'https://', 'http://' ), '//', $admin_url );
	}

	return array(
		'security'          => wp_create_nonce( 'tve-leads-front-js-track-123333' ),
		'ajax_url'          => $admin_url,
		'forms'             => array(),
		'action_conversion' => 'tve_leads_ajax_conversion',
		'action_impression' => 'tve_leads_ajax_impression',
	);
}

/**
 * enqueue scripts and styles for all the forms that are to be displayed on a page
 *
 * also, render the contents and save them for display later on
 *
 * this will be called before the shortcode - related function, thus it will populate the GLOBAL array before that
 * we use that function to append items to the global js array, which will be printed in the footer
 *
 */
function tve_leads_enqueue_form_scripts() {

	/**
	 * Check if the current post being displayed has thrive leads shortcodes. enqueue default scripts if YES
	 */
	$post           = get_post();
	$has_shortcodes = false;
	if ( $post && ( strpos( $post->post_content, '[thrive_2step' ) !== false || strpos( $post->post_content, '[thrive_lead' ) !== false ) ) {
		$has_shortcodes = true;
	}

	if ( ! $has_shortcodes && empty( $GLOBALS['tve_lead_forms'] ) && ( ! tve_leads_is_preview_page() || tve_leads_is_editor_page() ) ) {
		// no form defined / found
		return;
	}

	/** @var WP_Post $tve_lead_group */
	global $tve_lead_group;

	tve_leads_enqueue_default_scripts();

	if ( tve_leads_get_option( 'ajax_load' ) ) {
		return;
	}

	/* custom CSS for each form; Icon Packs are enqueued when we are rendering the content */
	if ( ! empty( $GLOBALS['tve_lead_forms'] ) ) {
		foreach ( $GLOBALS['tve_lead_forms'] as $_type => $data ) {

			/**
			 * at this point, we just proceed in the normal, server-side way
			 */

			tve_leads_enqueue_variation_scripts( $data['variation'] );

			/* finally, generate the content, and wrap it inside a <div> to mark it in the DOM */
			$GLOBALS['tve_lead_forms'][ $_type ]['form_output'] = tve_editor_custom_content( $data['variation'] );

			/* also record any of the form types that are displayed to use in the conversion tracking mechanism */
			$GLOBALS['tve_leads_form_config']['forms'][ $_type ] = array(
				'_key'             => $data['variation']['key'],
				'form_name'        => $data['variation']['post_title'],
				'trigger'          => $data['variation']['trigger'],
				'trigger_config'   => ! empty( $data['variation']['trigger_config'] ) ? $data['variation']['trigger_config'] : new stdClass(),
				'form_type_id'     => $data['form_type'] ? $data['form_type']->ID : '',
				'main_group_id'    => $tve_lead_group->ID,
				'main_group_name'  => $tve_lead_group->post_title,
				'active_test_id'   => ! empty( $data['active_test_id'] ) ? $data['active_test_id'] : '',
				'active_test_data' => ! empty( $data['active_test_id'] ) ? tve_leads_get_test( $data['active_test_id'] ) : array(),
			);
		}
	}
}

/**
 * register a form impression
 *
 * @param WP_Post|null $group
 * @param WP_Post      $form_type_or_shortcode or shortcode
 * @param array        $variation
 * @param int          $test_model_id          an active test associated with this event, if any
 * @param              $current_screen         array type and id of the current page: post, page, homepage, archive...
 *
 */
function tve_leads_register_impression( $group, $form_type_or_shortcode, $variation, $test_model_id, $current_screen ) {
	if ( current_user_can( 'manage_options' ) || TL_Product::has_access() || tve_dash_is_crawler() ) {
		return;
	}
	$conversion_cookie_key = "tl-conv-{$variation['key']}";

	if ( isset( $_COOKIE[ $conversion_cookie_key ] ) ) {
		return;
	}

	$group_id      = is_object( $group ) ? $group->ID : $group;
	$form_type_id  = is_object( $form_type_or_shortcode ) ? $form_type_or_shortcode->ID : $form_type_or_shortcode;
	$variation_key = is_array( $variation ) ? $variation['key'] : $variation;

	global $tvedb;
	$cookie_key = tve_leads_get_form_cookie_key( $group, $form_type_or_shortcode, $variation, '' );

	/**
	 * Updated on 24.04.2015 - we do not track non-unique impressions anymore.
	 */
	if ( isset( $_COOKIE[ $cookie_key ] ) ) {
		return;
	}

	$event_type = TVE_LEADS_UNIQUE_IMPRESSION;

	$event_log = array(
		'event_type'    => $event_type,
		'main_group_id' => $group_id ?: $form_type_id,
		// for shortcodes, we hold the shortcode id also in the main_group_id field
		'form_type_id'  => $form_type_id,
		'variation_key' => $variation_key,
		'is_unique'     => isset( $_COOKIE['tve_leads_unique'] ) ? 0 : 1,
	);
	$event_log = array_merge( $event_log, $current_screen );

	//set cookie for unique visitor
	if ( ! isset( $_COOKIE['tve_leads_unique'] ) ) {
		setcookie( 'tve_leads_unique', 1, time() + ( 30 * 24 * 3600 ), '/' );
		$_COOKIE['tve_leads_unique'] = 1;
	}

	$cookie_data = array();

	/* if a referrer URL is set, save it in a cookie and track it if the user converts */
	$referrer = isset( $_SERVER['HTTP_REFERER'] ) ? $_SERVER['HTTP_REFERER'] : '';
	if ( wp_doing_ajax() && ! empty( $_REQUEST['http_referrer'] ) ) {
		$referrer = esc_url_raw( $_REQUEST['http_referrer'] );
	}
	$referrer = preg_replace( '#http(s)?://#', '', rtrim( $referrer, '/' ) );
	if ( $referrer ) {
		$trimmed = preg_replace( '#http(s)?://#', '', trim( $referrer, '/' ) );
		$host    = preg_replace( '#http(s)?://#', '', trim( $_SERVER['HTTP_HOST'], '/' ) );
		if ( strpos( $trimmed, $host ) !== 0 ) { /* the referrer is different than the current domain */
			$event_log['referrer']   = $referrer;
			$cookie_data['referrer'] = $referrer;
		}
	}

	$cache_variation_data = $variation['cache_impressions'] !== null;
	$log_id               = $tvedb->insert_event( $event_log, $test_model_id, $cache_variation_data );

	$cookie_data['log_id'] = $log_id;

	setcookie( $cookie_key, serialize( $cookie_data ), time() + ( 30 * 24 * 3600 ), '/' );

	/* also set it here, so we can use it in the current request, if it'll be the case */
	$_COOKIE[ $cookie_key ] = $cookie_data;
}

/**
 * register a form conversion
 *
 * @param WP_Post|null $group
 * @param WP_Post|null $form_type
 * @param array        $variation
 * @param int          $test_model_id  an active test id associated with this event, if any
 * @param array        $post_data      any other required data to be saved
 * @param              $current_screen array type and id of the current page: post, page, homepage, archive...
 *
 */
function tve_leads_register_conversion( $group, $form_type, $variation, $test_model_id, $post_data, $current_screen ) {
	if ( current_user_can( 'manage_options' ) || TL_Product::has_access() ) {
		return;
	}

	global $tvedb;

	$event_log = array(
		'event_type'    => TVE_LEADS_CONVERSION,
		'main_group_id' => ! empty( $group ) ? $group->ID : $form_type->ID,
		'form_type_id'  => $form_type->ID,
		'variation_key' => $variation['key'],
		'user'          => $post_data['email'],
	);
	$event_log = array_merge( $event_log, $current_screen );

	$referrer = isset( $post_data['http_referrer'] ) ? filter_var( $post_data['http_referrer'], FILTER_SANITIZE_URL ) : '';

	if ( $referrer ) {
		$trimmed = preg_replace( '#http(s)?://#', '', trim( $referrer, '/' ) );
		$host    = preg_replace( '#http(s)?://#', '', trim( $_SERVER['HTTP_HOST'], '/' ) );
		if ( strpos( $trimmed, $host ) !== 0 ) { /* the referrer is different than the current domain */
			$event_log['referrer'] = $referrer;
		}
	}

	/**
	 * read these directly from ajax POST
	 */
	foreach ( array( 'utm_source', 'utm_medium', 'utm_campaign' ) as $_tracking_field ) {
		if ( ! empty( $post_data[ $_tracking_field ] ) ) {
			$event_log[ $_tracking_field ] = $post_data[ $_tracking_field ];
		}
	}
	/**
	 * also update the cached values if they are not null (if any previous cache has been calculated for this form variation)
	 */
	$cache_variation_data = $variation['cache_conversions'] !== null;
	$log_id               = $tvedb->insert_event( $event_log, $test_model_id, $cache_variation_data );

	$name = empty( $post_data['custom_fields']['name'] ) ? '' : $post_data['custom_fields']['name'];
	unset(
		$post_data['custom_fields']['name'],
		$post_data['custom_fields']['password'],
		$post_data['custom_fields']['confirm_password']
	);
	$tvedb->tve_leads_register_contact( $log_id, $name, $post_data['email'], $post_data['custom_fields'] );

	if ( ! empty( $test_model_id ) ) {
		tve_leads_test_check_winner( $test_model_id );

		/* Stop the underperforming variations */
		tve_leads_stop_underperforming_variations( $test_model_id );
	}
}

/**
 * register impression via new dashboard main ajax request
 *
 * @param array $current   not used
 * @param array $post_data post data sent by thrive dashboard
 */
function tve_leads_dash_main_ajax_impression( $current, $post_data ) {
	tve_leads_ajax_impression( $post_data );
}

/**
 * AJAX call entry point for the system that will track impressions for a form submission
 *
 * this is only called if the AJAX-loading of forms is disabled
 *
 * @param array $post_data optional, allows overwriting the default POST data
 */
function tve_leads_ajax_impression( $post_data = null ) {
	/**
	 * Crawlers and bots should never be registered as impressions.
	 */
	if ( tve_dash_is_crawler() ) {
		return;
	}
	$post_data      = empty( $post_data ) ? $_POST : $post_data;
	$forms          = ! empty( $post_data['tl_data'] ) ? $post_data['tl_data'] : array();
	$current_screen = ! empty( $post_data['current_screen'] ) ? $post_data['current_screen'] : array();

	foreach ( $forms as $form ) {
		$group     = get_post( $form['group_id'] );
		$form_type = tve_leads_get_form_type( $form['form_type_id'], array( 'get_variations' => false ) );
		$variation = tve_leads_get_form_variation( null, $form['variation_key'] );

		do_action( TVE_LEADS_ACTION_FORM_IMPRESSION, $group, $form_type, $variation, $form['active_test_id'], $current_screen );
	}
}

/**
 * action hook callback - register a conversion when an API-connected form is submitted and the user is subscribed to a list via an API
 *
 * @param array $post_data - the post data - all relevant information is in the 'thrive_leads' field
 */
function tve_leads_api_form_submit( $post_data ) {
	// check if the send asset option is selected, if yes send the asset to the user
	if ( ! empty( $post_data['_asset_option'] ) ) {
		/* validate consent options */
		$send_asset = true;

		if ( ! empty( $post_data['consent_config']['enabled'] ) && ! in_array( 'asset', $post_data['consent_config']['always_send'] ) ) {
			// asset has been marked as "Needing user consent" - check if user gave consent
			$send_asset = ! empty( $post_data['user_consent'] );
		}

		if ( $send_asset ) {
			tve_leads_send_asset( $post_data );
		}
	}

	if ( empty( $post_data['thrive_leads'] ) ) {
		return;
	}
	/* flatten the thrive_leads data into a single array. add anything else missing from $post_data, so that the hook can use all submitted data */
	$data = $post_data['thrive_leads'] + $post_data;

	/* Add custom fields so we can save them for the contacts view */
	$ignored_fields        = tve_get_lead_generation_ignored_fields();
	$data['custom_fields'] = array();
	foreach ( $post_data as $field => $value ) {
		if ( ! is_array( $value ) && ! in_array( $field, $ignored_fields ) ) {
			$data['custom_fields'][ $field ] = $value;
		}
	}

	tve_leads_process_conversion( $data );
}

/**
 *  Gets the connection type and calls the sendEmail method
 *
 * @param $post_data
 */
function tve_leads_send_asset( $post_data ) {

	$connection = get_option( 'tve_api_delivery_service', false );
	if ( ! $connection ) {
		return;
	}
	$api = Thrive_List_Manager::connection_instance( $connection );
	if ( ! $api ) {
		return;
	}

	try {
		$api->sendEmail( $post_data );
	} catch ( Exception $e ) {
		global $wpdb;

		/**
		 * at this point, we need to log the error in a DB table, so that the user can see all these error later on and (maybe) re-subscribe the user
		 */
		$log_data = array(
			'date'          => date( 'Y-m-d H:i:s' ),
			'error_message' => tve_sanitize_data_recursive( $e->getMessage(), 'sanitize_text_field' ),
			'api_data'      => serialize( tve_sanitize_data_recursive( $post_data, 'sanitize_text_field' ) ),
			'connection'    => $api->get_key(),
			'list_id'       => 'asset',
		);

		$wpdb->insert( $wpdb->prefix . 'tcb_api_error_log', $log_data );
	}

}

/**
 * handles a couple of preliminary (data integrity checks) and record the actual conversion of a form
 *
 * @param array $post_data
 *
 * @return mixed
 */
function tve_leads_process_conversion( $post_data ) {
	$data = array(
		'email'         => ! empty( $post_data['email'] ) ? $post_data['email'] : '',
		'variation_key' => ! empty( $post_data['tl_data']['_key'] ) ? $post_data['tl_data']['_key'] : 0,
		'main_group_id' => ! empty( $post_data['tl_data']['main_group_id'] ) ? $post_data['tl_data']['main_group_id'] : 0,
		'form_type_id'  => ! empty( $post_data['tl_data']['form_type_id'] ) ? $post_data['tl_data']['form_type_id'] : 0,
		'custom_fields' => ! empty( $post_data['custom_fields'] ) ? $post_data['custom_fields'] : array(),
		'tve_labels'    => ! empty( $post_data['tve_labels'] ) ? $post_data['tve_labels'] : array(),
		'http_referrer' => ! empty( $post_data['get_data']['http_referrer'] ) ? $post_data['get_data']['http_referrer'] : array(),
	);

	//add referrer and utm data from post
	foreach ( array( 'http_referrer', 'utm_source', 'utm_medium', 'utm_campaign' ) as $_field ) {
		if ( ! empty( $post_data[ $_field ] ) ) {
			$data[ $_field ] = $post_data[ $_field ];
		}
	}

	/* if we already have a conversion for this form for this email address, we need to skip this */
	$conversion_cookie_key = 'tl_conversion_' . md5( json_encode( array( $data['email'], $data['main_group_id'] ) ) );
	if ( ! defined( 'TVE_LEADS_TEST_DATA' ) || ! TVE_LEADS_TEST_DATA ) {
		if ( isset( $_COOKIE[ $conversion_cookie_key ] ) ) {
			return;
		}
	}

	$data['active_test_id'] = ! empty( $post_data['tl_data']['active_test_id'] ) ? $post_data['tl_data']['active_test_id'] : 0;
	$data['type']           = ! empty( $post_data['form_type'] ) ? $post_data['form_type'] : '';
	$data['trigger']        = ! empty( $post_data['tl_data']['trigger'] ) ? $post_data['tl_data']['trigger'] : 0;

	/**
	 * if any of the data is missing or incorrect, disregard the request
	 *
	 */
	$main = get_post( $data['main_group_id'] );
	if ( empty( $main ) ) {
		return;
	}
	if ( $data['main_group_id'] == $data['form_type_id'] && ( strpos( $post_data['type'], 'two_step_' ) === 0 || strpos( $post_data['type'], 'shortcode_' ) === 0 ) ) {
		/* this is a shortcode / 2-step object being submitted */
		$form_type = $main;
	} else {
		/* regular Lead Groups / Form types / form variations */
		$form_type = tve_leads_get_form_type( $data['form_type_id'], array(
			'get_variations' => false,
		) );
		if ( empty( $form_type ) || $form_type->post_parent != $main->ID ) {
			return;
		}
	}
	$variation = tve_leads_get_form_variation( $data['form_type_id'], $data['variation_key'] );
	if ( empty( $variation ) || $variation['post_status'] != TVE_LEADS_STATUS_PUBLISH ) {
		return;
	}

	/**
	 * Spam check - if the form contains the google reCaptcha field and no such value was submitted, this looks like a direct POST
	 */
	$content = $variation[ TVE_LEADS_FIELD_SAVED_CONTENT ];
	if ( empty( $post_data['_use_captcha'] ) && strpos( $content, 'data-connection="api"' ) !== false && strpos( $content, 'tve-captcha-container' ) !== false ) {
		exit();
	}

	/* remember the conversion cookie */
	setcookie( $conversion_cookie_key, '1', time() + ( 10 * 60 ), '/' );

	/* remember the conversion so we won't display the form later */
	tve_leads_set_conversion_cookie( $data['main_group_id'] );

	/**
	 * also, remember a conversion cookie for the default variation state (so that we can later on show the "already_subscribed" state
	 */
	setcookie( 'tl-conv-' . $variation['key'], 1, time() + 3600 * 24 * 365, '/' ); // 1 year :-)

	do_action( TVE_LEADS_ACTION_FORM_CONVERSION, $main, $form_type, $variation, $data['active_test_id'], $data, empty( $post_data['current_screen'] ) ? array() : $post_data['current_screen'] );
}

/**
 * AJAX call entry point for the system that will track conversion for a form submission
 */
function tve_leads_ajax_conversion() {
	/* the following line causes the plugin not to work on wp-engine */
//    check_ajax_referer('tve-leads-front-js-track-123333', 'security');

	tve_leads_process_conversion( $_POST );
}

/**
 * print the required JS code in the footer, accordingly for each form type that has been displayed
 *
 * all form type data is available in the $GLOBALS array
 *
 * should handle triggering the display of the forms, and also tracking conversion data
 */
function tve_leads_print_footer_scripts() {
	/* for some reason the next line of code does not work, we need to output it manually */
	//wp_localize_script('tve_leads_frontend', 'TL_Const', empty($GLOBALS['tve_leads_form_config']) ? array() : $GLOBALS['tve_leads_form_config']);
	if ( ! empty( $GLOBALS['tve_leads_form_config']['two_step_ids'] ) ) {
		$GLOBALS['tve_leads_form_config']['two_step_ids'] = array_unique( $GLOBALS['tve_leads_form_config']['two_step_ids'] );
	}
	$form_globals = isset( $GLOBALS['tve_leads_form_config'] ) ? $GLOBALS['tve_leads_form_config'] : array();
	if ( ! isset( $form_globals['action_impression'] ) ) {
		$default_config = tve_leads_prepare_script_config();
		unset( $default_config['forms'] ); // make sure we do not overwrite this key
		$form_globals += $default_config;
	}
	$GLOBALS['tve_leads_form_config'] = $form_globals;

	if ( ! empty( $GLOBALS['tve_leads_form_config']['shortcode_ids'] ) ) {
		$GLOBALS['tve_leads_form_config']['shortcode_ids'] = array_unique( $GLOBALS['tve_leads_form_config']['shortcode_ids'] );
	}
	$form_config      = empty( $GLOBALS['tve_leads_form_config'] ) ? array() : $GLOBALS['tve_leads_form_config'];
	$custom_post_data = array();
	foreach ( array( 'utm_source', 'utm_medium', 'utm_campaign' ) as $_tracking_field ) {
		if ( ! empty( $_REQUEST[ $_tracking_field ] ) ) {
			$custom_post_data[ $_tracking_field ] = sanitize_text_field( $_REQUEST[ $_tracking_field ] );
		}
	}
	foreach ( $_GET as $k => $v ) {
		if ( ! is_array( $v ) && ! empty( $v ) ) {
			$custom_post_data['get_data'][ $k ] = htmlentities( strip_tags( $v ) );
		}
	}
	if ( ! empty( $_SERVER['HTTP_REFERER'] ) ) {
		$custom_post_data['http_referrer'] = $_SERVER['HTTP_REFERER'];
	}
	$form_config['custom_post_data'] = $custom_post_data;
	$form_config['current_screen']   = tve_get_current_screen();
	$form_config['ignored_fields']   = tve_get_lead_generation_ignored_fields();
	if ( ! isset( $form_config['ajax_load'] ) ) {
		/* this makes sure the TL 2-steps also work while previewing a TCB Lightbox */
		$form_config['ajax_load'] = tve_leads_get_option( 'ajax_load' );
	}
	$js = json_encode( $form_config );

	/**
	 * TL_Const is localized by the main request but TU does a ajax request; the body_end HTML is filtered by this function
	 * and we have to set the TL_Const with the new values and this is the reason why we've extended TL_Const which already existed from the main request.
	 *
	 * @see tve_leads_filter_tu_body_end()
	 */
	//echo sprintf( '<script type="text/javascript">/*<![CDATA[*/if ( !window.TL_Const ) var TL_Const=%s/*]]> */</script>', $js );
	echo sprintf( '<script type="text/javascript">/*<![CDATA[*/if ( !window.TL_Const ) {var TL_Const=%s;} else { window.TL_Front && TL_Front.extendConst && TL_Front.extendConst(%s)} /*]]> */</script>', $js, $js );

	if ( ! empty( $GLOBALS['tve_lead_impressions'] ) && ! current_user_can( 'manage_options' ) && ! TL_Product::has_access() ) {
		$js = '<script type="text/javascript">var TL_Front = TL_Front || {}; TL_Front.impressions_data = TL_Front.impressions_data || {};';
		foreach ( $GLOBALS['tve_lead_impressions'] as $type => $data ) {
			if ( empty( $data['output_js'] ) ) {
				continue;
			}
			$js    .= sprintf( 'TL_Front.impressions_data.%s = %s;', $type, json_encode( $data ) );
			$found = true;
		}
		$js .= '</script>';
		if ( isset( $found ) ) {
			echo $js;
		}
	}

	/**
	 * any custom html that might be needed
	 */
	if ( ! empty( $GLOBALS['tve_leads_footer_html'] ) ) {
		echo $GLOBALS['tve_leads_footer_html'];
	}

	if ( empty( $GLOBALS['tl_triggers'] ) && empty( $GLOBALS['tve_lead_forms'] ) && empty( $GLOBALS['tve_lead_shortcodes'] ) && empty( $GLOBALS['tve_leads_two_step'] ) ) {
		return;
	}

	$ajax_load_forms = tve_leads_get_option( 'ajax_load' );

	if ( $ajax_load_forms ) {
		/**
		 * nothing from here onwards is required if we load the forms via ajax
		 * we'll need to output the javascript for the triggers on AJAX load
		 *
		 * @see tve_leads_ajax_load_forms
		 */
		return;
	}

	if ( ! empty( $GLOBALS['tve_lead_forms'] ) ) {
		foreach ( $GLOBALS['tve_lead_forms'] as $type => $data ) {
			tve_leads_output_trigger_js( $data['variation'], $type . '-' . $data['variation']['key'], $type );
		}
	}

	if ( ! empty( $GLOBALS['tve_lead_shortcodes'] ) ) {
		foreach ( $GLOBALS['tve_lead_shortcodes'] as $id => $variation ) {
			tve_leads_output_trigger_js( $variation, 'shortcode_' . $id, 'shortcode' );
		}
	}

	if ( ! empty( $GLOBALS['tve_leads_two_step'] ) ) {
		foreach ( $GLOBALS['tve_leads_two_step'] as $id => $data ) {
			/**
			 * depending on the variation template we display as lightbox or screen filler
			 */
			if ( strpos( $data['tpl'], 'screen_filler' ) !== false ) {
				tve_leads_output_trigger_js( $data, '2step-' . $id, 'screen_filler' );
				tve_leads_display_form_screen_filler( '', $data['form_output'], $data, array(), 'tve-leads-track-2step-' . $id, true );
			} elseif ( strpos( $data['tpl'], 'lightbox' ) !== false ) {
				tve_leads_output_trigger_js( $data, '2step-' . $id, 'lightbox' );
				tve_leads_display_form_lightbox( '', $data['form_output'], $data, 'tve-leads-track-2step-' . $id, null, array(), true );
			}
		}
	}

	if ( ! empty( $GLOBALS['tve_leads_set_cookies'] ) ) {
		/**
		 * this is an array of key - value pairs for the cookies to be set from javascript. Used in case of shortcodes - there is
		 * no way of setting the cookie server side in the php script, because the shortcode rendering function is called after the
		 * headers have been sent
		 */
		include dirname( dirname( __FILE__ ) ) . '/js/cookies.js.php';
	}
}

/**
 * Display scripts with impressions data used to increment impressions with ajax request
 *
 * @param $type
 *
 * @return string
 */
function tve_leads_display_js_impression_data( $type ) {
	if ( ! isset( $GLOBALS['tve_lead_impressions'][ $type ] ) ) {
		return;
	}

	$GLOBALS['tve_lead_impressions'][ $type ]['output_js'] = true;
}

/**
 * output the contents for a Ribbon form type
 * this is called in the WP_footer hook
 *
 * @param string $flag            used to control the output
 * @param string $form_output     if present, it will take this instead of the GLOBALS
 * @param array  $variation_state current variation state
 * @param array  $control         used to change the output in various ways
 *
 * @return void|string
 */
function tve_leads_display_form_ribbon( $flag = '', $form_output = null, $variation_state = null, $control = array() ) {
	if ( ! isset( $GLOBALS['tve_lead_forms']['ribbon']['form_output'] ) && empty( $form_output ) ) {
		return;
	}

	$form_output = isset( $form_output ) ? $form_output : $GLOBALS['tve_lead_forms']['ribbon']['form_output'];

	/**
	 * just output the form placeholder
	 */
	if ( ! empty( $GLOBALS['tve_lead_forms']['ribbon']['placeholder'] ) ) {
		tve_leads_prepare_variation_hook( 'ribbon' );
		echo $form_output;

		return;
	}

	/**
	 * load the ribbon trigger
	 */
	$GLOBALS['tl_triggers']['ribbon'] = true;

	$defaults = array(
		'wrap' => true,
		'hide' => false,
	);
	$control  = array_merge( $defaults, $control );

	$variation = isset( $variation_state ) ? $variation_state : $GLOBALS['tve_lead_forms']['ribbon']['variation'];

	$ribbon_position = ! in_array( $variation['position'], array( 'top', 'bottom', 'above' ) ) ? 'top' : $variation['position'];

	/**
	 * check if a conversion has been registered for this variation and, if so, we need to check if there is an "Already subscribed" state defined and show that instead
	 */
	$already_subscribed = tve_leads_get_already_subscribed_html( $variation, 'ribbon' );
	/* If the already subscribed state is hidden then we don't display anything and return empty */
	if ( $already_subscribed === TVE_ALREADY_SUBSCRIBED_HIDDEN ) {
		unset( $GLOBALS['tve_lead_impressions']['ribbon'] );
		if ( $flag === '__return_content' ) {
			return '';
		}

		echo '';

		return;
	}

	/**
	 * if no "Already Subscribed" state has been defined and the visitor comes from an inbound link which shows the already subscribed state,
	 * we don't display anything
	 */
	if ( empty( $already_subscribed ) && empty( $variation['parent_id'] ) && tve_leads_force_subscribed_state() ) {
		if ( $flag === '__return_content' ) {
			return '';
		}

		echo '';

		return;
	}

	$control['hide'] = $control['hide'] || ! empty( $already_subscribed );

	$html = tve_leads_state_html( $form_output, $variation, $control ) . $already_subscribed;

	if ( ! empty( $control['wrap'] ) ) {
		$html = sprintf(
			        '<div data-position="%s" data-tl-type="ribbon" class="tl-state-root tve-leads-ribbon%s tve-tl-anim tve-leads-track-ribbon-%s %s">',
			        $ribbon_position,
			        empty( $variation['trigger'] ) || $variation['trigger'] == 'page_load' ? '' : ' tve-trigger-hide',
			        $variation['key'],
			        'tl-anim-' . ( $ribbon_position == 'bottom' ? 'slide_bot' : 'slide_top' )
		        ) . $html;
		// if this is the main (default) state, we need to bring together all the other states
		$html .= apply_filters( 'tve_leads_variation_append_states', '', $variation ) . '</div>';
	}

	tve_leads_display_js_impression_data( 'ribbon' );

	if ( $flag === '__return_content' ) {
		return $html;
	}

	echo $html;
}

/**
 * output the contents for a Lightbox post type
 * this is called in the WP_footer hook
 *
 * @param string $flag                    used to control the output
 * @param string $form_output             , optional, allows overwriting the output included in the lightbox
 * @param array  $variation               - needs to be present if $form_output is passed in
 * @param string $container_id            should be passed in if the others are passed in
 * @param bool   $output_placeholder
 * @param array  $control                 used to control various parts of the content
 * @param bool   $skip_inbound_link_check whether or not to skip the check for 'already_subscribed' state from the inbound link params
 *
 * @return void|string
 */
function tve_leads_display_form_lightbox(
	$flag = '', $form_output = null, $variation = null, $container_id = null, $output_placeholder = null, $control = array(), $skip_inbound_link_check = false
) {
	/**
	 * first, some sanity checks
	 */
	if ( is_null( $form_output ) && ! isset( $GLOBALS['tve_lead_forms']['lightbox']['form_output'] ) ) {
		return;
	}
	$is_two_step = ! empty( $form_output );

	$form_output = ! is_null( $form_output ) ? $form_output : $GLOBALS['tve_lead_forms']['lightbox']['form_output'];

	/**
	 * if we load the forms with AJAX, we just need to output a placeholder for each form
	 */
	$placeholder = is_null( $output_placeholder ) ? ! empty( $GLOBALS['tve_lead_forms']['lightbox']['placeholder'] ) : $output_placeholder;
	if ( ! empty( $placeholder ) ) {
		tve_leads_prepare_variation_hook( 'lightbox' );
		echo $form_output;

		return;
	}

	$variation    = $variation ? $variation : $GLOBALS['tve_lead_forms']['lightbox']['variation'];
	$container_id = $container_id ? $container_id : 'tve-leads-track-lightbox-' . $variation['key'];

	/**
	 * load the open_lightbox function
	 */
	$GLOBALS['tl_triggers']['lightbox'] = true;

	$defaults = array(
		'hide'       => false,
		'wrap'       => true,
		'hide_inner' => true,
		'animation'  => true,
	);

	$control = array_merge( $defaults, $control );

	/**
	 * check the user needs to be shown the Already Subscribed state from the inbound link functionality
	 */
	$show_already_subscribed = $skip_inbound_link_check ? false : tve_leads_force_subscribed_state();

	/**
	 * check if a conversion has been registered for this variation and, if so, we need to check if there is an "Already subscribed" state defined and show that instead
	 */
	$already_subscribed = '';

	if ( empty( $variation['parent_id'] ) && ( isset( $_COOKIE[ 'tl-conv-' . $variation['key'] ] ) || $show_already_subscribed ) ) {
		$done = tve_leads_get_already_subscribed_state( $variation );

		/* If the already subscribed state is hidden then we don't display anything */
		if ( tve_leads_check_variation_visibility( $done ) ) {
			return;
		}

		if ( ! empty( $done ) ) {
			tve_leads_enqueue_variation_scripts( $done );
			$control['hide']       = true;
			$control['hide_inner'] = false;
			$already_subscribed    = tve_leads_display_form_lightbox( '__return_content', tve_editor_custom_content( $done ), $done, null, null, array(
				'wrap' => false,
			) );
		} elseif ( $show_already_subscribed ) {
			/** this means that no already subscribed state has been found, we don't output anything */
			$html = '';
			if ( $flag === '__return_content' ) {
				return $html;
			}

			echo $html;

			return;
		}
	}

	/* we need to set the animation from the parent (main) state, if any */
	if ( ! empty( $variation['parent_id'] ) ) {
		$parent           = tve_leads_get_form_variation( null, $variation['parent_id'] );
		$parent_form_type = tve_leads_get_form_type_from_variation( $parent, false, true );

		/**
		 * cases:
		 * - lightbox with multiple states opened from an inline form (shortcode, widget etc)
		 * - 2-step lightbox with multiple states
		 *
		 */
		if ( $parent['form_state'] != $variation['form_state'] && $parent_form_type != 'lightbox' ) {
			$parent = $variation;
		}
	}

	$config = tve_leads_lightbox_globals( $variation );
	list( $type, $key ) = explode( '|', $variation[ TVE_LEADS_FIELD_TEMPLATE ] );

	$animation = '';
	if ( ! empty( $control['animation'] ) ) {
		/**
		 * @see Thrive_Leads_State_Lightbox_Action::mainPostCallback()
		 */
		if ( ! empty( $control['state_animation'] ) ) {
			$animation = 'tve-tl-anim tl-anim-' . $control['state_animation'];
		} else {
			$animation = 'tve-tl-anim tl-anim-' . ( isset( $parent ) ? $parent['display_animation'] : $variation['display_animation'] );
		}
	}

	$html = @sprintf(
		'<div %s class="tl-style" id="%s" data-state="%s">
        <div class="%s tve_post_lightbox tve-leads-lightbox">
        <div style="%s" class="tl-lb-target %s"><div class="tve_p_lb_overlay" style="%s"%s></div>' .
		'<div data-anim="' . ( isset( $parent ) ? $parent['display_animation'] : $variation['display_animation'] ) . '" class="tve_p_lb_content %s bSe cnt%s" style="%s"%s><div class="tve_p_lb_inner" id="tve-p-scroller" style="%s"><article>%s</article></div>' .
		'<a href="javascript:void(0)" class="tve_p_lb_close%s" style="%s"%s title="Close">x</a></div></div></div></div>',
		empty( $control['hide'] ) ? '' : 'style="display:none"',
		'tve_' . preg_replace( '#_v(.*)$#', '', $key ),
		$variation['key'],
		'tve_' . $key,
		! empty( $control['hide_inner'] ) ? 'visibility: hidden; position: fixed; left: -9000px' : '',
		$container_id . ( empty( $control['animation'] ) ? ' tve_p_lb_background' : '' ) . '" data-s-state="' . ( empty( $done ) ? '' : $done['key'] ),
		$config['overlay']['css'],
		$config['overlay']['custom_color'],
		$animation,
		$config['content']['class'],
		$config['content']['css'],
		$config['content']['custom_color'],
		$config['inner']['css'],
		$form_output,
		$config['close']['class'],
		tve_leads_is_v2_template( $key ) ? 'display: none' : $config['close']['css'],
		$config['close']['custom_color']
	);
	if ( ! empty( $control['wrap_tl_style'] ) ) {
		$html = '<div>' . $html . '</div>';
	}

	$html .= $already_subscribed . ( empty( $variation['parent_id'] ) ? apply_filters( 'tve_leads_variation_append_states', '', $variation ) : '' ); // if this is the main (default) state, we need to bring together all the other states
	if ( empty( $variation['parent_id'] ) ) {
		$html = '<div class="tl-states-root tl-anim-' . $variation['display_animation'] . '">' . $html . '</div>';
	}

	if ( $flag === '__return_content' ) {
		return $html;
	}

	echo $html;

	if ( ! $is_two_step ) {
		tve_leads_display_js_impression_data( 'lightbox' );
	}
}

/**
 * output the contents for a Screen Filler post type
 * this is called in the WP_footer hook
 *
 * @param string $flag                    used to control the output method (echo vs return)
 * @param string $form_output             optional if present it will be used instead of the GLOBALS value
 * @param array  $variation               optional if present it will be used instead of the GLOBALS value
 * @param array  $control                 used to control various pieces of content
 * @param String $container_id            optional if you want to specify a custom container ID
 * @param bool   $skip_inbound_link_check whether or not to skip the check for 'already_subscribed' state from the inbound link params
 *
 * @return string the generated content, appended to the $content
 */
function tve_leads_display_form_screen_filler(
	$flag = '', $form_output = null, $variation = null, $control = array(), $container_id = null, $skip_inbound_link_check = false
) {
	if ( ! isset( $form_output ) && ! isset( $GLOBALS['tve_lead_forms']['screen_filler']['form_output'] ) ) {
		return;
	}

	$form_output = isset( $form_output ) ? $form_output : $GLOBALS['tve_lead_forms']['screen_filler']['form_output'];

	/**
	 * this is the case where we load the forms with AJAX - on page load, we just output a placeholder
	 */
	if ( ! empty( $GLOBALS['tve_lead_forms']['screen_filler']['placeholder'] ) ) {
		tve_leads_prepare_variation_hook( 'screen_filler' );
		echo $form_output;

		return;
	}

	/**
	 * load the open_screenfiller js function
	 */
	$GLOBALS['tl_triggers']['screen_filler'] = true;

	$defaults = array(
		'wrap' => true,
		'hide' => false,
	);
	$control  = array_merge( $defaults, $control );

	$variation    = ! empty( $variation ) ? $variation : $GLOBALS['tve_lead_forms']['screen_filler']['variation'];
	$container_id = $container_id ? $container_id : 'tve-leads-track-screen_filler-' . $variation['key'];

	/**
	 * check if a conversion has been registered for this variation and, if so, we need to check if there is an "Already subscribed" state defined and show that instead
	 */
	$already_subscribed = tve_leads_get_already_subscribed_html( $variation, 'screen_filler', $skip_inbound_link_check );
	/* If the already subscribed state is hidden then we don't display anything and return empty */
	if ( $already_subscribed === TVE_ALREADY_SUBSCRIBED_HIDDEN ) {
		unset( $GLOBALS['tve_lead_impressions']['screen_filler'] );
		if ( $flag == '__return_content' ) {
			return '';
		}

		echo '';

		return;
	}

	/**
	 * if no "Already Subscribed" state has been defined and the visitor comes from an inbound link which shows the already subscribed state,
	 * we don't display anything
	 */
	$force_subscribed_state = $skip_inbound_link_check ? false : tve_leads_force_subscribed_state();
	if ( empty( $already_subscribed ) && empty( $variation['parent_id'] ) && $force_subscribed_state ) {
		if ( $flag == '__return_content' ) {
			return '';
		}

		echo '';

		return;
	}

	$control['hide'] = $control['hide'] || ! empty( $already_subscribed );

	$html = tve_leads_state_html( $form_output, $variation, $control ) . $already_subscribed;

	if ( ! empty( $control['wrap'] ) ) {
		$html = sprintf(
			        '<div class="tve-leads-screen-filler tl-state-root %s tve-tl-anim %s" style="visibility: hidden;">',
			        $container_id,
			        'stl-anim-' . $variation['display_animation'] .
			        ( empty( $variation['trigger'] ) || $variation['trigger'] == 'page_load' ? '' : ' tve-trigger-hide' )
		        ) . $html;

		$html .= apply_filters( 'tve_leads_variation_append_states', '', $variation ) . '</div>';
	}

	if ( $flag === '__return_content' ) {
		return $html;
	}

	echo $html;

	tve_leads_display_js_impression_data( 'screen_filler' );
}


/**
 * output the contents for a Scroll mat form type
 * this is called in the WP_footer hook
 *
 * @param string $flag            used to control the output
 * @param string $form_output     if present, it will take this instead of the GLOBALS
 * @param array  $variation_state current variation state
 * @param array  $control         used to change the output in various ways
 *
 * @return void|string
 */
function tve_leads_display_form_greedy_ribbon( $flag = '', $form_output = null, $variation_state = null, $control = array() ) {
	if ( ! isset( $GLOBALS['tve_lead_forms']['greedy_ribbon']['form_output'] ) && empty( $form_output ) ) {
		return;
	}

	$form_output = isset( $form_output ) ? $form_output : $GLOBALS['tve_lead_forms']['greedy_ribbon']['form_output'];

	/**
	 * just output the form placeholder
	 */
	if ( ! empty( $GLOBALS['tve_lead_forms']['greedy_ribbon']['placeholder'] ) ) {
		tve_leads_prepare_variation_hook( 'greedy_ribbon' );
		echo $form_output;

		return;
	}

	/**
	 * load the greedy_ribbon trigger
	 */
	$GLOBALS['tl_triggers']['greedy_ribbon'] = true;

	$defaults = array(
		'wrap' => true,
		'hide' => false,
	);
	$control  = array_merge( $defaults, $control );

	$variation = isset( $variation_state ) ? $variation_state : $GLOBALS['tve_lead_forms']['greedy_ribbon']['variation'];

	$greedy_ribbon_position = ! in_array( $variation['position'], array(
		'top',
		'bottom',
	) ) ? 'top' : $variation['position'];

	/**
	 * check if a conversion has been registered for this variation and, if so, we need to check if there is an "Already subscribed" state defined and show that instead
	 */
	$already_subscribed = tve_leads_get_already_subscribed_html( $variation, 'greedy_ribbon' );
	/* If the already subscribed state is hidden then we don't display anything and return empty */
	if ( $already_subscribed === TVE_ALREADY_SUBSCRIBED_HIDDEN ) {
		unset( $GLOBALS['tve_lead_impressions']['greedy_ribbon'] );
		if ( $flag == '__return_content' ) {
			return '';
		}

		echo '';

		return;
	}

	/**
	 * if no "Already Subscribed" state has been defined and the visitor comes from an inbound link which shows the already subscribed state,
	 * we don't display anything
	 */
	if ( empty( $already_subscribed ) && empty( $variation['parent_id'] ) && tve_leads_force_subscribed_state() ) {
		if ( $flag == '__return_content' ) {
			return '';
		}

		echo '';

		return;
	}

	$control['hide'] = $control['hide'] || ! empty( $already_subscribed );

	$html = tve_leads_state_html( $form_output, $variation, $control ) . $already_subscribed;

	if ( ! empty( $control['wrap'] ) ) {
		$html = sprintf(
			        '<div data-position="%s" data-tl-type="greedy_ribbon" class="tl-state-root tve-leads-greedy_ribbon%s tve-tl-anim tve-leads-track-greedy_ribbon-%s %s">',
			        $greedy_ribbon_position,
			        empty( $variation['trigger'] ) || $variation['trigger'] == 'page_load' ? '' : ' tve-trigger-hide',
			        $variation['key'],
			        'tl-anim-' . ( $greedy_ribbon_position == 'top' ? 'slide_top' : 'slide_bot' )
		        ) . $html;
		// if this is the main (default) state, we need to bring together all the other states
		$html .= apply_filters( 'tve_leads_variation_append_states', '', $variation ) . '</div>';
	}

	tve_leads_display_js_impression_data( 'greedy_ribbon' );

	if ( $flag === '__return_content' ) {
		return $html;
	}

	echo $html;
}


/**
 * output the contents for a post footer
 * this is called in the WP the_content hook
 *
 * @param string $content     the post content
 * @param string $form_output optional if present it will be used instead of the GLOBALS value
 * @param array  $variation   optional if present it will be used instead of the GLOBALS value
 * @param array  $control     used to control various pieces of content
 *
 * @return string the generated content, appended to the $content
 */
function tve_leads_display_form_post_footer( $content, $form_output = null, $variation = null, $control = array() ) {
	global $tve_lead_group;

	if ( $content === '__return_content' ) {
		$content = '';
	}

	$variation   = ! empty( $variation ) ? $variation : $GLOBALS['tve_lead_forms']['post_footer']['variation'];
	$form_output = isset( $form_output ) ? $form_output : $GLOBALS['tve_lead_forms']['post_footer']['form_output'];

	/**
	 * if there is no output, bail early
	 */
	if ( empty( $form_output ) ) {
		return $content;
	}

	$defaults = array(
		'wrap' => true,
		'hide' => false,
	);
	$control  = array_merge( $defaults, $control );

	/**
	 * this rule is only applied if we are not loading the content via AJAX
	 */
	if ( ! tve_leads_is_preview_page() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) {
		$actual_id = get_the_ID();

		$allowed_post_types = array( 'post', 'page' );
		if ( ! empty( $tve_lead_group->saved_display_options['allowed_post_types'] ) ) {
			$post_types         = is_array( $tve_lead_group->saved_display_options['allowed_post_types'] ) ? $tve_lead_group->saved_display_options['allowed_post_types'] : array();
			$allowed_post_types = array_unique( array_merge( $allowed_post_types, $post_types ) );
		}

		/**
		 * if we aren't on an individual post / page / etc, bail
		 */
		if ( ! tve_leads_is_preview_page() && ! is_singular( $allowed_post_types ) && empty( $tve_lead_group->saved_display_options['flag_url_match'] ) ) {
			return $content;
		}
		/**
		 * we also need to check if the actual post type of the current post / page is of the required types
		 */
		if ( ! empty( $actual_id ) && ! in_array( get_post_type( $actual_id ), $allowed_post_types ) && empty( $tve_lead_group->saved_display_options['flag_url_match'] ) ) {
			return $content;
		}
	}

	/**
	 * finally, if some other conditions are not met, e.g post grid rendering posts that have a post_footer form, bail
	 */
	if ( class_exists( 'PostGridHelper' ) && PostGridHelper::$render_post_grid === false ) {
		return $content;
	}

	/**
	 * this is the case where we load the forms with AJAX - on page load, we just output a placeholder
	 */
	if ( ! empty( $GLOBALS['tve_lead_forms']['post_footer']['placeholder'] ) ) {
		tve_leads_prepare_variation_hook( 'post_footer' );

		return $content . $GLOBALS['tve_lead_forms']['post_footer']['form_output'];
	}

	/**
	 * check if a conversion has been registered for this variation and, if so, we need to check if there is an "Already subscribed" state defined and show that instead
	 */
	$already_subscribed = tve_leads_get_already_subscribed_html( $variation, 'post_footer' );
	/* If the already subscribed state is hidden then we don't display anything and return empty */

	if ( $already_subscribed === TVE_ALREADY_SUBSCRIBED_HIDDEN ) {
		unset( $GLOBALS['tve_lead_impressions']['post_footer'] );

		return $content;
	}

	/**
	 * if no "Already Subscribed" state has been defined and the visitor comes from an inbound link which shows the already subscribed state,
	 * we don't display anything
	 */
	if ( empty( $already_subscribed ) && empty( $variation['parent_id'] ) && tve_leads_force_subscribed_state() ) {
		return $content;
	}


	$control['hide'] = $control['hide'] || ! empty( $already_subscribed );

	$html = tve_leads_state_html( $form_output, $variation, $control ) . $already_subscribed;

	if ( ! empty( $control['wrap'] ) ) {
		$html = sprintf(
			        '<div class="tve-leads-post-footer tve-tl-anim tve-leads-track-post_footer-%s %s">',
			        $variation['key'],
			        'tl-anim-' . $variation['display_animation'] .
			        ( empty( $variation['trigger'] ) || $variation['trigger'] == 'page_load' ? '' : ' tve-trigger-hide' )
		        ) . $html;

		// if this is the main (default) state, we need to bring together all the other states
		$html .= apply_filters( 'tve_leads_variation_append_states', '', $variation ) . '</div>';
	}

	$content .= $html;

	tve_leads_display_js_impression_data( 'post_footer' );

	return $content;
}


/**
 * output the contents for a in content opt-in
 * this is called in the WP the_content hook
 *
 * @param String $content     content of the post
 * @param string $form_output optional if present it will be used instead of the GLOBALS value
 * @param array  $variation   optional if present it will be used instead of the GLOBALS value
 * @param array  $control     used to control various pieces of content
 *
 * @return String $content Content with the form included if the settings are met.
 */
function tve_leads_display_form_in_content( $content, $form_output = null, $variation = null, $control = array() ) {
	global $tve_lead_group;

	if ( $content === '__return_content' ) {
		$content = '';
	}

	$defaults = array(
		'wrap' => true,
		'hide' => false,
	);
	$control  = array_merge( $defaults, $control );

	$form_output = isset( $form_output ) ? $form_output : $GLOBALS['tve_lead_forms']['in_content']['form_output'];
	$variation   = isset( $variation ) ? $variation : $GLOBALS['tve_lead_forms']['in_content']['variation'];

	if ( empty( $form_output ) ) {
		return;
	}

	/**
	 * this rule is only applied if we are not loading the content via AJAX
	 */
	if ( ! tve_leads_is_preview_page() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) {
		$actual_id          = get_the_ID();
		$allowed_post_types = array( 'post', 'page' );
		if ( ! empty( $tve_lead_group->saved_display_options['allowed_post_types'] ) ) {
			$post_types         = is_array( $tve_lead_group->saved_display_options['allowed_post_types'] ) ? $tve_lead_group->saved_display_options['allowed_post_types'] : array();
			$allowed_post_types = array_unique( array_merge( $allowed_post_types, $post_types ) );
		}
		/**
		 * if we aren't on an individual post / page / etc, bail
		 */
		if ( ! tve_leads_is_preview_page() && ! is_singular( $allowed_post_types ) && empty( $tve_lead_group->saved_display_options['flag_url_match'] ) ) {
			return $content;
		}
		/**
		 * we also need to check if the actual post type of the current post / page is of the required types
		 */
		if ( ! empty( $actual_id ) && ! in_array( get_post_type( $actual_id ), $allowed_post_types ) && empty( $tve_lead_group->saved_display_options['flag_url_match'] ) ) {
			return $content;
		}
	}


	/**
	 * finally, if some other conditions are not met, e.g post grid rendering posts that have a post_footer form, bail
	 */
	if ( class_exists( 'PostGridHelper' ) && PostGridHelper::$render_post_grid === false ) {
		return $content;
	}

	/**
	 * this is the case where we load the forms with AJAX - on page load, we just output a placeholder
	 */
	if ( ! empty( $GLOBALS['tve_lead_forms']['in_content']['placeholder'] ) ) {
		tve_leads_prepare_variation_hook( 'in_content' );

		return '<div class="tve-tl-cnt-wrap">' . $content . '</div>';
	}

	/**
	 * check if a conversion has been registered for this variation and, if so, we need to check if there is an "Already subscribed" state defined and show that instead
	 */
	$already_subscribed = tve_leads_get_already_subscribed_html( $variation, 'in_content' );
	/* If the already subscribed state is hidden then we don't display anything and return empty */
	if ( $already_subscribed === TVE_ALREADY_SUBSCRIBED_HIDDEN ) {
		unset( $GLOBALS['tve_lead_impressions']['in_content'] );

		return $content;
	}

	/**
	 * if no "Already Subscribed" state has been defined and the visitor comes from an inbound link which shows the already subscribed state,
	 * we don't display anything
	 */
	if ( empty( $already_subscribed ) && empty( $variation['parent_id'] ) && tve_leads_force_subscribed_state() ) {
		return $content;
	}

	$control['hide'] = $control['hide'] || ! empty( $already_subscribed );

	$in_content = tve_leads_state_html( $form_output, $variation, $control ) . $already_subscribed;

	if ( ! empty( $control['wrap'] ) ) {
		$in_content = sprintf(
			              '<div class="tve-leads-in-content tve-tl-anim tve-leads-track-in_content-%s %s">',
			              $variation['key'],
			              'tl-anim-' . $variation['display_animation'] .
			              ( empty( $variation['trigger'] ) || $variation['trigger'] == 'page_load' ? '' : ' tve-trigger-hide' )
		              ) . $in_content;

		// if this is the main (default) state, we need to bring together all the other states
		$in_content .= apply_filters( 'tve_leads_variation_append_states', '', $variation ) . '</div>';
	}

	if ( tve_leads_is_preview_page() || defined( 'TVE_AJAX_LOAD_FORM' ) || ! empty( $variation['parent_id'] ) ) {
		return $in_content;
	}

	tve_leads_display_js_impression_data( 'in_content' );
	$paragraph_number = intval( $variation['position'] );

	if ( $paragraph_number === 0 ) {
		//we display the form at the beginning of the content
		return $in_content . $content;
	} else {
		$P_CLOSE = '</p>';

		$paragraphs = explode( $P_CLOSE, $content );
		foreach ( $paragraphs as $index => $paragraph ) {
			if ( trim( $paragraph ) ) {
				$paragraphs[ $index ] .= $P_CLOSE;
			}
			if ( $paragraph_number == $index + 1 ) {
				$paragraphs[ $index ] .= $in_content;
			}
		}

		return implode( '', $paragraphs );
	}

}


/**
 * output the contents for a slide in form type
 * this is called in the WP wp_footer hook
 *
 * @param string $flag        used to control the output - it will return the output when  set to '__return_content'
 * @param string $form_output optional if present it will be used instead of the GLOBALS value
 * @param array  $variation   optional if present it will be used instead of the GLOBALS value
 * @param array  $control     used to control various pieces of content
 *
 * @return void|string
 */
function tve_leads_display_form_slide_in( $flag = '', $form_output = null, $variation = null, $control = array() ) {
	$defaults = array(
		'wrap' => true,
		'hide' => false,
	);
	$control  = array_merge( $defaults, $control );

	$form_output = isset( $form_output ) ? $form_output : $GLOBALS['tve_lead_forms']['slide_in']['form_output'];
	$variation   = isset( $variation ) ? $variation : $GLOBALS['tve_lead_forms']['slide_in']['variation'];

	if ( empty( $form_output ) ) {
		return;
	}

	/**
	 * this is the case where we load the forms with AJAX - on page load, we just output a placeholder
	 */
	if ( ! empty( $GLOBALS['tve_lead_forms']['slide_in']['placeholder'] ) ) {
		tve_leads_prepare_variation_hook( 'slide_in' );
		echo $GLOBALS['tve_lead_forms']['slide_in']['form_output'];

		return;
	}

	/**
	 * check if a conversion has been registered for this variation and, if so, we need to check if there is an "Already subscribed" state defined and show that instead
	 */
	$already_subscribed = tve_leads_get_already_subscribed_html( $variation, 'slide_in' );
	/* If the already subscribed state is hidden then we don't display anything and return empty */
	if ( $already_subscribed === TVE_ALREADY_SUBSCRIBED_HIDDEN ) {
		unset( $GLOBALS['tve_lead_impressions']['slide_in'] );
		if ( $flag == '__return_content' ) {
			return '';
		}

		echo '';

		return;
	}

	/**
	 * if no "Already Subscribed" state has been defined and the visitor comes from an inbound link which shows the already subscribed state,
	 * we don't display anything
	 */
	if ( empty( $already_subscribed ) && empty( $variation['parent_id'] ) && tve_leads_force_subscribed_state() ) {
		if ( $flag == '__return_content' ) {
			return '';
		}

		echo '';

		return;
	}

	$control['hide'] = $control['hide'] || ! empty( $already_subscribed );

	/**
	 * load the slide_in open function
	 */
	$GLOBALS['tl_triggers']['slide_in'] = true;

	$animation = strpos( $variation['position'], 'left' ) !== false ? 'slide_left' : 'slide_right';

	$html = tve_leads_state_html( $form_output, $variation, $control ) . $already_subscribed;

	if ( ! empty( $control['wrap'] ) ) {
		$html = sprintf(
			        '<div class="tve-leads-slide-in tve-tl-anim tve-leads-track-slide_in-%s %s">',
			        $variation['key'],
			        'tl-anim-' . $animation . ' tl_' . $variation['position'] .
			        ( empty( $variation['trigger'] ) || $variation['trigger'] == 'page_load' ? '' : ' tve-trigger-hide' )
		        ) . $html;

		$html .= apply_filters( 'tve_leads_variation_append_states', '', $variation );

		$html .= '</div>';
	}

	tve_leads_display_js_impression_data( 'slide_in' );

	if ( $flag === '__return_content' ) {
		return $html;
	}

	echo $html;
}

/**
 *
 * displays / returns the html for a widget form
 *
 * @param string $flag        used to control the output
 * @param string $form_output optional if present it will be used instead of the GLOBALS value
 * @param array  $variation   optional if present it will be used instead of the GLOBALS value
 * @param array  $control     used to control various pieces of content
 *
 * @return string
 */
function tve_leads_display_form_widget( $flag = '', $form_output = null, $variation = null, $control = array() ) {
	$defaults = array(
		'wrap' => true,
		'hide' => false,
	);
	$control  = array_merge( $defaults, $control );

	$form_output = isset( $form_output ) ? $form_output : $GLOBALS['tve_lead_forms']['widget']['form_output'];

	$variation = ! empty( $variation ) ? $variation : $GLOBALS['tve_lead_forms']['widget']['variation'];

	/**
	 * check if a conversion has been registered for this variation and, if so, we need to check if there is an "Already subscribed" state defined and show that instead
	 */
	$already_subscribed = tve_leads_get_already_subscribed_html( $variation, 'widget' );
	/* If the already subscribed state is hidden then we don't display anything and return empty */
	if ( $already_subscribed === TVE_ALREADY_SUBSCRIBED_HIDDEN ) {
		unset( $GLOBALS['tve_lead_impressions']['widget'] );
		if ( $flag == '__return_content' ) {
			return '';
		}

		echo '';

		return;
	}

	/**
	 * if no "Already Subscribed" state has been defined and the visitor comes from an inbound link which shows the already subscribed state,
	 * we don't display anything
	 */
	if ( empty( $already_subscribed ) && empty( $variation['parent_id'] ) && tve_leads_force_subscribed_state() ) {
		if ( $flag == '__return_content' ) {
			return '';
		}

		echo '';

		return;
	}

	$control['hide'] = $control['hide'] || ! empty( $already_subscribed );

	$html = tve_leads_state_html( $form_output, $variation, $control ) . $already_subscribed;

	if ( ! empty( $control['wrap'] ) ) {
		$html = sprintf(
			        '<div class="tve-leads-widget tve-leads-track-widget-%s tve-tl-anim %s">',
			        $variation['key'],
			        'tl-anim-' . $variation['display_animation']
		        ) . $html;
		// if this is the main (default) state, we need to bring together all the other states
		$html .= apply_filters( 'tve_leads_variation_append_states', '', $variation );
		$html .= '</div>';
	}

	//add js object for adding impression
	tve_leads_display_js_impression_data( 'widget' );

	if ( $flag === '__return_content' ) {
		return $html;
	}

	echo $html;
}

/**
 *
 * display / return the html needed for a shortcode form
 *
 * @param string $flag        used to control the output
 * @param string $form_output optional if present it will be used instead of the GLOBALS value
 * @param array  $variation   optional if present it will be used instead of the GLOBALS value
 * @param array  $control     used to control various pieces of content
 *
 * @return string
 */
function tve_leads_display_form_shortcode( $flag, $form_output, $variation, $control = array() ) {
	$defaults = array(
		'wrap' => true,
		'hide' => false,
	);
	$control  = array_merge( $defaults, $control );

	$form_output = str_replace( array( "\n", "\r" ), '', $form_output );

	/**
	 * check if a conversion has been registered for this variation and, if so, we need to check if there is an "Already subscribed" state defined and show that instead
	 * If the inbound links are set and target all forms on the site, force the display of the already subscribed state
	 */
	$skip_inbound_link_check = empty( $_COOKIE['tl_inbound_target_all'] );
	$already_subscribed      = tve_leads_get_already_subscribed_html( $variation, 'shortcode', $skip_inbound_link_check, true );
	/* If the already subscribed state is hidden then we don't display anything and return empty */
	if ( $already_subscribed === TVE_ALREADY_SUBSCRIBED_HIDDEN ) {
		unset( $GLOBALS['tve_lead_impressions']['shortcode'] );
		if ( $flag == '__return_content' ) {
			return '';
		}

		echo '';

		return;
	}
	$control['hide'] = $control['hide'] || ! empty( $already_subscribed );

	$html = tve_leads_state_html( $form_output, $variation, $control ) . $already_subscribed;
	if ( ! empty( $control['wrap'] ) ) {
		$html = sprintf(
			        '<div class="tve-leads-shortcode%s tve-tl-anim %s tve-leads-track-%s">',
			        /* shortcode should always be displayed directly, no fade animation if lazy load is disabled */
			        wp_doing_ajax() ? '' : ' tve-leads-triggered',
			        'tl-anim-' . $variation['display_animation'] . ( empty( $variation['trigger'] ) || $variation['trigger'] == 'page_load' ? '' : ' tve-trigger-hide' ),
			        'shortcode_' . $variation['post_parent']
		        ) . $html;

		$html .= apply_filters( 'tve_leads_variation_append_states', '', $variation );

		$html .= '</div>';
	}

	if ( $flag === '__return_content' ) {
		return $html;
	}

	echo $html;
}


/**
 * output the contents for a PHP Insert form type
 * this is called in the WP_footer hook
 *
 * @param string $flag            used to control the output
 * @param string $form_output     if present, it will take this instead of the GLOBALS
 * @param array  $variation_state current variation state
 * @param array  $control         used to change the output in various ways
 *
 * @return void|string
 */
function tve_leads_display_form_php_insert( $flag = '', $form_output = null, $variation = null, $control = array() ) {
	if ( ! isset( $GLOBALS['tve_lead_forms']['php_insert']['form_output'] ) && empty( $form_output ) ) {
		return '';
	}

	$variation   = ! empty( $variation ) ? $variation : $GLOBALS['tve_lead_forms']['php_insert']['variation'];
	$form_output = isset( $form_output ) ? $form_output : $GLOBALS['tve_lead_forms']['php_insert']['form_output'];

	/**
	 * just output the form placeholder
	 */
	if ( ! empty( $GLOBALS['tve_lead_forms']['php_insert']['placeholder'] ) ) {
		tve_leads_prepare_variation_hook( 'php_insert' );

		return $form_output;
	}

	/**
	 * load the ribbon trigger
	 */
	$GLOBALS['tl_triggers']['php_insert'] = true;

	$defaults = array(
		'wrap' => true,
		'hide' => false,
	);
	$control  = array_merge( $defaults, $control );

	/**
	 * this is the case where we load the forms with AJAX - on page load, we just output a placeholder
	 */
	if ( ! empty( $GLOBALS['tve_lead_forms']['php_insert']['placeholder'] ) ) {
		return $GLOBALS['tve_lead_forms']['php_insert']['form_output'];
	}

	/**
	 * check if a conversion has been registered for this variation and, if so, we need to check if there is an "Already subscribed" state defined and show that instead
	 */
	$already_subscribed = tve_leads_get_already_subscribed_html( $variation, 'php_insert' );
	/* If the already subscribed state is hidden then we don't display anything and return empty */
	if ( $already_subscribed === TVE_ALREADY_SUBSCRIBED_HIDDEN ) {
		unset( $GLOBALS['tve_lead_impressions']['php_insert'] );
		if ( $flag == '__return_content' ) {
			return '';
		}

		echo '';

		return;
	}

	/**
	 * if no "Already Subscribed" state has been defined and the visitor comes from an inbound link which shows the already subscribed state,
	 * we don't display anything
	 */
	if ( empty( $already_subscribed ) && empty( $variation['parent_id'] ) && tve_leads_force_subscribed_state() ) {
		if ( $flag == '__return_content' ) {
			return '';
		}

		echo '';

		return;
	}

	$control['hide'] = $control['hide'] || ! empty( $already_subscribed );

	$html = tve_leads_state_html( $form_output, $variation, $control ) . $already_subscribed;

	if ( ! empty( $control['wrap'] ) ) {
		$html = sprintf(
			        '<div class="tve-leads-post-footer tve-tl-anim tve-leads-track-php_insert-%s %s">',
			        $variation['key'],
			        'tl-anim-' . $variation['display_animation'] .
			        ( empty( $variation['trigger'] ) || $variation['trigger'] == 'page_load' ? '' : ' tve-trigger-hide' )
		        ) . $html;

		// if this is the main (default) state, we need to bring together all the other states
		$html .= apply_filters( 'tve_leads_variation_append_states', '', $variation ) . '</div>';
	}

	tve_leads_display_js_impression_data( 'php_insert' );

	return $html;
}

/**
 * fix for WP redirect_canonical causing infinite redirect loops if the site url has uppercase letter (e.g. http://www.SomeWebsiteName.com)
 * in conjuction with TL, wp will try to redirect each time http://www.somewebsitename.com  to http://www.SomeWebsiteName.com causing the loop
 *
 * @param string $redirect_url
 * @param string $requested_url
 *
 * @return string
 */
function tve_leads_canonical_url_lowercase( $redirect_url, $requested_url ) {
	$base       = site_url();
	$lowercased = strtolower( $base );


	return str_replace( $base, $lowercased, $redirect_url );
}

/**
 * entry point from the main ajax request from the dashboard
 * this forwards the call to tve_leads_ajax_load_forms
 *
 * @param array $current not used
 * @param array $post_data
 *
 * @see tve_dash_frontend_ajax_load
 *
 */
function tve_leads_dash_main_ajax_load_forms( $current, $post_data ) {
	return tve_leads_ajax_load_forms( true, $post_data );
}

/**
 * Make sure there is no document.write() executed after domready - it will overwrite the body content
 *
 * @param string $content
 *
 * @return string
 */
function tve_leads_prepare_ajax_html( $content ) {
	return str_replace( 'document.write', 'window.TL_Front && TL_Front.document_write', $content );
}

/**
 * load all the forms needed by a request, after DOM ready - this is a way to bypass the various caching plugins out there
 * POST params received:
 *      main_group_id => if a group has been detected to be shown on this page as per targeting options
 *      shortcode_ids => all the shortcodes that are to be rendered in the page
 *      two_step_ids => all the 2 step shortcodes to be included in the page
 *
 * this will split up the work into 3 steps:
 *  the Lead Group
 *  any number of 2-step shortcodes
 *  any number of Lead shortcodes
 *
 * @param bool       $return    whether to return or directly send the json-encoded data
 * @param array|null $post_data allows overriding $_POST data
 *
 * @return array|void based on the $return parameter
 */
function tve_leads_ajax_load_forms( $return = false, $post_data = null ) {
	define( 'TVE_AJAX_LOAD_FORM', 1 );

	$post_data = null !== $post_data ? $post_data : $_POST;

	$excluded = array(
		'action',
		'main_group_id',
		'tl_target_all',
		'tl_groups',
		'tl_form_type',
		'tl_period_type',
		'tl_period_days',
	);
	if ( ! empty( $post_data['get_data'] ) ) {
		foreach ( $post_data['get_data'] as $k => $v ) {
			if ( ! in_array( $k, $excluded, true ) ) {
				$_REQUEST[ $k ] = $_GET[ $k ] = $v;
			}
		}
	}
	global $tve_lead_group;

	$response = array(
		'res'        => array(
			'fonts' => array(),
			'css'   => array(),
			'js'    => array(),
		), // resources = fonts / CSS
		'html'       => array(), // form_output
		'js'         => array(), // javascript variables to use on conversion tracking
		'body_end'   => array(), // html / javascript to append to the <body> element
		'variations' => array(), // variations we're going to deliver
	);

	/**
	 * Which type of screen are we going to display the forms. We need this to register impressions.
	 */
	$current_screen = empty( $post_data['current_screen'] ) ? array() : $post_data['current_screen'];

	/**
	 * this holds all the form variations that are being sent to be included in the page
	 */
	$output_variations = array();

	/**
	 * configuration array for the triggers - to allow conditional output for trigger-related javascript
	 */
	$load_forms = array();

	/**
	 * Step 1: Lead Groups that need to be displayed
	 */
	if ( ! empty( $post_data['main_group_id'] ) ) {
		$group_id                = $post_data['main_group_id'];
		$inbound_link_cookie_key = 'tl_inbound_link_params_' . $group_id;
		if ( isset( $_COOKIE[ $inbound_link_cookie_key ] ) ) {
			$inbound_link_params = thrive_safe_unserialize( stripslashes( $_COOKIE[ $inbound_link_cookie_key ] ) );
			/* Compatibility with caching plugins - if a cookie is set, hide the forms from this lead group */
			if ( empty( $inbound_link_params['tl_form_type'] ) ) { // this means: completely hide the forms in this group
				$force_hide = true;
			}
		}

		if ( ! isset( $force_hide ) ) {
			/**
			 * #perf -> no need to get the variations here, they are gathered during the `tve_leads_get_targeted_form_types` below
			 */
			$group = tve_leads_get_group( $group_id, array(
				'get_variations' => false,
			) );
			if ( $group === null ) { // looks like an outdated request ?
				if ( $return ) {
					return array();
				}
				exit( '' );
			}
			$tve_lead_group                        = $group;
			$tve_lead_group->saved_display_options = isset( $post_data['display_options'] ) ? $post_data['display_options'] : array();

			$form_types_to_be_shown = tve_leads_get_targeted_form_types( $group );
		}

		if ( ! isset( $force_hide ) && ! empty( $form_types_to_be_shown ) ) {
			foreach ( $form_types_to_be_shown as $form_type ) {
				$_type = $form_type->tve_form_type;

				$variation = tve_leads_determine_variation( $form_type );
				if ( empty( $variation ) ) {
					continue;
				}

				$response['variations'][ $variation['post_parent'] ][] = $variation['key'];

				$GLOBALS['tve_lead_forms'][ $_type ]['variation'] = $variation;

				$GLOBALS['tve_lead_forms'][ $_type ]['form_output'] = tve_editor_custom_content( $variation );
				$display_fn                                         = 'tve_leads_display_form_' . $_type;
				$response['html'][ $_type ]                         = call_user_func( $display_fn, '__return_content' );
				if ( empty( $response['html'][ $_type ] ) ) {
					unset( $response['html'][ $_type ] );
					unset( $GLOBALS['tve_lead_forms'][ $_type ] );
					continue;
				}

				$response['html'][ $_type ] = preg_replace( '/__CONFIG_lead_generation_(.+?)__CONFIG_lead_generation_/ms', '', $response['html'][ $_type ] );
				$response['html'][ $_type ] = tve_leads_prepare_ajax_html( $response['html'][ $_type ] );

				/* also record any of the form types that are displayed to use in the conversion tracking mechanism */
				$response['js'][ $_type ] = array(
					'_key'             => $variation['key'],
					'form_name'        => $variation['post_title'],
					'trigger'          => $variation['trigger'],
					'trigger_config'   => ! empty( $variation['trigger_config'] ) ? $variation['trigger_config'] : new stdClass(),
					'form_type_id'     => $form_type ? $form_type->ID : '',
					'main_group_id'    => $group->ID,
					'main_group_name'  => $group->post_title,
					'active_test_id'   => ! empty( $variation['test_model'] ) ? $variation['test_model']->id : 0,
					'active_test_data' => ! empty( $variation['test_model'] ) ? tve_leads_get_test( $variation['test_model']->id ) : array(),
				);
				/**
				 * hold this for later on, when outputting the js for triggers
				 */
				$variation['form_id']   = $_type . '-' . $variation['key'];
				$variation['form_type'] = $_type;

				if ( ! tve_dash_is_crawler() ) {
					do_action( TVE_LEADS_ACTION_FORM_IMPRESSION, $group, $form_type, $variation, empty( $variation['test_model'] ) ? null : $variation['test_model']->id, $current_screen );
				}

				$load_forms[ $_type ] = true;

				if ( $_type == 'in_content' ) {
					$response['in_content_pos'] = $variation['position'];
				}

				$output_variations[ $_type ] = $variation;
			}
		}
	}

	/**
	 * step 2 - handle Leads shortcodes - [thrive_leads id="2343"]
	 */
	if ( ! empty( $post_data['shortcode_ids'] ) ) {
		foreach ( array_unique( $post_data['shortcode_ids'] ) as $id ) {
			$shortcode = tve_leads_get_shortcode( $id, array( 'get_variations' => true ) );
			if ( $shortcode === null ) {
				continue;
			}
			$variation = tve_leads_determine_variation( $shortcode );
			if ( empty( $variation ) ) {
				continue;
			}

			$response['variations'][ $variation['post_parent'] ][] = $variation['key'];

			list( $type, $key ) = explode( '|', $variation[ TVE_LEADS_FIELD_TEMPLATE ] );
			$key = preg_replace( '#_v(\d)+#', '', $key );

			$type_key = 'shortcode_' . $id;

			$form_output                   = preg_replace( '/__CONFIG_lead_generation_(.+?)__CONFIG_lead_generation_/ms', '', tve_editor_custom_content( $variation ) );
			$response['html'][ $type_key ] = tve_leads_display_form_shortcode( '__return_content', $form_output, $variation );
			if ( empty( $response['html'][ $type_key ] ) ) {
				unset( $response['html'][ $type_key ] );
				continue;
			}
			$response['html'][ $type_key ] = tve_leads_prepare_ajax_html( $response['html'][ $type_key ] );

			/* also record any of the form types that are displayed to use in the conversion tracking mechanism */
			$response['js'][ 'shortcode_' . $id ] = array(
				'_key'             => $variation['key'],
				'form_name'        => $variation['post_title'],
				'trigger'          => $variation['trigger'],
				'trigger_config'   => ! empty( $variation['trigger_config'] ) ? $variation['trigger_config'] : new stdClass(),
				'form_type_id'     => $shortcode->ID,
				'main_group_id'    => $shortcode->ID,
				'main_group_name'  => $shortcode->post_title,
				'active_test_id'   => ! empty( $variation['test_model'] ) ? $variation['test_model']->id : 0,
				'active_test_data' => ! empty( $variation['test_model'] ) ? tve_leads_get_test( $variation['test_model']->id ) : array(),
				'content_locking'  => $shortcode->content_locking,
				'has_conversion'   => tve_leads_check_conversion_cookie( $shortcode->ID ),
				'lock'             => $shortcode->content_locking && ! empty( $variation['display_animation'] ) && $variation['display_animation'] === 'blur' ? 'tve_lock_blur' : 'tve_lock_hide',
			);

			$variation['form_id']   = 'shortcode_' . $id;
			$variation['form_type'] = 'shortcode';

			$output_variations[ 'shortcode_' . $id ] = $variation;

			if ( ! tve_dash_is_crawler() ) {
				do_action( TVE_LEADS_ACTION_FORM_IMPRESSION, $shortcode, $shortcode, $variation, empty( $variation['test_model'] ) ? null : $variation['test_model']->id, $current_screen );
			}
		}
	}

	/**
	 * step3 - handle 2-Step Lightbox shortcodes
	 */
	if ( ! empty( $post_data['two_step_ids'] ) ) {

		foreach ( array_unique( $post_data['two_step_ids'] ) as $id ) {

			$two_step = tve_leads_get_form_type( $id, array( 'get_variations' => true ) );
			if ( $two_step === null ) {
				continue;
			}

			$variation = tve_leads_determine_variation( $two_step );
			if ( empty( $variation ) ) {
				continue;
			}

			$response['variations'][ $variation['post_parent'] ][] = $variation['key'];

			$form_output = sprintf(
				'<div class="tve-leads-conversion-object" data-tl-type="two_step_%s">%s</div>',
				$id,
				tve_editor_custom_content( $variation )
			);

			//determine the variation template type and get the html accordingly
			if ( isset( $variation['tpl'] ) && strpos( $variation['tpl'], 'screen_filler' ) !== false ) {
				$response['html'][ 'two_step_' . $id ] = tve_leads_display_form_screen_filler( '__return_content', $form_output, $variation, array(), 'tve-leads-track-2step-' . $variation['key'], true );
				$variation['form_type']                = 'screen_filler';
			} else {
				$response['html'][ 'two_step_' . $id ] = tve_leads_display_form_lightbox( '__return_content', $form_output, $variation, 'tve-leads-track-2step-' . $variation['key'], null, array(), true );
				$variation['form_type']                = 'lightbox';
			}

			if ( empty( $response['html'][ 'two_step_' . $id ] ) ) {
				unset( $response['html'][ 'two_step_' . $id ] );
				continue;
			}

			$response['html'][ 'two_step_' . $id ] = preg_replace( '/__CONFIG_lead_generation_(.+?)__CONFIG_lead_generation_/ms', '', $response['html'][ 'two_step_' . $id ] );
			$response['html'][ 'two_step_' . $id ] = tve_leads_prepare_ajax_html( $response['html'][ 'two_step_' . $id ] );
			$variation['form_id']                  = '2step-' . $variation['key'];

			/**
			 * the trigger will always be a click event, and the element that receives the click already exists in the page
			 */
			$variation['trigger']        = 'click';
			$variation['trigger_config'] = array( 'c' => 'tl-2step-trigger-' . $id );

			/* also record any of the form types that are displayed to use in the conversion tracking mechanism */
			$response['js'][ 'two_step_' . $id ] = array(
				'_key'             => $variation['key'],
				'form_name'        => $variation['post_title'],
				'trigger'          => $variation['trigger'],
				'trigger_config'   => ! empty( $variation['trigger_config'] ) ? $variation['trigger_config'] : new stdClass(),
				'form_type_id'     => $two_step->ID,
				'main_group_id'    => $two_step->ID,
				'main_group_name'  => $two_step->post_title,
				'active_test_id'   => ! empty( $variation['test_model'] ) ? $variation['test_model']->id : 0,
				'active_test_data' => ! empty( $variation['test_model'] ) ? tve_leads_get_test( $variation['test_model']->id ) : array(),
			);

			$output_variations[ 'two_step_' . $id ] = $variation;
			if ( ! tve_dash_is_crawler() ) {
				do_action( TVE_LEADS_ACTION_FORM_IMPRESSION, $two_step, $two_step, $variation, empty( $variation['test_model'] ) ? null : $variation['test_model']->id, $current_screen );
			}
		}
	}

	if ( ! empty( $output_variations ) ) {

		/**
		 * javascript for the triggers
		 */
		ob_start();
		$output_variations = apply_filters( 'tve_leads_append_states_ajax', $output_variations );

		foreach ( $output_variations as $type => $variation ) {
			/**
			 * also send any css / fonts that are needed
			 */
			tve_leads_enqueue_variation_scripts( $variation );

			/**
			 * the triggers should only be included for the default state of a variation
			 */
			if ( empty( $variation['parent_id'] ) && ! empty( $variation['form_id'] ) ) {
				tve_leads_output_trigger_js( $variation, $variation['form_id'], $variation['form_type'] );
			}

			$response['variations'][ $variation['post_parent'] ][] = $variation['key'];
		}
		/**
		 * this should output all the required javascript (external scripts and localization) that would normally go into the footer
		 */
		$wp_scripts = wp_scripts();
		foreach ( $wp_scripts->queue as $handle ) {
			if ( $handle === 'tve_frontend' && ! empty( $_POST['tcb_js'] ) ) {
				@wp_deregister_script( $handle );
				@wp_dequeue_script( $handle );
			}
			/**
			 * Dequeue and deregister any other scripts that are not needed for Thrive Leads forms
			 */
			if ( $handle !== 'plupload' && strpos( $handle, 'tve' ) === false && strpos( $handle, 'tcb' ) === false && strpos( $handle, 'tqb' ) === false ) {
				@wp_deregister_script( $handle );
				@wp_dequeue_script( $handle );
			}
		}
		do_action( 'wp_print_footer_scripts' );
		$response['body_end'] = ob_get_clean();
	}

	/**
	 * TAR-1190 Make sure the contents do not include a document.write() call in a script - it will cause the page to go blank when executed
	 */
	$response['body_end'] = tve_leads_prepare_ajax_html( $response['body_end'] );

	/**
	 * Any other resources that might have been included (e.g. from the already subscribed state
	 */
	if ( ! empty( $GLOBALS['tve_leads_ajax_load_res_global'] ) ) {
		$response['res']['js']    = array_merge( $response['res']['js'], $GLOBALS['tve_leads_ajax_load_res_global']['js'] );
		$response['res']['fonts'] = array_merge( $response['res']['fonts'], $GLOBALS['tve_leads_ajax_load_res_global']['fonts'] );
		$response['res']['css']   = array_merge( $response['res']['css'], $GLOBALS['tve_leads_ajax_load_res_global']['css'] );
	}

	$response['res']['js']    = array_unique( $response['res']['js'] );
	$response['res']['fonts'] = array_unique( $response['res']['fonts'] );
	$response['res']['css']   = array_unique( $response['res']['css'] );

	$wp_styles  = wp_styles();
	$wp_scripts = wp_scripts();
	/**
	 * if the icon pack needs to be loaded, include this also
	 */
	if ( wp_style_is( 'thrive_icon_pack' ) ) {
		/** @var _WP_Dependency $dep */
		$dep                                          = $wp_styles->registered['thrive_icon_pack'];
		$response['res']['fonts']['thrive_icon_pack'] = $wp_styles->_css_href( $dep->src, $dep->ver, 'thrive_icon_pack' );
	}
	if ( wp_style_is( 'thrive_events' ) ) {
		$dep                                     = $wp_styles->registered['thrive_events'];
		$response['res']['css']['thrive_events'] = $wp_styles->_css_href( $dep->src, $dep->ver, 'thrive_events' );
	}
	if ( wp_script_is( 'tl-wistia-popover' ) ) {
		$response['res']['js']['tl-wistia-popover'] = '//fast.wistia.com/assets/external/E-v1.js';
	}
	if ( wp_style_is( 'mediaelement' ) && ( $data = $wp_styles->query( 'mediaelement' ) ) ) {
		$url = $data->ver ? add_query_arg( 'ver', $data->ver, $data->src ) : $data->src;
		if ( ! preg_match( '|^(https?:)?//|', $url ) ) {
			$url = $wp_styles->base_url . $url;
		}
		$response['res']['css']['mediaelement'] = $url;
	}
	if ( wp_script_is( 'mediaelement' ) && ( $data = $wp_scripts->query( 'mediaelement' ) ) ) {
		$url = $data->ver ? add_query_arg( 'ver', $data->ver, $data->src ) : $data->src;
		if ( ! preg_match( '|^(https?:)?//|', $url ) ) {
			$url = $wp_scripts->base_url . $url;
		}
		$response['res']['js']['mediaelement'] = $url;
	}

	$response = apply_filters( 'tve_leads_ajax_load_forms', $response );

	if ( $return ) {
		return $response;
	}

	exit( json_encode( $response ) );
}

/**
 * append the form variation title on editor and preview pages
 *
 * @param string $title
 *
 * @return string $title
 */
function tve_leads_editor_page_title( $title ) {
	if ( isset( $_GET['_key'] ) && ( isset( $_GET[ TVE_EDITOR_FLAG ] ) || tve_leads_is_preview_page() ) ) {
		global $variation;
		$args = func_get_args();
		$sep  = isset( $args[1] ) ? $args[1] : ' ';
		if ( ! empty( $variation ) && ! empty( $variation['post_title'] ) ) {
			return $title . ' ' . $sep . ' ' . $variation['post_title'];
		}
	}

	return $title;
}

/**
 * Append the content with and element to be used in JS
 * Used for scrolling triggers
 *
 * @param $content
 *
 * @return string
 * @see scroll_percent.js.php
 *
 */
function tve_leads_filter_end_content( $content ) {
	/* make sure we only output this once per page */
	if ( ! is_singular() || ! in_the_loop() || isset( $GLOBALS['tve_leads_end_content'] ) ) {
		return $content;
	}

	if ( empty( $GLOBALS['tve_lead_forms'] ) && ( ! tve_leads_is_preview_page() || tve_leads_is_editor_page() ) ) {
		/* if we don't have forms, we don't display the end of content span */
		return $content;
	}

	$GLOBALS['tve_leads_end_content'] = true;
	$content                          .= '<span id="tve_leads_end_content" style="display: block; visibility: hidden; border: 1px solid transparent;"></span>';

	return $content;
}

/**
 * Set the path where the translation files are being kept
 */
function tve_leads_load_plugin_textdomain() {
	$domain = 'thrive-leads';
	$locale = $locale = apply_filters( 'plugin_locale', get_locale(), $domain );
	$path   = 'thrive-leads/languages/';
	load_textdomain( $domain, WP_LANG_DIR . '/thrive/' . $domain . "-" . $locale . ".mo" );
	load_plugin_textdomain( $domain, false, $path );
}


/**
 * set cookies for the inbound link functionality
 */
function tve_leads_set_inbound_link_cookies() {
	//needed for backwards compatibility for the old inbound links (newly named = smartlinks)
	tve_convert_old_inbound_link_cookies();

	//prepare the inbound link params and save them as cookies
	if ( isset( $_REQUEST['tl_inbound'] ) ) {
		$expected    = array(
			'tl_target_all',
			'tl_groups',
			'tl_form_type',
			'tl_period_type',
			'tl_period_days',
		);
		$cookie_data = array();
		foreach ( $expected as $field ) {
			$cookie_data[ $field ] = '';
			if ( isset( $_REQUEST[ $field ] ) ) {
				$data = $_REQUEST[ $field ];
				if ( is_array( $data ) ) {
					$data = array_map( 'stripslashes', $data );
				} else {
					$data = stripslashes( $data );
				}
				$cookie_data[ $field ] = thrive_safe_unserialize( $data );
			}
		}
		if ( $cookie_data['tl_target_all'] ) {
			$groups = tve_leads_get_group_ids();
		} else {
			$groups = isset( $cookie_data['tl_groups'] ) ? $cookie_data['tl_groups'] : array();
		}
		tve_save_inbound_link_cookies( $groups, $cookie_data );
	}
}

/**
 * backwards compatibility for the old inbound links (newly named = smartlinks)
 * convert old inbound link cookies to the new format of inbound link cookies
 */
function tve_convert_old_inbound_link_cookies() {
	if ( isset( $_COOKIE['tl_use_inbound_link_params'] ) && isset( $_COOKIE['tl_inbound_link_params'] ) ) {
		$params = thrive_safe_unserialize( stripslashes( $_COOKIE['tl_inbound_link_params'] ) );
		if ( $params['tl_target_all'] == 1 ) {
			$groups = tve_leads_get_group_ids();
		} else {
			$groups = isset( $params['tl_groups'] ) ? $params['tl_groups'] : array();
		}
		tve_save_inbound_link_cookies( $groups, $params );
		unset( $_COOKIE['tl_use_inbound_link_params'] );
		unset( $_COOKIE['tl_inbound_link_params'] );
		setcookie( 'tl_use_inbound_link_params', null, - 1, '/' );
		setcookie( 'tl_inbound_link_params', null, - 1, '/' );
	}
}

/**
 * save the inbound link cookies having the group ids and the display params
 *
 * @param $groups
 * @param $cookie_data
 */
function tve_save_inbound_link_cookies( $groups, $cookie_data ) {
	$expires = time() + 3;
	switch ( $cookie_data['tl_period_type'] ) {
		case '':
			//Until the visitor closes the browser tab
			$expires = time() + 3;
			break;
		case '1':
			//Only once
			$expires = 0;
			break;
		case '2':
			//A custom period of time
			$expires = time() + ( (int) $cookie_data['tl_period_days'] * 24 * 3600 );
			break;
		case '3':
			//For as long as possible -> added 1+ year
			$expires = time() + YEAR_IN_SECONDS;
			break;
	}

	/**
	 * If all forms are targeted, save this flag in the cookie, so we can later check this condition for shortocdes
	 * This only works if all forms are targeted and the display option is "Show already subscribed state"
	 */
	if ( ! empty( $cookie_data['tl_target_all'] ) && (int) $cookie_data['tl_form_type'] === 1 ) {
		setcookie( 'tl_inbound_target_all', 1, $expires, '/' );
		$_COOKIE['tl_inbound_target_all'] = 1;
	}

	if ( ! empty( $groups ) ) {
		foreach ( $groups as $group ) {
			$groupParams       = array(
				'tl_form_type'   => $cookie_data['tl_form_type'],
				'tl_period_type' => $cookie_data['tl_period_type'],
				'tl_period_days' => $cookie_data['tl_period_days'],
			);
			$params            = serialize( $groupParams );
			$group_cookie_name = 'tl_inbound_link_params_' . $group;
			setcookie( $group_cookie_name, $params, $expires, '/' );

			$_COOKIE[ $group_cookie_name ] = $params;
		}
	}
}

/**
 * Filter before and after params for TL Widget
 *
 * @param $params
 *
 * @return mixed
 */
function thrive_dynamic_sidebar_params( $params ) {
	if ( ! tve_check_if_thrive_theme() ) {
		return $params;
	}
	/**
	 * on our themes, we need to remove any other inside div in order for the widget to have the correct padding
	 */
	if ( $params[0]['widget_name'] === 'Thrive Leads Widget' ) {
		$params[0]['before_widget'] = '<section id="' . $params[0]['widget_id'] . '">';
		$params[0]['after_widget']  = '</section>';
	}

	return $params;
}

/**
 * Searches for ThriveBox Menu Item and apply some logic on them
 * Does not display subitems
 * Unset the menu item object if the ThriveBox does not have content
 *  - already subscribed state is hidden
 *  - TB does not have any form variation set
 *
 * @param $menu_items
 *
 * @return mixed
 */
function tve_leads_wp_nav_menu_objects( $menu_items ) {
	$is_editor_page = is_editor_page_raw( true );

	$GLOBALS['tve_leads_rendered_two_step_ids'] = empty( $GLOBALS['tve_leads_rendered_two_step_ids'] ) ? array() : $GLOBALS['tve_leads_rendered_two_step_ids'];
	$tb_menu_item_ids                           = array();
	$ajax_load_forms                            = tve_leads_get_option( 'ajax_load' );

	foreach ( $menu_items as $key => $item ) {

		//do not render children
		if ( in_array( $item->menu_item_parent, $tb_menu_item_ids ) ) {
			unset( $menu_items[ $key ] );
			continue;
		}

		//continue if not TB menu item
		if ( ! property_exists( $item, 'object' ) || $item->object !== TVE_LEADS_POST_TWO_STEP_LIGHTBOX ) {
			continue;
		}

		//remove css class for has children
		if ( $class_key = array_search( 'menu-item-has-children', $item->classes ) ) {
			unset( $item->classes[ $class_key ] );
		}

		$item->classes[] = 'tve-leads-two-step-trigger';
		$item->classes[] = 'tl-2step-trigger-' . $item->object_id;
		$item->url       = 'javascript:void(0)';

		//save the id to see if next items are children of current item
		$tb_menu_item_ids[] = $item->ID;

		/* also store a data-tcb-events property in the menu item */
		$item->thrive_events = sprintf(
			'__TCB_EVENT_[{"config":{"l_id":"%s"},"a":"thrive_leads_2_step","t":"click"}]_TNEVE_BCT__',
			$item->object_id
		);

		/* do not render thriveboxes in the editor */
		if ( $is_editor_page ) {
			continue;
		}

		//for cases when a menu item is set to be displayed on more than 1 menu
		//e.g. top menu and/or footer menu
		$already_rendered = in_array( $item->object_id, $GLOBALS['tve_leads_rendered_two_step_ids'] );
		if ( $already_rendered ) {
			continue;
		}

		if ( $already_rendered === false ) {
			$content = tve_leads_two_step_render( array( 'id' => $item->object_id ), '' );
		}
		if ( empty( $content ) && ! $ajax_load_forms ) { //and not ajax load
			unset( $menu_items[ $key ] );
		}

		//stack TB id
		$GLOBALS['tve_leads_rendered_two_step_ids'][] = $item->object_id;
	}

	return $menu_items;
}

/**
 * accessing the one click signup link, subscribeing the user(name, email) to the selected apis, update the conversions (new name: Signup Segue)
 */
function tve_leads_one_click_signup() {
	$data = $_REQUEST;
	if ( ! empty( $data ) && tve_leads_with_value( $data, 'post_type' ) && $data['post_type'] == TVE_LEADS_POST_ONE_CLICK_SIGNUP ) {
		$postId          = get_the_ID();
		$api_connections = get_post_meta( $postId, 'tve_leads_api_connections', true );
		if ( tve_leads_is_one_click_signup_valid( $postId, $data ) ) {
			$available           = Thrive_List_Manager::get_available_apis( true );
			$available_api_names = array();
			foreach ( $available as $key => $connection ) {
				array_push( $available_api_names, $key );
			}
			$result       = array();
			$extra_fields = array();
			foreach ( $api_connections as $key => $connection ) {
				if ( ! in_array( $connection['apiName'], $available_api_names ) ) {
					continue;
				}
				$extra_fields = $connection;
				unset( $extra_fields['apiName'] );
				unset( $extra_fields['list'] );
				foreach ( $extra_fields as $k => $field ) {
					$extra_fields[ $k ] = urldecode( $field );
				}

				$subscriber_data = array( "name" => $data['tl_name'], "email" => $data['tl_email'] );
				$subscriber_data = array_merge( $subscriber_data, $extra_fields );
				$result[ $key ]  = tve_api_add_subscriber( $connection['apiName'], $connection['list'], $subscriber_data );
			}

			/* Update signup count */
			$signups = (int) get_post_meta( $postId, 'tve_leads_signups', true ) + 1;
			update_post_meta( $postId, 'tve_leads_signups', $signups );

			/* Here we save log data for the one click conversion. More or less is the same as the normal conversion, but with less data */
			global $tvedb;
			$current_screen = tve_get_current_screen();
			$event_log      = array(
				'event_type'    => TVE_LEADS_ONE_CLICK_CONVERSION,
				'main_group_id' => (int) $postId,
				'form_type_id'  => (int) $postId,
				'variation_key' => '',
				'user'          => sanitize_email( $data['tl_email'] ),
			);
			$event_log      = array_merge( $event_log, $current_screen );

			$referrer = isset( $data['http_referrer'] ) ? esc_url_raw( $data['http_referrer'] ) : '';

			if ( $referrer ) {
				$trimmed = preg_replace( '#http(s)?://#', '', trim( $referrer, '/' ) );
				$host    = preg_replace( '#http(s)?://#', '', trim( $_SERVER['HTTP_HOST'], '/' ) );
				if ( strpos( $trimmed, $host ) !== 0 ) { /* the referrer is different than the current domain */
					$event_log['referrer'] = $referrer;
				}
			}

			foreach ( array( 'utm_source', 'utm_medium', 'utm_campaign' ) as $_tracking_field ) {
				if ( ! empty( $data[ $_tracking_field ] ) ) {
					$event_log[ $_tracking_field ] = sanitize_text_field( $data[ $_tracking_field ] );
				}
			}

			$log_id = $tvedb->insert_event( $event_log, null, false );

			/* If the user doesn't exist in the contact list, we add it there */
			$user_exists = $tvedb->tve_get_contact( 'email', $data['tl_email'] );
			if ( empty( $user_exists ) ) {
				$tvedb->tve_leads_register_contact( $log_id, $data['tl_name'], $data['tl_email'], $extra_fields );
			}

			//redirect is needed even if the subscribe wasn't successfully done
			tve_leads_redirect_after_subscribe( $postId );
		}
	}
}

/**
 * validating the one click signup link's parameters (new name: Signup Segue)
 * return true/false
 *
 * @param $postId
 * @param $data
 *
 * @return bool
 */
function tve_leads_is_one_click_signup_valid( $postId, $data ) {
	$redirect_url    = get_post_meta( $postId, 'tve_leads_redirect_url', true );
	$api_connections = get_post_meta( $postId, 'tve_leads_api_connections', true );
	if ( ! empty( $redirect_url ) ) {
		if ( $redirect_url['type'] == 'single-redirect' ) {
			$valid = tve_leads_with_value( $redirect_url, 'single' ) ? true : false;
		} else {
			$valid = tve_leads_with_value( $redirect_url, 'before' ) || tve_leads_with_value( $redirect_url, 'during' ) || tve_leads_with_value( $redirect_url, 'after' ) ? true : false;
		}
	} else {
		$valid = false;
	}
	$valid &= empty( $api_connections ) ? false : true;
	/* SUPP-3008 tl_name should not be required */
	$valid &= ! tve_leads_with_value( $data, 'tl_email' ) || $data['tl_email'] == '[email]' || ! filter_var( $data['tl_email'], FILTER_VALIDATE_EMAIL ) ? false : true;

	return $valid;
}

/**
 * filter for validating the one click signup link's parameters (new name: Signup Segue)
 * return error messages if it's not valid
 *
 * @return string
 */
function tve_leads_one_click_signup_validation( $content ) {
	$data = $_REQUEST;
	if ( ! empty( $data ) && tve_leads_with_value( $data, 'post_type' ) && $data['post_type'] == TVE_LEADS_POST_ONE_CLICK_SIGNUP ) {
		$postId          = get_the_ID();
		$redirect_url    = get_post_meta( $postId, 'tve_leads_redirect_url', true );
		$api_connections = get_post_meta( $postId, 'tve_leads_api_connections', true );
		if ( ! empty( $redirect_url ) ) {
			if ( $redirect_url['type'] == 'single-redirect' ) {
				$returnErrorMsg = ! tve_leads_with_value( $redirect_url, 'single' ) ? true : false;
			} else {
				$returnErrorMsg = ! tve_leads_with_value( $redirect_url, 'before' ) && ! tve_leads_with_value( $redirect_url, 'during' ) && ! tve_leads_with_value( $redirect_url, 'after' ) ? true : false;
			}
		} else {
			$returnErrorMsg = true;
		}
		if ( $returnErrorMsg ) {
			$thrive_leads_dashboard = '<a href="' . admin_url( 'admin.php?page=thrive_leads_dashboard#dashboard' ) . '">' . __( 'Thrive Leads', 'thrive-leads' ) . '</a>';

			return "<p>" . __( "Error: You haven't specified a thank you page.  Visit the ", 'thrive-leads' ) . $thrive_leads_dashboard . __( " dashboard to set up your redirect links.", 'thrive-leads' ) . "</p>";
		}

		if ( empty( $api_connections ) ) {
			$thrive_leads_dashboard = '<a href="' . admin_url( 'admin.php?page=thrive_leads_dashboard#dashboard' ) . '">' . __( 'Thrive Leads', 'thrive-leads' ) . '</a>';

			return "<p>" . __( "Error: You haven't connected to a service, visit the ", 'thrive-leads' ) . $thrive_leads_dashboard . __( " dashboard to connect to a service.", 'thrive-leads' ) . "</p>";
		}

		if ( ! empty( $data['tl_name'] ) && $data['tl_name'] == '[name]' ) {
			return "<p>" . __( "Error: Registration has failed because the signup link is incorrect. tl_name is invalid", 'thrive-leads' ) . "</p><p>" . __( "Replace the [name] value of the tl_name parameter with a valid name", 'thrive-leads' ) . "</p>";
		}

		if ( ! tve_leads_with_value( $data, 'tl_email' ) || $data['tl_email'] == '[email]' || ! filter_var( $data['tl_email'], FILTER_VALIDATE_EMAIL ) ) {
			return "<p>" . __( "Error: Registration has failed because the signup link is incorrect. tl_email is invalid", 'thrive-leads' ) . "</p><p>" . __( "Replace the [email] value of the tl_email parameter with a valid email address", 'thrive-leads' ) . "</p>";
		}
	}

	return $content;
}

/**
 * checks if key is existing and it's not empty from array
 *
 * @param $data
 * @param $key
 *
 * @return bool
 */
function tve_leads_with_value( $data, $key ) {
	return isset( $data[ $key ] ) && ( $data[ $key ] != '' ) ? true : false;
}

/**
 * filter to check if the one click signup link is accessed (new name: Signup Segue)
 * if yes, the Thrive Architect's auto responder file should be included (so, return true)
 *
 * @return bool
 */
function tve_leads_include_auto_responder_file( $value ) {
	if ( $value ) {
		return true;
	}
	$data = $_REQUEST;
	if ( ! empty( $data ) && tve_leads_with_value( $data, 'post_type' ) && $data['post_type'] == TVE_LEADS_POST_ONE_CLICK_SIGNUP ) {
		$postId = $data['p'];
		if ( tve_leads_is_one_click_signup_valid( $postId, $data ) ) {
			return true;
		}
	}

	return $value;
}

/**
 * redirect functionality for the one click signup link (new name: Signup Segue)
 *
 * @param $postId
 */
function tve_leads_redirect_after_subscribe( $postId ) {
	$redirect_url = get_post_meta( $postId, 'tve_leads_redirect_url', true );
	if ( $redirect_url['type'] == 'single-redirect' ) {
		$url = tve_leads_with_value( $redirect_url, 'single' ) ? $redirect_url['single'] : '';
	} else {
		$timezone          = new DateTimeZone( tve_get_timezone_string() );
		$start_date_string = $redirect_url['event_start_date'] . " " . $redirect_url['event_start_time'] . ":00";
		$start_date        = new DateTime( $start_date_string, $timezone );
		$end_date          = new DateTime( $start_date_string, $timezone );

		$duration = explode( ":", $redirect_url['event_duration_time'] );
		$end_date->modify( "+{$duration[0]} hours" );
		$end_date->modify( "+{$duration[1]} minutes" );
		$current_date = new DateTime( current_time( 'Y-m-d H:i:s', get_option( 'gmt_offset' ) ), $timezone );

		if ( $current_date < $start_date ) {
			$url = tve_leads_with_value( $redirect_url, 'before' ) ? $redirect_url['before'] : '';
		} else if ( $current_date > $end_date ) {
			$url = tve_leads_with_value( $redirect_url, 'after' ) ? $redirect_url['after'] : '';
		} else {
			$url = tve_leads_with_value( $redirect_url, 'during' ) ? $redirect_url['during'] : '';
		}
	}

	header( 'Location: ' . $url );
	exit;
}

function tve_leads_load_dash_version() {
	$tve_dash_path      = dirname( dirname( __FILE__ ) ) . '/thrive-dashboard';
	$tve_dash_file_path = $tve_dash_path . '/version.php';

	if ( is_file( $tve_dash_file_path ) ) {
		$version                                  = require_once( $tve_dash_file_path );
		$GLOBALS['tve_dash_versions'][ $version ] = array(
			'path'   => $tve_dash_path . '/thrive-dashboard.php',
			'folder' => '/thrive-leads',
			'from'   => 'plugins',
		);
	}
}

/**
 * make sure the TL_product is displayed in thrive dashboard
 *
 * @param array $items
 *
 * @return array
 */
function tve_leads_add_to_dashboard( $items ) {
	$items[] = new TL_Product();

	return $items;
}

/**
 * Initialize the UpdateChecker
 */
function tve_leads_update_checker() {
	new TVE_PluginUpdateChecker(
		'http://service-api.thrivethemes.com/plugin/update',
		dirname( dirname( __FILE__ ) ) . '/thrive-leads.php',
		'thrive-leads',
		12,
		'',
		'thrive_leads'
	);
	add_filter( 'puc_request_info_result-thrive-leads', 'tve_leads_set_product_icon' );
}

/**
 * Adding the product icon for the update core page
 *
 * @param $info
 *
 * @return mixed
 */
function tve_leads_set_product_icon( $info ) {
	$info->icons['1x'] = TVE_LEADS_ADMIN_URL . 'img/thrive-leads-logo.png';

	return $info;
}

/**
 * Append some html for TU main ajax call
 *
 * @param string $html
 *
 * @return string
 */
function tve_leads_filter_tu_body_end( $html ) {

	ob_start();
	tve_leads_print_footer_scripts();
	$html .= ob_get_clean();

	return $html;
}

/**
 * Gets the inconclusive tests
 */
function tve_get_inconclusive_tests() {
	$running_tests = tve_get_running_inconclusive_tests();

	if ( is_array( $running_tests ) && ! empty( $running_tests ) ) {
		wp_enqueue_script( 'tve-inconclusive-tests-js', plugins_url( 'thrive-leads/admin/js-min/inconclusive_tests.min.js' ) );


		$thrive_leads_special_routes = array(
			'routes' => array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
			),
		);
		wp_localize_script( 'tve-inconclusive-tests-js', 'ThriveLeadsInconclusive', $thrive_leads_special_routes );

		add_action( 'admin_notices', 'tve_inconclusive_tests_notice' );


	}
}

/**
 * Hook into TD Notification Manager and push trigger types
 *
 * @param $trigger_types
 *
 * @return array
 */
function tve_leads_filter_nm_trigger_types( $trigger_types ) {

	if ( ! in_array( 'email_sign_up', array_keys( $trigger_types ) ) ) {
		$trigger_types['email_sign_up'] = __( 'Email Sign up', 'thrive-leads' );
	}

	if ( ! in_array( 'split_test_ends', array_keys( $trigger_types ) ) ) {
		$trigger_types['split_test_ends'] = __( 'A/B Test Ends', 'trigger-leads' );
	}

	return $trigger_types;
}

/**
 * Require the Thrive_Leads_Cloud_Templates_Api.php but after its parent class is loaded
 */
function tve_leads_init_cloud_templates_api() {
	require_once TVE_LEADS_PATH . 'inc/classes/Thrive_Leads_Cloud_Templates_Api.php';
}

/**
 * Add post types that you want to be excluded from google index
 *
 * @param array $post_types
 *
 * @return array
 */
function tve_exclude_post_types_from_index( $post_types ) {
	$post_types[] = TVE_LEADS_POST_FORM_TYPE;
	$post_types[] = TVE_LEADS_POST_GROUP_TYPE;
	$post_types[] = TVE_LEADS_POST_SHORTCODE_TYPE;
	$post_types[] = TVE_LEADS_POST_TWO_STEP_LIGHTBOX;
	$post_types[] = TVE_LEADS_POST_ASSET_GROUP;
	$post_types[] = TVE_LEADS_POST_ONE_CLICK_SIGNUP;

	return $post_types;
}

/**
 * When editing lead forms, the base selector is the body, not #tve_editor
 *
 * @param $selector
 *
 * @return string
 */
/* this is not used at the moment.
function tve_editor_selection_root( $selector ) {

	switch ( get_post_type() ) {
		case TVE_LEADS_POST_FORM_TYPE:
		case TVE_LEADS_POST_SHORTCODE_TYPE:
		case TVE_LEADS_POST_TWO_STEP_LIGHTBOX:

			$selector = 'body';
			break;
	}

	return $selector;
} */

/**
 * Called on plugin activation.
 * Check for minimum required WordPress version
 */
function thrive_leads_activation_hook() {
	if ( function_exists( 'tcb_wordpress_version_check' ) && ! tcb_wordpress_version_check() ) {
		/**
		 * Dashboard not loaded yet, force it to load here
		 */
		if ( ! function_exists( 'tve_dash_show_activation_error' ) ) {
			/* Load the dashboard included in this plugin */
			tve_leads_load_dash_version();
			tve_dash_load();
		}

		tve_dash_show_activation_error( 'wp_version', 'Thrive Leads', TCB_MIN_WP_VERSION );
	} else {
		if ( method_exists( '\TCB\Lightspeed\Main', 'first_time_enable_lightspeed' ) ) {
			\TCB\Lightspeed\Main::first_time_enable_lightspeed();
		}
	}
}

/**
 * Called after dash has been loaded
 */
function tve_leads_dashboard_loaded() {
	require_once plugin_dir_path( __FILE__ ) . 'classes/Thrive_Leads_TL_Product.php';
}

/**
 * Add some Leads post types to Architect Post Grid Element Banned Types
 *
 * @param array $banned_types
 *
 * @return array
 */
function tve_leads_add_post_grid_banned_types( $banned_types = array() ) {
	$banned_types[] = 'tve_lead_shortcode';
	$banned_types[] = 'tve_lead_2s_lightbox';
	$banned_types[] = 'tve_form_type';
	$banned_types[] = 'tve_lead_group';
	$banned_types[] = 'tve_lead_1c_signup';

	return $banned_types;
}

/**
 * Replace editor selector for the lightboxes so we can select the TL Element which is outside #tve_editor by default
 *
 * @param $editor_selector
 *
 * @return string
 */
function tve_leads_editor_selector( $editor_selector ) {
	global $post;
	if ( ! empty( $post ) && ( ( $post->post_type === TVE_LEADS_POST_FORM_TYPE && get_post_meta( $post->ID, 'tve_form_type', true ) === 'lightbox' ) || $post->post_type === TVE_LEADS_POST_TWO_STEP_LIGHTBOX ) ) {
		$editor_selector = 'body';
	}

	return $editor_selector;
}

/**
 * Exclude the js dist folder from caching and minify-ing for WP-Rocket
 *
 * @param $excluded_js
 *
 * @return array
 */
function tve_leads_rocket_exclude_js( $excluded_js ) {
	$home_url = home_url();

	$excluded_js[] = str_replace( $home_url, '', plugins_url( '/thrive-leads/admin/js-min' ) ) . '/(.*).js';
	$excluded_js[] = str_replace( $home_url, '', plugins_url( '/thrive-leads/js' ) ) . '/(.*).js';

	return $excluded_js;
}

/**
 * Exclude the css files from caching and minify-ing for WP-Rocket
 *
 * @param $excluded_css
 *
 * @return array
 */
function tve_leads_rocket_exclude_css( $excluded_css ) {
	$home_url = home_url();

	$excluded_css[] = str_replace( $home_url, '', plugins_url( '/thrive-leads/editor-layouts/css' ) ) . '/(.*).css';
	$excluded_css[] = str_replace( $home_url, '', plugins_url( '/thrive-leads/admin/css' ) ) . '/(.*).css';

	return $excluded_css;
}

/**
 * Add thrive-leads as a fake cache plugin to force_ajax_send
 * Used to prevent server-side caching while the site is visited by a bot
 *
 * @param $known_plugins
 *
 * @return array
 */
function tve_leads_detect_cache( $known_plugins ) {

	$known_plugins[] = 'thrive-leads/thrive-leads.php';

	return $known_plugins;
}


/**
 * Post visibility options blacklist
 *
 * @param $post_types
 *
 * @return array
 */
function tve_leads_post_visibility_options( $post_types ) {
	$post_types = array_merge( $post_types, array(
		TVE_LEADS_POST_FORM_TYPE,
		TVE_LEADS_POST_GROUP_TYPE,
		TVE_LEADS_POST_SHORTCODE_TYPE,
		TVE_LEADS_POST_TWO_STEP_LIGHTBOX,
		TVE_LEADS_POST_ONE_CLICK_SIGNUP,
	) );

	return $post_types;
}

/**
 * Fires an action that can be hooked by 3rd party code. Triggered only when lazy-loading is active,
 * during the main request (when generating placeholders for forms)
 *
 * @param string $form_type_key key from the $GLOBALS array storing the form data
 */
function tve_leads_prepare_variation_hook( $form_type_key ) {
	if ( ! empty( $GLOBALS['tve_lead_forms'][ $form_type_key ]['form_type'] ) ) {
		$form_type = $GLOBALS['tve_lead_forms'][ $form_type_key ]['form_type'];

		if ( ! empty( $form_type->variations ) ) {
			foreach ( $form_type->variations as $possible_variation ) {
				/**
				 * Action - allows hooking into the main request for when lazy-load is enabled.
				 * Offers possibility to 3rd party plugins to enqueue and prepare scripts during the main html output
				 *
				 * @param array   $possible_variation one of the possible variations to be displayed
				 * @param WP_Post $form_type          corresponding form type
				 */
				do_action( 'tve_leads_ajax_load_prepare_variation', $possible_variation, $form_type );
			}
		}
	}
}

/**
 * Search thrive leads design variations if they have a specific string in their architect content
 */
add_filter( 'tcb_architect_content_has_string', static function ( $has_string, $string, $post_id ) {
	if ( ! $has_string ) {
		global $tvedb;
		if ( $tvedb->search_string_in_designs( $string ) ) {
			$has_string = true;
		}
	}

	return $has_string;
}, 14, 3 );

/**
 * Add info article url for Leads Shortcode element
 */
add_filter( 'thrive_kb_articles', static function ( $articles ) {
	$articles['leads_shortcode'] = 'https://api.intercom.io/articles/4425952';

	return $articles;
} );

/**
 * Don't display metrics ribbon if we don't have any license
 */
add_filter( 'tve_dash_metrics_should_enqueue', static function ( $should_enqueue ) {
	$screen = tve_get_current_screen_key();
	if ( $screen === 'thrive-dashboard_page_thrive_leads_dashboard' && ! tve_leads_license_activated() ) {
		$should_enqueue = false;
	}

	return $should_enqueue;
}, 10, 1 );
