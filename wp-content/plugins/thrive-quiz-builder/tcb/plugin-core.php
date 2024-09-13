<?php
/* global constants */
defined( 'TVE_TCB_ROOT_PATH' ) || define( 'TVE_TCB_ROOT_PATH', plugin_dir_path( __FILE__ ) );
defined( 'TVE_VERSION' ) || DEFINE( 'TVE_VERSION', include TVE_TCB_ROOT_PATH . 'version.php' );
defined( 'TVE_TCB_DB_VERSION' ) || define( 'TVE_TCB_DB_VERSION', '1.2' );
defined( 'TVE_LANDING_PAGE_TEMPLATE' ) || DEFINE( 'TVE_LANDING_PAGE_TEMPLATE', plugins_url() . '/thrive-visual-editor/landing-page/templates' );
defined( 'TVE_LANDING_PAGE_TEMPLATE_DOWNLOADED' ) || DEFINE( 'TVE_LANDING_PAGE_TEMPLATE_DOWNLOADED', plugins_url() . '/../uploads/tcb_lp_templates/templates' );
/* will we need another key for Thrive Leads ? */
defined( 'TVE_EDITOR_FLAG' ) || define( 'TVE_EDITOR_FLAG', 'tve' );
defined( 'TVE_FRAME_FLAG' ) || define( 'TVE_FRAME_FLAG', 'tcbf' );
defined( 'TVE_GLOBAL_CSS_PREFIX' ) || define( 'TVE_GLOBAL_CSS_PREFIX', ':not(#tve)' );
defined( 'TVE_TCB_CORE_INCLUDED' ) || define( 'TVE_TCB_CORE_INCLUDED', true );
defined( 'TCB_THUMBNAIL_META_KEY' ) || define( 'TCB_THUMBNAIL_META_KEY', '_tcb_template_thumb' );
define( 'TCB_CT_POST_TYPE', 'tcb_content_template' );
define( 'TVE_CLOUD_TEMPLATES_FOLDER', 'tcb_content_templates' );
define( 'TCB_MIN_WP_VERSION', '4.8' );
define( 'TVE_GLOBAL_COLOR_VAR_CSS_PREFIX', '--tcb-color-' );
define( 'TVE_LP_COLOR_VAR_CSS_PREFIX', '--tcb-tpl-color-' );
define( 'TVE_DYNAMIC_VAR_CSS_PREFIX', '--tcb-dynamic-' );
define( 'TVE_DYNAMIC_BACKGROUND_URL_VAR_CSS_PREFIX', '--tcb-dynamic-background-url-' );
define( 'TVE_DYNAMIC_COLOR_VAR_CSS_PREFIX', '--tcb-dynamic-color-' );
define( 'TVE_LOCAL_COLOR_VAR_CSS_PREFIX', '--tcb-local-color-' );
define( 'TVE_GLOBAL_GRADIENT_VAR_CSS_PREFIX', '--tcb-gradient-' );
define( 'TVE_LP_GRADIENT_VAR_CSS_PREFIX', '--tcb-tpl-gradient-' );
define( 'TVE_LOCAL_GRADIENT_VAR_CSS_PREFIX', '--tcb-local-gradient-' );
define( 'TVE_MAIN_COLOR_H', '--tcb-main-master-h' ); //Main Color Hue
define( 'TVE_MAIN_COLOR_S', '--tcb-main-master-s' ); //Main Color Saturation
define( 'TVE_MAIN_COLOR_L', '--tcb-main-master-l' ); //Main Color Lightness
define( 'TVE_MAIN_COLOR_A', '--tcb-main-master-a' ); //Main Color Alpha
define( 'TVE_GLOBAL_STYLE_CLS_PREFIX', 'tcb-global-' );
define( 'TVE_GLOBAL_STYLE_BUTTON_CLS_PREFIX', TVE_GLOBAL_STYLE_CLS_PREFIX . 'button-' );
define( 'TVE_GLOBAL_STYLE_SECTION_CLS_PREFIX', TVE_GLOBAL_STYLE_CLS_PREFIX . 'section-' );
define( 'TVE_GLOBAL_STYLE_CONTENTBOX_CLS_PREFIX', TVE_GLOBAL_STYLE_CLS_PREFIX . 'contentbox-' );
define( 'TVE_GLOBAL_STYLE_LINK_CLS_PREFIX', TVE_GLOBAL_STYLE_CLS_PREFIX . 'link-' );
define( 'TVE_GLOBAL_STYLE_TEXT_CLS_PREFIX', TVE_GLOBAL_STYLE_CLS_PREFIX . 'text-' );
defined( 'TVE_ICON_API' ) || define( 'TVE_ICON_API', '//landingpages.thrivethemes.com/cloud-api/icons-api.php' );
defined( 'TVE_IS_PROCESSING_CUSTOM_CSS' ) || define( 'TVE_IS_PROCESSING_CUSTOM_CSS', 'tve_is_processing_custom_css' );

