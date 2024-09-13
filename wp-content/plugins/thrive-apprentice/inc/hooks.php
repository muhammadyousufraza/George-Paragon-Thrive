<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-university
 */

use TCB\VideoReporting\Video;
use TVA\Assessments\TVA_User_Assessment;
use TVA\Drip\Campaign;
use TVA\Drip\Trigger\Time_After_First_Lesson;
use TVA\Product;
use TVA\Reporting\Events\Video_Completed;
use TVA\Stripe\Connection;
use TVD\Content_Sets\Set;
use TVD\Content_Sets\Term_Rule;
use function TVA\Architect\Visual_Builder\tcb_tva_visual_builder;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

/**
 * Add TA Product to Thrive Dashboard
 */
add_filter( 'tve_dash_installed_products', 'tva_add_to_dashboard' );

/**
 * Clean the iframe of unused data
 */
add_action( 'wp', 'tva_clean_inner_frame' );

/**
 * Initialize the theme builder integration
 */
add_action( 'after_setup_theme', array( 'TVA\TTB\Main', 'init' ), 9 );

/**
 * Check for any download query string parameters and try to download a resource
 */
add_action( 'init', array( 'TVA_Resource', 'on_init_try_download' ) );

/**
 * Enqueue front-end scripts
 */
add_action( 'wp_enqueue_scripts', 'tva_frontend_enqueue_scripts', 100 );

/**
 * Add the template styles to the head
 */
add_action( 'wp_head', 'tva_add_head_styles' );

/**
 * Register required post types
 *
 * Priority 9 because we need post-types and taxonomies registered before priority 10
 * see [ TVA\Access\Main::class, 'init' ] hook line 128
 */
add_action( 'init', 'tva_init', 9 );

/**
 * Run the product migration later on the init hook - this way we can be sure all db tables exist when running the migration
 */
add_action( 'init', [ 'TVA\Product_Migration', 'migrate' ], 11 );

/**
 * On template redirect, fire the TVA HOOKS needed by the third party developers
 */
add_action( 'template_redirect', 'tva_hooks' );

/**
 * On template redirect - update last online timestamp for the active user
 */
add_action( 'template_redirect', 'tva_update_last_online' );

/**
 * Create the initial rest routes
 */
add_action( 'rest_api_init', 'tva_create_initial_rest_routes' );

/**
 * After plugin is loaded load ThriveDashboard Section
 */
add_action( 'plugins_loaded', 'tva_load_dash_version' );

/**
 * include the correct template
 */
add_action( 'template_include', 'tva_template', 99, 1 );

/**
 * Redirect users in specific cases
 */
add_filter( 'template_redirect', 'tva_template_redirect' );

/**
 * Restrict access to demo courses
 */
add_filter( 'template_redirect', 'tva_redirect_if_private' );

/**
 * Modifies the admin bar before render by hiding some pages
 */
add_action( 'wp_before_admin_bar_render', 'tva_modify_admin_bar_before_render', PHP_INT_MAX );

/**
 * Create a sidebar for apprentice
 */
add_action( 'widgets_init', 'tva_widgets_init' );

/**
 * change the taxonomy query to order the lessons
 */
add_action( 'pre_get_posts', 'tva_exclude_posts_from_search' );

/**
 * Change the next/posts links attributes
 */
add_filter( 'next_post_link', 'tva_next_posts_link_attributes' );
add_filter( 'previous_post_link', 'tva_prev_posts_link_attributes' );

/**
 * Re-construct all the next post and previous post queries
 */
add_filter( 'get_next_post_where', 'tva_get_where_post_type_adjacent_post', 10, 5 );
add_filter( 'get_previous_post_where', 'tva_get_where_post_type_adjacent_post', 10, 5 );
add_filter( 'get_next_post_sort', 'tva_get_next_sort_post_type_adjacent_post', 10, 2 );
add_filter( 'get_previous_post_sort', 'tva_get_prev_sort_post_type_adjacent_post', 10, 2 );
add_filter( 'get_next_post_join', 'tva_post_join', 10, 5 );
add_filter( 'get_previous_post_join', 'tva_post_join', 10, 5 );

add_action( 'init', [ TVA\Reporting\Main::class, 'init' ], 1 );
/**
 * Compatibility with other membership plugins.
 * Example: MemberPress hooks with priority 10 - we need to define callbacks before them
 */
