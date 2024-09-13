<?php
/**
 * Global functions file
 */

/**
 * check if the current TCB version is the one required by Thrive Ultimatum
 */
function tve_ult_check_tcb_version() {
	if ( ! tve_in_architect() ) { // the internal TCB code will always be up to date
		return true;
	}

	$internal_architect_version = include TVE_Ult_Const::plugin_path() . 'tcb/version.php';

	/* make sure that the we have the same version of architect inside the plugin and as individual plugin, otherwise conflicts can appear */

	return ! ( ! defined( 'TVE_VERSION' ) || ! version_compare( TVE_VERSION, $internal_architect_version, '=' ) );
}

/**
 * make sure the TL_product is displayed in thrive dashboard
 *
 * @param array $items
 *
 * @return array
 */
function tve_ult_add_to_dashboard( $items ) {

	$items[] = new TU_Product();

	return $items;
}

/**
 * Load the version file of Thrive Dashboard
 */
function tve_ult_load_dash_version() {
	$tve_dash_path      = dirname( __DIR__ ) . '/thrive-dashboard';
	$tve_dash_file_path = $tve_dash_path . '/version.php';

	if ( is_file( $tve_dash_file_path ) ) {
		$version                                  = require_once( $tve_dash_file_path );
		$GLOBALS['tve_dash_versions'][ $version ] = array(
			'path'   => $tve_dash_path . '/thrive-dashboard.php',
			'folder' => '/thrive-ultimatum',
			'from'   => 'plugins',
		);
	}
}

function tu_hide_export_content( $allow, $post ) {
	if ( $post->post_type === TVE_Ult_Const::POST_TYPE_NAME_FOR_CAMPAIGN ) {
		$allow = false;
	}

	return $allow;
}

/**
 * Registers needed post types
 */
function tve_ult_init() {
	register_post_type( TVE_Ult_Const::POST_TYPE_NAME_FOR_CAMPAIGN, array(
		'publicly_queryable' => true,
		'query_var'          => false,
		'description'        => 'Entity for TU Campaign',
		'rewrite'            => false,
		'labels'             => array(
			'name' => 'Thrive Ultimatum - Campaign',
		),
		'_edit_link'         => 'post.php?post=%d',
		'map_meta_cap'       => true,
		'capabilities'       => array(
			'edit_others_posts'    => TVE_DASH_EDIT_CPT_CAPABILITY,
			'edit_published_posts' => TVE_DASH_EDIT_CPT_CAPABILITY,
		),
	) );

	register_post_type( TVE_Ult_Const::POST_TYPE_NAME_FOR_SCHEDULE, array(
		'description'         => 'Each campaign can have more schedules',
		'publicly_queryable'  => true,
		'query_var'           => false,
		'exclude_from_search' => true,
		'rewrite'             => false,
		'hierarchical'        => true,
		'labels'              => array(
			'name' => 'Thrive Ultimatum - Schedules',
		),
		'_edit_link'          => 'post.php?post=%d',
		'map_meta_cap'        => true,
		'capabilities'        => array(
			'edit_others_posts'    => TVE_DASH_EDIT_CPT_CAPABILITY,
			'edit_published_posts' => TVE_DASH_EDIT_CPT_CAPABILITY,
		),
	) );

	tvu_update_saved_templates();
}

function tvu_update_saved_templates() {
	$already_updated = get_option( 'tvu_saved_tpl_updated' );
	if ( empty( $already_updated ) ) {
		$contents = get_option( TU_Template_Manager::OPTION_TPL_CONTENT, array() );
		$meta     = get_option( TU_Template_Manager::OPTION_TPL_META, array() );
		/**
		 * In some instances those return empty strings
		 */
		if ( empty( $contents ) ) {
			$contents = array();
		}
		if ( empty( $meta ) ) {
			$meta = array();
		}

		foreach ( $meta as $index => $data ) {
			if ( empty( $data['id'] ) ) {
				$start  = $index;
				$tpl_id = 'tvu-tpl-' . substr( uniqid( '', true ), $start, 7 );

				$contents[ $index ]['id'] = $tpl_id;
				$meta[ $index ]['id']     = $tpl_id;
			}
		}

		add_option( TU_Template_Manager::OPTION_TPL_CONTENT, null, '', 'no' );

		update_option( TU_Template_Manager::OPTION_TPL_CONTENT, $contents );
		update_option( TU_Template_Manager::OPTION_TPL_META, $meta );

		update_option( 'tvu_saved_tpl_updated', true );
	}
}

/**
 * registers the Thrive Ultimatum widget
 */
function tve_ult_register_widget() {
	require_once TVE_Ult_Const::plugin_path( 'inc/classes/class-tu-campaign-widget.php' );

	register_widget( 'TU_Campaign_Widget' );
}