defined( 'TVE_EXTENDED_MEMORY_LIMIT' ) || define( 'TVE_EXTENDED_MEMORY_LIMIT', '512M' );

/**
 * Used to store the TCB Flag HTML Element
 * Used also in TA Visual Builder
 */
defined( 'TVE_FLAG_HTML_ELEMENT' ) || define( 'TVE_FLAG_HTML_ELEMENT', '<div class="tcb_flag" style="display: none"></div>' );

global $tve_style_family_classes;
$tve_style_family_classes = [ 'Flat' => 'tve_flt' ];

// global options
global $tve_thrive_shortcodes;

/*
 * theme shortcodes available in TCB
 * list of shortcode identifier => callback function
 * the callback function will be called with an array of attributes and must return a html code to be inserted into the DOM
 */
$tve_thrive_shortcodes = [
	'post_symbol'                         => 'tcb_symbol_shortcode',
	'optin'                               => 'tve_do_optin_shortcode',
	'posts_list'                          => 'tve_do_posts_list_shortcode',
	'custom_menu'                         => 'tve_do_custom_menu_shortcode',
	'custom_phone'                        => 'tve_do_custom_phone_shortcode',
	'post_grid'                           => 'tve_do_post_grid_shortcode',
	'widget_menu'                         => 'tve_render_widget_menu',
	'leads_shortcode'                     => 'tve_do_leads_shortcode',
	'tve_leads_additional_fields_filters' => 'tve_leads_additional_fields_filters',
	'social_default'                      => 'tve_social_render_default',
	'tvo_shortcode'                       => 'tvo_render_shortcode',
	'ultimatum_shortcode'                 => 'tve_ult_render_shortcode',
	'quiz_shortcode'                      => 'tqb_render_shortcode',
	'thrive_widget'                       => 'thrive_widget_render',
];

/**
 * If a file called .flag-staging-templates exists, turn off caching of cloud templates
 */
if ( file_exists( plugin_dir_path( __FILE__ ) . ' . flag - staging - templates' ) && ! defined( 'TCB_CLOUD_API_LOCAL' ) ) {
	define( 'TVE_STAGING_TEMPLATES', true );
	define( 'TCB_CLOUD_API_LOCAL', 'https://staging.landingpages.thrivethemes.com/cloud-api/index-api.php' );
	defined( 'TCB_TEMPLATE_DEBUG' ) || define( 'TCB_TEMPLATE_DEBUG', true );
	defined( 'TCB_CLOUD_DEBUG' ) || define( 'TCB_CLOUD_DEBUG', true );
	defined( 'TL_CLOUD_DEBUG' ) || define( 'TL_CLOUD_DEBUG', true );
}

if ( file_exists( plugin_dir_path( __FILE__ ) . '.flag-nocache' ) ) {
	defined( 'TCB_TEMPLATE_DEBUG' ) || define( 'TCB_TEMPLATE_DEBUG', true );
	defined( 'TCB_CLOUD_DEBUG' ) || define( 'TCB_CLOUD_DEBUG', true );
	defined( 'TL_CLOUD_DEBUG' ) || define( 'TL_CLOUD_DEBUG', true );
}

