<?php

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

namespace TVA\Drip;

use TVA\Drip\Schedule\Non_Repeating;
use TVA\Drip\Schedule\Repeating;
use TVA\Drip\Schedule\Specific;
use TVA\Drip\Schedule\Utils;
use TVA\Drip\Trigger\Specific_Date_Time_Interval;
use TVA\Drip\Trigger\Time_After_First_Lesson;
use TVA\Drip\Trigger\Time_After_Purchase;
use TVA\Product;
use TVD\Content_Sets\Set;

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

add_filter( 'tva_admin_localize', static function ( $params ) {

	$params['drip'] = [
		'campaign_types'    => Drip::get_campaign_types(),
		'content_triggers'  => Drip::get_content_triggers(),
		'trigger_views'     => [], //Used in frontend to attach views
		'schedule_defaults' => [
			'specific'      => Specific::get_defaults(),
			'non_repeating' => Non_Repeating::get_defaults(),
			'repeating'     => Repeating::get_defaults(),
		],
		'weekdays'          => Repeating::get_weekdays( false, true ),
	];

	return $params;
} );

add_action( 'init', array( '\TVA\Drip\Campaign', 'register_post_type' ) );

/**
 * Registers callback for datetime schedule CRON
 *
 * Called when the cron timestamps expires or when the user calls it manually via a cron manager plugin
 */
add_action( Specific_Date_Time_Interval::EVENT, '\TVA\Drip\datetime_schedule_callback', 10, 3 );
/**
 * Register callback for First Access schedule CRON
 *
 * Called when the cron timestamps expires or when the user calls it manually via a cron manager plugin
 */
add_action( Time_After_First_Lesson::EVENT, '\TVA\Drip\start_course_schedule_callback', 10, 4 );

/**
 * Register callback for Purchase Product schedule CRON
 *
 * Called when the cron timestamps expires or when the user calls it manually via a cron manager plugin
 */
add_action( Time_After_Purchase::EVENT, '\TVA\Drip\purchase_schedule_callback', 10, 4 );


/**
 * Hooks into access manager has access function and modifies the access for drip
 *
 * @param boolean $access_allowed
 */
add_filter( 'tva_access_manager_allow_access', static function ( $access_allowed ) {

	if ( is_tax( \TVA_Const::COURSE_TAXONOMY ) ) {
		/**
		 * For course overview page, we need to skip the campaign logic
		 */
		return $access_allowed;
	}

	if ( is_user_logged_in() && ! empty( tva_access_manager()->get_product() ) && ! empty( tva_course()->get_id() ) && tva_access_manager()->has_access_to_object( get_queried_object() ) ) { //For now we support drip only for courses
		/**
		 * If there is a campaign for the course we override the $access_allowed value with the value from the campaign logic
		 */
		$access_allowed = ! tva_access_manager()->is_object_locked( get_queried_object() );
	}

	return $access_allowed;
}, 9 );

/**
 * Before a content set is updated, record all the courses from the content set
 *
 * @param Set $set
 */
add_action( 'tvd_content_set_before_update', static function ( $set ) {
	/** @var Set $set */
	global $tvd_content_set_courses;
	if ( empty( $tvd_content_set_courses ) ) {
		$tvd_content_set_courses = [];
	}
	if ( ! isset( $tvd_content_set_courses[ $set->ID ] ) ) {
		$tvd_content_set_courses[ $set->ID ] = $set->get_tva_courses_ids();
	}
} );


/**
 * After a content set is updated, re-schedule all drip campaigns that have been added / removed to a course that belongs to the set
 *
 * @param Set $set
 */
add_action( 'tvd_content_set_after_update', static function ( $set ) {
	/** @var Set $set */
	global $tvd_content_set_courses;
	$old_course_ids = empty( $tvd_content_set_courses[ $set->ID ] ) ? [] : $tvd_content_set_courses[ $set->ID ];
	$new_course_ids = $set->get_tva_courses_ids();

	$added_courses   = array_diff( $new_course_ids, $old_course_ids );
	$removed_courses = array_diff( $old_course_ids, $new_course_ids );

	foreach ( Product::get_from_set( [ $set->ID ], array( 'return_all' => true ) ) as $product ) {
		foreach ( $removed_courses as $course_id ) {
			$product->assign_drip_campaign( null, $course_id, false ); // remove the campaign associated with course_id
		}

		/**
		 * When a content set has been modified, we need to update the access log to mark users
		 * that receive access to courses and also users that lost access to courses
		 */
		\TVA\Access\Main::bulk_update_courses_access( $product, $added_courses, $removed_courses );
	}

	$modified_ids = array_merge( $added_courses, $removed_courses );
	foreach ( Campaign::get_items_for_course( $modified_ids ) as $campaign ) {
		$campaign->reschedule_events();
	}
} );

