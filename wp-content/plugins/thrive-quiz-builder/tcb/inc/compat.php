<?php
/**
 * this file handles known compatibility issues with other plugins / themes
 */

/**
 * general admin conflict notifications
 */
add_action( 'admin_notices', 'tve_admin_notices' );

/**
 * filter for including wp affiliates scripts and styles if the shortcode is found in TCB content
 */
add_filter( 'affwp_force_frontend_scripts', 'tve_compat_wp_affiliate_scripts' );

add_filter( 'fp5_filter_has_shortcode', 'tve_compat_flowplayer5_has_shortcode' );

/**
 *
 * Compatibility with S2Member plugin - it fails to include CSS / JS on pages / posts created with TCB
 */
add_filter( 'ws_plugin__s2member_lazy_load_css_js', '__return_true' );

/**
 *
 * Compatibility with Survey Funnel plugin - it fails to include CSS / JS on pages / posts created with TCB
 */
if ( function_exists( 'is_plugin_active' ) && is_plugin_active( 'surveyfunnel/survey_funnel.php' ) ) {
	add_action( 'wp_enqueue_scripts', 'tve_compat_survey_funnel', 11 );
}

/**
 * Compatibility with Total Themes & Advanced Custom Fields
 */
if ( isset( $_GET['tve'] ) && 'true' === $_GET['tve'] ) {
	add_filter( 'wpex_toggle_bar_active', '__return_false' );
	add_filter( 'acf/settings/enqueue_select2', '__return_false' );
}

/**
 * Checks if a post / page has a shortcode in TCB content
 *
 * @param string                  $shortcode
 * @param int|string|null|WP_Post $post_id
 * @param bool                    $use_wp_shortcode_check whether or not to use has_shortcode() or strpos
 *
 * @return bool
 */
function tve_compat_has_shortcode( $shortcode, $post_id = null, $use_wp_shortcode_check = false ) {
	if ( is_null( $post_id ) ) {
		$post_id = get_the_ID();
	} else {
		$post_id = is_a( $post_id, 'WP_Post' ) ? $post_id->ID : $post_id;
	}
	$content = tve_get_post_meta( $post_id, 'tve_updated_post' );
	if ( ! $use_wp_shortcode_check ) {
		return strpos( $content, $shortcode ) !== false;
	}
	if ( $post_id ) {
		return has_shortcode( $content, '[' . str_replace( [ '[', ']' ], '', $shortcode ) . ']' );
	}

	return false;
}

/**
 * display any possible conflicts with other plugins / themes as error notification in the admin panel
 */
function tve_admin_notices() {
	$has_wp_seo_conflict = tve_has_wordpress_seo_conflict();

	if ( $has_wp_seo_conflict ) {
		$link = sprintf( '<a href="%s">%s</a>', admin_url( 'admin.php?page=wpseo_advanced&tab=permalinks' ), __( 'WordPress SEO settings', 'thrive-cb' ) );
		$message
		      = sprintf( __( 'Thrive Architect and Thrive Leads cannot work with the current configuration of WordPress SEO. Please go to %s and disable the %s"Redirect ugly URL\'s to clean permalinks"%s option',
			'thrive-cb' ), $link, '<strong>', '</strong>' );
		echo sprintf( '<div class="error"><p>%s</p></div>', esc_html( $message ) );
	}
}

/**
 * check if the user has a known "Coming soon" or "Membership protection" plugin installed
 * our landing pages seem to overwrite their "Coming soon" functionality
 * this would check for any coming soon plugins that use the template_redirect hook
 */
