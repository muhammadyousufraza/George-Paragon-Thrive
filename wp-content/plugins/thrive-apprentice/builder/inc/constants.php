<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

defined( 'THEME_URL' ) || define( 'THEME_URL', get_template_directory_uri() );
defined( 'THEME_PATH' ) || define( 'THEME_PATH', get_template_directory() );

defined( 'THEME_VERSION' ) || define( 'THEME_VERSION', '3.28' );
defined( 'THEME_DOMAIN' ) || define( 'THEME_DOMAIN', 'thrive-theme' );
defined( 'REQUIRED_WP_VERSION' ) || define( 'REQUIRED_WP_VERSION', '4.9' );
/* minimum version of TAr needed for TTB launch */
defined( 'ARCHITECT_LAUNCH_VERSION' ) || define( 'ARCHITECT_LAUNCH_VERSION', '2.4.9' );

defined( 'THRIVE_TEMPLATE' ) || define( 'THRIVE_TEMPLATE', 'thrive_template' );
defined( 'THRIVE_SECTION' ) || define( 'THRIVE_SECTION', 'thrive_section' );
defined( 'THRIVE_LAYOUT' ) || define( 'THRIVE_LAYOUT', 'thrive_layout' );
defined( 'THRIVE_TYPOGRAPHY' ) || define( 'THRIVE_TYPOGRAPHY', 'thrive_typography' );
defined( 'SKIN_TAXONOMY' ) || define( 'SKIN_TAXONOMY', 'thrive_skin_tax' );

defined( 'THRIVE_THEME_FLAG' ) || define( 'THRIVE_THEME_FLAG', 'tvet' );
defined( 'THRIVE_PREVIEW_FLAG' ) || define( 'THRIVE_PREVIEW_FLAG', '_preview' );
defined( 'THRIVE_NO_BAR' ) || define( 'THRIVE_NO_BAR', 'thrive_no_bar' );
defined( 'THRIVE_SKIN_PREVIEW' ) || define( 'THRIVE_SKIN_PREVIEW', 'thrive_skin_preview' );

defined( 'THRIVE_TEMPLATE_STYLE' ) || define( 'THRIVE_TEMPLATE_STYLE', 'thrive_template_style' );
defined( 'TTB_REST_NAMESPACE' ) || define( 'TTB_REST_NAMESPACE', 'ttb/v1' );
defined( 'TCB_REST_NAMESPACE' ) || define( 'TCB_REST_NAMESPACE', 'tcb/v1' );

defined( 'THRIVE_PRIMARY_TEMPLATE' ) || define( 'THRIVE_PRIMARY_TEMPLATE', 'primary_template' );
defined( 'THRIVE_SECONDARY_TEMPLATE' ) || define( 'THRIVE_SECONDARY_TEMPLATE', 'secondary_template' );
defined( 'THRIVE_VARIABLE_TEMPLATE' ) || define( 'THRIVE_VARIABLE_TEMPLATE', 'variable_template' );

defined( 'THRIVE_POST_LIST_LOCALIZE' ) || define( 'THRIVE_POST_LIST_LOCALIZE', 'thrive_post_list_localize' );
defined( 'THRIVE_SIDEBARS_OPTION' ) || define( 'THRIVE_SIDEBARS_OPTION', 'thrive_sidebars' );
defined( 'THRIVE_DEFAULT_SIDEBAR' ) || define( 'THRIVE_DEFAULT_SIDEBAR', 'default-theme-sidebar' );

defined( 'THRIVE_MENU_SLUG' ) || define( 'THRIVE_MENU_SLUG', 'thrive-theme-dashboard' );
defined( 'THRIVE_THEME_DASH_PAGE' ) || define( 'THRIVE_THEME_DASH_PAGE', 'thrive-dashboard_page_thrive-theme-dashboard' );

defined( 'ARCHITECT_INTEGRATION_PATH' ) || define( 'ARCHITECT_INTEGRATION_PATH', THEME_PATH . '/integrations/architect' );
defined( 'THEME_ASSETS_URL' ) || define( 'THEME_ASSETS_URL', THEME_URL . '/inc/assets/dist' );

/* TAr plugin slug */
defined( 'THRIVE_ARCHITECT_SLUG' ) || define( 'THRIVE_ARCHITECT_SLUG', 'thrive-visual-editor/thrive-visual-editor.php' );

