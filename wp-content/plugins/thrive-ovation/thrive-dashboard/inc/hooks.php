<?php

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

function tve_dash_set_dash_url() {
	if ( $GLOBALS['tve_dash_loaded_from'] === 'plugins' ) {
		defined( 'TVE_DASH_URL' ) || define( 'TVE_DASH_URL', untrailingslashit( plugins_url() ) . '/' . trim( $GLOBALS['tve_dash_included']['folder'], '/\\' ) . '/thrive-dashboard' );
	} else {
		defined( 'TVE_DASH_URL' ) || define( 'TVE_DASH_URL', untrailingslashit( get_template_directory_uri() ) . '/thrive-dashboard' );
	}
}

/**
 * Hook for "init" wp action
 */
function tve_dash_init_action() {
	tve_dash_set_dash_url();

	defined( 'TVE_DASH_IMAGES_URL' ) || define( 'TVE_DASH_IMAGES_URL', TVE_DASH_URL . '/css/images' );

	require_once( TVE_DASH_PATH . '/inc/font-import-manager/classes/Tve_Dash_Font_Import_Manager.php' );
	require_once( TVE_DASH_PATH . '/inc/font-manager/font-manager.php' );

	/**
	 * Run any database migrations
	 */
	if ( defined( 'TVE_TESTS_RUNNING' ) || is_admin() ) {
		TD_DB_Manager::collect_migration_managers();
	}
}

/**
 * Add main Thrive Dashboard item to menu
 */
function tve_dash_admin_menu() {
	add_menu_page(
		'Thrive Dashboard',
		'Thrive Dashboard',
		TVE_DASH_CAPABILITY,
		'tve_dash_section',
		'tve_dash_section',
		TVE_DASH_IMAGES_URL . '/logo-icon.png'
	);

	if ( is_super_admin() ) {
		add_submenu_page(
			'',
			__( 'Access Manager', 'thrive-dash' ),
			__( 'Access Manager', 'thrive-dash' ),
			'manage_options',
			'tve_dash_access_manager',
			function () {
				require_once( TVE_DASH_PATH . '/inc/access-manager/includes/templates/access-manager.php' );
			}
		);
	}

	add_submenu_page(
		'',
		__( 'System Info', 'thrive-dash' ),
		__( 'System Info', 'thrive-dash' ),
		'manage_options',
		'tve-debug',
		function () {
			tve_dash_enqueue();
			require_once( TVE_DASH_PATH . '/inc/plugin-updates/debug-screen.php' );
		}
	);

	add_submenu_page(
		'',
		__( 'Update Info', 'thrive-dash' ),
		__( 'Update Info', 'thrive-dash' ),
		'manage_options',
		'tve-updates',
		static function () {
			require_once( TVE_DASH_PATH . '/inc/plugin-updates/update-channel.php' );
		}
	);

	add_submenu_page(
		'',
		__( 'Update Info', 'thrive-dash' ),
		__( 'Update Info', 'thrive-dash' ),
		'manage_options',
		'tve-update-switch-stable-channel',
		static function () {
			//Nonce check
			check_admin_referer( 'tvd_switch_stable_channel_nonce' );

			$defaults = array(
				'page'            => 'tve-update-switch-stable-channel',
				'name'            => '',
				'current_version' => 0, //Needed only for UI
				'plugin_file'     => '',
				'_wpnonce'        => '', //Nonce key
				'plugin_slug'     => '',
				'type'            => '', //Theme OR Plugin
			);
			$args     = wp_parse_args( $_GET, $defaults );

			if ( ! empty( $args['type'] ) && ! empty( $args['tvd_channel'] ) && $args['tvd_channel'] === 'tvd_switch_to_stable_channel' && in_array( $args['type'], [
					'plugin',
					'theme',
				] ) ) {
				$name = sanitize_text_field( $args['name'] );

				if ( $args['type'] === 'theme' ) {

					$theme = 'thrive-theme';

					require_once( TVE_DASH_PATH . '/inc/plugin-updates/classes/class-tvd-theme-upgrader.php' );

					$theme_upgrader = new TVD_Theme_Upgrader( new Theme_Upgrader_Skin( array(
						'title' => $name,
						'nonce' => 'upgrade-plugin_' . $theme,
						'url'   => 'index.php?page=' . esc_url( $args['page'] ) . '&theme_file=' . $theme . 'action=upgrade-theme',
						'theme' => $theme,
					) ) );
					$theme_upgrader->get_latest_version( $theme );
				} else if ( $args['type'] === 'plugin' ) {
					require_once( TVE_DASH_PATH . '/inc/plugin-updates/classes/class-tvd-plugin-upgrader.php' );

					$plugin_upgrader = new TVD_Plugin_Upgrader( new Plugin_Upgrader_Skin( array(
						'title'  => $name,
						'nonce'  => 'upgrade-plugin_' . esc_html( $args['plugin_slug'] ),
						'url'    => 'index.php?page=' . esc_url( $args['page'] ) . '&plugin_file=' . esc_url( $args['plugin_file'] ) . 'action=upgrade-plugin',
						'plugin' => esc_html( $args['plugin_slug'] ),
					) ) );
					$plugin_upgrader->get_latest_version( $args['plugin_file'] );
				}
			} else {
				tve_dash_enqueue();
				require_once( TVE_DASH_PATH . '/inc/plugin-updates/update-switch-stable-channel.php' );
			}
		}
	);

	/**
	 * @param tve_dash_section parent slug
	 */
	do_action( 'tve_dash_add_menu_item', 'tve_dash_section' );

	$menus = array(
		'license_manager'     => array(
			'parent_slug' => tve_dash_is_plugin_active( 'thrive-product-manager' ) ? '' : 'tve_dash_section',
			'page_title'  => __( 'Thrive License Manager', 'thrive-dash' ),
			'menu_title'  => __( 'License Manager', 'thrive-dash' ),
			'capability'  => 'manage_options',
			'menu_slug'   => 'tve_dash_license_manager_section',
			'function'    => 'tve_dash_license_manager_section',
		),
		'general_settings'    => array(
			'parent_slug' => '',
			'page_title'  => __( 'Thrive General Settings', 'thrive-dash' ),
			'menu_title'  => __( 'General Settings', 'thrive-dash' ),
			'capability'  => TVE_DASH_CAPABILITY,
			'menu_slug'   => 'tve_dash_general_settings_section',
			'function'    => 'tve_dash_general_settings_section',
		),
		/* Font Manager Page */
		'font_manager'        => array(
			'parent_slug' => '',
			'page_title'  => __( 'Thrive Font Manager', 'thrive-dash' ),
			'menu_title'  => __( 'Thrive Font Manager', 'thrive-dash' ),
			'capability'  => TVE_DASH_CAPABILITY,
			'menu_slug'   => 'tve_dash_font_manager',
			'function'    => 'tve_dash_font_manager_main_page',
		),
		/* Font Import Manager Page */
		'font_import_manager' => array(
			'parent_slug' => '',
			'page_title'  => __( 'Thrive Font Import Manager', 'thrive-dash' ),
			'menu_title'  => __( 'Thrive Font Import Manager', 'thrive-dash' ),
			'capability'  => TVE_DASH_CAPABILITY,
			'menu_slug'   => 'tve_dash_font_import_manager',
			'function'    => 'tve_dash_font_import_manager_main_page',
		),
		'icon_manager'        => array(
			'parent_slug' => '',
			'page_title'  => __( 'Icon Manager', 'thrive-dash' ),
			'menu_title'  => __( 'Icon Manager', 'thrive-dash' ),
			'capability'  => TVE_DASH_CAPABILITY,
			'menu_slug'   => 'tve_dash_icon_manager',
			'function'    => 'tve_dash_icon_manager_main_page',
		),
	);

	$thrive_products_order = tve_dash_get_menu_products_order();
	$menus                 = array_merge( $menus, apply_filters( 'tve_dash_admin_product_menu', array() ) );

	foreach ( $thrive_products_order as $order => $menu_short ) {
		if ( array_key_exists( $menu_short, $menus ) ) {
			add_submenu_page( $menus[ $menu_short ]['parent_slug'] ?: '', $menus[ $menu_short ]['page_title'], $menus[ $menu_short ]['menu_title'], $menus[ $menu_short ]['capability'], $menus[ $menu_short ]['menu_slug'], $menus[ $menu_short ]['function'] );
		}
	}
}