add_action( 'init', [ TVA\Access\Main::class, 'init' ], 9 );

/**
 * initialize the update checker here because the required classes are loaded by dashboard at plugins_loaded
 */
add_action( 'init', 'tva_update_checker' );

add_action( 'thrive_dashboard_loaded', 'tva_dashboard_loaded' );

/**
 * Add content builder admin bar options
 */
add_action( 'admin_bar_menu', 'tva_admin_bar', 101 );

/**
 * Register Menu
 */

add_action( 'init', 'tva_register_menu' );

/**
 * Hook into the wordpress account creation action
 */
add_action( 'tvd_after_create_wordpress_account', 'tva_perform_auto_login', 10, 2 );

/**
 * Apprentice registration form
 */
add_action( 'register_form', 'tva_build_registration_page_html' );

/**
 * Rregister user
 */
add_action( 'tva_register', 'tva_register_user' );

/**
 * Redirect after user registration
 */
add_action( 'wp_login', 'tva_redirect_user' );

/**
 * Hide Apprentice register page from admin
 */
add_filter( 'parse_query', 'tva_hide_default_register_page' );

/**
 * Process comments on course page
 */
add_filter( 'preprocess_comment', 'tva_process_comment_data' );

/**
 * Count course copmments
 */
add_filter( 'get_comments_number', 'tva_count_course_comments', 10, 1 );

/**
 * Force disqus to grab course data when a comment is posted
 */
add_action( 'wp_footer', 'tva_add_course_to_disqus', 1000 );

/**
 * Load disqus comment template on courses page
 */
add_filter( 'comments_template', 'tva_handle_comments_template', 1000 );

/**
 * Handle comment notify email
 */
add_filter( 'comment_moderation_text', 'tva_comment_moderation_text', 10, 2 );

/**
 * Handle comment notify email head
 */
add_filter( 'comment_moderation_subject', 'tva_comment_moderation_subject', 10, 2 );

/**
 * Add facebook SDK
 */
add_action( 'wp_head', 'tva_load_fb_sdk' );

/**
 * Add Facebook html, It is also used in Thrive themes to make the comment template compatible with apprentice
 */
add_action( 'tva_on_fb_comments', 'tva_load_fb_comment_html' );

/**
 * Replace the hidden post permalink with term urlr
 */
add_filter( 'post_type_link', 'tva_on_comment_course_permalink', 10, 3 );
//
///**
// * Replace hidden post edit url with term's permalink
// */
add_filter( 'get_edit_post_link', 'tva_on_comment_course_url', 10, 2 );
//
///**
// * Get the term url by its comment
// */
add_filter( 'get_comment_link', 'tva_get_term_url_by_comment', 1000, 2 );

/**
 * Get term title by its comment
 */
add_filter( 'the_title', 'tva_on_comment_course_title', 10, 2 );

add_filter( 'comments_open', 'tva_ensure_comments_open' );

///////////////////////////////////////////////////////////////
/// 				THRIVE COMMENTS HOOKS                   ///
/// ///////////////////////////////////////////////////////////

/**
 * Load TC template
 */
add_filter( 'tcm_show_comments', 'tva_load_tc_template' );

/**
 * Get comments for tc in frontend
 */
add_filter( 'tcm_get_comments', 'tva_tcm_get_comments', 10, 2 );

/**
 * Get course data for tc in frontend
 */
add_filter( 'tcm_comments_localization', 'tva_tcm_comments_localization' );

/**
 * Add comment meta for new comments in forntend
 */
add_filter( 'tcm_comments_fields', 'tva_tcm_comments_fields', 10, 2 );

/**
 * TC: Get comment's course in admin moderation
 */
add_filter( 'tcm_get_post_for_comment', 'tva_tcm_get_course_for_comment', 10, 2 );

/**
 * TC: Add courses to autocomplete list in moderation section
 */
add_filter( 'tcm_posts_autocomplete', 'tva_tcm_posts_autocomplete', 10, 2 );

/**
 * TC: Add comments from courses to moderation list in wp-admin
 */
add_filter( 'rest_comment_query', 'tva_rest_comment_query', 1000, 2 );

/**
 * TC: Handle new child comments created in comments moderation
 */
add_filter( 'rest_preprocess_comment', 'tva_rest_preprocess_comment', 10, 2 );

/**
 * TC: Handle new comments created with tc in frontend
 */