require_once TVE_TCB_ROOT_PATH . 'inc/traits/trait-is-singleton.php';
require_once TVE_TCB_ROOT_PATH . 'inc/traits/trait-has-ranges.php';

require_once TVE_TCB_ROOT_PATH . 'inc/classes/class-tcb-custom-fields-shortcode.php';
require_once TVE_TCB_ROOT_PATH . 'inc/compat.php';
require_once TVE_TCB_ROOT_PATH . 'inc/backwards.php';
require_once TVE_TCB_ROOT_PATH . 'inc/helpers/social.php';
require_once TVE_TCB_ROOT_PATH . 'inc/helpers/cloud.php';
require_once TVE_TCB_ROOT_PATH . 'inc/functions.php';
require_once TVE_TCB_ROOT_PATH . 'inc/classes/class-tcb-editor-ajax.php';
require_once TVE_TCB_ROOT_PATH . 'inc/classes/class-tcb-editor.php';
require_once TVE_TCB_ROOT_PATH . 'inc/classes/class-tcb-elements.php';
require_once TVE_TCB_ROOT_PATH . 'inc/classes/class-tcb-color-manager.php';
require_once TVE_TCB_ROOT_PATH . 'inc/classes/class-tcb-font-manager.php';
require_once TVE_TCB_ROOT_PATH . 'inc/classes/class-tcb-icon-manager.php';
require_once TVE_TCB_ROOT_PATH . 'inc/classes/class-tcb-editor-meta-boxes.php';
require_once TVE_TCB_ROOT_PATH . 'inc/classes/lightspeed/class-main.php';
require_once TVE_TCB_ROOT_PATH . 'inc/classes/class-tcb-post.php';
require_once TVE_TCB_ROOT_PATH . 'inc/classes/class-tcb-utils.php';
require_once TVE_TCB_ROOT_PATH . 'inc/classes/class-tcb-content-handler.php';

require_once TVE_TCB_ROOT_PATH . 'inc/classes/class-tcb-search-form.php';

require_once TVE_TCB_ROOT_PATH . 'inc/classes/post-list/class-tcb-post-list.php';
require_once TVE_TCB_ROOT_PATH . 'inc/classes/post-list/class-tcb-post-list-content.php';
require_once TVE_TCB_ROOT_PATH . 'inc/classes/post-list/class-tcb-post-list-author-image.php';
require_once TVE_TCB_ROOT_PATH . 'inc/classes/post-list/class-tcb-post-list-featured-image.php';
require_once TVE_TCB_ROOT_PATH . 'inc/classes/post-list/class-tcb-post-list-user-image.php';
require_once TVE_TCB_ROOT_PATH . 'inc/classes/post-list/class-tcb-post-list-shortcodes.php';
require_once TVE_TCB_ROOT_PATH . 'inc/classes/post-list/pagination/class-tcb-pagination.php';
require_once TVE_TCB_ROOT_PATH . 'inc/classes/post-list/pagination/class-tcb-pagination-load-more.php';
require_once TVE_TCB_ROOT_PATH . 'inc/classes/post-list/pagination/class-tcb-pagination-none.php';
require_once TVE_TCB_ROOT_PATH . 'inc/classes/post-list/pagination/class-tcb-pagination-numeric.php';
require_once TVE_TCB_ROOT_PATH . 'inc/classes/logo/class-tcb-logo.php';
require_once TVE_TCB_ROOT_PATH . 'inc/classes/post-list-filter/class-tcb-post-list-filter.php';

require_once TVE_TCB_ROOT_PATH . 'inc/woocommerce/classes/class-main.php';
require_once TVE_TCB_ROOT_PATH . 'inc/smash-balloon/classes/class-main.php';

require_once TVE_TCB_ROOT_PATH . 'inc/classes/notifications/class-main.php';
require_once TVE_TCB_ROOT_PATH . 'inc/classes/conditional-display/class-main.php';

require_once TVE_TCB_ROOT_PATH . 'inc/classes/video-reporting/class-main.php';