function tve_hooked_in_template_redirect() {
	include_once ABSPATH . '/wp-admin/includes/plugin.php';

	$hooked_in_template_redirect = [
		'wishlist-member/wpm.php',
		'ultimate-coming-soon-page/ultimate-coming-soon-page.php',
		'easy-pie-coming-soon/easy-pie-coming-soon.php',
		'coming-soon-page/coming_soon.php',
		'cc-coming-soon/cc-coming-soon.php',
		'wordpress-seo/wp-seo.php',
		'wordpress-seo-premium/wp-seo-premium.php',
		'membermouse/index.php',
		'ultimate-member/index.php',
		'woocommerce/woocommerce.php',
		'maintenance/maintenance.php',
		'simply-schedule-appointments/simply-schedule-appointments.php',
		'borlabs-cookie/borlabs-cookie.php',
	];

	foreach ( $hooked_in_template_redirect as $plugin ) {
		if ( is_plugin_active( $plugin ) ) {
			return true;
		}
	}

	/**
	 * SUPP-1749 if the domain mapping plugin is installed, Landing Pages will not be redirected to the corresponding domain. This ensures that the redirection will take place
	 */
	if ( is_plugin_active( 'wordpress-mu-domain-mapping/domain_mapping.php' ) ) {
		return true;
	}

	return false;
}

/**
 * Check if the user has the WordPress SEO plugin installed and the "Redirect to clean URLs" option checked
 *
 * @return bool
 */
function tve_has_wordpress_seo_conflict() {
	return is_plugin_active( 'wordpress-seo/wp-seo.php' ) && ( $wpseo_options = get_option( 'wpseo_permalinks' ) ) && ! empty( $wpseo_options['cleanpermalinks'] );
}


/**
 * called inside the 'init' hook
 *
 * this is used to fix any plugin conflicts that might appear
 *
 * 1. YARPP - we need to disable their the_content filter when in editing mode,
 *      - they apply the_content filter automatically when querying the database for related posts
 *      - they have a filter for blacklisting a filters the_content, but that does not solve the issue - wp will never call our filter anymore
 *
 * 2. TheRetailer theme - they remove the WP media js files for some reason (??)
 *
 * 3. Enfold theme - tinymce buttons causing errors (localization)
 */
function tve_fix_plugin_conflicts() {

	global $yarpp;
	if ( is_editor_page_raw() ) {
		if ( $yarpp ) {
			remove_filter( 'the_content', [ $yarpp, 'the_content' ], 1200 );
		}
		/**
		 * Theretailer theme deregisters the mediaelement for some reason
		 */
		if ( function_exists( 'theretailer_deregister' ) ) {
			remove_action( 'wp_enqueue_scripts', 'theretailer_deregister' );
		}

		/**
		 * Removed Last Modified Plugin content from TAR Editor page
		 *
		 * https://wordpress.org/plugins/wp-last-modified-info/
		 */
		if ( function_exists( 'lmt_print_last_modified_info_post' ) ) {
			remove_filter( 'the_content', 'lmt_print_last_modified_info_post' );
		}

		/**
		 * Removed Last Modified Plugin content from TAR Editor pages
		 *
		 * https://wordpress.org/plugins/wp-last-modified-info/
		 */
		if ( function_exists( 'lmt_print_last_modified_info_page' ) ) {
			remove_filter( 'the_content', 'lmt_print_last_modified_info_page' );
		}
	}
}

/* hook to fix various conflicts that might appear. first one: YARPP */
add_action( 'init', 'tve_fix_plugin_conflicts', PHP_INT_MAX );

/**
 * Called on init - priority 11
 *
 */
function tve_compat_right_after_init() {

	if ( is_admin() ) {
		/**
		 * EventEspresso plugin hijacks the admin UI for editing posts - causing TAr to not load for a Event post type
		 * They overwrite the default WordPress post.php?post=3432&action=edit with a custom page & implementation
		 */
		if ( function_exists( 'espresso_version' ) ) {
			/**
			 * Identify TAr URL using:
			 * page => espresso_events / espresso_venues
			 * action => architect
			 * post => numeric
			 */
			$is_espresso_page  = ! empty( $_REQUEST['page'] ) && strpos( $_REQUEST['page'], 'espresso_' ) === 0; //phpcs:ignore
			$is_architect_link = ! empty( $_REQUEST['tve'] ) && ! empty( $_REQUEST['action'] ) && $_REQUEST['action'] === 'architect'; //phpcs:ignore
			$is_post           = ! empty( $_REQUEST['post'] ) && is_numeric( $_REQUEST['post'] );

			if ( $is_espresso_page && $is_architect_link && $is_post ) {
				$GLOBALS['post'] = get_post( absint( $_REQUEST['post'] ) );
				do_action( 'post_action_architect' );
			}
		}
	}
}