add_filter( 'tcm_rest_moderation_response', 'tva_comment_rest_moderation_response' );

/**
 * TC: Count comments in frontend for courses
 */
add_filter( 'tcm_comment_count', 'tva_tcm_comment_count', 10, 2 );

/**
 * TC: Handle featured comments
 */
add_filter( 'tcm_get_featured_comments', 'tva_tcm_get_featured_comments', 10, 2 );

/**
 * Subscribe user to comment
 */
add_action( 'tcm_post_subscribe', 'tva_tcm_post_subscribe' );

/**
 * Unsubscribe user from comment
 */
add_action( 'tcm_post_unsubscribe', 'tva_tcm_post_unsubscribe' );

/**
 * Get term url in TC mail template
 */
add_filter( 'tcm_comment_notification_email', 'tva_tcm_get_term_url', 10, 2 );

/**
 * Get term subscribers
 */
add_filter( 'tcm_post_subscribers', 'tva_get_term_subscribers', 10, 2 );

/**
 * Count comments for TC
 */
add_filter( 'tcm_user_comment_count', 'tva_tcm_user_comment_count', 10, 2 );

/**
 * Most popular courses
 */
add_filter( 'tcm_most_popular_posts', 'tva_tcm_most_popular_posts', 10, 3 );

/**
 * Add courses to TC meta filters
 */
add_filter( 'tcm_reports_featured_query', 'tva_tcm_reports_featured_query', 10, 2 );

/**
 * Add courses to TC reports filters
 */
add_filter( 'tcm_reports_post_filter', 'tva_tcm_reports_post_filter', 10, 2 );

/**
 * Add terms to TC comments reports
 */
add_filter( 'tcm_reports_extra_filter', 'tva_tcm_reports_extra_filter', 10, 2 );

/**
 * Count the comments in TC admin moderation header.
 */
add_filter( 'tcm_header_comment_count', 'tva_tcm_header_comment_count', 10, 2 );

/**
 * Add terms to TC voting charts
 */
add_filter( 'tcm_reports_votes_extra_filter', 'tva_tcm_reports_votes_extra_filter', 10, 2 );

/**
 * Process comment obj after save
 */
add_filter( 'tcm_comment_after_save', 'tva_tcm_comment_after_save' );

/**
 * Count unreplied comments
 */
add_filter( 'tcm_get_unreplied_args', 'tva_tcm_get_unreplied_args', 10, 2 );

/**
 * Filter comment delegate query
 */
add_filter( 'tcm_comment_delegate', 'tva_tcm_comment_delegate', 10, 2 );

/**
 * Expand TC delegate meta query, add a new clause
 */
add_filter( 'tcm_delegate_rest_meta_query', 'tva_tcm_delegate_rest_meta_query', 10, 2 );

/**
 * Add comments from courses in TC
 */
add_filter( 'tcm_delegate_extra_where', 'tva_tcm_delegate_extra_where', 10, 5 );

/**
 * Expand TC delegate query, add a join clause
 */
add_filter( 'tcm_delegate_extra_join', 'tva_tcm_delegate_extra_join' );

add_filter( 'tcm_close_comments', 'tva_tcm_close_comments' );

add_filter( 'tcm_most_upvoted', 'tva_tcm_most_upvoted' );

add_filter( 'tcm_get_post', 'tva_tcm_get_post' );

///////////////////////////////////////////////////////////////
/// 			    END THRIVE COMMENTS HOOKS               ///
/// ///////////////////////////////////////////////////////////

/**
 * Add our post types
 */
add_filter( 'tcb_autocomplete_selected_post_types', 'tva_add_post_types' );

/**
 * Add our courses to the results
 */
add_filter( 'tcb_autocomplete_returned_posts', 'tva_add_courses_to_results', 10, 2 );

add_filter( 'tcm_privacy_post_types', 'tva_tcm_privacy_post_types' );

add_filter( 'tcm_label_privacy_text', 'tva_tcm_label_privacy_text', 10, 2 );

add_action( 'wp_footer', 'add_frontend_svg_file' );

/* adds the svg file containing all the svg icons for the admin pages */
add_action( 'admin_head', 'add_frontend_svg_file' );

add_action( 'template_redirect', static function () {
	if ( ! tva_is_apprentice() || TVA_Product::has_access() ) {
		return;
	}

	$course = tcb_tva_visual_builder()->get_active_course();
	if ( $course instanceof TVA_Course_V2 && ! in_array( $course->get_status(), [ 'publish', 'hidden', 'archived' ] ) ) {
		global $wp_query;
		$wp_query->set_404();
		status_header( 404 );
		nocache_headers();
	}
}, 0 );