require_once TVE_TCB_ROOT_PATH . 'inc/classes/user-templates/class-main.php';
/* we must include these before tve_global_options_init() */
TCB\UserTemplates\Main::includes();

require_once TVE_TCB_ROOT_PATH . 'inc/automator/class-main.php';
require_once TVE_TCB_ROOT_PATH . 'inc/classes/symbols/class-tcb-symbols-post-type.php';
require_once TVE_TCB_ROOT_PATH . 'inc/classes/symbols/class-tcb-symbol-template.php';
require_once TVE_TCB_ROOT_PATH . 'inc/classes/symbols/class-tcb-symbols-dashboard.php';
require_once TVE_TCB_ROOT_PATH . 'inc/classes/symbols/class-tcb-symbols-taxonomy.php';
require_once TVE_TCB_ROOT_PATH . 'inc/classes/symbols/class-tcb-symbols-block.php';

require_once TVE_TCB_ROOT_PATH . 'inc/classes/class-tcb-menu-walker.php';
require_once TVE_TCB_ROOT_PATH . 'landing-page/inc/class-tcb-lp-palettes.php';
require_once TVE_TCB_ROOT_PATH . 'landing-page/inc/class-tcb-landing-page.php';
require_once TVE_TCB_ROOT_PATH . 'inc/classes/class-tcb-lightbox.php';
require_once TVE_TCB_ROOT_PATH . 'inc/classes/class-tcb-login-element-handler.php';
require_once TVE_TCB_ROOT_PATH . 'inc/classes/class-tcb-user-profile-handler.php';
require_once TVE_TCB_ROOT_PATH . 'inc/classes/class-tcb-menu-settings.php';
require_once TVE_TCB_ROOT_PATH . 'inc/helpers/form.php';
require_once TVE_TCB_ROOT_PATH . 'inc/helpers/file-upload.php';
require_once TVE_TCB_ROOT_PATH . 'inc/helpers/form-hooks.php';
require_once TVE_TCB_ROOT_PATH . 'inc/classes/class-tcb-show-when.php';
require_once TVE_TCB_ROOT_PATH . 'inc/classes/class-tcb-scripts.php';

/* init the Event Manager */
require_once TVE_TCB_ROOT_PATH . 'event-manager/init.php';

add_action( 'admin_init', 'tve_revert_page_to_theme' );

/* ajax calls through WP API */
add_action( 'wp_ajax_tve_social_count', 'tve_social_ajax_count' );
add_action( 'wp_ajax_nopriv_tve_social_count', 'tve_social_ajax_count' );
add_action( 'wp_ajax_tve_cf_submit', 'tve_submit_contact_form' );
add_action( 'wp_ajax_nopriv_tve_cf_submit', 'tve_submit_contact_form' );
add_action( 'admin_action_tve_new_post', 'tve_new_post' );

add_filter( 'wp_img_tag_add_loading_attr', 'tve_image_lazy_load', 10, 3 );

/**
 * AJAX call to return the TCB-added content for a post
 */
add_action( 'wp_ajax_tve_get_seo_content', 'tve_get_seo_content' );

/**
 * Sends an ajax response containing the TCB-saved post content, stripped of tags for yoast SEO integration
 *
 * @return void
 */
function tve_get_seo_content() {
	$id = isset( $_POST['post_id'] ) ? (int) $_POST['post_id'] : 0;

	if ( ! $id ) {
		wp_send_json( [
			'post_id' => 0,
			'content' => '',
		] );
	}
	/**
	 * Mimic the the_content filter on the post - this will return all TCB content
	 */
	global $post;
	$post = get_post( $id );

	global $wp_query;
	$wp_query->query( [ 'p' => $id, 'post_type' => $post->post_type ] );

	/* Make sure Architect content is parsed */
	add_filter( 'the_content', 'tve_clean_wp_editor_content', - 100 );
	add_filter( 'the_content', 'tve_editor_content', PHP_INT_MAX );

	$content = apply_filters( 'tve_get_seo_content', '', $id );

	if ( empty( $content ) ) {
		/* used ob_start to avoid any output generated by tve_editor_content) */
		ob_start();
		the_content();
		$content = ob_get_clean();
	}

	$content = str_replace( [ 'data-src', 'data-url' ], [ 'src', 'url' ], $content );

	wp_send_json( array(
		'post_id'            => $id,
		'content'            => $content,
		'is_edited_with_tar' => (int) get_post_meta( $id, 'tcb_editor_enabled', true ),
	) );
}

