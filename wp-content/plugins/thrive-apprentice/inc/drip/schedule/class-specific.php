<?php

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

namespace TVA\Drip\Schedule;

class Specific extends Base {
	/**
	 * A date and time string representation in the format yyyy-mm-dd hh:mm ( php date format: Y-m-d H:i )
	 *
	 * @var string
	 */
	protected $datetime = '';

	/**
	 * Default value for date: today + 7 days
	 */
	protected function init_defaults() {
		$this->datetime = current_datetime()->modify( '+7 days' )->format( 'Y-m-d 12:00' );
	}

	/**
	 * Get the next occurrence after $from_date.
	 *
	 * @param \DateTimeInterface|string|null $from_date if null, it will default to WordPress's current time
	 *
	 * @return \DateTimeInterface|null null if no event occurs after $from_date
	 */
	public function get_next_occurrence( $from_date = null ) {
		$from_date  = static::get_datetime( $from_date );
		$event_date = static::get_datetime( $this->datetime . ':00' );

		if ( static::is_past( $event_date, $from_date ) ) {
			return null;
		}

		return $event_date;
	}
}