add_action( 'thrive_theme_template_meta', static function ( $meta ) {
	if ( ! tva_is_apprentice() || TVA_Product::has_access() ) {
		return $meta;
	}

	$obj  = get_queried_object();
	$term = $obj instanceof WP_Post ?
		( wp_get_post_terms( $obj->ID, TVA_Const::COURSE_TAXONOMY ) ? wp_get_post_terms( $obj->ID, TVA_Const::COURSE_TAXONOMY )[0] : null ) :
		get_term( $obj->term_id, TVA_Const::COURSE_TAXONOMY );

	if ( $term instanceof WP_Term ) {
		$course = new TVA_Course_V2( $term );

		if ( $course instanceof TVA_Course_V2 && ! in_array( $course->get_status(), [ 'publish', 'hidden', 'archived' ] ) ) {
			$meta['primary_template']   = THRIVE_ERROR404_TEMPLATE;
			$meta['secondary_template'] = '';
			$meta['variable_template']  = '';

			global $wp_query;
			$wp_query->set_404();
			status_header( 404 );
			nocache_headers();
		}
	}

	return $meta;
}, PHP_INT_MAX );

add_filter( 'mm_bypass_content_protection', 'tva_custom_content_protection' );
add_filter( 'mm_bypass_content_protection', 'tva_mm_filter_access' );

add_filter( 'wishlistmember_login_redirect_override', 'tva_wishlistmember_login_redirect_override' );

add_filter( 'login_redirect', 'tva_login_redirect', 1, 3 );

add_action( 'wp_login_errors', 'tva_login_form_redirect', 10, 2 );

///////////////////////////////////////////////////////////////////
///                    TOP INTEGRATION                       //////
///////////////////////////////////////////////////////////////////

add_filter( 'thrive_ab_monetary_services', 'tva_filter_ab_monetary_services' );

add_filter( 'thrive_ab_pre_impression', 'tva_ab_event_saved' );

add_filter( 'tva_order_tag_data', 'tva_filter_order_tag_data' );

add_action( 'tva_after_sendowl_process_notification', 'tva_try_do_top_conversion', 10, 2 );

/**
 * Skip tcb license check
 */
add_filter( 'tcb_skip_license_check', 'tva_tcb_skip_license_check' );

/**
 * Exclude demo posts generated by TA from sitemap generated by Yoast
 */
add_filter( 'wpseo_exclude_from_sitemap_by_post_ids', 'tva_wpseo_exclude_from_sitemap_by_post_ids' );

/**
 * Exclude the url of private courses from sitemap generated by Yoast
 */
add_filter( 'wpseo_sitemap_entry', 'tva_wpseo_sitemap_entry', 10, 3 );

add_filter( 'thrive_leads_skip_request', 'tva_thrive_leads_skip_request' );

add_action( 'tva_after_save_course', 'tva_update_yoast_term_tax_meta', 10, 2 );

add_action( 'init', 'tva_load_plugin_textdomain' );

add_action( 'tcb_post_login_actions', 'tva_tcb_post_login_actions' );

add_filter( 'tcb_after_user_logged_in', 'tva_tcb_after_user_logged_in' );

add_filter( 'thrive_theme_get_posts_args', 'tva_theme_exclude_ta_pages' );

add_action( 'pre_get_document_title', 'tva_pre_get_document_title', 99 );

add_action( 'tcb_filter_landing_page_templates', 'tva_tcb_filter_landing_page_templates' );

add_action( 'tcb_allow_central_style_panel', 'tva_tcb_allow_central_style_panel' );

/**
 * Filter routes for WP-API-SWAGGER to list just some of them
 */
add_filter( 'rest_endpoints', 'tva_filter_endpoints_for_thrive_cart' );

/**
 * WP-API-SWAGGER uses Basic Authorization
 * - but we overwrite its logic with TVA Token
 */
add_filter( 'authenticate', 'tva_filter_authenticate', 100, 3 );

add_action( 'thrive_theme_shortcode_prefixes', 'tva_thrive_theme_shortcode_prefixes' );

add_action( 'tcm_post_url', 'tva_tcm_post_url' );

