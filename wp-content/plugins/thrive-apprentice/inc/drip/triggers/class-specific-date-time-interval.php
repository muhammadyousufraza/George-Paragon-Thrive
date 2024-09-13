<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

namespace TVA\Drip\Trigger;

/**
 * Class Specific_Date_Time_Interval
 *
 * @package TVA\Drip\Trigger
 * @project : thrive-apprentice
 */
class Specific_Date_Time_Interval extends Base {

	/**
	 * Event Name
	 */
	const EVENT = 'tva_campaign_datetime_schedule';

	/**
	 * Trigger Name
	 */
	const NAME = 'datetime';

	/**
	 * @var string can be after or before
	 */
	protected $occurrence = '';

	/**
	 * @var \TVA\Drip\Schedule\Specific
	 */
	protected $schedule = null;

	/**
	 * Returns true if the trigger is valid
	 *
	 * if the occurrence is before we need to check if the next occurrence is not NULL - This is usefull for campaigns that start at the moment when it's published and closes at a specific interval
	 * if the occurence is after we need to check if next occurrence is equal to NULL - This is usefull for campaigns that start at a specific interval and never closes
	 *
	 * @param int $product_id
	 * @param int $post_id
	 *
	 * @return boolean
	 */
	public function is_valid( $product_id, $post_id ) {
		return ( $this->occurrence === 'before' && $this->schedule->get_next_occurrence() !== null ) || ( $this->occurrence === 'after' && $this->schedule->get_next_occurrence() === null );
	}

	/**
	 * Prepare the parameters for scheduling an event
	 *
	 * @param int                            $product_id
	 * @param int                            $post_id
	 * @param int                            $customer_id
	 * @param \DateTimeInterface|string|null $from_date date when to start the calculation. Defaults to the current date / time
	 */
	public function schedule_event( $product_id, $post_id, $customer_id = null, $from_date = null ) {
		if ( $this->occurrence === 'after' && $this->schedule->get_next_occurrence() !== null ) {
			parent::schedule_event( $product_id, $post_id, $customer_id, $from_date );
		}
	}
}