/**
 * Plugin Action Links
 *
 * Injects a stable link into plugin actions links used to switch Beta Versions of Thrive Plugins to Stable Versions
 *
 * @param $actions
 * @param $plugin_file
 * @param $plugin_data
 * @param $context
 *
 * @return array $actions
 */
add_filter( 'plugin_action_links', static function ( $actions, $plugin_file, $plugin_data, $context ) {

	if ( ! isset( $plugin_data['Version'] ) || ( function_exists( 'is_plugin_active' ) && ! is_plugin_active( $plugin_file ) ) ) {
		return $actions;
	}

	// Multisite check.
	if ( is_multisite() && ( ! is_network_admin() && ! is_main_site() ) ) {
		return $actions;
	}

	$slug = isset( $plugin_data['slug'] ) ? $plugin_data['slug'] : dirname( plugin_basename( $plugin_file ) );

	if ( strpos( $slug, 'thrive-' ) !== false && strpos( $plugin_data['Version'], 'beta' ) !== false && tvd_update_is_using_stable_channel() ) {
		$stable_url = add_query_arg(
			array(
				'current_version' => urlencode( $plugin_data['Version'] ),
				'name'            => urlencode( $plugin_data['Name'] ),
				'plugin_slug'     => urlencode( $slug ),
				'_wpnonce'        => wp_create_nonce( 'tvd_switch_stable_channel_nonce' ),
				'type'            => 'plugin',
				'plugin_file'     => $plugin_file,
				'page'            => 'tve-update-switch-stable-channel',
			), admin_url( 'admin.php' ) );

		$actions['tvd-switch-stable-update'] = '<a href="' . esc_url( $stable_url ) . '">' . __( 'Switch to stable version', 'thrive-dash' ) . '</a>';
	}

	return $actions;
}, 10, 4 );

function tve_dash_icon_manager_main_page() {
	$tve_icon_manager = Tve_Dash_Thrive_Icon_Manager::instance();
	$tve_icon_manager->mainPage();
}

function tve_dash_font_import_manager_main_page() {
	$font_import_manager = Tve_Dash_Font_Import_Manager::getInstance();
	$font_import_manager->mainPage();
}

/**
 * Checks if the current screen (current admin screen) needs to have the dashboard scripts and styles enqueued
 *
 * @param string $hook current admin page hook
 */
function tve_dash_needs_enqueue( $hook ) {
	$accepted_hooks = array(
		'toplevel_page_tve_dash_section',
		'thrive-dashboard_page_tve_dash_license_manager_section',
		'admin_page_tve_dash_api_connect',
		'admin_page_tve_dash_api_error_log',
		'admin_page_tve_dash_api_connect',
		'thrive-dashboard_page_tve_dash_access_manager',
		'admin_page_tve-updates',
	);

	$accepted_hooks = apply_filters( 'tve_dash_include_ui', $accepted_hooks, $hook );

	return in_array( $hook, $accepted_hooks );
}

function tve_dash_admin_enqueue_scripts( $hook ) {

	if ( $hook === 'themes.php' && tve_dash_is_ttb_active() ) {

		$thrive_theme = wp_get_theme();

		if ( tvd_update_is_using_stable_channel() && strpos( $thrive_theme->get( 'Version' ), 'beta' ) !== false ) {
			$stable_url = add_query_arg(
				array(
					'current_version' => urlencode( $thrive_theme->get( 'Version' ) ),
					'name'            => urlencode( $thrive_theme->get( 'Name' ) ),
					'plugin_slug'     => urlencode( 'thrive-theme' ),
					'_wpnonce'        => wp_create_nonce( 'tvd_switch_stable_channel_nonce' ),
					'type'            => 'theme',
					'page'            => 'tve-update-switch-stable-channel',
				), admin_url( 'admin.php' ) );

			wp_enqueue_script( 'tve-dash-theme-switch-stable', TVE_DASH_URL . '/inc/plugin-updates/js/themes-switch-stable.js', array(
				'jquery',
				'backbone',
				'theme',
			), false, true );

			wp_localize_script( 'tve-dash-theme-switch-stable', 'TVD_STABLE_THEME',
				array(
					'name'      => $thrive_theme->name,
					'link_html' => '<a href="' . $stable_url . '" style="position:absolute;right: 5px; bottom: 5px;" class="tvd-switch-stable-theme button">Switch to stable version</a>',
				)
			);
		}
	}

	if ( tve_dash_needs_enqueue( $hook ) ) {
		tve_dash_enqueue();
		wp_enqueue_media(); //Weeded for wp object localization in JS
	}

	/**
	 * Enqueue roboto from gutenberg blocks
	 */
	if ( ! tve_dash_is_google_fonts_blocked() && tve_should_load_blocks() ) {
		tve_dash_enqueue_style( 'tve-block-font', '//fonts.googleapis.com/css?family=Roboto:400,500,700' );
	}
}