/* hook into init at priority 11 to allow fixing some conflicts with 3rd party plugins */
add_action( 'init', 'tve_compat_right_after_init', 11 );

/**
 * apply some of currently known 3rd party filters to the TCB saved_content
 *
 * Digital Access Pass: dap_*
 *
 * @param string $content
 *
 * @return string
 */
function tve_compat_content_filters_before_shortcode( $content ) {
	$content = tcb_dap_shortcode_in_content( $content );

	/**
	 * s3 amazon links - they don't handle shortcodes in the "WP" way
	 */
	if ( function_exists( 's3mv' ) ) {
		$content = s3mv( $content );
	}

	if ( function_exists( 'ec' ) ) {
		$content = ec( $content );
	}

	/**
	 * A3 Lazy Load plugin
	 * This plugin adds a filter on "the_content" inside of "wp" action callback -> the same as TCB does
	 * Its "the_content" filter callback is executed first because of its name -> A3
	 * We call its filter implementation on TCB content
	 */
	if ( class_exists( 'A3_Lazy_Load' ) && method_exists( 'A3_Lazy_Load', 'filter_content_images' ) ) {
		global $a3_lazy_load_global_settings;
		if ( $a3_lazy_load_global_settings['a3l_apply_image_to_content'] ) {
			$content = A3_Lazy_Load::filter_content_images( $content );
		}
	}

	/**
	 * EduSearch plugin not handling shortcodes in the "WP" way
	 * they search for [edu-search] strings and process those
	 */
	if ( function_exists( 'esn_filter_content' ) ) {
		$content = esn_filter_content( $content );
	}

	/**
	 * Paid Memberships pro has a really strange way of defining shortcodes
	 */
	if ( function_exists( 'pmpro_wp' ) ) {
		global $post;
		$o_content          = $post->post_content;
		$post->post_content = $content;
		pmpro_wp();
		$post->post_content = $o_content;
	}

	/**
	 * QuickLATEX plugin compatibility.
	 */
	if ( function_exists( 'quicklatex_parser' ) ) {
		$content = quicklatex_parser( $content );
	}

	/**
	 * if getting the excerpt, remove all shortcodes.
	 *
	 * @see wp_trim_excerpt()
	 */
	if ( doing_filter( 'get_the_excerpt' ) ) {
		$content = strip_shortcodes( $content );
	}

	/**
	 * SUPP-6382 Fixes a conflict with the SyntaxHighlighter plugin
	 */
	if ( class_exists( 'SyntaxHighlighter', false ) ) {
		/** @var SyntaxHighlighter $SyntaxHighlighter */
		// phpcs:disable
		global $SyntaxHighlighter;
		if ( ! empty( $SyntaxHighlighter ) && method_exists( $SyntaxHighlighter, 'parse_shortcodes' ) ) {
			$content = $SyntaxHighlighter->parse_shortcodes( $content );
		}
		// phpcs:enable
	}

	return $content;
}


/**
 * apply some of currently known 3rd party filters to the TCB saved_content - after do_shortcode is being called
 *
 * FormMaker: Form_maker_fornt_end_main
 *
 * @param string $content
 *
 * @return string
 */
function tve_compat_content_filters_after_shortcode( $content ) {
	/**
	 * FormMaker does not use WP shortcode as they should
	 */
	if ( function_exists( 'Form_maker_fornt_end_main' ) ) {
		$content = Form_maker_fornt_end_main( $content );
	}

	/**
	 * in case they will ever correct the function name
	 */
	if ( function_exists( 'Form_maker_front_end_main' ) ) {
		$content = Form_maker_front_end_main( $content );
	}

	/* Compat with TOC Plus plugin
	*/
	if ( class_exists( 'toc', false ) ) {
		global $tic;
		if ( ! empty( $tic ) && method_exists( $tic, 'the_content' ) ) {
			$content = $tic->the_content( $content );
		}
	}

	return $content;
}

/**
 * check if we are on a page / post and there is a [affiliate_area] shortcode in TCB content
 *
 * @param bool $bool current value
 *
 * @return bool
 */