/**
 * Set the path where the translation files are being kept
 */
function tve_ult_load_plugin_textdomain() {
	$domain = 'thrive-ult';
	$locale = $locale = apply_filters( 'plugin_locale', get_locale(), $domain );
	$path   = 'thrive-ultimatum/languages/';
	load_textdomain( $domain, WP_LANG_DIR . '/thrive/' . $domain . '-' . $locale . '.mo' );
	load_plugin_textdomain( $domain, false, $path );
}

/**
 * Hooks into the leads conversion so it can start a evergreen
 * campaign or trigger a conversion event which also starts a evergreen campaign
 *
 * @param $group
 * @param $form_type
 * @param $variation
 * @param $test_model_id
 * @param $post_data
 * @param $current_screen
 */
function tve_ult_check_campaign_trigger( $group, $form_type, $variation, $test_model_id, $post_data, $current_screen ) {
	// get all campaigns
	$campaigns = tve_ult_get_campaigns( array(
		'get_designs'  => false,
		'only_running' => true,
		'get_logs'     => false,
		'lockdown'     => true,
	) );

	foreach ( $campaigns as $campaign ) {
		$settings = $campaign->settings;

		//check if we have only one trigger id and if we do let's make it an array
		if ( ! empty( $settings['trigger'] ) && ! is_array( $settings['trigger']['ids'] ) ) {
			$settings['trigger']['ids'] = array( $settings['trigger']['ids'] );
		}

		if ( ! empty( $settings['trigger'] ) && ! empty( $settings['trigger']['ids'] ) && in_array( $group->ID, $settings['trigger']['ids'] ) ) {
			//only for evergreen campaigns
			$use_gmt = ! empty( $settings['gmt_offset'] );

			// set the start date of the campaign
			$start_date['date'] = date( 'j F Y', $campaign->tu_schedule_instance->now( true, $use_gmt ) );
			$start_date['time'] = date( 'H:i:s', $campaign->tu_schedule_instance->now( false, $use_gmt ) );

			// set the end date of the campaign
			$end_date['date'] = date( 'j F Y', strtotime( $start_date['date'] . '  ' . $start_date['time'] . ' + ' . $settings['end'] . ' days' ) );
			$end_date['time'] = $start_date['time'];

			$params = array( 'start_date' => $start_date, 'end_date' => $end_date );

			$params['lockdown'] = array(
				'email'  => $post_data['email'],
				'type'   => 'leads',
				'log_id' => '',
			);

			if ( ! empty( $campaign->lockdown ) ) {
				$campaign->tu_schedule_instance->set_cookie_and_save_log( $params );

			} else if ( ! isset( $_COOKIE[ TVE_Ult_Const::COOKIE_NAME . $campaign->ID ] ) ) {

				if ( $campaign->type === TVE_Ult_Const::CAMPAIGN_TYPE_EVERGREEN ) {
					tve_ult_send_evergreen_campaign_start_hook( $campaign->ID, $post_data['email'] );
				}

				$data = $campaign->tu_schedule_instance->set_cookie_data( $params );
				$campaign->tu_schedule_instance->setCookie( $data['value'], $data['expire'] );
			}
		}

		foreach ( $campaign->conversion_events as $event ) {
			if ( $event['trigger_options']['trigger'] === TVE_Ult_Const::TRIGGER_OPTION_CONVERSION && in_array( $group->ID, $event['trigger_options']['trigger_ids'] ) ) {
				$campaign->tu_schedule_instance->do_conversion( $event['trigger_options'] );
			}
		}
	}
}

/**
 * Sends the evergreen campaign hook
 *
 * @param {int} $campaign_id
 * @param {string} $email
 */