/**
 * Whether we should thrive blocks
 *
 * @return bool
 */
function tve_should_load_blocks() {
	$allow  = false;
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

	if ( $screen !== null ) {
		$allow = $screen->is_block_editor();
	}

	return $allow;
}

/**
 * Dequeue conflicting scripts
 *
 * @param string $hook
 */
function tve_dash_admin_dequeue_conflicting( $hook ) {
	if ( isset( $GLOBALS['tve_dash_resources_enqueued'] ) || tve_dash_needs_enqueue( $hook ) ) {
		// NewsPaper messing about and including css / scripts all over the admin panel
		wp_dequeue_style( 'select2' );
		wp_deregister_style( 'select2' );
		wp_dequeue_script( 'select2' );
		wp_deregister_script( 'select2' );

		//FS poster select2
		wp_dequeue_style( 'fsp-select2' );
		wp_deregister_style( 'fsp-select2' );
		wp_dequeue_style( 'fsp-select2-custom' );
		wp_deregister_style( 'fsp-select2-custom' );
		wp_dequeue_script( 'fsp-select2' );
		wp_deregister_script( 'fsp-select2' );
	}
}

/**
 * Additional generic data for Vue views
 * Should be included only in Dashboard pages that use Vue
 *
 * @return void
 */
function tve_dash_enqueue_vue() {
	include_once TVE_DASH_PATH . '/css/font/dashboard-icons.svg';
	wp_enqueue_style( 'media' );
	wp_enqueue_media();
	tve_dash_enqueue_script( 'tve-dash-main-vue', TVE_DASH_URL . '/assets/dist/js/dash-vue.js', [
		'lodash',
		'jquery',
	] );

	wp_localize_script( 'tve-dash-main-vue', 'TD', [
		'rest_nonce' => wp_create_nonce( 'wp_rest' ),
		'dash_url'   => esc_url( admin_url( 'admin.php?page=tve_dash_section' ) ),
	] );

	wp_enqueue_style( 'td-font', '//fonts.googleapis.com/css?family=Roboto:200,300,400,500,600,700,800' );

	/**
	 * SUPP-15199 remove active campaign calendar that overwrites setfullyear and breaks other things
	 */
	remove_filter( 'mce_external_plugins', 'activecampaign_add_buttons' );
}

function tve_dash_enqueue_licensing_assets() {
	add_action( 'admin_print_footer_scripts', static function () {
		tve_dash_output_backbone_templates( [ 'license-modal' => TVE_DASH_PATH . '/templates/backbone/license-modal.phtml' ] );
	} );

	tve_dash_enqueue_style( 'tve-dash-licensing-css', TVE_DASH_URL . '/css/licensing.css' );

	tve_dash_enqueue_script( 'tve-dash-main-js', TVE_DASH_URL . '/js/dist/tve-dash' . ( tve_dash_is_debug_on() ? '.js' : '.min.js' ), array(
		'jquery',
		'backbone',
	) );
	wp_localize_script( 'tve-dash-main-js', 'TVE_Dash_Const', tve_dash_get_dash_const_options() );
}

/**
 * js localized options
 */
function tve_dash_get_dash_const_options() {
	$options = array(
		'nonce'              => wp_create_nonce( 'tve-dash' ),
		'dash_url'           => TVE_DASH_URL,
		'actions'            => array(
			'backend_ajax'        => 'tve_dash_backend_ajax',
			'ajax_delete_api_log' => 'tve_dash_api_delete_log',
			'ajax_retry_api_log'  => 'tve_dash_api_form_retry',
		),
		'routes'             => array(
			'settings'                      => 'generalSettings',
			'license'                       => 'license',
			'active_states'                 => 'activeState',
			'error_log'                     => 'getErrorLogs',
			'affiliate_links'               => 'affiliateLinks',
			'add_aff_id'                    => 'saveAffiliateId',
			'get_aff_id'                    => 'getAffiliateId',
			'token'                         => 'token',
			'save_token'                    => 'saveToken',
			'delete_token'                  => 'deleteToken',
			'change_capability'             => 'changeCapability',
			'update_user_functionality'     => 'updateUserFunctionality',
			'reset_capabilities_to_default' => 'resetCapabilitiesToDefault',
		),
		'translations'       => array(
			'UnknownError'     => __( 'Unknown error', 'thrive-dash' ),
			'Deleting'         => __( 'Deleting...', 'thrive-dash' ),
			'Testing'          => __( 'Testing...', 'thrive-dash' ),
			'Loading'          => __( 'Loading...', 'thrive-dash' ),
			'ConnectionWorks'  => __( 'Connection works!', 'thrive-dash' ),
			'ConnectionFailed' => __( 'Connection failed!', 'thrive-dash' ),
			'Unlimited'        => __( 'Unlimited', 'thrive-dash' ),
			'CapabilityError'  => __( 'You are not allowed to remove this capability!', 'thrive-dash' ),
			'RequestError'     => 'Request error, please contact Thrive developers !',
			'Copy'             => 'Copy',
			'ImportedKit'      => __( 'Kit successfully imported', 'thrive-dash' ),
			'RemovedKit'       => __( 'Kit removed', 'thrive-dash' ),
		),
		'products'           => array(
			TVE_Dash_Product_LicenseManager::ALL_TAG => 'All products',
			TVE_Dash_Product_LicenseManager::TCB_TAG => 'Thrive Architect',
			TVE_Dash_Product_LicenseManager::TL_TAG  => 'Thrive Leads',
			TVE_Dash_Product_LicenseManager::TCW_TAG => 'Thrive Clever Widgets',
		),
		'license_types'      => array(
			'individual' => __( 'Individual product', 'thrive-dash' ),
			'full'       => __( 'Full membership', 'thrive-dash' ),
		),
		'is_polylang_active' => tve_dash_is_plugin_active( 'polylang' ),
		'tvd_fa_kit'         => get_option( 'tvd_fa_kit', '' ),
		'license_rest_url'   => get_rest_url() . 'td/v1/license_warning',
	);


	/**
	 * Allow vendors to hook into this
	 * TVE_Dash is the output js object
	 */
	return apply_filters( 'tve_dash_localize', $options );
}