add_action( 'wp_enqueue_scripts', 'tve_enqueue_editor_scripts' );

/**
 * always enqueue the dash frontend script
 */
add_filter( 'tve_dash_enqueue_frontend', '__return_true' );

/**
 * hook for social share counts via ajax
 */
add_filter( 'tve_dash_main_ajax_tcb_social', 'tve_social_dash_ajax_share_counts', 10, 2 );

/**
 * Autoresponder APIs AJAX calls
 */
if ( wp_doing_ajax() || apply_filters( 'tve_leads_include_auto_responder', false ) ) {
	/**
	 * submit Lead Generation form element via AJAX
	 */
	add_action( 'wp_ajax_nopriv_tve_api_form_submit', 'tve_api_form_submit' );
	add_action( 'wp_ajax_tve_api_form_submit', 'tve_api_form_submit' );
	add_action( 'wp_ajax_nopriv_tve_custom_form_submit', 'tve_custom_form_submit' );
	add_action( 'wp_ajax_tve_custom_form_submit', 'tve_custom_form_submit' );
}

/** CONTENT REVISION HOOKS */
/**
 * Append fields to be tracked of changes
 * This filter is called in revisions view
 */
add_filter( '_wp_post_revision_fields', 'tve_post_revision_fields', 10, 1 );
/** Restore content to revision */
add_action( 'wp_restore_post_revision', 'tve_restore_post_to_revision', 11, 2 );
/** Decide if post has changed and save a revision for it */
add_filter( 'wp_save_post_revision_post_has_changed', 'tve_post_has_changed', 10, 3 );

add_action( 'wp_enqueue_scripts', 'tve_remove_conflicting_scripts', PHP_INT_MAX );

// add the same tve_editor_filter but on this case on Landing Page templates - only applies to TCB
add_filter( 'tve_landing_page_content', 'tve_editor_content' );

// add TCB buttons to admin post/page listing screen
add_filter( 'page_row_actions', 'thrive_page_row_buttons', 10, 2 );
add_filter( 'post_row_actions', 'thrive_page_row_buttons', 10, 2 );

add_action( 'wp_head', function () {
	/* we need to always load this into the head section, because some themes styles will overwrite the font settings */
	tve_load_font_css();
	tve_load_global_variables();

	/* load meta tags so scrapers can find them */
	tve_load_meta_tags();

	\TCB\Lightspeed\Main::preload_assets( get_the_ID() );
} );

// add thrive edit link to admin bar
add_filter( 'tve_dash_admin_bar_nodes', 'thrive_editor_admin_bar' );

// To fight against themes creating custom wpautop scripts and injecting rogue <br/> and <p> tags into content we have to apply shortcodes early, then add our content to the page
// at priority 101, hence the two separate "the_content" actions
if ( ! is_admin() ) {
	add_action( 'wp', 'tve_wp_action' );
	function tve_wp_action() {
		add_filter( 'the_content', 'tve_clean_wp_editor_content', - 100 );
		add_filter( 'the_content', 'tve_editor_content', is_editor_page() ? PHP_INT_MAX : 10 );
		if ( tcb_editor()->is_inner_frame() ) {
			global $post;
			if ( ! empty( $post->post_password ) ) {
				/* remove password protection on editor pages */
				add_filter( 'post_password_required', '__return_false' );
			}
		}
	}
} else {
	require_once( TVE_TCB_ROOT_PATH . 'admin/class-tcb-admin.php' );
}

// manipulate social sharing hooks so that they work with TCB
if ( has_filter( 'dd_hook_wp_content' ) ) {
	remove_filter( 'the_content', 'dd_hook_wp_content' );
	add_filter( 'the_content', 'dd_hook_wp_content', 103 );
}