function tve_ult_send_evergreen_campaign_start_hook( $campaign_id = 0, $email = '' ) {
	global $tve_ult_db;

	/**
	 * @var WP_Post $campaign
	 */
	$campaign = tve_ult_get_campaign( $campaign_id );

	$countdown_event_id = $tve_ult_db->count_logs( array(
		'campaign_id' => $campaign_id,
	) );

	$countdown_event_id ++;

	$start_date = date( 'Y-m-d', tve_ult_current_time( 'timestamp' ) );
	$days       = empty( $campaign->settings['days_duration'] ) ? 0 : (int) $campaign->settings['days_duration'];
	$hours      = empty( $campaign->settings['hours_duration'] ) ? 0 : (int) $campaign->settings['hours_duration'];
	$minutes    = empty( $campaign->settings['minutes_duration'] ) ? 0 : (int) $campaign->settings['minutes_duration'];
	$seconds    = empty( $campaign->settings['seconds_duration'] ) ? 0 : (int) $campaign->settings['seconds_duration'];

	if ( empty( $days ) && empty( $hours ) && empty( $minutes ) && empty( $seconds ) ) {
		/* this is for old campaigns that didn't had a specific end date set, so we're using only days */
		$days = empty( $campaign->settings['duration'] ) ? 0 : (int) $campaign->settings['duration'];
	}

	$duration = $days * DAY_IN_SECONDS + $hours * HOUR_IN_SECONDS + $minutes * MINUTE_IN_SECONDS + $seconds;
	$end_date = date( 'Y-m-d H:i:s', strtotime( date( 'Y-m-d', tve_ult_current_time( 'timestamp' ) ) . ' +' . $duration . ' seconds' ) );

	$user_data = tvd_get_current_user_details();

	if ( ! is_user_logged_in() && ! empty( $email ) ) {
		$matched_user = get_user_by( 'email', $email );
		if ( ! empty( $matched_user ) ) {
			$user_data = tvd_get_current_user_details( $matched_user->ID );
		}
	}

	/**
	 * When an evergreen campaign starts for a specific user, this hook is triggered.The trigger will only be fired once per evergreen campaign per user.
	 * </br>
	 * Example use case:- Synchronize data with a third party system when a new user triggers an evergreen countdown.  You may use this hook to send the end time and date of the countdown campaign to your autoresponder for use in follow up emails.
	 *
	 * @param array Campaign Details
	 * @param null|array User Details
	 *
	 * @api
	 */
	do_action( 'thrive_ultimatum_evergreen_campaign_start', array(
		'campaign_id'           => $campaign_id,
		'campaign_name'         => $campaign->post_title,
		'campaign_type'         => $campaign->type,
		'campaign_start_date'   => $start_date,
		'campaign_end_date'     => $end_date,
		'campaign_trigger_type' => $campaign->settings['trigger']['type'],
		'countdown_event_id'    => $countdown_event_id,
		'user_email'            => $email,
	), $user_data );
}

/**
 * appends the WordPress tables prefix and the default tve_ult prefix to the table name
 *
 * @param string $table
 *
 * @return string the modified table name
 */
function tve_ult_table_name( $table ) {
	global $wpdb;

	return $wpdb->prefix . TVE_Ult_Const::DB_PREFIX . $table;
}

/**
 * check if there is a valid activated license for the TU plugin
 *
 * @return bool
 */
function tve_ult_license_activated() {
	return TVE_Dash_Product_LicenseManager::getInstance()->itemActivated( TVE_Dash_Product_LicenseManager::TU_TAG );
}

/**
 * wrapper over the wp_enqueue_script function
 * it will add the plugin version to the script source if no version is specified
 *
 * @param        $handle
 * @param string $src
 * @param array  $deps
 * @param bool   $ver
 * @param bool   $in_footer
 */
function tve_ult_enqueue_script( $handle, $src = false, $deps = array(), $ver = false, $in_footer = false ) {
	if ( $ver === false ) {
		$ver = TVE_Ult_Const::PLUGIN_VERSION;
	}

	if ( defined( 'TVE_DEBUG' ) && TVE_DEBUG ) {
		$src = preg_replace( '#\.min\.js$#', '.js', $src );
	}

	wp_enqueue_script( $handle, $src, $deps, $ver, $in_footer );
}

/**
 * wrapper over the wp_enqueue_style function
 * it will add the plugin version to the style link if no version is specified
 *
 * @param             $handle
 * @param string|bool $src
 * @param array       $deps
 * @param bool|string $ver
 * @param string      $media
 */
function tve_ult_enqueue_style( $handle, $src = false, $deps = array(), $ver = false, $media = 'all' ) {
	if ( $ver === false ) {
		$ver = TVE_Ult_Const::PLUGIN_VERSION;
	}
	wp_enqueue_style( $handle, $src, $deps, $ver, $media );
}

/**
 * Get the TCB editor EDIT URL for a design
 *
 * @param int     $post_id campaign
 * @param int     $design_id
 * @param boolean $escape_url
 *
 * @return string the url to open the editor for this variation
 */
function tve_ult_get_editor_url( $post_id, $design_id, $escape_url = true ) {
	$editor_link = set_url_scheme( get_edit_post_link( $post_id ) );
	$editor_link = add_query_arg( array(
		'tve'                                => 'true',
		TVE_Ult_Const::DESIGN_QUERY_KEY_NAME => $design_id,
		'r'                                  => uniqid(),
		'action'                             => 'architect',
	), $editor_link );

	if ( $escape_url ) {
		$editor_link = esc_url( $editor_link );
	}
	/**
	 * we need to make sure that if the admin is https, then the editor link is also https, otherwise any ajax requests through wp ajax api will not work
	 */
	$admin_ssl = strpos( admin_url(), 'https' ) === 0;

	return $admin_ssl ? str_replace( 'http://', 'https://', $editor_link ) : $editor_link;
}