function tve_compat_wp_affiliate_scripts( $bool ) {
	if ( $bool || ! is_singular() || is_editor_page() ) {
		return $bool;
	}

	$tve_saved_content = tve_get_post_meta( get_the_ID(), 'tve_updated_post' );

	return has_shortcode( $tve_saved_content, 'affiliate_area' ) || has_shortcode( $tve_saved_content, 'affiliate_creatives' );

}

/**
 * checks if the current post is protected by a membership plugin and cannot be displayed
 *
 * @return bool
 */
function tve_membership_plugin_can_display_content() {

	global $post;

	/**
	 * we should not apply this during the_excerpt filter
	 */
	if ( doing_filter( 'get_the_excerpt' ) ) {
		return true;
	}

	/**
	 *
	 * WooCommerce Membership compatibility - hide TCB content for non-members
	 */
	if ( function_exists( 'wc_memberships_is_post_content_restricted' ) && wc_memberships_is_post_content_restricted() && ! doing_filter( 'get_the_excerpt' ) ) {
		if ( ! current_user_can( 'wc_memberships_view_restricted_post_content', $post->ID ) || ! current_user_can( 'wc_memberships_view_delayed_post_content', $post->ID ) ) {
			return false;
		}
	}

	/**
	 * Simple Membership plugin compatibility - hide TCB content for non members
	 */
	if ( class_exists( 'BAccessControl' ) ) {
		$control = SwpmAccessControl::get_instance();

		if ( ! $control->can_i_read_post( $post ) ) {
			return false;
		}
	}

	/**
	 * Paid Memberships Pro plugin
	 */
	if ( function_exists( 'pmpro_has_membership_access' ) ) {
		$has_access = pmpro_has_membership_access();
		if ( ! $has_access ) {
			return false;
		}
	}

	/**
	 * MemberPress plugin compatibility - hide TCB content for protected posts/pages
	 */
	$uri = ! empty( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( $_SERVER['REQUEST_URI'] ) : '';
	if ( class_exists( 'MeprRule' ) && ( MeprRule::is_locked( $post ) || MeprRule::is_uri_locked( $uri ) ) ) {
		return false;
	}

	/**
	 * Filter hook that allows plugins to hook into TCB and prevent TCB content from being displayed if e.g. the user does not have access to this content
	 *
	 * @param bool $can_display
	 *
	 * @since 1.200.3
	 *
	 */
	return apply_filters( 'tcb_can_display_content', true );

}

/**
 * compatibility with flowplayer 5 shortcodes
 *
 * @param bool $has_shortcode
 */
function tve_compat_flowplayer5_has_shortcode( $has_shortcode ) {
	if ( is_editor_page_raw() ) {
		return $has_shortcode;
	}

	return tve_compat_has_shortcode( 'flowplayer' );
}

/**
 * compatibility with Survey Funnel
 */
function tve_compat_survey_funnel() {
	global $is_survey_page, $post;
	if ( $is_survey_page === true ) {
		return;
	}
	$content_updated = tve_get_post_meta( $post->ID, 'tve_updated_post' );

	if ( stristr( $content_updated, '[survey_funnel' ) ) {
		$is_survey_page = true;
		wp_script_is( 'survey_funnel_ajax' ) || wp_enqueue_script( 'survey_funnel_ajax', SF_PLUGIN_URL . '/js/ajax.js', [ 'jquery' ], '1.0', false );
		wp_script_is( 'survey_funnel' ) || wp_enqueue_script( 'survey_funnel', SF_PLUGIN_URL . '/js/survey_funnel.js', [ 'jquery' ], '1.0', false );
		wp_script_is( 'survey_funnel_fancybox' )
		|| wp_enqueue_script( 'survey_funnel_fancybox', SF_PLUGIN_URL . '/jquery/fancyBox-2.1.5/source/jquery.fancybox.pack.js', [ 'jquery' ], '1.0', false );

		wp_style_is( 'survey_funnel_styles' ) || wp_enqueue_style( 'survey_funnel_styles', SF_PLUGIN_URL . '/css/styles.css' );
		wp_style_is( 'survey_funnel_client_styles' ) || wp_enqueue_style( 'survey_funnel_client_styles', SF_PLUGIN_URL . '/css/survey_funnel.css' );
		wp_style_is( 'survey_funnel_client_styles_fancybox' )
		|| wp_enqueue_style( 'survey_funnel_client_styles_fancybox', SF_PLUGIN_URL . '/jquery/fancyBox-2.1.5/source/jquery.fancybox.css' );
	}

}

/**
 * Fix Thrive Architect conflicts before footer
 */
function tve_fix_page_conflicts_before_footer() {

	/**
	 *  For SlickQuiz plugin
	 */
	if ( class_exists( 'SlickQuiz' ) ) {

		remove_filter( 'the_content', 'tve_editor_content', 10 );

		if ( is_editor_page() ) {
			remove_filter( 'the_content', 'tve_editor_content', PHP_INT_MAX );
		}
	}
}

add_action( 'wp_footer', 'tve_fix_page_conflicts_before_footer', 2000 );

/**
 * Remove the content filter for sensei plugin
 */
function tve_wc_sensei_no_content_filter() {

	if ( class_exists( 'Sensei_Course' ) ) {

		if ( ! is_editor_page() ) {
			remove_filter( 'the_content', 'tve_editor_content', 10 );
		}
	}

}

add_action( 'wc_sensei_no_content_filter', 'tve_wc_sensei_no_content_filter', 2000 );

/**
 * Paid Memberships pro has a really strange way of defining shortcodes
 */
if ( function_exists( 'pmpro_wp' ) ) {
	function tve_pmpro_shortcodes() {
		global $post;
		if ( ! empty( $post ) ) {
			$tve_content                  = tve_get_post_meta( $post->ID, 'tve_updated_post' );
			$GLOBALS['tve_pmp_o_content'] = $post->post_content;
			$post->post_content           = $tve_content . $post->post_content;
		}
	}

	function tve_pmpro_shortcodes_cleanup() {
		global $post;
		if ( isset( $GLOBALS['tve_pmp_o_content'] ) ) {
			$post->post_content = $GLOBALS['tve_pmp_o_content'];
		}
	}

	add_action( 'wp', 'tve_pmpro_shortcodes', 0 );
	add_action( 'wp', 'tve_pmpro_shortcodes_cleanup', 100 );
}

/**
 * Event Manager compatibility
 */
function tve_em_remove_content_filter() {
	global $EM_Event;

	if ( ! empty( $EM_Event ) && get_post_type() === 'event' && is_singular() ) {
		remove_filter( 'the_content', 'tve_editor_content', 10 );
	}
}

add_action( 'wp', 'tve_em_remove_content_filter', 2000 );

function tve_em_event_output_placeholder( $replace, $em_event, $full_result, $target ) {
	if ( $full_result == '#_EVENTNOTES' ) {
		$replace = tve_editor_content( tve_clean_wp_editor_content( $em_event->post_content ) );
		$replace = do_shortcode( $replace );
	}

	return $replace;
}

add_filter( 'em_event_output_placeholder', 'tve_em_event_output_placeholder', 10, 4 );

/**
 * Solves a problem with shortcodes that span over multiple elements (e.g. conditional shortcodes).
 * Example:
 * [has_access] <div class="thrv-button">etc</div> [/has_access] - this would only show the button if somebody has access, or only show it after a javascript gets executed
 *
 * The problem is that when adding the shortcode using a test element, this turns into:
 * <div class="thrv_text_element"><p>[has_access]</p></div><div class="thrv-button">etc</div><div class="thrv_text_element"><p>[/has_access]</p></div>
 *
 * At this point, the content inside the shortcode is actually an invalid html. The shortcode function will receive this:
 * function has_access( $attr, $content ) {
 *      // $content = '</p></div><div class="thrv-button">etc</div><div class="thrv_text_element"><p>'
 * }
 *
 * This function will correct the $content, transforming it into:
 * <div class="thrv-button">etc</div><div class="thrv_text_element"><p></p></div>
 *
 * @param string $output
 * @param string $tag
 * @param array  $attr
 * @param array  $m
 *
 * @return string
 */
function tcb_ensure_shortcode_html_structure( $output, $tag, $attr, $m ) {

	if ( isset( $m[5] ) && ! empty( $m[5] ) && strpos( $output, 'thrv_text_element' ) !== false ) {
		$content     = $m[5];
		$closed_tags = '#^(</p>|</div>)#';
		while ( preg_match( $closed_tags, $content, $match ) === 1 ) {
			$content = substr( $content, strlen( $match[1] ) ) . $match[1];
		}
		$output = str_replace( $m[5], $content, $output );
	}

	return $output;
}

add_filter( 'do_shortcode_tag', 'tcb_ensure_shortcode_html_structure', 10, 4 );

/**
 * Re-Add template_include filters after remove_all_filters( 'template_include' );
 */
function tve_compat_re_add_template_include_filters() {
	if ( ! function_exists( 'is_plugin_active' ) ) {
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	}

	if ( is_plugin_active( 'maintenance/maintenance.php' ) ) {
		global $mtnc;
		if ( ! empty( $mtnc ) ) {
			add_action( 'template_include', [ $mtnc, 'mtnc_template_include' ], 999999 );
		}
	}
}

/**
 * Compat Function that contains compatibility fixes for Thrive Architect On plugins_loaded hook
 */
function tve_compat_plugins_loaded_hook() {
	global $sitepress;
	if ( ! empty( $sitepress ) && ! empty( $_REQUEST[ TVE_EDITOR_FLAG ] ) ) {
		remove_action( 'init', [ $sitepress, 'js_load' ], 2 );
	}
}

/**
 * Digital Access Pass %% links in the content, e.g.: %%LOGIN_FORM%%
 *
 * @param string $content content with possible shortcodes
 *
 * @return string updated content
 */
function tcb_dap_shortcode_in_content( $content ) {
	if ( function_exists( 'dap_login' ) ) {
		$content = dap_login( $content );
	}

	if ( function_exists( 'dap_personalize' ) ) {
		$content = dap_personalize( $content );
	}

	if ( function_exists( 'dap_personalize_error' ) ) {
		$content = dap_personalize_error( $content );
	}

	if ( function_exists( 'dap_product_links' ) ) {
		$content = dap_product_links( $content );
	}

	return $content;
}

add_action( 'plugins_loaded', 'tve_compat_plugins_loaded_hook' );


/**
 * Added hooks to ensure compatibility between TAR and WP Last Modified Info plugin
 */
add_filter( 'wplmi_display_priority_post', 'tve_wp_last_modified_info' );
add_filter( 'wplmi_display_priority_page', 'tve_wp_last_modified_info' );

/**
 * Compatibility with WP Last Modified Info plugin
 *
 * WP Market:
 * https://wordpress.org/plugins/wp-last-modified-info/
 *
 * GIT Source Code:
 * https://github.com/iamsayan/wp-last-modified-info
 *
 * @param int $hook_priority
 *
 * @return int
 */
function tve_wp_last_modified_info( $hook_priority = 10 ) {

	if ( ! is_editor_page_raw() ) {
		$hook_priority = PHP_INT_MAX;
	}

	return $hook_priority;
}

/**
 * Compatibility with Imagify plugin
 */
add_filter( 'imagify_allow_picture_tags_for_webp', 'tve_prevent_imagify_webp' );
/**
 * Don’t use `<picture>` tags when displaying the site in the editor’s iframe.
 *
 * @param bool $allow True to allow the use of <picture> tags (default). False to prevent their use.
 *
 * @return bool
 */
function tve_prevent_imagify_webp( $allow ) {
	return ! is_editor_page();
}


/**
 * WP-Rocket Compatibility - exclude files from caching
 */
add_filter( 'rocket_exclude_css', 'tve_rocket_exclude_css' );
add_filter( 'rocket_exclude_js', 'tve_rocket_exclude_js' );

/**
 * Exclude the js dist folder from caching and minify-ing
 *
 * @param $excluded_js
 *
 * @return array
 */
function tve_rocket_exclude_js( $excluded_js ) {

	$excluded_js[] = str_replace( home_url(), '', plugins_url( '/thrive-visual-editor/editor/js/dist' ) ) . '/(.*).js';

	return $excluded_js;
}

/**
 * Exclude the css files from caching and minify-ing
 *
 * @param $excluded_css
 *
 * @return array
 */
function tve_rocket_exclude_css( $excluded_css ) {

	$excluded_css[] = str_replace( home_url(), '', plugins_url( '/thrive-visual-editor/editor/css' ) ) . '/(.*).css';

	return $excluded_css;
}

/**
 * Compatibility with one signal push notification
 *
 * We don't need their scripts inside the editor
 * Added in wp_head because there is the place where they register their scripts
 */
add_action( 'wp_head', function () {
	if ( is_editor_page_raw() ) {
		wp_deregister_script( 'remote_sdk' );
		wp_dequeue_script( 'remote_sdk' );
	}
}, PHP_INT_MAX );

/**
 * Compatibility with:
 *  -> Oliver POS - A WooCommerce Point of Sale (POS)
 *  -> Quiz and Survey Master
 *
 * We don't need their styles inside the editor
 * Added in admin_enqueue_scripts because there is the place where they register their styles
 */
add_action( 'admin_enqueue_scripts', function () {
	if ( is_editor_page_raw() ) {
		wp_deregister_style( 'oliver-pos-feedback-css' );
		wp_dequeue_style( 'oliver-pos-feedback-css' );

		wp_deregister_style( 'qsm_admin_style' );
		wp_dequeue_style( 'qsm_admin_style' );
	}
}, PHP_INT_MAX );

/**
 * Compatibility with Rank Math
 *
 * We don't need their scripts inside the editor
 * Added in wp_enqueue_scripts because there is the place where they register their scripts
 */
add_action( 'wp_enqueue_scripts', function () {
	if ( is_editor_page_raw() ) {
		wp_deregister_script( 'rank-math-analytics-stats' );
		wp_dequeue_script( 'rank-math-analytics-stats' );
	}
}, PHP_INT_MAX );

/**
 * Fixes a compatibility issue with optimole that causes src attribute replacement to not function correctly on landing pages
 */
add_action( 'tcb_landing_page_template_redirect', function () {
	if ( ! is_editor_page() && did_action( 'optml_replacer_setup' ) ) {
		do_action( 'optml_after_setup' );
	}
} );

/**
 * Fixes compatibility with optimole that causes the Symbols not to render in the Globals dashboard
 */
add_filter( 'optml_should_replace_page', function ( $value ) {
	if ( ! empty( $_REQUEST['optimole_skip_processing'] ) ) {
		$value = true;
	}

	return $value;
} );

/**
 * Lazyload from optimole conflicts with autoplay videos
 */
add_filter( 'optml_iframe_lazyload_flags', function ( $strings ) {
	$strings [] = 'autoplay'; // disable optimole lazyload for autoplay videos
	$strings [] = 'autopause=0'; // vimeo uses autopause instead of autoplay, because why not

	return $strings;
} );

/**
 * Filter to add plugins to the TOC list.
 *
 * @param array TOC plugins.
 */
add_filter( 'rank_math/researches/toc_plugins', function ( $toc_plugins ) {
	$toc_plugins['thrive-visual-editor/thrive-visual-editor.php'] = 'Thrive Architect';

	return $toc_plugins;
} );

/**
 * Don't load metrics files on the editor page
 */
add_filter( 'tve_dash_metrics_should_enqueue', function ( $should_enqueue ) {
	if ( is_editor_page_raw( true ) || TCB_Editor()->is_main_frame() ) {
		$should_enqueue = false;
	}

	return $should_enqueue;
} );

/**
 * Fixes a custom menu regression that added these classes to all saved menus
 */
add_filter( 'tve_thrive_shortcodes', static function ( $content ) {
	return str_replace( ' tve-custom-menu-switch-icon-tablet tve-custom-menu-switch-icon-mobile', '', $content );
} );

/**
 * Do not generate sitemap for symbols
 */
add_filter( 'tve_dash_yoast_sitemap_exclude_post_types', static function ( $post_types ) {
	$post_types[] = TCB_Symbols_Post_Type::SYMBOL_POST_TYPE;

	return $post_types;
} );

/**
 * Do not generate sitemap for symbols taxonomy
 */
add_filter( 'tve_dash_yoast_sitemap_exclude_taxonomies', static function ( $taxonomies ) {
	$taxonomies[] = TCB_Symbols_Taxonomy::SYMBOLS_TAXONOMY;

	return $taxonomies;
} );

/**
 * Replace page & post identifiers
 *
 */
add_action( 'after_thrive_clone_item', static function ( $new_id, $old_id ) {
	$css = tve_get_post_meta( $new_id, 'tve_custom_css' );

	$css = str_replace( [ "page-id-$old_id", "postid-$old_id" ], [ "page-id-$new_id", "postid-$new_id" ], $css );

	tve_update_post_meta( $new_id, 'tve_custom_css', $css );
}, 10, 2 );

/**
 * Modify the page, header/footer, and template sections content so that Digital Access Pass shortcodes are rendered
 */
add_filter( 'thrive_template_structure', 'tcb_dap_shortcode_in_content' );
add_filter( 'thrive_template_header_content', 'tcb_dap_shortcode_in_content' );
add_filter( 'thrive_template_footer_content', 'tcb_dap_shortcode_in_content' );

add_action( 'current_screen', 'tcb_current_screen' );

/**
 * Some pages don't have a title, so we need to set it manually
 */
function tcb_current_screen() {
	$screen = tve_get_current_screen_key();
	global $title;
	if ( $screen && empty( $title ) && ( strpos( $screen, 'tcb_' ) !== false || strpos( $screen, 'tve_' ) !== false ) ) {
		$title = 'Thrive Architect';
	}
}

/**
 * Returns an array containing shortcode tags, this will be used to whitelist theses shortcodes when saving the plain content
 *
 * @return mixed|void
 */
function tcb_plain_content_whitelisted_shortcodes() {
	return apply_filters( 'tcb_plain_content_whitelisted_shortcodes', [ 'mepr-membership-registration-form' ] );
}

/**
 * Sometimes we do not want to do shortcodes while saving the playn text content(the one added in post content)
 * The reason we need to do this for MemberPress is because they only load the shortcode content IF it's shortcode tag is present in post content,
 * so we cannot do_shortcode on it on save because the shortcode tag will be replaced
 */
/**
 * In the actions bellow we remove the whitelisted shortcodes from the $shortcode_tags global(so they wont be computed on do_shortcode),
 * and restore the $shortcode_tags after the do_shortcode
 */
add_action( 'tcb_plain_content_do_shortcode_before', function () {
	global $shortcode_tags, $tcb_whitelisted_plain_content_shortcodes;

	if ( ! is_array( $tcb_whitelisted_plain_content_shortcodes ) ) {
		$tcb_whitelisted_plain_content_shortcodes = [];
	}

	$whitelisted_shortcodes = tcb_plain_content_whitelisted_shortcodes();

	foreach ( $whitelisted_shortcodes as $tag ) {
		if ( isset( $shortcode_tags[ $tag ] ) ) {
			$tcb_whitelisted_plain_content_shortcodes[ $tag ] = $shortcode_tags[ $tag ];

			unset( $shortcode_tags[ $tag ] );
		}
	}
} );

add_action( 'tcb_plain_content_do_shortcode_after', function () {
	global $shortcode_tags, $tcb_whitelisted_plain_content_shortcodes;

	if ( ! is_array( $tcb_whitelisted_plain_content_shortcodes ) ) {
		$tcb_whitelisted_plain_content_shortcodes = [];
	}

	$whitelisted_shortcodes = tcb_plain_content_whitelisted_shortcodes();

	foreach ( $whitelisted_shortcodes as $tag ) {
		if ( isset( $tcb_whitelisted_plain_content_shortcodes[ $tag ] ) ) {
			$shortcode_tags[ $tag ] = $tcb_whitelisted_plain_content_shortcodes[ $tag ];

			unset( $tcb_whitelisted_plain_content_shortcodes[ $tag ] );
		}
	}
} );