// make sure WP editor page doesn't overwrite TCB content
add_filter( 'is_protected_meta', 'tve_hide_custom_fields', 10, 2 );

add_action( 'thrive_dashboard_loaded', 'tcb_dashboard_loaded' );

/* hook for displaying the main editor page ( control panel + content frame ) - only if the tve param is present */
if ( ! empty( $_REQUEST[ TVE_EDITOR_FLAG ] ) ) {
	if ( is_admin() ) {
		add_action( 'init', array( tcb_editor(), 'on_admin_init' ), 20 );
		/* Disable cache on editor page*/
		add_action( 'init', array( tcb_editor(), 'disable_content_cache' ), 20 );
	}
	add_action( 'post_action_architect', array( tcb_editor(), 'post_action_architect' ), 0 );
}

add_action( 'rest_api_init', 'tcb_rest_api_init' );

function tcb_rest_api_init() {
	tcb_create_admin_rest_routes();

	TCB_Post_List::rest_api_init();
	TCB_Logo::rest_api_init();
	TCB_Post_List_Filter::rest_api_init();

	if ( ! empty( $_POST['tar_editor_page'] ) && TCB_Product::has_external_access() ) {
		TCB_Utils::restore_post_waf_content();
	}
}

// hook for detecting if a post is setup as a Custom Editable piece of content
add_action( 'template_redirect', 'tcb_custom_editable_content', 9 );

/**
 * filter used to clean meta-data stuff from the content, when displaying it on frontend, e.g.: lead generation code being saved in the HTML causes SEO issues
 */
add_filter( 'tcb_clean_frontend_content', 'tcb_clean_frontend_content' );

/**
 * init the Pinterest SDK
 */
add_action( 'tve_socials_init_pinterest', 'tve_socials_init_pinterest' );

add_filter( 'tve_filter_custom_fonts_for_enqueue_in_editor', 'tve_filter_custom_fonts_for_enqueue_in_editor' );

/**
 * shows a message in the main media uploader window that states: "Only .xxx files are allowed"
 */
add_action( 'post-upload-ui', 'tve_media_restrict_filetypes' );

add_action( 'init', function () {

	/* use settings API to store non post-level settings */
	tve_global_options_init();

	/* hook to defined location of translations files */
	tve_load_plugin_textdomain();

	/* only TCB-specific classes should be loaded here */
	tve_load_tcb_classes();

	\TCB\Notifications\Main::init();

	\TCB\ConditionalDisplay\Main::init();

	\TCB\UserTemplates\Main::init();

	\TCB\Integrations\SmashBalloon\Main::init();

	TCB_Menu_Settings::init();

	TCB_Editor_Meta_Boxes::init();

	\TCB\VideoReporting\Main::init();
} );

\TCB\Lightspeed\Main::init();

add_action( 'wp_footer', array( tcb_editor(), 'inner_frame_menus' ), 100 );
add_action( 'wp', array( tcb_editor(), 'clean_inner_frame' ) );

/**
 * Actions used for handling the interim login ( login via popup in TCB editor page )
 */
add_filter( 'tvd_auth_check_data', 'tcb_auth_check_data' );

add_action( 'thrive_prepare_migrations', 'tcb_prepare_db_migrations' );


if ( ! function_exists( 'tve_editor_url' ) ) {
	/**
	 * @return string the absolute url to this plugin's folder
	 *
	 * @param string $file optional, a path inside the plugin folder
	 */
	function tve_editor_url( $file = null ) {
		return rtrim( TVE_EDITOR_URL . ( null !== $file ? ltrim( $file, '/\\' ) : '' ), '/' );
	}
}

/**
 * Enable unavailable shortcode tooltips inside the editor
 */
add_filter( 'td_smartsite_shortcode_tooltip', 'is_editor_page_raw' );

/**
 * Symbol css backwards compatible
 */
add_filter( 'tcb_symbol_css_before', 'symbols_css_backwards_compatible', 10, 2 );