/**
 * Builds TCB editor PREVIEW URL for a design
 *
 * @param int     $post_id of a campaign
 * @param int     $design_id
 * @param boolean $escape_url
 *
 * @return string url to open the editor for this design
 */
function tve_ult_get_preview_url( $post_id, $design_id, $escape_url = true ) {
	$post        = get_post( $post_id );
	$editor_link = set_url_scheme( get_permalink( $post_id ) );
	$editor_link = apply_filters( 'preview_post_link', add_query_arg( array(
		TVE_Ult_Const::DESIGN_QUERY_KEY_NAME => $design_id,
		'r'                                  => uniqid(),
	), $editor_link ), $post );

	if ( $escape_url ) {
		$editor_link = esc_url( $editor_link );
	}

	return $editor_link;
}

/**
 * Enqueues scripts and styles for a specific design
 *
 * @param array $for_design
 *
 * @return array
 */
function tve_ult_enqueue_design_scripts( $for_design = null ) {

	if ( empty( $for_design ) ) {
		global $design;
		$for_design = $design;
	}

	foreach ( array( 'fonts', 'css', 'js' ) as $f ) {
		$GLOBALS['tve_ult_res'][ $f ] = isset( $GLOBALS['tve_ult_res'][ $f ] ) ? $GLOBALS['tve_ult_res'][ $f ] : array();
	}

	if ( empty( $for_design ) || empty( $for_design[ TVE_Ult_Const::FIELD_TEMPLATE ] ) ) {
		/**
		 * For newly created designs always include default css to properly display elements on insert
		 */
		tve_ult_enqueue_style( 'tve_ult_cloud_templates', TVE_Ult_Const::plugin_url( 'tcb-bridge/editor-templates/css/cloud_styles.css' ) );

		return array(
			'fonts' => array(),
			'css'   => array( 'tve_ult_cloud_templates' => TVE_Ult_Const::plugin_url( 'tcb-bridge/editor-templates/css/cloud_styles.css' ) . '?ver=' . TVE_Ult_Const::PLUGIN_VERSION ),
			'js'    => array(),
		);
	}

	/** enqueue Custom Fonts, if any */
	$fonts = tve_ult_editor_enqueue_custom_fonts( $for_design );

	$config = tve_ult_editor_get_template_config( $for_design[ TVE_Ult_Const::FIELD_TEMPLATE ] );

	/** custom fonts for the form */
	if ( ! empty( $config['fonts'] ) ) {
		foreach ( $config['fonts'] as $font ) {
			$fonts[ 'tve-ult-font-' . md5( $font ) ] = $font;
			wp_enqueue_style( 'tve-ult-font-' . md5( $font ), $font );
		}
	}
	$css = array();
	/** include also the CSS for each type design */
	if ( strpos( $for_design[ TVE_Ult_Const::FIELD_TEMPLATE ], 'cloud' ) !== false ) {
		tve_ult_enqueue_style( 'tve_ult_cloud_templates', TVE_Ult_Const::plugin_url( 'tcb-bridge/editor-templates/css/cloud_styles.css' ) );
		$css['tve_ult_cloud_templates'] = TVE_Ult_Const::plugin_url( 'tcb-bridge/editor-templates/css/cloud_styles.css' ) . '?ver=' . TVE_Ult_Const::PLUGIN_VERSION;
	} else if ( ! empty( $config['css'] ) ) {
		$css_key = 'tve-ult-' . TU_Template_Manager::type( $for_design[ TVE_Ult_Const::FIELD_TEMPLATE ] ) . '-' . str_replace( '.css', '', $config['css'] );
		tve_ult_enqueue_style( $css_key, TVE_Ult_Const::plugin_url( 'tcb-bridge/editor-templates/css/' . TU_Template_Manager::type( $for_design['post_type'] ) . '/' . $config['css'] ) );
		$css[ $css_key ] = TVE_Ult_Const::plugin_url( 'tcb-bridge/editor-templates/css/' . TU_Template_Manager::type( $for_design['post_type'] ) . '/' . $config['css'] . '?ver=' . TVE_Ult_Const::PLUGIN_VERSION );
	}

	/** if any sdk is needed for the social sharing networks, enqueue that also */
	$globals = $for_design[ TVE_Ult_Const::FIELD_GLOBALS ];
	$js      = array();
	if ( ! empty( $globals['js_sdk'] ) ) {
		foreach ( $globals['js_sdk'] as $handle ) {
			$link                          = tve_social_get_sdk_link( $handle );
			$js[ 'tve_js_sdk_' . $handle ] = $link;
			wp_script_is( 'tve_js_sdk_' . $handle ) || wp_enqueue_script( 'tve_js_sdk_' . $handle, $link, array(), false );
		}
	}

	if ( ! empty( $for_design[ TVE_Ult_Const::FIELD_ICON_PACK ] ) ) {
		tve_enqueue_icon_pack();
	}

	if ( ! empty( $for_design[ TVE_Ult_Const::FIELD_MASONRY ] ) ) {
		wp_enqueue_script( 'jquery-masonry' );
		$js['jquery-masonry'] = includes_url( 'js/jquery/jquery.masonry.min.js' );
	}

	$GLOBALS['tve_ult_res']['fonts'] = array_merge( $GLOBALS['tve_ult_res']['fonts'], $fonts );
	$GLOBALS['tve_ult_res']['js']    = array_merge( $GLOBALS['tve_ult_res']['js'], $js );
	$GLOBALS['tve_ult_res']['css']   = array_merge( $GLOBALS['tve_ult_res']['css'], $css );

	return array(
		'fonts' => $fonts,
		'js'    => $js,
		'css'   => $css,
	);

}