/**
 * enqueue the dashboard CSS and javascript files
 */
function tve_dash_enqueue() {
	$js_suffix = tve_dash_is_debug_on() ? '.js' : '.min.js';

	tve_dash_enqueue_script( 'tve-dash-main-js', TVE_DASH_URL . '/js/dist/tve-dash' . $js_suffix, array(
		'jquery',
		'backbone',
	) );

	wp_enqueue_script( 'jquery-zclip', TVE_DASH_URL . '/js/util/jquery.zclip.1.1.1/jquery.zclip.min.js', array( 'jquery' ) );
	tve_dash_enqueue_style( 'tve-dash-styles-css', TVE_DASH_URL . '/css/styles.css' );

	wp_localize_script( 'tve-dash-main-js', 'TVE_Dash_Const', tve_dash_get_dash_const_options() );
	tve_dash_enqueue_script( 'tvd-fa-kit', get_option( 'tvd_fa_kit', '' ) );

	/**
	 * Localize token data
	 */
	$token_options          = array();
	$token_options['model'] = get_option( 'thrive_token_support' );
	if ( ! empty( $token_options['model']['token'] ) && ! get_option( 'tve_dash_generated_token' ) ) {
		/* Backwards-compat: store this option separately in the database */
		update_option( 'tve_dash_generated_token', array(
			'token'   => $token_options['model']['token'],
			'referer' => $token_options['model']['referer'],
		) );
	}
	wp_localize_script( 'tve-dash-main-js', 'TVE_Token', $token_options );

	/**
	 * output the main tpls for backbone views used in dashboard
	 */

	add_action( 'admin_print_footer_scripts', 'tve_dash_backbone_templates' );

	Tve_Dash_Icon_Manager::enqueue_fontawesome_styles();
	/**
	 * set this flag here so we can later remove conflicting scripts / styles
	 */
	$GLOBALS['tve_dash_resources_enqueued'] = true;
}

/**
 * main entry point for the incoming ajax requests
 *
 * passes the request to the TVE_Dash_AjaxController for processing
 */
function tve_dash_backend_ajax() {
	check_ajax_referer( 'tve-dash' );

	if ( ! current_user_can( TVE_DASH_CAPABILITY ) ) {
		wp_die( '' );
	}
	$response = TVE_Dash_AjaxController::instance()->handle();

	wp_send_json( $response );
}


function tve_dash_reset_license() {
	$options = array(
		'tcb'    => 'tve_license_status|tve_license_email|tve_license_key',
		'tl'     => 'tve_leads_license_status|tve_leads_license_email|tve_leads_license_key',
		'tcw'    => 'tcw_license_status|tcw_license_email|tcw_license_key',
		'themes' => 'thrive_license_status|thrive_license_key|thrive_license_email',
		'dash'   => 'thrive_license',
	);

	if ( ! empty( $_POST['products'] ) ) {
		$filtered = array_intersect_key( $options, array_map( 'sanitize_text_field', array_flip( $_POST['products'] ) ) );
		foreach ( explode( '|', implode( '|', $filtered ) ) as $option ) {
			delete_option( $option );
		}
		$message = 'Licenses reset for: ' . implode( ', ', array_keys( $filtered ) );

		$dash_license = get_option( 'thrive_license', array() );
		foreach ( array_map( 'sanitize_text_field', $_POST['products'] ) as $prod ) {
			unset( $dash_license[ $prod ] );
		}
		update_option( 'thrive_license', $dash_license );

	}

	require dirname( dirname( ( __FILE__ ) ) ) . '/templates/settings/reset.phtml';
}

function tve_dash_load_text_domain() {
	$domain = 'thrive-dash';
	$locale = $locale = apply_filters( 'plugin_locale', get_locale(), $domain );

	$path = 'thrive-dashboard/languages';
	//$path = apply_filters('tve_dash_filter_plugin_languages_path', $path);

	load_textdomain( $domain, WP_LANG_DIR . '/thrive/' . $domain . "-" . $locale . ".mo" );
	load_plugin_textdomain( $domain, false, $path );
}

/**
 *
 * fetches and outputs the backbone templates needed for thrive dashboard
 *
 * called on 'admin_print_footer_scripts'
 *
 */
function tve_dash_backbone_templates() {
	$templates       = tve_dash_get_backbone_templates( plugin_dir_path( dirname( __FILE__ ) ) . 'templates/backbone', 'backbone' );
	$templates_modal = tve_dash_get_backbone_templates( plugin_dir_path( dirname( __FILE__ ) ) . 'templates/modal', 'templates' );

	tve_dash_output_backbone_templates( array_merge( $templates, $templates_modal ) );
}

/**
 * Returns the disable state of the google fonts
 *
 * @return bool
 */
function tve_dash_is_google_fonts_blocked() {
	return (bool) get_option( 'tve_google_fonts_disable_api_call', '' );
}

/**
 * Returns the disable state of the google fonts
 *
 * @return bool
 */
function tve_dash_allow_video_src() {
	return (bool) get_option( 'tve_allow_video_src', '' );
}

/**
 * output script nodes for backbone templates
 *
 * @param array $templates
 */
