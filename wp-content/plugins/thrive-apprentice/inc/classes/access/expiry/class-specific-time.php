<?php

namespace TVA\Access\Expiry;

use TVA\Drip\Schedule\Utils;

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Specific time for expiry
 */
class Specific_Time extends Base {

	use Utils;

	/**
	 * Condition type
	 */
	const CONDITION = 'specific_time';

	/**
	 * Event Name
	 */
	const EVENT = 'tva_product_expiry_specific_time';

	/**
	 * Reminder event name
	 */
	const EVENT_REMINDER = 'tva_product_expiry_specific_time_reminder';

	/**
	 * @param string $condition
	 *
	 * @return void
	 */
	public function add( $condition ) {
		update_term_meta( $this->product->get_id(), static::PRODUCT_EXPIRY_META_KEY, $condition );

		$schedule_datetime = static::get_datetime( $condition );
		$this->schedule_event( $schedule_datetime );

		if ( $this->product->has_access_expiry_reminder() ) {
			$reminder = $this->product->get_access_expiry()['reminder'];

			$this->add_reminder( $schedule_datetime, $reminder );
		}
	}

	/**
	 * @param string $condition
	 *
	 * @return void
	 */
	public function remove( $condition ) {
		$schedule_datetime = static::get_datetime( $condition );

		$this->unschedule_event( $schedule_datetime );

		$reminder = $this->product->get_access_expiry()['reminder'];
		//We remove the reminder in bulk
		$this->remove_reminder( $schedule_datetime, $reminder );

		delete_term_meta( $this->product->get_id(), static::PRODUCT_EXPIRY_META_KEY );
	}

	/**
	 * Triggered only when reminder has been modified
	 *
	 * @param $expiry
	 * @param $old_reminder
	 * @param $new_reminder
	 *
	 * @return void
	 */
	public function reminder_modified( $expiry, $old_reminder, $new_reminder ) {
		$this->remove_reminder( $expiry, $old_reminder );

		$this->add_reminder( $expiry, $new_reminder );
	}
}