/**
 * Enqueue the default styles when they are needed
 *
 * @return array the enqueued styles
 */
function tve_ult_enqueue_default_scripts() {

	$GLOBALS['tve_ult_res']['css']      = isset( $GLOBALS['tve_ult_res']['css'] ) ? $GLOBALS['tve_ult_res']['css'] : array();
	$GLOBALS['tve_ult_res']['js']       = isset( $GLOBALS['tve_ult_res']['js'] ) ? $GLOBALS['tve_ult_res']['js'] : array();
	$GLOBALS['tve_ult_res']['localize'] = isset( $GLOBALS['tve_ult_res']['localize'] ) ? $GLOBALS['tve_ult_res']['localize'] : array();

	if ( ! wp_script_is( 'tve_frontend' ) ) {
		if ( tve_ultimatum_has_lightspeed() ) {
			\TCB\Lightspeed\JS::get_instance( get_the_ID() )->enqueue_scripts();
		}

		$frontend_options = array(
			'is_editor_page'   => is_editor_page(),
			'page_events'      => array(),
			'is_single'        => 1,
			'ajaxurl'          => admin_url( 'admin-ajax.php' ),
			'social_fb_app_id' => function_exists( 'tve_get_social_fb_app_id' ) ? tve_get_social_fb_app_id() : '',
		);

		/**
		 * Allows adding frontend options from different plugins
		 *
		 * @param $frontend_options
		 */
		$frontend_options = apply_filters( 'tve_frontend_options_data', $frontend_options );

		wp_localize_script( 'tve_frontend', 'tve_frontend_options', $frontend_options );

		$GLOBALS['tve_ult_res']['localize']['tve_frontend_options'] = $frontend_options;
	}
}

/**
 * Checks if we are previewing a design
 *
 * @return bool
 */
function tve_ult_is_preview_page() {
	global $design;

	return tve_ult_is_editable( get_the_ID() ) && ! empty( $design );
}

/**
 * Checks if we are editing a design
 */
function tve_ult_is_editor_page() {
	if ( ! empty( $_REQUEST['ultimatum_editor_page'] ) && wp_doing_ajax() ) {
		return true;
	}

	global $design;

	return isset( $_GET[ TVE_EDITOR_FLAG ] ) && ! empty( $design ) && tve_ult_is_editable( get_the_ID() );
}

/**
 * wrapper over the wp get_option function - it appends the tve_ult_ prefix to the option name
 *
 * @param      $name
 * @param bool $default
 *
 * @return mixed|void
 */
function tve_ult_get_option( $name, $default = false ) {
	$name  = 'tve_ult_' . preg_replace( '/^tve_ult_/', '', $name );
	$value = get_option( $name, $default );
	if ( $name === 'tve_ult_ajax_load' ) {
		return (int) $value;
	}

	return $value;
}

/**
 * Adds close button to the admin bar when editing a design
 *
 * @param $wp_admin_bar
 */
function tve_ult_admin_bar( $wp_admin_bar ) {

	if ( get_post_type() === 'tve_ult_campaign' ) {
		$args = array(
			'id'    => 'tve_button',
			'title' => '<span class="thrive-adminbar-icon"></span>' . __( 'Close Design Editor', 'thrive-ult' ),
			'href'  => 'javascrip:void(0)',
			'meta'  => array(
				'class'   => 'thrive-admin-bar',
				'onclick' => 'window.close();',
			),
		);

		$wp_admin_bar->add_node( $args );
	}

	if ( ! is_admin() && current_user_can( 'edit_posts' ) && $campaigns = tve_ult_get_campaigns_for_promotion_page( get_the_ID() ) ) {
		$args = array(
			'id'    => 'tve_ult_button',
			'title' => '<span class="tvd-tooltipped" data-tooltip="' . __( 'You are currently viewing a page restricted by Thrive Ultimatum. You can only view this page because you are logged in as an admin.', 'thrive-ult' ) . '"><span class="thrive-adminbar-icon-ultimatum"></span>' . __( 'Admin Mode', 'thrive-ult' ) . '</span>',
			'meta'  => array(
				'class' => 'tvu-admin-bar-button',
			),
		);

		$wp_admin_bar->add_node( $args );
	}
}