function tve_dash_output_backbone_templates( $templates, $prefix = '', $suffix = '' ) {

	foreach ( $templates as $tpl_id => $path ) {
		$tpl_id = $prefix . $tpl_id . $suffix;

		ob_start();
		include $path;
		$content = ob_get_clean();

		echo '<script type="text/template" id="' . esc_attr( $tpl_id ) . '">' . tve_dash_escape_script_tags( $content ) . '</script>';

	}
}

/**
 * Some plugins add inline scripts thinking that this is the frontend render, which ruins the backbone html <script></script> tags and breaks the HTML afterwards.
 * As a fix, we replace the inner script tags with <tve-script>, and reverse this operation when we apply backbone templates in the editor.
 *
 * @param $content
 *
 * @return string|string[]
 */
function tve_dash_escape_script_tags( $content ) {
	return str_replace( array( '<script', '</script>' ), array( '<tve-script', '</tve-script>' ), $content );
}

/**
 * include the backbone templates in the page
 *
 * @param string $dir basedir for template search
 * @param string $root
 */
function tve_dash_get_backbone_templates( $dir = null, $root = 'backbone' ) {
	if ( null === $dir ) {
		$dir = plugin_dir_path( dirname( __FILE__ ) ) . 'templates/backbone';
	}

	$folders   = scandir( $dir );
	$templates = array();

	foreach ( $folders as $item ) {
		if ( in_array( $item, array( ".", ".." ) ) ) {
			continue;
		}

		if ( is_dir( $dir . '/' . $item ) ) {
			$templates = array_merge( $templates, tve_dash_get_backbone_templates( $dir . '/' . $item, $root ) );
		}

		if ( is_file( $dir . '/' . $item ) ) {
			$_parts     = explode( $root, $dir );
			$_truncated = end( $_parts );
			$tpl_id     = ( ! empty( $_truncated ) ? trim( $_truncated, '/\\' ) . '/' : '' ) . str_replace( array(
					'.php',
					'.phtml',
				), '', $item );

			$tpl_id = str_replace( array( '/', '\\' ), '-', $tpl_id );

			$templates[ $tpl_id ] = $dir . '/' . $item;
		}
	}

	return $templates;
}

/**
 * enqueue the frontend.js script
 */
function tve_dash_frontend_enqueue() {

	/**
	 * action filter - can be used to skip inclusion of dashboard frontend script
	 *
	 * each product should hook and return true if it needs this script
	 *
	 * @param bool $include
	 */
	$include = apply_filters( 'tve_dash_enqueue_frontend', false );

	if ( ! $include ) {
		return false;
	}

	tve_dash_set_dash_url();

	tve_dash_enqueue_script( 'tve-dash-frontend', TVE_DASH_URL . '/js/dist/frontend.min.js', array( 'jquery' ), false, true );

	$captcha_api    = Thrive_Dash_List_Manager::credentials( 'recaptcha' );
	$show_recaptcha = ! empty( $captcha_api['connection']['version'] ) && $captcha_api['connection']['version'] === 'v3' && ! empty( $captcha_api['connection']['browsing_history'] );

	if ( apply_filters( 'thrive_dashboard_show_recaptcha', $show_recaptcha ) ) {
		tve_dash_enqueue_script( 'tve-dash-recaptcha', 'https://www.google.com/recaptcha/api.js?render=' . $captcha_api['site_key'] );
	}
	unset( $captcha_api['secret_key'] );
	/**
	 * When a caching plugin is active on the user's site, we need to always send the first ajax load request - we cannot know for sure if the page will be cached for a crawler or a regular visitor
	 */

	$force_ajax_send = tve_dash_detect_cache_plugin();
	$data            = array(
		'ajaxurl'         => admin_url( 'admin-ajax.php' ),
		/**
		 * 'force_send_ajax' => true if any caching plugin is active
		 */
		'force_ajax_send' => $force_ajax_send !== false,
		/**
		 * 'is_crawler' only matters in case there is cache plugin active
		 * IF we find an active caching plugin -> 'is_crawler' is irrelevant and the initial ajax request will be always sent
		 */
		'is_crawler'      => $force_ajax_send !== false ? false : (bool) tve_dash_is_crawler( true ),
		// Apply the filter to allow overwriting the bot detection. Can be used by 3rd party plugins to force the initial ajax request
		'recaptcha'       => $captcha_api,
		'post_id'         => get_the_ID(),
	);
	wp_localize_script( 'tve-dash-frontend', 'tve_dash_front', $data );
}

/**
 * main AJAX request entry point
 * this is sent out by thrive dashboard on every request
 *
 * $_POST[data] has the following structure:
 * [tcb] => array(
 *  key1 => array(
 *      action => some_tcb_action
 *      other_data => ..
 *  ),
 *  key2 => array(
 *      action => another_tcb_action
 *  )
 * ),
 * [tl] => array(
 * ..
 * )
 */
function tve_dash_frontend_ajax_load() {
	$response = array();
	if ( empty( $_POST['tve_dash_data'] ) || ! is_array( $_POST['tve_dash_data'] ) ) { // phpcs:ignore
		wp_send_json( $response );
	}

	if ( isset( $_POST['post_id'] ) ) {
		global $post;

		$post = get_post( $_POST['post_id'] );
	}
	//set a global to know we are on dashboard lazy load
	$GLOBALS['tve_dash_frontend_ajax_load'] = true;
	foreach ( map_deep( $_POST['tve_dash_data'], 'sanitize_text_field' ) as $key => $data ) {
		/**
		 * this is a really ugly one, but is required, because code from various plugins relies on $_POST / $_REQUEST
		 */
		foreach ( $data as $k => $v ) {
			$_REQUEST[ $k ] = $v;
			$_POST[ $k ]    = $v;
			$_GET[ $k ]     = $v;
		}
		/**
		 * action filter - each product should have its own implementation of this
		 *
		 * @param array $data
		 */
		$response[ $key ] = apply_filters( 'tve_dash_main_ajax_' . $key, array(), $data );
	}

	if ( ! empty( $GLOBALS['tve_dash_resources'] ) ) {
		$response['__resources'] = $GLOBALS['tve_dash_resources'];
	}

	/**
	 * Used for changing the response on dashboard requests
	 */
	$response = apply_filters( 'tve_dash_frontend_ajax_response', $response );

	$GLOBALS['tve_dash_frontend_ajax_load'] = false;

	wp_send_json( $response );
}