add_filter( 'tve_allowed_post_type', 'tva_disable_ab_testing', 1000, 2 );

add_filter( 'tve_link_autocomplete_post_types', 'tva_add_apprentice_post_types' );

add_filter( 'tve_link_autocomplete_default_post_types', 'tva_default_add_post_types' );

add_filter( 'thrive_theme_template_content', 'tva_thrive_theme_template_content', 10, 2 );

add_filter( 'tcb_lazy_load_data', 'tva_tcb_lazy_load_data', 10, 3 );

add_filter( 'thrive_dashboard_extra_user_data', 'tva_extra_user_data' );

add_action( 'template_include', 'tva_set_user_data', 99, 1 );

add_filter( 'tve_user_profile_hidden', '__return_false' );

add_action( 'tva_course_after_delete', [ TVA_User_Assessment::class, 'delete_course_submissions' ] );

/**
 * Add info article url for Apprentice Lesson List element
 */
add_filter(
	'thrive_kb_articles',
	static function ( $articles ) {
		$articles['apprentice_lesson_list'] = 'https://api.intercom.io/articles/4794728';
		$articles['certificate_qr']         = 'https://api.intercom.io/articles/6685758';

		return $articles;
	}
);

add_filter( 'wishlistmember_only_show_content_for_level', 'tva_wl_content_for_level' );

/**
 * Register cron for publishing courses
 */
add_action( 'tva_publish_future_term', 'tva_publish_future_term' );

/**
 * After a post is published check the parents and publish them if the case
 */
add_action( 'publish_future_post', 'tva_publish_parents', 11 );

/**
 * Filter image html used by TTB in post list
 */
add_action( 'thrive_theme_post_thumbnail', static function ( $image, $post_id, $post_type ) {

	if ( TVA_Const::LESSON_POST_TYPE === $post_type ) {
		$lesson      = new TVA_Lesson( $post_id );
		$cover_image = $lesson->cover_image;
		$image       = ! empty( $cover_image ) ? '<img src="' . $cover_image . '" loading="lazy">' : $image;
	}

	return $image;
}, 10, 3 );

/**
 * Filter image url used by TTB in post list
 */
add_action( 'thrive_theme_post_thumbnail_url', static function ( $image_url, $post_id, $post_type ) {

	if ( TVA_Const::LESSON_POST_TYPE === $post_type ) {
		$lesson      = new TVA_Lesson( $post_id );
		$cover_image = $lesson->cover_image;
		$image_url   = ! empty( $cover_image ) ? $cover_image : $image_url;
	}

	return $image_url;
}, 10, 3 );

add_filter( 'tve_dash_features', 'tva_enable_dashboard_features' );

/**
 * Checks all courses with WordPress protection rule
 * - if there is found a course which gives access to the newly user created
 *   then a enrolment trigger is fired
 */
add_action( 'user_register', array( 'TVA_Customer', 'on_user_register' ) );

/**
 * Excludes demo lessons/chapters/modules from {post_type}.xml sitemap
 */
add_filter( 'wp_sitemaps_posts_query_args', static function ( $args, $post_type ) {

	$list = array(
		'tva_lesson',
		'tva_chapter',
		'tva_module',
	);

	if ( in_array( $post_type, $list ) ) {
		$args['meta_query'][] = array(
			'key'     => 'tva_is_demo',
			'compare' => 'NOT EXISTS',
		);
	}

	return $args;
}, 10, 2 );

/**
 * Add Stripe connection to the list of available connections
 */
add_filter( 'tve_filter_available_connection', static function ( $list ) {

	if ( get_option( Connection::ACCOUNT_OPTION, '' ) ) {
		$list['stripe'] = "TVA\\Stripe\\Dash_Api_Connection";
	}

	return $list;
} );

/**
 * Excludes demo courses from tva_courses sitemap.xml
 */
add_filter( 'wp_sitemaps_taxonomies_query_args', static function ( $args, $taxonomy ) {

	if ( TVA_Const::COURSE_TAXONOMY === $taxonomy ) {
		$args['meta_query'][] = array(
			'key'     => 'tva_status',
			'value'   => 'private',
			'compare' => '!=',
		);
	}

	return $args;
}, 10, 2 );

/**
 * Exclude private lessons & modules from content sets UI
 *
 * @param boolean $allow
 * @param WP_Post $post
 *
 * @return boolean
 */