/**
 * Initialize the Update Checker
 */
function tve_ult_update_checker() {
	new TVE_PluginUpdateChecker(
		'http://service-api.thrivethemes.com/plugin/update',
		TVE_Ult_Const::plugin_path( 'thrive-ultimatum.php' ),
		'thrive-ultimatum',
		12,
		'',
		'thrive_ultimatum'
	);
	add_filter( 'puc_request_info_result-thrive-ultimatum', 'tve_ult_set_product_icon' );
}


/**
 * Called after dash has been loaded
 */
function tve_ult_dashboard_loaded() {
	require_once __DIR__ . '/classes/class-tu-product.php';
}


/**
 * Adding the product icon for the update core page
 *
 * @param $info
 *
 * @return mixed
 */

function tve_ult_set_product_icon( $info ) {
	$info->icons['1x'] = TVE_Ult_Const::plugin_url( 'admin/img/logo_90x90.png' );

	return $info;
}

/**
 * Filter before and after params for Thrive Ultimatum Widgets
 * This only applies if the user has a Thrive theme installed -> remove the white space around the widget.
 *
 * @param array $params
 *
 * @return mixed
 */
function tve_ult_dynamic_sidebar_params( $params ) {
	if ( ! tve_check_if_thrive_theme() ) {
		return $params;
	}
	/**
	 * on our themes, we need to remove any other inside div in order for the widget to have the correct padding
	 */
	if ( $params[0]['widget_name'] === __( 'Thrive Ultimatum', 'thrive-ult' ) ) {
		$params[0]['before_widget'] = '<section id="' . $params[0]['widget_id'] . '">';
		$params[0]['after_widget']  = '</section>';
	}

	return $params;
}

/**
 * Push the campaigns with their shortcode designs into array
 * Used int TCB tve_path_params so we can know what campaign has what shortcode designs
 *
 * @param $data
 *
 * @return mixed
 */
function tve_ult_append_shortcode_campaigns( $data ) {

	$campaigns = tve_ult_get_campaign_with_shortcodes();

	$data['tu_shortcode_campaigns'] = array();
	foreach ( $campaigns as $campaign ) {
		$data['tu_shortcode_campaigns'][ $campaign->ID ] = array(
			'post_title' => $campaign->post_title,
		);
		foreach ( $campaign->designs as $design ) {
			$data['tu_shortcode_campaigns'][ $campaign->ID ]['designs'][ $design['id'] ] = $design['post_title'];
		}
	}

	return $data;
}

/**
 * Callback for TCB TU Shortcode Element
 * for rendering a shortcode design
 *
 * @param      $arguments
 * @param bool $is_editor
 *
 * @return string
 */
function tve_ult_render_shortcode( $arguments, $is_editor = true ) {
	if ( ! $is_editor ) {
		return class_exists( 'TU_Shortcode_Countdown' ) ? TU_Shortcode_Countdown::instance()->code( $arguments['tve_ult_campaign'], $arguments['tve_ult_shortcode'] ) : '';
	}

	$design    = tve_ult_get_design( $arguments['tve_ult_shortcode'] );
	$resources = tve_ult_enqueue_design_scripts( $design );

	if ( empty( $design['tpl'] ) ) {
		return '<div class="thrive-shortcode-html">' . __( 'Shortcode Design not found', 'thrive-ult' ) . '</div>';
	}

	$html = tve_ult_editor_custom_content( $design, $is_editor );
	list( $type, $key ) = TU_Template_Manager::tpl_type_key( $design[ TVE_Ult_Const::FIELD_TEMPLATE ] );
	$html = sprintf(
		'<div class="tve-ult-shortcode tvu-triggered">
			<div class="tl-style" id="tvu_%s" data-state="%s">%s</div>
		</div>',
		$key,
		$design['id'],
		$html
	);

	if ( ! empty( $design[ TVE_Ult_Const::FIELD_INLINE_CSS ] ) ) {
		$css  = apply_filters( 'tcb_custom_css', $design[ TVE_Ult_Const::FIELD_INLINE_CSS ] );
		$html .= sprintf( '<style type="text/css" class="tve_custom_style">%s</style>', stripslashes( $css ) );
	}

	ob_start();
	echo $html;
	foreach ( $resources['fonts'] as $font ) {
		echo '<link href="' . $font . '"/>';
	}
	foreach ( $resources['css'] as $css ) {
		echo '<link href="' . $css . '" type="text/css" rel="stylesheet"/>';
	}
	foreach ( $resources['js'] as $js ) {
		echo '<script type="text/javascript" src="' . $js . '"></script>';
	}
	$output = ob_get_clean();

	return '<div class="thrive-shortcode-html">' . str_replace( array(
			'id="tve_editor"',
			'tve_editor_main_content',
		), '', $output ) . '</div>';
}

