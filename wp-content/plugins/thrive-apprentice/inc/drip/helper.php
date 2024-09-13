<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

namespace TVA\Drip;

use TVA\Drip\Trigger\Specific_Date_Time_Interval;
use TVA\Drip\Trigger\Time_After_First_Lesson;
use TVA\Drip\Trigger\Time_After_Purchase;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Callback for datetime schedule CRON that is triggered when the time passes for a datetime drip trigger
 *
 * @param int $product_id
 * @param int $campaign_id
 * @param int $post_id
 */
function datetime_schedule_callback( $product_id, $campaign_id, $post_id ) {
	$campaign = new Campaign( (int) $campaign_id );

	if ( $campaign->cron_allow_execute( (int) $product_id, $post_id ) ) {

		$campaign->set_customer( false );

		$trigger = $campaign->get_trigger_for_post( $post_id, Specific_Date_Time_Interval::NAME );

		if ( $trigger ) {
			$campaign->cron_check_post_unlocked( $product_id, $post_id );
		}
	}
}

/**
 * Callback for start course schedule user-based CRON
 *
 * @param int $product_id
 * @param int $campaign_id
 * @param int $post_id
 * @param int $customer_id
 */
function start_course_schedule_callback( $product_id, $campaign_id, $post_id, $customer_id ) {
	$campaign = new Campaign( (int) $campaign_id );

	if ( $campaign->cron_allow_execute( (int) $product_id, $post_id ) && get_userdata( $customer_id ) !== false ) {

		$campaign->set_customer( new \TVA_Customer( $customer_id ) );

		$trigger = $campaign->get_trigger_for_post( $post_id, Time_After_First_Lesson::NAME );

		if ( $trigger && ! $campaign->is_user_drip_complete( $post_id ) ) {
			$trigger->mark_user_completed( $post_id );

			$campaign->cron_check_post_unlocked( $product_id, $post_id, $customer_id );
		}
	}
}

/**
 * Callback for purchase schedule user-based CRON
 *
 * @param int $product_id
 * @param int $campaign_id
 * @param int $post_id
 * @param int $customer_id
 */
function purchase_schedule_callback( $product_id, $campaign_id, $post_id, $customer_id ) {
	$campaign = new Campaign( (int) $campaign_id );

	if ( $campaign->cron_allow_execute( (int) $product_id, $post_id ) && get_userdata( $customer_id ) !== false ) {

		$campaign->set_customer( new \TVA_Customer( $customer_id ) );

		$trigger = $campaign->get_trigger_for_post( $post_id, Time_After_Purchase::NAME );

		if ( $trigger && ! $campaign->is_user_drip_complete( $post_id ) ) {
			$trigger->mark_user_completed( $post_id );

			$campaign->cron_check_post_unlocked( $product_id, $post_id, $customer_id );
		}
	}
}

/**
 * Callback from external plugin (Automator)
 * Sets a particular content as unlocked for a specific user
 *
 * @param int $post_id
 * @param int $user_id
 */
function unlock_content_for_specific_user( $post_id, $user_id ) {
	$post = get_post( $post_id );
	/* make sure that this does not get called with invalid parameters */
	if ( $post ) {
		$customer = new \TVA_Customer( $user_id );
		$customer->set_drip_content_unlocked( $post_id );
		$product = \TVA\Product::get_from_set( \TVD\Content_Sets\Set::get_for_object( $post, $post_id ), array(), $post ) ;

		/**
		 * Triggered when content is unlocked for a specific user
		 *
		 * @param \WP_User $user    User object for which content is unlocked
		 * @param \WP_Post $post    The post object that is unlocked
		 * @param \WP_Term $product The product term that the campaign belongs to
		 */
		do_action( 'tva_drip_content_unlocked_for_specific_user', $customer->get_user(), $post, $product );
	}
}

/**
 * Callback from external plugin (Automator)
 * Sets a particular content as unlocked for everyone
 *
 * @param int $post_id
 */
function unlock_content_for_everyone( $post_id ) {
	update_post_meta( $post_id, 'tva_drip_content_unlocked_for_everyone', 1 );
}