add_filter( 'tvd_content_sets_allow_select_post', static function ( $allow, $post ) {
	if ( in_array( $post->post_type, array( TVA_Const::LESSON_POST_TYPE, TVA_Const::MODULE_POST_TYPE ) ) && get_post_meta( $post->ID, 'tva_is_demo', true ) ) {
		$allow = false;
	}

	return $allow;
}, 10, 2 );

/**
 * Exclude private courses from content sets UI
 *
 * @param string  $status
 * @param WP_Term $term
 *
 * @return boolean
 */
add_filter( 'tvd_content_sets_get_term_status', static function ( $status, $term ) {
	if ( $term->taxonomy === TVA_Const::COURSE_TAXONOMY ) {
		$status = get_term_meta( $term->term_id, 'tva_status', true );
	}

	return $status;
}, 10, 2 );

/**
 * Exclude private courses from content sets UI
 *
 * @param boolean $allow
 * @param WP_Term $term
 *
 * @return boolean
 */
add_filter( 'tvd_content_sets_allow_select_term', static function ( $allow, $term ) {
	if ( $term->taxonomy === TVA_Const::COURSE_TAXONOMY && get_term_meta( $term->term_id, 'tva_status', true ) === 'private' ) {
		$allow = false;
	}

	return $allow;
}, 10, 2 );

/**
 * @param array     $response
 * @param Term_Rule $term_rule
 */
add_filter( 'tvd_content_sets_get_option_fields', static function ( $response, $term_rule ) {

	if ( $term_rule->content === TVA_Const::COURSE_TAXONOMY && in_array( $term_rule->field, [ 'topic', 'difficulty', 'label' ] ) ) {

		switch ( $term_rule->field ) {
			case 'topic':
				$items = TVA_Topic::get_items( true );
				break;
			case 'difficulty':
				$items = TVA_Level::get_items( true );
				break;
			case 'label';
				$items = tva_get_labels();
				break;
			default;
				$items = array();
				break;
		}

		foreach ( $items as $item ) {
			$title = ! empty( $item['name'] ) ? $item['name'] : '';
			if ( ! $title ) {
				$title = ! empty( $item['title'] ) ? $item['title'] : '';
			}

			$response[ $item['ID'] ] = $title;
		}
	}

	return $response;
}, 10, 2 );

add_filter( 'tvd_content_sets_field_value', static function ( $response, $term_rule, $term ) {
	if ( $term_rule->content === TVA_Const::COURSE_TAXONOMY && in_array( $term_rule->field, [ 'topic', 'difficulty', 'label', 'author' ] ) ) {
		$course = new TVA_Course_V2( $term );
		switch ( $term_rule->field ) {
			case 'difficulty':
				$response = $course->get_level_id();
				break;
			case 'topic':
				$response = $course->get_topic_id();
				break;
			case 'label':
				$response = $course->get_label_id();
				break;
			case 'author':
				$response = $course->get_author()->get_user()->ID;
				break;
			default:
				break;
		}
	}

	return $response;
}, 10, 3 );

/**
 * Hooks into get content sets main request and handles the case when a lesson/module is viewed from a protected course
 * The restriction from lesson/module should be applied from the course
 *
 * @param array                 $matched
 * @param false|WP_Post|WP_Term $post_or_term
 */
add_filter( 'tva_access_manager_get_content_sets_from_request', static function ( $matched = array(), $post_or_term = false ) {

	$should_query_for_course = $post_or_term instanceof WP_Post || is_singular();

	if ( $should_query_for_course ) {
		$post_id = $post_or_term ? $post_or_term->ID : get_the_ID();
	}

	if ( $should_query_for_course && in_array( get_post_type( $post_id ), [ TVA_Const::LESSON_POST_TYPE, TVA_Const::MODULE_POST_TYPE, TVA_Course_Completed::POST_TYPE, TVA_Const::ASSESSMENT_POST_TYPE ] ) ) {
		$terms = wp_get_post_terms( $post_id, TVA_Const::COURSE_TAXONOMY );

		if ( is_array( $terms ) && isset( $terms[0] ) && $terms[0] instanceof WP_Term ) {
			/**
			 * Here we need to check if the course ID belongs to a set
			 * But also we need to cover the case when a lesson or module can be single inside a set
			 */
			$matched = array_merge( $matched, Set::get_for_object( $terms[0], $terms[0]->term_id ) );
		}

		if ( get_post_type( $post_id ) === TVA_Const::LESSON_POST_TYPE ) {
			$lesson        = TVA_Post::factory( get_post( $post_id ) );
			$module_parent = $lesson->get_parent_by_type( TVA_Const::MODULE_POST_TYPE );

			if ( ! empty( $module_parent ) ) {
				/**
				 * If the current post is a lesson, and the lesson belongs to a module, we need to check if a set exists for the module
				 */
				$matched = array_merge( $matched, Set::get_for_object( $module_parent->get_the_post(), $module_parent->ID ) );
			}
		}
	}

	return $matched;
}, 10, 2 );