/* Replace tve_editor from css with global css prefix ( :not(#tve) )*/
add_filter( 'tcb_custom_css', 'tcb_custom_css' );

/**
 *Replaces element type with post_list if the type is post_list_featured
 */
add_filter( 'tcb_cloud_templates_replace_featured_type', [ 'TCB_Post_List', 'featured_type_replace' ] );

/**
 *Replaces element tag with post_list_featured if the type is post_list_featured
 */
add_filter( 'tcb_cloud_templates_replace_featured_tag', [ 'TCB_Post_List', 'post_list_tag_replace' ], 10, 2 );

/**
 * Checks if the post type is not blacklisted
 */
add_filter( 'tve_allowed_post_type', 'tar_is_post_type_allowed', 10, 2 );

/**
 * Default styles - printed earlier than global style, only on non-editor pages
 */
add_action( 'wp_head', function () {
	if ( tcb_should_print_unified_styles() ) {
		tcb_print_frontend_styles();
	}
}, 90, 0 );

/**
 * Backwards compatibility
 * Replace #tve_editor with the new selector
 *
 * Also checks if the disable css option is checked from Thrive Dashboard.
 * If so, we strip the import statements from the css string
 *
 * Gets called from all products that have TAR as a dependency
 *
 * @param $css
 *
 * @return string
 */
function tcb_custom_css( $css ) {

	if ( function_exists( 'tve_dash_is_google_fonts_blocked' ) && tve_dash_is_google_fonts_blocked() ) {
		$css = preg_replace( '/@import url\((\\\)?\"(http:|https:)?\/\/fonts\.(googleapis|gstatic)\.com([^)]*)\);/', '', $css );
	}

	/**
	 * Whether the css should be minified or not
	 */
	if ( apply_filters( 'tve_should_minify_css', true ) ) {
		$css = tve_minify_css( $css );
	}

	return str_replace( '#tve_editor', tcb_selection_root(), $css );
}

/**
 * Try some css minification
 *
 * @param string $css
 *
 * @return string
 */
function tve_minify_css( $css = '' ) {

	/* replace new line with empty space */
	$css = preg_replace( '/\n|\r/m', '', $css );

	/* replace more than two spaces with just one space */
	$css = preg_replace( '/\s{2,}/m', '', $css );

	/* remove spaces before and after , : and ; */
	$css = preg_replace_callback( '/\s*([,;{}])(?!:)\s*/m', static function ( $match ) {
		return $match[1];
	}, $css );

	return $css;
}

/**
 * render all necessary things for page-level event manager
 *
 * @param array $events
 */