/* @see https://developer.wordpress.org/files/2014/10/template-hierarchy.png */
defined( 'THRIVE_ARCHIVE_TEMPLATE' ) || define( 'THRIVE_ARCHIVE_TEMPLATE', 'archive' );
defined( 'THRIVE_SINGLE_TEMPLATE' ) || define( 'THRIVE_SINGLE_TEMPLATE', 'single' );
defined( 'THRIVE_SINGULAR_TEMPLATE' ) || define( 'THRIVE_SINGULAR_TEMPLATE', 'singular' );
defined( 'THRIVE_PAGE_TEMPLATE' ) || define( 'THRIVE_PAGE_TEMPLATE', 'page' );
defined( 'THRIVE_POST_TEMPLATE' ) || define( 'THRIVE_POST_TEMPLATE', 'post' );
defined( 'THRIVE_CUSTOM_TEMPLATE' ) || define( 'THRIVE_CUSTOM_TEMPLATE', 'custom' );
defined( 'THRIVE_HOMEPAGE_TEMPLATE' ) || define( 'THRIVE_HOMEPAGE_TEMPLATE', 'home' );
defined( 'THRIVE_BLOG_TEMPLATE' ) || define( 'THRIVE_BLOG_TEMPLATE', 'blog' );
defined( 'THRIVE_SEARCH_TEMPLATE' ) || define( 'THRIVE_SEARCH_TEMPLATE', 'search' );
defined( 'THRIVE_ERROR404_TEMPLATE' ) || define( 'THRIVE_ERROR404_TEMPLATE', 'error404' );

defined( 'THRIVE_HEADER_SECTION' ) || define( 'THRIVE_HEADER_SECTION', 'header' );
defined( 'THRIVE_FOOTER_SECTION' ) || define( 'THRIVE_FOOTER_SECTION', 'footer' );
defined( 'THRIVE_SIDEBAR_TEMPLATE' ) || define( 'THRIVE_SIDEBAR_TEMPLATE', 'sidebar' );
defined( 'THRIVE_BASE' ) || define( 'THRIVE_BASE', 'base' );

defined( 'THRIVE_AUTHOR_ARCHIVE_TEMPLATE' ) || define( 'THRIVE_AUTHOR_ARCHIVE_TEMPLATE', 'author' );
defined( 'THRIVE_CATEGORY_TEMPLATE' ) || define( 'THRIVE_CATEGORY_TEMPLATE', 'category' );
defined( 'THRIVE_TAXONOMY_TEMPLATE' ) || define( 'THRIVE_TAXONOMY_TEMPLATE', 'taxonomy' );
defined( 'THRIVE_DATE_TEMPLATE' ) || define( 'THRIVE_DATE_TEMPLATE', 'date' );
defined( 'THRIVE_TAG_TEMPLATE' ) || define( 'THRIVE_TAG_TEMPLATE', 'post_tag' );

$upload_dir = wp_upload_dir();
defined( 'UPLOAD_DIR_PATH' ) || define( 'UPLOAD_DIR_PATH', $upload_dir['basedir'] );
defined( 'UPLOAD_DIR_URL_NO_PROTOCOL' ) || define( 'UPLOAD_DIR_URL_NO_PROTOCOL', str_replace( [ 'http:', 'https:' ], '', $upload_dir['baseurl'] ) );
defined( 'UPLOAD_DIR_URL' ) || define( 'UPLOAD_DIR_URL', $upload_dir['baseurl'] );

defined( 'THRIVE_SOCIAL_OPTION_NAME' ) || define( 'THRIVE_SOCIAL_OPTION_NAME', 'thrive_social_urls' );
defined( 'THRIVE_META_POST_TEMPLATE' ) || define( 'THRIVE_META_POST_TEMPLATE', 'thrive_post_template' );
defined( 'THRIVE_META_POST_AMP_STATUS' ) || define( 'THRIVE_META_POST_AMP_STATUS', 'thrive_post_amp_status' );
defined( 'THRIVE_USE_INLINE_CSS' ) || define( 'THRIVE_USE_INLINE_CSS', 'thrive_use_inline_css' );

/* branding constants */
defined( 'THRIVE_LOGO_URL_OPTION' ) || define( 'THRIVE_LOGO_URL_OPTION', 'thrive-theme-logo-url' );
defined( 'THRIVE_FAVICON_OPTION' ) || define( 'THRIVE_FAVICON_OPTION', 'thrive-theme-favicon' );
defined( 'THRIVE_FAVICON_PLACEHOLDER' ) || define( 'THRIVE_FAVICON_PLACEHOLDER', THEME_URL . '/inc/assets/svg/favicon_placeholder.svg' );

defined( 'THRIVE_COMMENT_FORM_ERROR_MESSAGES' ) || define( 'THRIVE_COMMENT_FORM_ERROR_MESSAGES', 'thrive_comment_form_error_messages' );

defined( 'THRIVE_CONTENT_WIDTH_CLASS' ) || define( 'THRIVE_CONTENT_WIDTH_CLASS', 'layout-content-width' );
defined( 'THRIVE_POST_WRAPPER_CLASS' ) || define( 'THRIVE_POST_WRAPPER_CLASS', 'post-wrapper' );
defined( 'THRIVE_WRAPPER_CLASS' ) || define( 'THRIVE_WRAPPER_CLASS', 'thrv_wrapper' );

defined( 'THRIVE_BLOG_LIST_IDENTIFIER' ) || define( 'THRIVE_BLOG_LIST_IDENTIFIER', '#main' );