/**
 * Compatibility with WP Deferred Javascripts
 */
add_filter( 'do_not_defer', 'exclude_canvas_script' );
function exclude_canvas_script( $do_not_defer ) {

	$defer_array = array(
		'tho-footer-js',
		'tve-main-frame',
		'tve_editor',
		get_site_url() . '/wp-includes/js/utils.min.js',
		'thrive-main-script',
		'thrive-main-script',
		'thrive-admin-postedit',
		'tve-leads-editor',
		'tve-dash-frontend',
		'tvo_slider',
		'tve_frontend',
		'tve_leads_frontend',
		'media-editor', // wp media
		'jquery-ui-sortable', // sortable
		'tge-editor', // Quiz Builder Graph Editor
		'jquery-ui-draggable', // Quiz Builder Image Editor
		'tge-jquery', // Quiz Builder Image Editor
		'spectrum-script', // Quiz Builder Image Editor
		'tie-editor-script', // Quiz Builder Image Editor
		'tie-html2canvas', // Quiz Builder Image Editor
		'tqb-frontend', // Quiz Builder Front-End
	);

	$do_not_defer = array_merge( $do_not_defer, $defer_array );

	return $do_not_defer;
}

/**
 * For $post_types we add meta data that block google crawl to index the page.
 */
function tve_dash_custom_post_no_index() {
	if ( ! tve_dash_should_index_page() ) {
		echo '<meta name="robots" content="noindex">';
	}
}

/**
 * Whether or not the current page should be indexed by crawlers
 *
 * @return bool
 */
function tve_dash_should_index_page() {
	/**
	 * Filter a list of post types that should not be indexed by crawlers.
	 *
	 * @param array $post_types
	 *
	 * @return array
	 */
	$post_types = apply_filters( 'tve_dash_exclude_post_types_from_index', array() );

	$should_index = empty( $post_types ) || ! is_singular( $post_types );

	/**
	 * Allows filtering whether the current page should be indexed.
	 *
	 * @param bool $should_index
	 *
	 * @return bool
	 */
	return apply_filters( 'tve_dash_should_index_page', $should_index );
}

function tve_dash_current_screen() {
	/**
	 * Some pages don't have a title, so we need to set it manually
	 */
	$screen = tve_get_current_screen_key();
	global $title;
	if ( $screen && empty( $title ) && strpos( $screen, 'tve_dash' ) !== false ) {
		$title = 'Thrive Dashboard';
	}

	if ( $screen === 'admin_page_tve_dash_license_manager_section' && tve_dash_is_plugin_active( 'thrive-product-manager' ) ) {
		$url = thrive_product_manager()->get_admin_url();
		wp_redirect( $url );
		die;
	}
}

/**
 * Add thrive edit links in admin bar
 *
 * @param WP_Admin_Bar $wp_admin_bar
 */
function tve_dash_admin_bar_menu( $wp_admin_bar ) {
	$thrive_parent_node = tve_dash_get_thrive_parent_node();

	/**
	 * Allow plugins to add their own node in the admin bar as a child to the parent thrive node
	 */
	$nodes = apply_filters( 'tve_dash_admin_bar_nodes', array() );

	if ( empty( $nodes ) ) {
		return;
	}

	$no_of_nodes = count( $nodes );
	/** If we have more than one node add the parent and sort items */
	if ( $no_of_nodes > 1 ) {
		$wp_admin_bar->add_node( $thrive_parent_node );

		/* Sort the nodes by order */
		usort( $nodes, function ( $a, $b ) {
			return $a['order'] - $b['order'];
		} );
	}

	/** Let wordpress know about the nodes */
	foreach ( $nodes as $node ) {
		$node['parent'] = ( $no_of_nodes > 1 ) ? $thrive_parent_node['id'] : '';
		$wp_admin_bar->add_node( $node );
	}
}

add_action( 'admin_bar_menu', 'tve_dash_admin_bar_menu', 999 );

/**
 * Update setting for Thrive Suite
 */
add_action( 'wp_ajax_tve_update_settings', static function () {

	check_ajax_referer( 'tve-dash' );

	$value = sanitize_text_field( $_POST['value'] );

	if ( current_user_can( 'manage_options' ) && in_array( $value, array( 'stable', 'beta' ) ) ) {

		update_option( 'tve_update_option', $value );

		/**
		 * We need to delete transients on channel changed to refresh the update cache
		 */
		foreach ( get_plugins() as $plugin_file => $plugin_data ) {
			$slug = dirname( plugin_basename( $plugin_file ) );

			if ( strpos( $slug, 'thrive-' ) !== false ) {
				delete_option( 'external_updates-' . $slug );
			}
		}
		wp_clean_update_cache();

		wp_die( 'Success!' );
	}

	wp_die( 'Nope!' );
} );

/* Quick query to remove all of our transients */
add_action( 'wp_ajax_tve_debug_reset_transient', function () {

	check_ajax_referer( 'tve-dash' );

	if ( current_user_can( 'manage_options' ) ) {
		global $wpdb;

		tvd_reset_transient();

		if ( ! empty( $wpdb->last_error ) ) {
			wp_die( 'Error: ' . esc_html( $wpdb->last_error ) );
		}
	}

	wp_die( 'Transients removed successfully' );
} );


/**
 * WP-Rocket Compatibility - exclude files from caching
 */
add_filter( 'rocket_exclude_js', 'tvd_rocket_exclude_js' );
/**
 * Exclude the js dist folder from caching and minify-ing
 *
 * @param $excluded_js
 *
 * @return array
 */
function tvd_rocket_exclude_js( $excluded_js ) {

	$excluded_js[] = str_replace( home_url(), '', TVE_DASH_URL ) . 'js/dist/(.*).js';

	return $excluded_js;
}

add_action( 'admin_notices', 'tve_dash_incompatible_tar_version' );
add_action( 'admin_head', 'dashboard_license_notifications_styles' );