/**
 * Query params for inner frame - we also pass the ultimatum for the form
 *
 * @param array $params
 *
 * @return array
 */
function tu_editor_edit_link_query_args( $params = array() ) {
	if ( ! empty( $_GET[ TVE_Ult_Const::DESIGN_QUERY_KEY_NAME ] ) ) {
		$params[ TVE_Ult_Const::DESIGN_QUERY_KEY_NAME ] = $_GET[ TVE_Ult_Const::DESIGN_QUERY_KEY_NAME ];
	}

	return $params;
}

/**
 * Called on plugin activation.
 * Check for minimum required WordPress version
 */
function tve_ult_activation_hook() {
	if ( function_exists( 'tcb_wordpress_version_check' ) && ! tcb_wordpress_version_check() ) {
		/**
		 * Dashboard not loaded yet, force it to load here
		 */
		if ( ! function_exists( 'tve_dash_show_activation_error' ) ) {
			/* Load the dashboard included in this plugin */
			tve_ult_load_dash_version();
			tve_dash_load();
		}

		tve_dash_show_activation_error( 'wp_version', 'Thrive Ultimatum', TCB_MIN_WP_VERSION );
	} else if ( method_exists( '\TCB\Lightspeed\Main', 'first_time_enable_lightspeed' ) ) {
		\TCB\Lightspeed\Main::first_time_enable_lightspeed();
	}
}

/**
 * Hook into TD's migration manager
 *
 * @throws Exception
 */
function tve_ult_prepare_db_migrations() {
	TD_DB_Manager::add_manager(
		TVE_Ult_Const::plugin_path( 'db' ),
		'tve_ult_db_version',
		TVE_Ult_Const::DB_VERSION,
		'Thrive Ultimatum',
		'tve_ult_',
		'tve_ult_db_reset'
	);
}

/**
 * Triggers a campaign based on a code and parameters.
 *
 * @param int    $campaign_id The ID of the campaign.
 * @param string $code        The code to trigger the campaign.
 * @param array  $params      Additional parameters for the campaign.
 *
 * @return \WP_REST_Response The response object indicating success or failure.
 */
function tu_webhook_trigger_campaign( $campaign_id, $code, $params = array() ) {

	$encrypted_email = md5( $params['email'] );

	$campaign = tve_ult_get_campaign( $campaign_id, array( 'get_settings' => true, 'get_logs' => false ) );

	if ( empty( $campaign ) || ! $campaign->tu_schedule_instance instanceof TU_Schedule_Evergreen ) {
		return new \WP_REST_Response( false, 200 );
	}

	if ( $code !== $campaign->settings['trigger']['code'] ) {
		return new \WP_REST_Response( false, 200 );
	}

	if ( tve_ult_get_email_log( $campaign_id, $encrypted_email ) ) {
		return new \WP_REST_Response( false, 200 );
	}

	$use_gmt = $campaign->tu_schedule_instance->use_gmt();

	// set the start date of the campaign
	$date = date( 'Y-m-d H:i:s', $campaign->tu_schedule_instance->now( false, $use_gmt ) );

	$model = array(
		'campaign_id' => $campaign_id,
		'email'       => $params['email'],
		'started'     => $date,
	);

	return tve_ult_save_email_log( $model );
}

/**
 * Trigger campaign start from fluentCRM action
 *
 * @param array $campaign_id
 * @param array $code
 * @param array $params
 *
 * @return array|boolean
 */
function tu_fluentcrm_trigger_campaign( $tagIds, $contact ) {

	$campaigns = tve_ult_get_campaigns( array(
		'get_designs'  => false,
		'get_events'   => false,
		'only_running' => true,
		'lockdown'     => true,
	) );

	foreach ( $campaigns as $campaign ) {
		$trigger = $campaign->settings['trigger'];
		if ( ! empty( $trigger ) && $trigger['api'] === 'fluentcrm' && ! empty( array_intersect( $tagIds, $trigger['ids'] ) ) ) {
			$encrypted_email = md5( $contact->email );

			if ( tve_ult_get_email_log( $campaign->ID, $encrypted_email ) ) {
				continue;
			}

			$use_gmt = $campaign->tu_schedule_instance->use_gmt();

			// set the start date of the campaign
			$date = date( 'Y-m-d H:i:s', $campaign->tu_schedule_instance->now( false, $use_gmt ) );

			$model = array(
				'campaign_id' => $campaign->ID,
				'email'       => $encrypted_email,
				'started'     => $date,
			);

			tve_ult_save_email_log( $model );
		}
	}

	return true;
}