defined( 'CONTENT_SWITCH_ITEMS_TO_LOAD' ) || define( 'CONTENT_SWITCH_ITEMS_TO_LOAD', 5 );
defined( 'THRIVE_THEME_SWITCHED_CONTENT' ) || define( 'THRIVE_THEME_SWITCHED_CONTENT', 'thrive_theme_switched_content' );
defined( 'THRIVE_STANDARD_POST_FORMAT' ) || define( 'THRIVE_STANDARD_POST_FORMAT', 'standard' );

defined( 'THRIVE_FEATURED_IMAGE_OPTION' ) || define( 'THRIVE_FEATURED_IMAGE_OPTION', 'thrive-theme-default-featured-image' );
defined( 'THRIVE_FEATURED_IMAGE_PLACEHOLDER' ) || define( 'THRIVE_FEATURED_IMAGE_PLACEHOLDER', THEME_URL . '/inc/assets/images/featured_image.png' );
defined( 'THRIVE_SOCIAL_MEDIA_IMAGE_PLACEHOLDER' ) || define( 'THRIVE_SOCIAL_MEDIA_IMAGE_PLACEHOLDER', THEME_URL . '/inc/assets/images/featured_image.png' );
defined( 'THRIVE_AUTHOR_IMAGE_PLACEHOLDER' ) || define( 'THRIVE_AUTHOR_IMAGE_PLACEHOLDER', THEME_URL . '/inc/assets/images/author_image.png' );

defined( 'THRIVE_THEME_BUTTON_CLASS' ) || define( 'THRIVE_THEME_BUTTON_CLASS', 'theme-button' );
defined( 'THRIVE_THEME_BUTTON_COMPONENT' ) || define( 'THRIVE_THEME_BUTTON_COMPONENT', 'theme_button' );
defined( 'THRIVE_THEME_BUTTON_LAYOUT_TEXT_ONLY' ) || define( 'THRIVE_THEME_BUTTON_LAYOUT_TEXT_ONLY', 'text' );
defined( 'THRIVE_THEME_BUTTON_LAYOUT_ICON_ONLY' ) || define( 'THRIVE_THEME_BUTTON_LAYOUT_ICON_ONLY', 'icon' );
defined( 'THRIVE_THEME_BUTTON_LAYOUT_TEXT_AND_ICON' ) || define( 'THRIVE_THEME_BUTTON_LAYOUT_TEXT_AND_ICON', 'text_plus_icon' );

defined( 'THRIVE_THEME_SOUNDCLOUD_EMBED_URL' ) || define( 'THRIVE_THEME_SOUNDCLOUD_EMBED_URL', 'https://w.soundcloud.com/player/?url=' );
defined( 'THEME_FOLDER' ) || define( 'THEME_FOLDER', 'thrive-theme' );

defined( 'THEME_UPLOADS_PREVIEW_SUB_DIR' ) || define( 'THEME_UPLOADS_PREVIEW_SUB_DIR', THEME_FOLDER . '/preview' );

defined( 'THRIVE_DEMO_CONTENT_THUMBNAIL' ) || define( 'THRIVE_DEMO_CONTENT_THUMBNAIL', 'thrive_theme_demo_content_thumb' );

defined( 'THEME_SKIN_COLOR_VARIABLE_PREFIX' ) || define( 'THEME_SKIN_COLOR_VARIABLE_PREFIX', '--tcb-skin-color-' );
defined( 'THEME_SKIN_GRADIENT_VARIABLE_PREFIX' ) || define( 'THEME_SKIN_GRADIENT_VARIABLE_PREFIX', '--tcb-skin-gradient-' );

defined( 'THEME_MAIN_COLOR_H' ) || define( 'THEME_MAIN_COLOR_H', '--tcb-theme-main-master-h' );//Main Color Hue
defined( 'THEME_MAIN_COLOR_S' ) || define( 'THEME_MAIN_COLOR_S', '--tcb-theme-main-master-s' );//Main Color Saturation
defined( 'THEME_MAIN_COLOR_L' ) || define( 'THEME_MAIN_COLOR_L', '--tcb-theme-main-master-l' );//Main Color Lightness
defined( 'THEME_MAIN_COLOR_A' ) || define( 'THEME_MAIN_COLOR_A', '--tcb-theme-main-master-a' );//Main Color Alpha

defined( 'THRIVE_EXPORT_ID' ) || define( 'THRIVE_EXPORT_ID', 'export_id' );
defined( 'READING_WORDS_PER_MINUTE' ) || define( 'READING_WORDS_PER_MINUTE', 200 );

defined( 'THRIVE_THEME_INSIDE_PRE_GET_POSTS' ) || define( 'THRIVE_THEME_INSIDE_PRE_GET_POSTS', 'thrive_theme_inside_pre_get_posts' );

defined( 'THRIVE_THEME_HOMEPAGE_SET_FROM_WIZARD' ) || define( 'THRIVE_THEME_HOMEPAGE_SET_FROM_WIZARD', 'thrive_theme_homepage_set_from_wizard' );