function dashboard_license_notifications_styles() {
	include_once TVE_DASH_PATH . '/css/images/licensing/licensing-icons.svg';
	?>
	<style>
		<?php include_once TVE_DASH_PATH . '/css/dashboard-notifications.css'; ?>
	</style>
	<?php
}

add_action( 'wp_loaded', 'tve_dash_expired_license_notices' );

function tve_dash_expired_license_notices() {

	tvd_register_admin_license_notices( tve_dash_expired_license_count( false ), false );
	tvd_register_admin_license_notices( tve_dash_expired_license_count( true ), true );

}

function tve_dash_expired_license_count( $check_grace_period = true ) {
	$installed_products = tve_dash_get_products( false );

	$count          = 0;
	$single_product = null;
	foreach ( $installed_products as $product ) {
		$should = false;
		if ( $product->get_tag() !== 'tap' ) {
			if ( $check_grace_period ) {
				$should = TD_TTW_User_Licenses::get_instance()->is_in_grace_period( $product->get_tag() );
			} else {
				$should = ! TD_TTW_User_Licenses::get_instance()->has_active_license( $product->get_tag() ) && ! TD_TTW_User_Licenses::get_instance()->is_in_grace_period( $product->get_tag() );
			}
		}

		if ( $should ) {
			++ $count;
			$single_product = $product;
		}
	}

	$response = null;
	if ( $count && TD_TTW_User_Licenses::get_instance()->has_membership() ) {
		$response = 'suite';
	} elseif ( $count === 1 ) {
		$response = $single_product;
	} elseif ( $count > 1 ) {
		$response = 'multiple';
	}

	return $response;
}

function tve_get_grace_period( $product_type = 'suite' ) {
	if ( $product_type === 'multiple' ) {
		$installed_products = tve_dash_get_products( false );

		$shortest_grace_period = null;
		foreach ( $installed_products as $product ) {
			if ( $product->get_tag() !== 'tap' ) {
				$should = TD_TTW_User_Licenses::get_instance()->is_in_grace_period( $product->get_tag() );
				if ( $should ) {
					if ( $shortest_grace_period === null ) {
						$shortest_grace_period = TD_TTW_User_Licenses::get_instance()->get_grace_period_left( $product->get_tag() );
					} else {
						$current_grace_period  = TD_TTW_User_Licenses::get_instance()->get_grace_period_left( $product->get_tag() );
						$shortest_grace_period = $shortest_grace_period > $current_grace_period ? $current_grace_period : $shortest_grace_period;
					}
				}
			}
		}

		return $shortest_grace_period;
	} else {
		return TD_TTW_User_Licenses::get_instance()->get_grace_period_left( '' );
	}

}

function tve_get_admin_license_notice( $product, $grace_period = false ) {
	$message = '';
	if ( $grace_period ) {
		$classes    = ' is-dismissible';
		$extra_text = __( 'You may close this lightbox and continue using your software during your grace period for another <b>' . tve_get_grace_period( $product ) . '</b> days.', 'thrive-dash' );
	} else {
		$classes    = ' tvd-expired';
		$extra_text = '';
	}

	switch ( $product ) {
		case 'suite':
			ob_start();
			include TVE_DASH_PATH . '/inc/ttw-account/templates/licences/suite-message.phtml';
			$message = ob_get_clean();
			break;
		case 'multiple':
			ob_start();
			include TVE_DASH_PATH . '/inc/ttw-account/templates/licences/multiple-message.phtml';
			$message = ob_get_clean();
			break;
		default:
			$message = tvd_get_individual_plugin_license_message( $product, false );

			break;
	}

	return $message;
}

function tvd_get_individual_plugin_license_link( $tag = false ) {
	if ( empty( $tag ) ) {
		return 'https://thrivethemes.com/suite/';
	}
	$products = [
		'tcb' => 'https://thrivethemes.com/architect/',
		'tl'  => 'https://thrivethemes.com/leads/',
		'tu'  => 'https://thrivethemes.com/ultimatum/',
		'tvo' => 'https://thrivethemes.com/ovation/',
		'tqb' => 'https://thrivethemes.com/quizbuilder/',
		'tcm' => 'https://thrivethemes.com/comments/',
		'tva' => 'https://thrivethemes.com/apprentice/',
		'tab' => 'https://thrivethemes.com/optimize/',
		'ttb' => 'https://thrivethemes.com/themebuilder/',
	];

	return $products[ $tag ];
}