/**
 * Called from tva_login_form when computed the login form
 *
 * @param string $url
 *
 * @see   tva_login_form()
 */
add_filter( 'tva_access_manager_get_login_redirect_url', static function ( $url ) {

	if ( ! empty( tcb_tva_visual_builder()->get_active_course() ) ) {
		$url = tcb_tva_visual_builder()->get_active_course()->get_link();
	}

	return $url;
} );

/**
 * Filter that hooks into access manager and modifies the permissions
 *
 * @param boolean $access_allowed
 */
add_filter( 'tva_access_manager_allow_access', static function ( $access_allowed ) {

	/**
	 * For course homepage the access will be allowed if the overview protection setting is on in the Access Restriction tab
	 */
	if ( is_tax( TVA_Const::COURSE_TAXONOMY ) && empty( get_term_meta( get_queried_object_id(), 'tva_protect_overview', true ) ) ) {
		$access_allowed = true;
	}

	/**
	 * For system pages, always allow access
	 */
	if ( get_queried_object() instanceof WP_Post && tva_get_settings_manager()->is_system_page( get_queried_object() ) ) {
		$access_allowed = true;
	}

	return $access_allowed;
} );

/**
 * Triggered from dashboard main front request
 *
 * Additional apprentice logic can be inserted here
 *
 * @param array $current what should return. By default it will return an empty array
 * @param array $post_data
 */
add_filter( 'tve_dash_main_ajax_ta_access_post', static function ( $current = array(), $post_data = array() ) {
	if ( is_user_logged_in() && ! empty( $post_data['post_id'] ) && is_numeric( $post_data['post_id'] ) ) {
		$factory_post = TVA_Post::factory( get_post( $post_data['post_id'] ) );
		$customer     = tva_customer();

		if ( tva_access_manager()->has_access_to_object( $factory_post->get_the_post() ) && ( $factory_post instanceof TVA_Lesson || $factory_post instanceof TVA_Module ) ) {
			$course  = $factory_post->get_course_v2();
			$product = $course->get_product();

			if ( ! $customer->get_begin_course_timestamp( $course->get_id() ) ) {
				$customer->set_begin_course_timestamp( $course->get_id() );
			}

			if ( $product instanceof Product ) {
				$campaign = $product->get_drip_campaign_for_course( $course );

				if ( $campaign instanceof Campaign ) {
					$time_of_first_access = $customer->get_begin_course_timestamp( $course->get_id() );
					$post_ids             = $campaign->get_posts_with_trigger( Time_After_First_Lesson::NAME );

					if ( $campaign->trigger === Time_After_First_Lesson::NAME ) {

						$master_trigger_meta_key = Time_After_First_Lesson::get_user_meta_key( $campaign->ID, $campaign->ID );

						if ( empty( get_user_meta( $customer->get_id(), $master_trigger_meta_key ) ) ) {
							update_user_meta( $customer->get_id(), $master_trigger_meta_key, 1 );
						}
					}

					foreach ( $post_ids as $post_id ) {
						$trigger = $campaign->get_trigger_for_post( $post_id, Time_After_First_Lesson::NAME );
						if ( $trigger instanceof TVA\Drip\Trigger\Base ) {
							$trigger->schedule_event( $product->get_id(), $post_id, $customer->get_id(), $time_of_first_access );
						}
					}
				}

			}
		}
	}

	return $current;
}, 10, 2 );


/**
 * On delete and update user role for user profiles we need to invalidate the users with access cache on product level
 */
add_action( 'delete_user', static function () {
	TVA_Course_V2::delete_count_enrolled_users_cache( 0 );
	Product::delete_count_users_with_access_cache( 0 );
} );

add_action( 'set_user_role', static function () {
	TVA_Course_V2::delete_count_enrolled_users_cache( 0 );
	Product::delete_count_users_with_access_cache( 0 );
} );