if ( ! function_exists( 'tve_page_events' ) ) {
	function tve_page_events( $events = [] ) {
		$triggers = tve_get_event_triggers( 'page' );
		$actions  = tve_get_event_actions( 'page' );

		/* hold all the javascript callbacks required for the identified actions */
		$javascript_callbacks = isset( $GLOBALS['tve_event_manager_callbacks'] ) ? $GLOBALS['tve_event_manager_callbacks'] : [];

		/* holds all the Global JS required by different actions and event triggers on page load */
		$registered_javascript_globals = isset( $GLOBALS['tve_event_manager_global_js'] ) ? $GLOBALS['tve_event_manager_global_js'] : [];

		/* hold all instances of the Action classes in order to output stuff in the footer, we need to get out of the_content filter */
		$registered_actions = isset( $GLOBALS['tve_event_manager_actions'] ) ? $GLOBALS['tve_event_manager_actions'] : [];

		/* each trigger instance might also need a bit of javascript to trigger it */
		$registered_triggers = isset( $GLOBALS['tve_event_manager_triggers'] ) ? $GLOBALS['tve_event_manager_triggers'] : [];

		/*
		 * all page level events
		 */
		foreach ( $events as $index => $event_config ) {
			if ( empty( $event_config['t'] ) || empty( $event_config['a'] ) || ! isset( $triggers[ $event_config['t'] ] ) || ! isset( $actions[ $event_config['a'] ] ) ) {
				continue;
			}
			/** @var TCB_Event_Action_Abstract $action */
			$action                = $actions[ $event_config['a'] ];
			$registered_actions [] = [
				'class'        => $action,
				'event_config' => $event_config,
			];

			/** @var TCB_Event_Trigger_Abstract $trigger */
			$trigger                = $triggers[ $event_config['t'] ];
			$registered_triggers [] = [
				'class'        => $trigger,
				'event_config' => $event_config,
			];

			if ( ! isset( $javascript_callbacks[ $event_config['a'] ] ) ) {
				$javascript_callbacks[ $event_config['a'] ] = $action->getJsActionCallback();
			}
			if ( ! isset( $registered_javascript_globals[ 'action_' . $event_config['a'] ] ) ) {
				$registered_javascript_globals[ 'action_' . $event_config['a'] ] = $action;
			}
			if ( ! isset( $registered_javascript_globals[ 'trigger_' . $event_config['t'] ] ) ) {
				$registered_javascript_globals[ 'trigger_' . $event_config['t'] ] = $trigger;
			}
		}

		if ( empty( $javascript_callbacks ) ) {
			return;
		}

		/* we need to add all the javascript callbacks into the page */
		/* this cannot be done using wp_localize_script WP function, as each if the callback will actually be JS code */
		///euuuughhh

		$GLOBALS['tve_event_manager_callbacks'] = $javascript_callbacks;
		$GLOBALS['tve_event_manager_global_js'] = $registered_javascript_globals;
		$GLOBALS['tve_event_manager_actions']   = $registered_actions;
		$GLOBALS['tve_event_manager_triggers']  = $registered_triggers;

		/* execute the mainPostCallback on all of the related actions, some of them might need to register stuff (e.g. lightboxes) */
		foreach ( $GLOBALS['tve_event_manager_actions'] as $key => $item ) {
			if ( empty( $item['main_post_callback_'] ) ) {
				$GLOBALS['tve_event_manager_actions'][ $key ]['main_post_callback_'] = true;
				$item['class']->mainPostCallback( $item['event_config'] );
			}
		}

		/* remove previously assigned callback, if any */
		remove_action( 'wp_print_footer_scripts', 'tve_print_footer_events', - 50 );
		add_action( 'wp_print_footer_scripts', 'tve_print_footer_events', - 50 );
	}
}


add_filter( 'tve_frontend_options_data', 'tve_frontend_data' );

/**
 * Smart complete related stuff added through a filter so other plugins can use smart complete when TAr doesnt laod the frontend files directly
 *
 * @param $frontend_options
 *
 * @return mixed
 */
function tve_frontend_data( $frontend_options ) {
	$is_editor                        = is_editor_page();
	$frontend_options['ip']           = tve_dash_get_ip();
	$frontend_options['current_user'] = tve_current_user_data();

	if ( isset( $frontend_options['is_single'] ) && $frontend_options['is_single'] === '1' ) {
		$post_id                        = get_the_ID();
		$frontend_options['post_id']    = $post_id;
		$frontend_options['post_title'] = get_the_title();
		$frontend_options['post_type']  = get_post_type();
		$frontend_options['post_url']   = get_permalink();
		if ( ! $is_editor ) {
			$tcb_post                  = tcb_post( $post_id );
			$frontend_options['is_lp'] = $tcb_post->is_landing_page();
		}
	}

	if ( ! $is_editor ) {
		$frontend_options['post_request_data'] = empty( $_POST ) ? [] : $_POST;
	}

	return $frontend_options;
}

add_action( 'after_switch_theme', 'tve_reset_cloud_templates' );

/**
 * On theme switch we delete the Cloud Template Cache from transients
 */
function tve_reset_cloud_templates() {
	delete_transient( tve_get_cloud_templates_transient_name() );
}

add_action( 'wp_footer', function () {
	/**
	 * In case the login element has refresh page and success message enabled then after refresh this show the success message
	 */
	echo "<script type='text/javascript'>";
	include( 'editor/js/inline/toast-message.js' );
	echo '</script>';
} );