function tvd_get_individual_plugin_license_message( $product, $inner = true ) {
	$message = '';
	if ( is_a( $product, 'TVE_Dash_Product_Abstract' ) ) {
		$tag          = $product->get_tag();
		$expired      = ! TD_TTW_User_Licenses::get_instance()->has_active_license( $tag );
		$grace_period = TD_TTW_User_Licenses::get_instance()->is_in_grace_period( $tag );

		if ( $expired || $grace_period ) {
			if ( $grace_period ) {
				$classes = ' is-dismissible';
				$text    = 'An active license is needed to access your software and manage your content. You’ll also get access to new features, updates, security improvements, templates and support. Your website visitors can continue to access your content. You may close this notification and continue using your software for another <b>' . TD_TTW_User_Licenses::get_instance()->get_grace_period_left( $tag ) . '</b> days.';
			} else {
				$classes = ' tvd-expired';
				$text    = "An active license is needed to access your software and manage your content. You’ll also get access to new features, updates, security improvements, templates and support. Doesn't sound right? Your license might need to be refreshed.";
			}
			if ( $inner ) {
				$classes .= ' tvd-inner-dashboard';
			}

			$message = sprintf( '<div class="notice error tve-dashboard-license-message tvd-license' . $classes . ' %s">
                    <svg class="td-icon"><use xlink:href="#icon-%s"></use></svg>
                    <h4>Heads up! Your %s license has expired.</h4>
                    <p>' . __( $text, 'thrive-dash' ) . ' %s</p>
                    <div>
                        <a href="https://help.thrivethemes.com/en/articles/8223498-what-happens-when-your-thrive-product-license-expires" target="_blank">' . __( 'Learn more', 'thrive-dash' ) . '</a>
                        <a class="tve-license-link" target="_blank" href="' . tvd_get_individual_plugin_license_link( $tag ) . '">' . __( 'Renew now', 'thrive-dash' ) . '</a>
                    </div>
                </div>', $tag, $tag, $product->get_title(), '<a href="' . TD_TTW_User_Licenses::get_instance()->get_recheck_url() . '">' . __( 'Click here to refresh your license now.', 'thrive-dash' ) . '</a>' );
		}

	}

	return $message;
}


function tvd_register_admin_license_notices( $product, $grace_period = false ) {

	if ( $product ) {
		$message = tve_get_admin_license_notice( $product, $grace_period );

		if ( ! empty( $message ) ) {
			add_action( 'admin_notices', static function () use ( $message ) {
				echo $message;
			} );
		}
	}

}


/**
 * Unify all alerts that inform the users that certain products are not compatible with TAR version
 */
function tve_dash_incompatible_tar_version() {

	$installed_products             = tve_dash_get_products( false );
	$products_incompatible_with_tar = array();

	/**
	 * @var TVE_Dash_Product_Abstract $product
	 */
	foreach ( $installed_products as $product ) {
		if ( $product->needs_architect() && $product->get_incompatible_architect_version() ) {

			$parts = parse_url( $product->get_admin_url() );
			parse_str( $parts['query'], $query );

			$products_incompatible_with_tar[] = array(
				'title'  => $product->get_title(),
				'screen' => ! empty( $query['page'] ) ? $query['page'] : '',
			);
		}
	}

	$products_counter = count( $products_incompatible_with_tar );

	if ( $products_counter > 0 ) {

		$titles  = array_column( $products_incompatible_with_tar, 'title' );
		$screens = array_column( $products_incompatible_with_tar, 'screen' );

		if ( in_array( str_replace( 'thrive-dashboard_page_', '', tve_get_current_screen_key( 'base' ) ), $screens, true ) ) {
			return;
		}

		$version      = 'version';
		$products_str = $titles[0];
		$is_not       = 'is not';
		if ( $products_counter > 1 ) {
			$version = 'versions';
			$is_not  = 'are not';

			if ( $products_counter === 2 ) {
				$products_str = implode( ' and ', $titles );
			} elseif ( $products_counter > 2 ) {
				$products_str = implode( ', ', $titles );

				$pos = strrpos( $products_str, ', ' );
				if ( $pos !== false ) {
					$products_str = substr_replace( $products_str, ' and ', $pos, strlen( ', ' ) );
				}
			}
		}

		$text = sprintf( 'Current %s of %s %s compatible with the current version of Thrive Architect. Please update all plugins to the latest versions.', $version, $products_str, $is_not );

		$text .= ' <a href="' . network_admin_url( 'plugins.php' ) . '">' . __( 'Manage plugins', 'thrive-dash' ) . '</a>';

		echo sprintf( '<div class="error"><p>%s</p></div>', $text );
	}
}

/**
 * Called on wp_login hook
 *
 * Updates the last login for a specific user & fires the login hook
 *
 * @param string  $user_login
 * @param WP_User $user
 */
function tve_dash_on_user_login( $user_login, $user ) {
	update_user_meta( $user->ID, 'tve_last_login', current_time( 'timestamp' ) );

	$user_form_data = tvd_get_login_form_data( 'success' );

	tve_trigger_core_user_login_action( $user_login, $user_form_data, $user );
}

/**
 * Called on wp_login_failed hook
 *
 * @param string        $username
 * @param WP_Error|null $error
 */
function tve_dash_on_user_login_failed( $username, $error = null ) {
	$user_form_data = tvd_get_login_form_data( 'fail' );

	tve_trigger_core_user_login_action( $username, $user_form_data, null );
}

/**
 * A wrapper over the thrive_core_user_login action for the system to include only once the hook in the 3rd party developer documentation
 *
 * @param string       $user_login
 * @param array        $user_form_data
 * @param WP_User|null $user
 */
function tve_trigger_core_user_login_action( $user_login, $user_form_data, $user ) {
	/**
	 * This hook is fired when a user logs into the platform.The hook can be fired multiple times per user.
	 * </br>
	 * Example use case:- Show the users specific content depending on the login URL
	 *
	 * @param string Username
	 * @param array User Form Data [href = #formdata]
	 * @param WP_User|null WP_User [href = #user]
	 *
	 * @api
	 */
	do_action( 'thrive_core_user_login',
		$user_login,
		$user_form_data,
		$user
	);
}

/**
 * Yoast Compatibility
 *
 * Do not generate sitemap for some of our custom post types
 */
add_filter( 'wpseo_sitemap_exclude_post_type', static function ( $exclude, $post_type ) {

	if ( in_array( $post_type, apply_filters( 'tve_dash_yoast_sitemap_exclude_post_types', array() ), true ) ) {
		$exclude = true;
	}

	return $exclude;
}, 10, 2 );

/**
 * Yoast Compatibility
 *
 * Do not generate sitemap for some of our custom taxonomies
 */
add_filter( 'wpseo_sitemap_exclude_taxonomy', static function ( $exclude, $tax_name ) {

	if ( in_array( $tax_name, apply_filters( 'tve_dash_yoast_sitemap_exclude_taxonomies', array() ), true ) ) {
		$exclude = true;
	}

	return $exclude;
}, 10, 2 );

/**
 * Loads the class used for Conditional Display in TAR
 */
function tve_load_conditional_display_classes() {
	if ( class_exists( '\TCB\ConditionalDisplay\Main', false ) ) {
		require_once TVE_DASH_PATH . '/inc/conditional-display/class-main.php';
		\TVE\Architect\ConditionalDisplay\Main::init();
	}
}

add_action( 'init', 'tve_load_conditional_display_classes', 11 );

add_action( 'thrive_prepare_migrations',
	/**
	 * @throws Exception
	 */
	static function () {
		\TD_DB_Manager::add_manager(
			TVE_DASH_PATH . '/inc/db-manager/migrations',
			'tve_td_db_version',
			TVE_DASH_DB_VERSION,
			'Thrive Dashboard',
			/* this is empty because the table prefixes are different (reporting uses 'thrive_' and smart site uses 'td_'), so they are added to the tables directly */
			'',
			'tve_dash_reset'
		);
	} );