/**
 * Add Courses page as a Dynamic Link in the 'Site' category
 *
 * @param array $global_data
 */
add_filter( 'tvd_global_data', static function ( $global_data ) {

	$global_data[] = array(
		'name' => __( 'Courses page', 'thrive-apprentice' ),
		'url'  => tva_get_settings_manager()->factory( 'index_page' )->get_link(),
		'show' => true,
	);

	return $global_data;
} );

/**
 * Show accurate notices for Thrive Automator
 */
add_filter( 'tap_notice_check_plugin', function ( $is_plugin_active, $missing_plugin, $missing_key ) {
	if ( $missing_plugin === 'Thrive Apprentice' ) {
		$is_plugin_active = true;
	}

	return $is_plugin_active;
}, 10, 3 );

/**
 * For course pages change the max post per page limit so that the posts inside it won't be paginated
 *
 * @var WP_Query $query
 */
add_action( 'pre_get_posts', static function ( $query ) {
	/* We only need to increase the posts_per_page on an Apprentice Course, not on all Post Lists from Course Page */
	if ( tva_is_course_page() && isset( $query->query['tva_courses'] ) ) {
		$query->set( 'posts_per_page', 2000 );
	}
}, 10, 1 );

/**
 * Dont display metrics ribbon if we don't have any license
 */
add_filter( 'tve_dash_metrics_should_enqueue', static function ( $should_enqueue ) {
	$screen = tve_get_current_screen_key();
	if ( $screen === 'thrive-dashboard_page_thrive_apprentice' && ! tva_license_activated() ) {
		$should_enqueue = false;
	}

	return $should_enqueue;
}, 10, 1 );


/**
 * Hooks into save post functionality and deletes course cache once a post corresponding to a course is inserted / updated
 *
 * @param int     $post_ID Post ID.
 * @param WP_Post $post    Post object.
 * @param bool    $update  Whether this is an existing post being updated.
 */
add_action( 'save_post', static function ( $post_ID, $post, $update ) {
	if ( in_array( $post->post_type, [ TVA_Const::LESSON_POST_TYPE, TVA_Const::ASSESSMENT_POST_TYPE, TVA_Const::CHAPTER_POST_TYPE, TVA_Const::MODULE_POST_TYPE ] ) ) {
		$course = TVA_Post::factory( $post )->get_course_v2();
		if ( $course instanceof TVA_Course_V2 ) {
			delete_term_meta( $course->get_id(), 'tva_lessons_count' );
			delete_term_meta( $course->get_id(), 'tva_assessments_count' );
			delete_term_meta( $course->get_id(), 'tva_items_count' );
		}
	}
}, 10, 3 );


/**
 * Hooks into delete post functionality and deletes course cache once a post corresponding to a course
 *
 * @param int     $post_ID Post ID.
 * @param WP_Post $post    Post object.
 */
add_action( 'before_delete_post', static function ( $post_id, $post ) {
	if ( in_array( $post->post_type, [ TVA_Const::LESSON_POST_TYPE, TVA_Const::ASSESSMENT_POST_TYPE, TVA_Const::CHAPTER_POST_TYPE, TVA_Const::MODULE_POST_TYPE ] ) ) {
		$course = TVA_Post::factory( $post )->get_course_v2();
		if ( $course instanceof TVA_Course_V2 ) {
			delete_term_meta( $course->get_id(), 'tva_lessons_count' );
			delete_term_meta( $course->get_id(), 'tva_assessments_count' );
			delete_term_meta( $course->get_id(), 'tva_items_count' );
		}
	}
}, 10, 2 );

add_filter( 'tva_can_be_marked_as_completed', static function ( $allowed = true, $config = array(), $post_id = 0 ) {
	if ( $allowed && is_array( $config ) && true === $config['video_progress'] ) {
		$reporting = TVE\Reporting\Logs::get_instance();
		$lesson    = new TVA_Lesson( $post_id );
		$video_id  = Video::get_post_id_by_video_url( $lesson->get_video()->source );

		$results = $reporting->set_query( [
			'event_type' => Video_Completed::key(),
			'filters'    => [
				'item_id' => $video_id,
				'post_id' => $post_id,
				'user_id' => get_current_user_id(),
			],
		] )->get_results();

		return ! empty( $results );
	}

	return true;
}, 10, 3 );