/**
 * Before deleting a content set, make sure all drip campaigns associated with any courses from the content set are rescheduled
 */
add_action( 'tvd_content_set_before_delete', static function ( $set ) {
	/** @var Set $set */
	$course_ids = $set->get_tva_courses_ids();

	foreach ( Product::get_from_set( [ $set->ID ], true ) as $product ) {
		foreach ( $course_ids as $course_id ) {
			$product->assign_drip_campaign( null, $course_id, false ); // remove the campaign associated with course_id
		}

		/**
		 * When a content set has been deleted, we need to update the access log to mark users
		 * that have access as access lost
		 */
		\TVA\Access\Main::bulk_update_courses_access( $product, [], $course_ids );
	}

	foreach ( Campaign::get_items_for_course( $course_ids ) as $campaign ) {
		$campaign->reschedule_events();
	}
} );

/**
 * Admin logic for displaying new weekly logic admin notice
 */
add_action( 'admin_notices', static function () {
	if ( ! empty( get_option( 'tva_new_weekly_logic_hide_notice' ) ) ) {
		return;
	}

	$items_without_new_weekly_logic = Campaign::get_items_without_weekly_logic();

	if ( count( $items_without_new_weekly_logic ) > 0 ) {

		$campaign_route = tva_get_route_url( 'campaigns' );
		$nonce          = wp_create_nonce( 'wp_rest' );

		$message = '<strong>' . esc_html__( 'Heads up, there is an important change to your Thrive Apprentice Drip campaigns', 'thrive-apprentice' ) . '</strong>';
		$message .= '<p>' . esc_html__( 'We have updated the way that systematically unlocked drip campaigns are calculated and this may affect your campaigns.', 'thrive-apprentice' ) . ' <a target="_blank" rel="noopener" href="https://help.thrivethemes.com/en/articles/8804157-understanding-the-new-drip-behavior-in-thrive-apprentice">' . esc_html__( 'Read this article', 'thrive-apprentice' ) . '</a> ' . esc_html__( 'to understand how this change is likely to affect you and your customers. You will have until April 2024 to make any changes needed.', 'thrive-apprentice' ) . '</p>';

		$message .= '<a href="javascript:void(0);" id="tva-drip-weekly-admin-notice-button-one" class="button button-primary" style="margin-right: 10px;">' . esc_html__( 'Apply to all campaigns', 'thrive-apprentice' ) . '</a>';
		$message .= '<a href="javascript:void(0);" id="tva-drip-weekly-admin-notice-button-two" class="button button-secondary">' . esc_html__( 'Keep current behaviour for now', 'thrive-apprentice' ) . '</a>';
		$message .= "<script>
 jQuery('#tva-drip-weekly-admin-notice-button-one').on('click', function () {	
	 	jQuery.ajax( {
			headers: {
				'X-WP-Nonce': '$nonce'
			},
			type: 'POST',
			url: '$campaign_route/new_weekly_logic'
		} );
	 	jQuery('#tva-drip-weekly-admin-notice').remove();
 });
  jQuery('#tva-drip-weekly-admin-notice-button-two').on('click', function () {
	 	jQuery.ajax( {
			headers: {
				'X-WP-Nonce': '$nonce'
			},
			type: 'POST',
			url: '$campaign_route/new_weekly_logic_hide_notice'
		} );
		jQuery('#tva-drip-weekly-admin-notice').remove();
 });
</script>";
		printf( '<div id="tva-drip-weekly-admin-notice" class="%1$s"><p>%2$s</p></div>', 'notice notice-error td_nm_wordpress_notice', strip_tags( $message, '<a><strong><span><p><script>' ) );
	}
} );