/**
 * The function triggers an evergreen countdown campaign for a specific user
 *
 * @param int    $campaign_id Campaign Id
 * @param string $user_email  User Email
 *
 * @return boolean
 *          - true if the campaign was successfully started</br>
 *          - false if the type of the selected campaign isn’t evergreen
 *
 * @api
 */
function tu_start_campaign( $campaign_id, $user_email ) {

	$campaign = tve_ult_get_campaign( $campaign_id, array( 'get_settings' => true ) );

	if ( $campaign->type !== TVE_Ult_Const::CAMPAIGN_TYPE_EVERGREEN ) {
		return false;
	}

	$use_gmt         = $campaign->tu_schedule_instance->use_gmt();
	$encrypted_email = md5( $user_email );
	$date            = date( 'Y-m-d H:i:s', $campaign->tu_schedule_instance->now( false, $use_gmt ) );

	/**
	 * Only check if email exists
	 */
	if ( $user_email && tve_ult_get_email_log( $campaign_id, $encrypted_email ) ) {
		return true;
	}

	$model = array(
		'campaign_id' => $campaign_id,
		'email'       => $encrypted_email,
		'started'     => $date,
	);

	$campaign->tu_schedule_instance->applies( true, $user_email );

	/**
	 * Return for no email because we dont have to save the log
	 */
	return $user_email ? tve_ult_save_email_log( $model ) : true;
}

/**
 * The function removes a user from an evergreen countdown campaign
 *
 * @param int    $campaign_id Campaign Id
 * @param string $user_email  User Email
 *
 * @return boolean
 *          - true if the campaign was successfully started</br>
 *          - false if the type of the selected campaign isn’t evergreen
 *
 * @api
 */
function tu_end_campaign( $campaign_id, $user_email ) {

	$campaign = tve_ult_get_campaign( $campaign_id, array( 'get_settings' => true ) );

	if ( $campaign->type !== TVE_Ult_Const::CAMPAIGN_TYPE_EVERGREEN ) {
		return false;
	}

	$encrypted_email = md5( $user_email );
	$email_log       = tve_ult_get_email_log( $campaign_id, $encrypted_email );
	if ( empty( $email_log ) ) {
		return null;
	}

	$email_log['end'] = 1;

	return tve_ult_save_email_log( $email_log );
}

/**
 * Utility function for
 *
 * @return mixed
 */
function tve_ult_search_by_title( $search, $wp_query ) {
	if ( ! empty( $search ) && ! empty( $wp_query->query_vars['search_terms'] ) ) {
		global $wpdb;
		$q = $wp_query->query_vars;

		$n      = ! empty( $q['exact'] ) ? '' : '%';
		$search = array();
		foreach ( ( array ) $q['search_terms'] as $term ) {
			$search[] = $wpdb->prepare( "$wpdb->posts.post_title LIKE %s", $n . $wpdb->esc_like( $term ) . $n );
		}
		$search = ' AND ' . implode( ' AND ', $search );
	}

	return $search;
}

/**
 * Get all campaigns for a specific user email and check if he has the cookie set or set it
 * e.g. the cookies might not be set if the user has been added to the campaign from a webhook or a cron job
 *
 * @return void
 */
function tve_ult_check_email_cookies() {
	if ( is_user_logged_in() ) {
		$user = wp_get_current_user();
		if ( $user instanceof WP_User ) {

			global $tve_ult_db;

			$email         = $user->user_email;
			$campaign_logs = $tve_ult_db->get_campaigns_by_email( $email );

			foreach ( $campaign_logs as $logs ) {
				$cookie_name = TVE_Ult_Const::COOKIE_NAME . $logs['campaign_id'];
				if ( ! isset( $_COOKIE[ $cookie_name ] ) ) {
					$campaign_id = $logs['campaign_id'];
					$campaign    = tve_ult_get_campaign( $campaign_id, [
						'get_settings' => true,
						'get_logs'     => false,
					] );

					if ( $campaign->type !== TVE_Ult_Const::CAMPAIGN_TYPE_EVERGREEN ) {
						continue;
					}

					$evergreen_instance = $campaign->tu_schedule_instance;
					$parts              = explode( ' ', $logs['started'] );
					$cookie_data        = [
						'start_date' => [
							'date' => $parts[0],
							'time' => $parts[1],
						],
						'lockdown'   => [
							'email'  => $email,
							'log_id' => $logs['id'],
						],
					];

					$cookie_data = $evergreen_instance->set_cookie_data( $cookie_data, $campaign_id );

					$evergreen_instance->setCookie( $cookie_data['value'], $cookie_data['expire'] );
				}
			}
		}
	}
}

