<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

namespace TVA\Drip\Trigger;

/**
 * Class Time_After_First_Lesson
 *
 * @package TVA\Drip\Trigger
 */
class Time_After_First_Lesson extends Base {

	/**
	 * Trigger Name
	 */
	const NAME = 'first-lesson';

	/**
	 * Event name
	 */
	const EVENT = 'tva_campaign_start_course_schedule';

	/**
	 * User Meta Key
	 */
	const USER_META_KEY = 'tva_first_access_schedule_post_%s_campaign_%s_completed';

	/**
	 * @var null|\TVA\Drip\Schedule\Non_Repeating
	 */
	protected $schedule = null;

	/**
	 * @param int $product_id
	 * @param int $post_id
	 *
	 * @return boolean
	 */
	public function is_valid( $product_id, $post_id ) {
		$timestamp = $this->get_customer()->get_begin_course_timestamp( $this->get_course()->get_id() );

		if ( empty( $timestamp ) ) {
			return false;
		}

		return $this->schedule->get_next_occurrence( $timestamp ) < current_datetime();
	}

	/**
	 * Get the DateTime when user purchased the product
	 *
	 * @param array $args
	 *
	 * @return \DateTime|\DateTimeImmutable|null
	 */
	protected function _compute_original_event_date( $args ) {

		$customer = new \TVA_Customer( $args['user_id'] );

		return $customer->get_begin_course_timestamp( $args['course_id'] ) ?: null;
	}
}
